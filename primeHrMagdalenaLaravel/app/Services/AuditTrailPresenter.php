<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

/**
 * Turns an `audits` row into the handful of short strings the Audit Trail
 * page actually shows.
 *
 * The page used to render the raw row: the full URL, the raw IP, the whole
 * user-agent string, and one line per changed field — so a single update
 * touching twenty columns produced a twenty-line table row, and the height of
 * every row depended on the record behind it. That is what made the table
 * unreadable once there was more than a page of history in it.
 *
 * So the split here is deliberate: `row()` produces a **fixed-size** summary
 * for the table (one line of changed field names, a count, a short device
 * label) and carries the full detail alongside it for the drawer. Nothing is
 * dropped — the URL, IP, agent and complete before/after diff all travel in
 * the same array and are shown when a row is opened.
 *
 * Formatting lives here rather than in the Blade so the table and the detail
 * modal cannot disagree about what a value looks like: both read one array.
 */
class AuditTrailPresenter
{
    /**
     * The events `owen-it/laravel-auditing` writes, and how each one reads.
     *
     * Note `updated` is **warning**, not danger. The page previously gave it
     * `on-hold`, which statusBadge.css maps to the danger palette — so every
     * ordinary edit rendered in the same red as a deletion and the colour
     * stopped carrying any information.
     */
    public const EVENTS = [
        'created'  => ['label' => 'Created',  'badge' => 'is-success'],
        'updated'  => ['label' => 'Updated',  'badge' => 'is-warning'],
        'deleted'  => ['label' => 'Deleted',  'badge' => 'is-danger'],
        'restored' => ['label' => 'Restored', 'badge' => 'is-info'],
    ];

    /**
     * Keys whose value is never displayed. Nothing on the auditable models
     * currently writes one, but an audit row is a verbatim copy of whatever
     * a model held — so the guard belongs at the point of display, not in a
     * promise about which columns exist today.
     */
    private const REDACTED = [
        'password', 'password_confirmation', 'remember_token',
        'api_token', 'token', 'secret', 'access_token',
    ];

    /** A single value is truncated for display; the table never shows values at all. */
    private const VALUE_LIMIT = 200;

    /** How many field names fit on the table's one-line change summary. */
    private const SUMMARY_FIELDS = 3;

    /**
     * Field names that do not survive `snake_case → Title Case` intact.
     * Everything else is derived, so this list stays short by design.
     */
    private const LABELS = [
        'ip_address'       => 'IP address',
        'user_agent'       => 'Browser',
        'url'              => 'URL',
        'saln_submitted'   => 'SALN submitted',
        'gsis_number'      => 'GSIS number',
        'philhealth_number' => 'PhilHealth number',
        'pagibig_number'   => 'Pag-IBIG number',
        'tin_number'       => 'TIN',
        'sss_number'       => 'SSS number',
        'dtr_remarks'      => 'DTR remarks',
        'qr_code'          => 'QR code',
    ];

    /**
     * One table row plus everything its detail drawer needs.
     */
    public function row(Audit $audit, ?User $user): array
    {
        $changes  = $this->changes($audit);
        $event    = strtolower((string) $audit->event);
        $meta     = self::EVENTS[$event] ?? ['label' => Str::title($event ?: 'Unknown'), 'badge' => 'is-neutral'];
        $name     = $user?->username ?? 'System';
        $employee = $user?->employee;

        return [
            'id'            => $audit->id,
            'event'         => $event,
            'event_label'   => $meta['label'],
            'badge'         => $meta['badge'],

            'record_type'   => class_basename((string) $audit->auditable_type) ?: 'Record',
            'record_id'     => $audit->auditable_id,

            'user_name'     => $name,
            'user_photo'    => $this->photo($employee),
            'user_initials' => $this->initials($employee, $name),

            'changes'       => $changes,
            'change_count'  => count($changes),
            'summary'       => $this->summary($changes, $event),

            'url'           => (string) $audit->url,
            'path'          => $this->path((string) $audit->url),
            'ip'            => (string) ($audit->ip_address ?: '—'),
            'user_agent'    => (string) ($audit->user_agent ?: '—'),
            'device'        => $this->device((string) $audit->user_agent),

            'date'          => $this->day($audit->created_at),
            'time'          => $audit->created_at?->format('h:i A') ?? '',
            'relative'      => $audit->created_at?->diffForHumans() ?? '',
            'full'          => $audit->created_at?->format('l, F j, Y \a\t g:i:s A') ?? '',
        ];
    }

    /**
     * The before/after pairs for one audit.
     *
     * `created` has no "before" and `deleted` has no "after"; `updated`
     * carries only the touched keys in both arrays, but the union is taken
     * rather than either side alone — a key present on one side only is still
     * a change and would otherwise vanish from the diff.
     */
    public function changes(Audit $audit): array
    {
        $old = is_array($audit->old_values) ? $audit->old_values : [];
        $new = is_array($audit->new_values) ? $audit->new_values : [];

        $rows = [];

        foreach (array_keys($old + $new) as $key) {
            $before = $old[$key] ?? null;
            $after  = $new[$key] ?? null;

            // An "update" that did not move the value is noise: it inflates
            // the change count the table shows and pads the diff in the drawer.
            if (array_key_exists($key, $old) && array_key_exists($key, $new) && $before === $after) {
                continue;
            }

            $rows[] = [
                'key'   => $key,
                'field' => $this->label($key),
                'old'   => array_key_exists($key, $old) ? $this->value($key, $before) : null,
                'new'   => array_key_exists($key, $new) ? $this->value($key, $after) : null,
            ];
        }

        return $rows;
    }

