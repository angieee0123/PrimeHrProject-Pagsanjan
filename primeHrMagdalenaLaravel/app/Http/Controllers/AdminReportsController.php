<?php

namespace App\Http\Controllers;

use App\Services\AdminReportService;
use Illuminate\Http\Request;

/**
 * Admin Reports — every panel is driven from live data.
 *
 * The figures themselves are built by `AdminReportService`, which
 * `AdminReportsExportController` also reads: the file a tab hands out and the
 * table it was clicked from are the same computation, so they cannot disagree.
 */
class AdminReportsController extends Controller
{
    public function index(Request $request, AdminReportService $reports)
    {
        $period = $reports->resolvePeriod($request);

        return view('admin.reports.adminReports', [
            'reports'     => $reports->all($period),
            'activeTab'   => $period['tab'],
            'year'        => $period['year'],
            'month'       => $period['month'],
            'semi'        => $period['semi'],
            'periodLabel' => $period['label'],
            'years'       => $period['years'],
        ]);
    }
}
