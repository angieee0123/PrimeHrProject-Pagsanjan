<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditTrailPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

/**
 * Admin → Audit Trail.
 *
 * **Filtering, sorting and paging all happen here, in SQL.** They used to
 * happen in the browser, over the fifteen rows the server had already sent:
 * picking a user filtered *that page* and the footer then reported "3 total
 * records" for a table holding thousands, so the page was at its least
 * trustworthy exactly when there was enough history to need searching. The
 * two paginations also fought — a server page link and a client page button
 * both claimed to move you through the same list.
 *
 * Every filter is a query-string parameter, so a filtered view is a URL that
 * can be bookmarked, shared with a colleague, or reloaded after following a
 * link away.
 */
class AuditController extends Controller
{
    /** Anything outside this list falls back to the default; `sort` reaches ORDER BY. */
    private const SORTABLE = ['created_at', 'event', 'auditable_type', 'user_id'];

    private const PER_PAGE = [15, 25, 50, 100];

    public function index(Request $request, AuditTrailPresenter $presenter)
    {
        $filters = [
            'q'     => trim((string) $request->query('q', '')),
            'event' => (string) $request->query('event', ''),
            'user'  => (string) $request->query('user', ''),
            'type'  => (string) $request->query('type', ''),
            'from'  => (string) $request->query('from', ''),
            'to'    => (string) $request->query('to', ''),
        ];

        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : 15;

        $sort = in_array($request->query('sort'), self::SORTABLE, true)
            ? (string) $request->query('sort')
            : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $query = $this->filtered($filters);

        // Counted over the filtered set, not the whole table, so the chips
        // above the table always add up to what the table is showing.
        $counts = (clone $query)
            ->selectRaw('event, COUNT(*) as aggregate')
            ->groupBy('event')
            ->pluck('aggregate', 'event');

        $audits = (clone $query)
            ->orderBy($sort, $dir)
            // `created_at` has second resolution and a request can write
            // several audits inside one second; `id` gives those a stable order
            // so a row does not hop between pages on reload.
            ->orderBy('id', $dir)
            ->paginate($perPage)
            ->withQueryString();

        // One query for the whole page's users instead of a `User::find()` per
        // row inside the Blade loop — plus one for their employee records, which
        // is where the avatar photo and the person's real name live. Eager
        // loading it here is what keeps the "Performed by" avatar from costing a
        // query per row; `employee_id` has to be selected or the relation has no
        // key to match on.
        $users = User::whereIn('id', $audits->pluck('user_id')->filter()->unique())
            ->with('employee:id,first_name,last_name,photo')
            ->get(['id', 'username', 'employee_id'])
            ->keyBy('id');

        $rows = $audits->getCollection()
            ->map(fn (Audit $audit) => $presenter->row($audit, $users->get($audit->user_id)))
            ->values();

        return view('admin.audit.index', [
            'audits'       => $audits,
            'rows'         => $rows,
            'counts'       => $counts,
            'filters'      => $filters,
            'perPage'      => $perPage,
            'perPageSizes' => self::PER_PAGE,
            'sort'         => $sort,
            'dir'          => $dir,
            'auditUsers'   => $this->filterUsers(),
            'recordTypes'  => $this->filterRecordTypes(),
            'activeChips'  => $this->activeChips($filters, $request),
        ]);
    }

    /** The base query with every active filter applied. */
    private function filtered(array $filters)
    {
        return Audit::query()
            ->when($filters['event'] !== '', fn ($q) => $q->where('event', $filters['event']))
            ->when($filters['user'] !== '', fn ($q) => $q->where('user_id', $filters['user']))
            ->when($filters['type'] !== '', fn ($q) => $q->where('auditable_type', $filters['type']))
            ->when($filters['from'] !== '', fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['q']) . '%';

                // The username lives on `users`, not on the audit row, so it is
                // resolved to ids first rather than joined — the audit table has
                // no foreign key to join on (`user_id` is a morph).
                $userIds = User::where('username', 'like', $term)->pluck('id');

                $q->where(function ($inner) use ($term, $userIds) {
                    $inner->where('url', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhere('auditable_type', 'like', $term)
                        ->orWhere('old_values', 'like', $term)
                        ->orWhere('new_values', 'like', $term);

                    if ($userIds->isNotEmpty()) {
                        $inner->orWhereIn('user_id', $userIds);
                    }
                });
            });
    }

    /** Users who appear in the log at all — the only ones worth offering. */
    private function filterUsers()
    {
        $ids = Audit::whereNotNull('user_id')->distinct()->pluck('user_id');

        return User::whereIn('id', $ids)->orderBy('username')->get(['id', 'username']);
    }

    /**
     * Record types present in the log, as `[FQCN => 'Employee']`. Twenty-one
     * models are auditable, so without this the log is one undifferentiated
     * stream and "what changed on employees today" cannot be asked.
     */
    private function filterRecordTypes()
    {
        return Audit::distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->filter()
            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
            ->sort()
            ->all();
    }

    /**
     * The dismissible chips under the toolbar: what is currently narrowing the
     * list, and the URL that drops each one.
     *
     * An empty table with no visible reason is the failure mode this prevents
     * — a date filter set three visits ago reads as "there is no history".
     */
    private function activeChips(array $filters, Request $request): array
    {
        $labels = [
            'q'     => fn ($v) => 'Search: "' . Str::limit($v, 24) . '"',
            'event' => fn ($v) => 'Action: ' . Str::title($v),
            'user'  => fn ($v) => 'User: ' . (User::find($v)?->username ?? "#$v"),
            'type'  => fn ($v) => 'Record: ' . class_basename($v),
            'from'  => fn ($v) => 'From ' . $v,
            'to'    => fn ($v) => 'To ' . $v,
        ];

        $chips = [];

        foreach ($filters as $key => $value) {
            if ($value === '' || ! isset($labels[$key])) {
                continue;
            }

            $chips[] = [
                'label' => $labels[$key]($value),
                // Dropping one filter keeps the others, and resets to page 1 —
                // page 4 of the old result set rarely exists in the new one.
                'url'   => $request->fullUrlWithQuery([$key => null, 'page' => null]),
            ];
        }

        return $chips;
    }
}