    /** `department_id` → `Department`, `first_name` → `First name`. */
    public function label(string $key): string
    {
        if (isset(self::LABELS[$key])) {
            return self::LABELS[$key];
        }

        // A foreign key is named for what it points at, not for the column.
        $key = Str::endsWith($key, '_id') ? Str::beforeLast($key, '_id') : $key;

        return Str::ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * The one line the table's Changes column shows. Field *names* only —
     * values are what make a row unbounded in height, and they are one click
     * away in the drawer.
     */
    private function summary(array $changes, string $event): string
    {
        if ($changes === []) {
            return $event === 'deleted' ? 'Record removed' : 'No field values recorded';
        }

        $names = array_column($changes, 'field');
        $shown = array_slice($names, 0, self::SUMMARY_FIELDS);
        $rest  = count($names) - count($shown);

        return implode(', ', $shown) . ($rest > 0 ? " +{$rest} more" : '');
    }

    /** Display form of a single stored value. */
    private function value(string $key, mixed $value): string
    {
        if (in_array(strtolower($key), self::REDACTED, true)) {
            return '••••••••';
        }

        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return Str::limit(trim((string) $value), self::VALUE_LIMIT);
    }

    /** `https://host/admin/personnel/8?x=1` → `/admin/personnel/8` */
    private function path(string $url): string
    {
        if ($url === '') {
            return '—';
        }

        // Audits written from a console command store the command name, not a
        // URL — and `parse_url` would read `leave:accrue` off `artisan:leave:
        // accrue` as though `artisan` were a scheme. Only shorten what is
        // actually a web address.
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return Str::limit($url, 60);
        }

        return parse_url($url, PHP_URL_PATH) ?: Str::limit($url, 60);
    }

    /**
     * A user agent is ~150 characters of version numbers. What an auditor
     * reads off it is "which browser, which machine" — the raw string is
     * still kept and shown in the drawer for the cases where the rest matters.
     */
    private function device(string $agent): string
    {
        if (trim($agent) === '') {
            return 'Unknown device';
        }

        $browser = match (true) {
            str_contains($agent, 'Edg/')                             => 'Edge',
            str_contains($agent, 'OPR/') || str_contains($agent, 'Opera') => 'Opera',
            str_contains($agent, 'Chrome/')                          => 'Chrome',
            str_contains($agent, 'Firefox/')                         => 'Firefox',
            str_contains($agent, 'Safari/')                          => 'Safari',
            default                                                  => 'Browser',
        };

        $platform = match (true) {
            str_contains($agent, 'Windows')              => 'Windows',
            str_contains($agent, 'Android')              => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS X')             => 'macOS',
            str_contains($agent, 'Linux')                => 'Linux',
            default                                      => null,
        };

        return $platform ? "$browser on $platform" : $browser;
    }

    /**
     * The day, as the column shows it.
     *
     * Most of what anyone opens this page for happened today, and twenty-five
     * consecutive rows reading "Aug 17, 2026" carry no information at all —
     * the eye has to check each one to learn nothing. "Today" and "Yesterday"
     * are the two days worth naming; every other row keeps its exact date, and
     * the full timestamp is on the cell's tooltip and in the drawer either way,
     * so nothing about this costs precision.
     */
    private function day(?\Illuminate\Support\Carbon $at): string
    {
        return match (true) {
            $at === null      => '—',
            $at->isToday()    => 'Today',
            $at->isYesterday() => 'Yesterday',
            default           => $at->format('M d, Y'),
        };
    }

    /**
     * The avatar the "Performed by" column shows.
     *
     * `employees.photo` already holds a public URL (`/storage/employees/photos/x.png`),
     * so it is used as-is — the same value the sidebar, dashboard and personnel
     * tables render. A user with no linked employee row, or one whose employee
     * has no photo, returns null and the initials tile answers instead; the
     * views also fall back on a load error, because the column can outlive the
     * file it points at.
     */
    private function photo(?Employee $employee): ?string
    {
        $photo = trim((string) ($employee?->photo ?? ''));

        return $photo === '' ? null : $photo;
    }

    /**
     * Two letters for the avatar tile, used when there is no photo.
     *
     * The employee's name is preferred over the username because that is what
     * a photo would have shown: `jdelacruz` initialises to "JD" either way, but
     * `admin01` gives "AD" while the person behind it is "Juan Dela Cruz".
     */
    private function initials(?Employee $employee, string $name): string
    {
        $first = trim((string) ($employee?->first_name ?? ''));
        $last  = trim((string) ($employee?->last_name ?? ''));

        if ($first !== '' || $last !== '') {
            return Str::upper(Str::substr($first, 0, 1) . Str::substr($last, 0, 1));
        }

        $clean = trim($name);

        return $clean === '' ? '?' : Str::upper(Str::substr($clean, 0, 2));
    }
}
