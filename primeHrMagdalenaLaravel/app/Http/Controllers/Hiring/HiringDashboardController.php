<?php

namespace App\Http\Controllers\Hiring;

use App\Services\Recruitment\HiringDashboardService;
use Illuminate\View\View;

/**
 * Recruitment overview: the figures, the positions accepting applications, and
 * the queue waiting for a decision.
 *
 * Every number is computed in `HiringDashboardService` from the same rows the
 * ranking and approval pages read, so this page cannot disagree with them. The
 * reference pulled the same figures from `GET /api/dashboard`; the data source
 * moved, the page did not.
 */
class HiringDashboardController extends HiringController
{
    public function __construct(private readonly HiringDashboardService $dashboard)
    {
    }

    public function index(): View
    {
        return view('admin.recruitment.index', [
            'dashboard' => $this->dashboard->dashboard(),
            'vacancies' => $this->dashboard->openVacancies(),
            'approvals' => $this->dashboard->pendingApprovals(),
            // There is no integration account any more, so there is no ceiling
            // to disclose. The view hides the block when this is null.
            'identity' => null,
            'failure' => null,
            'vacancyFailure' => null,
            'approvalFailure' => null,
        ]);
    }
}
