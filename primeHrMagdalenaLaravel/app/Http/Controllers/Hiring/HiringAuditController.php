<?php

namespace App\Http\Controllers\Hiring;

use App\Models\AuditLog;
use App\Services\Recruitment\RecruitmentPresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The recruitment audit trail, read-only.
 *
 * Every recruitment action is recorded here against the officer who took it,
 * with a snapshot of their name. This is the security record for the module —
 * the same table the reference called `audit_log`, now written locally rather
 * than by a separate service that could only credit its own service account.
 *
 * Restricted to administrators: it is oversight of who did what, not screening
 * work. HR staff do not need it to shortlist anybody.
 */
class HiringAuditController extends HiringController
{
    /** The reference capped this response at 500 rows. */
    private const LIMIT = 500;

    public function index(Request $request): View
    {
        $this->requireManager($request);

        $all = AuditLog::query()
            ->orderByDesc('at')
            ->limit(self::LIMIT)
            ->get();

        // Both filters are applied in memory so the pickers can list every
        // action that actually appears in the log, not only the selected one.
        $actions = $all->pluck('action')->filter()->unique()->sort()->values()->all();
        $entities = $all->pluck('entity')->filter()->unique()->sort()->values()->all();

        $action = trim((string) $request->query('action', ''));
        $entity = trim((string) $request->query('entity', ''));

        $rows = $all;

        if ($action !== '') {
            $rows = $rows->where('action', $action);
        }

        if ($entity !== '') {
            $rows = $rows->where('entity', $entity);
        }

        return view('admin.recruitment.audit', [
            'entries' => $rows->values()->map(static fn (AuditLog $e) => RecruitmentPresenter::audit($e))->all(),
            'actions' => $actions,
            'entities' => $entities,
            'action' => $action,
            'entity' => $entity,
            'total' => $all->count(),
            'failure' => null,
        ]);
    }
}
