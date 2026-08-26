<?php

namespace App\Http\Controllers;

use App\Services\AdminReportService;
use App\Services\CsvReportWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * "Export CSV" on the Admin Reports page — one endpoint per tab.
 *
 * The page's only button was `onclick="window.print()"`, labelled
 * "Export / Print". Printing a browser page is not an export: the rendered
 * table carries percentage *bars* rather than numbers, status *chips* rather
 * than words, and nothing that says which municipality issued it or what
 * period it covers once it leaves the screen.
 *
 * One method per tab, the same rule the Leave & Benefits, Travel Order and
 * Pass Slip exports follow: a tab exports what that tab is about, and never
 * the neighbouring tab with columns hidden. A single shared endpoint would
 * have to print a Gross Pay column on the Headcount file and a Department
 * column on the Deductions one.
 *
 * Every figure comes from `AdminReportService` — the same object
 * `AdminReportsController` renders the page from — so the file and the cards
 * above the button cannot disagree. The endpoint re-runs the report
 * server-side against the period in the query string; it never scrapes the
 * table on screen.
 *
 * A note on the money columns, matching `PayrollExportController`: every peso
 * figure is written as a plain number (`12345.67`), never "₱12,345.67". The
 * column header carries the currency, so a spreadsheet can total the column
 * instead of reading it as text — the first thing anyone opening a payroll
 * report tries to do.
 */
class AdminReportsExportController extends Controller
{
    public function __construct(private readonly AdminReportService $reports)
    {
    }

    // ────────────────────────── Payroll Summary ──────────────────────────

    public function payroll(Request $request)
    {
        return $this->build($request, 'payroll', function (CsvReportWriter $csv, array $report, array $period) {
            $rows = $report['rows'];
            $totals = $report['totals'];

            $csv->columns([
                'No.', 'Employee ID', 'Employee Name', 'Department / Office',
                'Gross Pay (PHP)', 'Total Deductions (PHP)', 'Net Pay (PHP)',
                'Deduction Rate (%)', 'Payroll Status',
            ]);

            foreach ($rows as $i => $r) {
                $csv->row([
                    $i + 1,
                    $r['code'],
                    $r['name'],
                    $r['dept'],
                    $this->money($r['gross']),
                    $this->money($r['deductions']),
                    $this->money($r['net']),
                    $r['gross'] > 0 ? number_format($r['deductions'] / $r['gross'] * 100, 1) : '0.0',
                    $r['status'],
                ]);
            }

            if ($rows->isEmpty()) {
                $csv->emptyNotice($report['empty']);
            } else {
                $csv->row([
                    '', '', 'TOTAL (' . $rows->count() . ' ' . str('employee')->plural($rows->count()) . ')', '',
                    $this->money($totals['gross']),
                    $this->money($totals['deductions']),
                    $this->money($totals['net']),
                    $totals['gross'] > 0 ? number_format($totals['deductions'] / $totals['gross'] * 100, 1) : '0.0',
                    '',
                ]);
            }

            $csv->summary('Summary', [
                'Employees on Payroll:'     => $rows->count(),
                'Gross Payroll (PHP):'      => number_format((float) $totals['gross'], 2),
                'Total Deductions (PHP):'   => number_format((float) $totals['deductions'], 2),
                'Total Net Pay (PHP):'      => number_format((float) $totals['net'], 2),
                'Average Net Pay (PHP):'    => number_format($rows->count() ? (float) $totals['net'] / $rows->count() : 0, 2),
                'Approved / Paid:'          => $totals['settled'],
                'Draft / Pending:'          => max(0, $rows->count() - (int) $totals['settled']),
            ]);

            $csv->summary('Breakdown by Payroll Status', $this->countsBy($rows, fn ($r) => $r['status']));

            $csv->summary('Net Pay per Department / Office',
                $rows->groupBy('dept')
                    ->map(fn (Collection $g) => $g->sum('net'))
                    ->sortDesc()
                    ->mapWithKeys(fn ($net, $dept) => [$dept . ' (PHP):' => number_format((float) $net, 2)])
                    ->all()
            );

            $csv->notes([
                'One row per salary computation overlapping the pay period above — a computation spanning two periods appears in both.',
                'Total Deductions is gross pay less net pay: mandatory contributions, loan amortisations, and attendance deductions combined. The Deductions Report tab itemises them.',
                'Only Approved and Paid computations are final; Draft and Pending figures may still change.',
            ]);
        });
    }

    // ────────────────────────── Department Breakdown ──────────────────────────

    public function department(Request $request)
    {
        return $this->build($request, 'department', function (CsvReportWriter $csv, array $report, array $period) {
            $rows = $report['rows'];
            $totals = $report['totals'];

            $csv->columns([
                'No.', 'Department / Office', 'Personnel Paid',
                'Gross Payroll (PHP)', 'Total Deductions (PHP)', 'Net Payroll (PHP)',
                'Average Net Pay (PHP)', 'Share of Gross (%)',
            ]);

            foreach ($rows as $i => $r) {
                $csv->row([
                    $i + 1,
                    $r['dept'],
                    $r['headcount'],
                    $this->money($r['gross']),
                    $this->money($r['gross'] - $r['net']),
                    $this->money($r['net']),
                    $this->money($r['headcount'] ? $r['net'] / $r['headcount'] : 0),
                    number_format($r['pct'], 1),
                ]);
            }

            if ($rows->isEmpty()) {
                $csv->emptyNotice($report['empty']);
            } else {
                $csv->row([
                    '', 'TOTAL (' . $rows->count() . ' ' . str('department')->plural($rows->count()) . ')',
                    $rows->sum('headcount'),
                    $this->money($totals['gross']),
                    $this->money($totals['gross'] - $totals['net']),
                    $this->money($totals['net']),
                    $this->money($rows->sum('headcount') ? $totals['net'] / $rows->sum('headcount') : 0),
                    '100.0',
                ]);
            }

            $largest = $rows->first();

            $csv->summary('Summary', [
                'Departments Paid:'       => $rows->count(),
                'Personnel Paid:'         => $totals['headcount'],
                'Gross Payroll (PHP):'    => number_format((float) $totals['gross'], 2),
                'Total Deductions (PHP):' => number_format((float) $totals['gross'] - (float) $totals['net'], 2),
                'Net Payroll (PHP):'      => number_format((float) $totals['net'], 2),
                'Largest Share:'          => $largest ? $largest['dept'] . ' — ' . $largest['pct'] . '% of gross' : 'No data',
            ]);

            $csv->notes([
                'Personnel Paid counts distinct employees with a computation in this period, not the department\'s full plantilla — someone with no payroll this period is not counted here. The Headcount Report tab reports the roster.',
                'Share of Gross is each department\'s gross payroll as a percentage of the municipality\'s gross for the period.',
                'Employees with no department assigned are grouped under "Unassigned".',
            ]);
        });
    }

    // ────────────────────────── Deductions Report ──────────────────────────

    public function deductions(Request $request)
    {
        return $this->build($request, 'deductions', function (CsvReportWriter $csv, array $report, array $period) {
            $rows = $report['rows'];
            $totals = $report['totals'];

            $csv->columns([
                'No.', 'Deduction', 'Category', 'Employees Affected',
                'Total Amount (PHP)', 'Average per Employee (PHP)', 'Share of Itemised (%)',
            ]);

            foreach ($rows as $i => $r) {
                $csv->row([
                    $i + 1,
                    $r['name'],
                    $r['category'],
                    $r['employees'],
                    $this->money($r['amount']),
                    $this->money($r['employees'] ? $r['amount'] / $r['employees'] : 0),
                    number_format($r['pct'], 1),
                ]);
            }

            if ($rows->isEmpty()) {
                $csv->emptyNotice($report['empty']);
            } else {
                $csv->row([
                    '', 'TOTAL (' . $rows->count() . ' ' . str('type')->plural($rows->count()) . ')', '',
                    $totals['employees'],
                    $this->money($totals['amount']),
                    '',
                    '100.0',
                ]);
            }

            $csv->summary('Summary', [
                'Deduction Types:'                => $rows->count(),
                'Employees Affected:'             => $totals['employees'],
                'Itemised Deductions (PHP):'      => number_format((float) $totals['amount'], 2),
                'Gross Less Net Pay (PHP):'       => number_format((float) $totals['gross_less_net'], 2),
                'Unitemised Difference (PHP):'    => number_format((float) $totals['gross_less_net'] - (float) $totals['amount'], 2),
                'Mandatory Contributions (PHP):'  => number_format((float) $totals['mandatory'], 2),
            ]);

            $csv->summary('Total per Category',
                $rows->groupBy('category')
                    ->map(fn (Collection $g) => $g->sum('amount'))
                    ->sortDesc()
                    ->mapWithKeys(fn ($amount, $category) => [$category . ' (PHP):' => number_format((float) $amount, 2)])
                    ->all()
            );

            $notes = [
                'Itemised lines come from each payslip\'s stored deduction breakdown. Late and Undertime are read from their own columns on the computation, not from that breakdown.',
                'Share of Itemised is each line as a percentage of the itemised total, not of gross pay.',
                'Mandatory covers GSIS, PhilHealth and Pag-IBIG contributions.',
            ];

            // The same reconciliation warning the page shows above the table.
            // It belongs on the file too: a reader totalling the Amount column
            // against a payslip needs to know why the two can differ.
            if (!empty($report['note'])) {
                array_unshift($notes, 'RECONCILIATION: ' . $report['note']);
            }

            $csv->notes($notes);
        });
    }

    // ────────────────────────── Headcount Report ──────────────────────────

    public function headcount(Request $request)
    {
        return $this->build($request, 'headcount', function (CsvReportWriter $csv, array $report, array $period) {
            $rows = $report['rows'];
            $totals = $report['totals'];

            $csv->columns([
                'No.', 'Department / Office', 'Permanent', 'Job Order',
                'Other Status', 'Total Personnel', 'Share of Personnel (%)',
            ]);

            foreach ($rows as $i => $r) {
                $csv->row([
                    $i + 1,
                    $r['dept'],
                    $r['permanent'],
                    $r['joborder'],
                    $r['other'],
                    $r['total'],
                    number_format($r['pct'], 1),
                ]);
            }

            if ($rows->isEmpty()) {
                $csv->emptyNotice($report['empty']);
            } else {
                $csv->row([
                    '', 'TOTAL (' . $rows->count() . ' ' . str('department')->plural($rows->count()) . ')',
                    $rows->sum('permanent'),
                    $rows->sum('joborder'),
                    $rows->sum('other'),
                    $totals['total'],
                    '100.0',
                ]);
            }

            $csv->summary('Summary', [
                'Total Personnel:'          => $totals['total'],
                'Permanent:'                => $totals['permanent'],
                'Job Order:'                => $totals['joborder'],
                'Other Status:'             => max(0, (int) $totals['total'] - (int) $totals['permanent'] - (int) $totals['joborder']),
                'Departments with Personnel:' => $rows->count(),
                'Average per Department:'   => $rows->count() ? number_format($totals['total'] / $rows->count(), 1) : '0.0',
                'Largest Office:'           => $rows->first() ? $rows->first()['dept'] . ' — ' . $rows->first()['total'] . ' personnel' : 'No data',
            ]);

            $csv->notes([
                'A live snapshot as of the generation date above — this report is a roster count, not a pay-period figure, so the period filter on screen does not narrow it.',
                'Counts employees holding an employment record. "Other Status" covers Casual, Contractual, Co-terminous and any status that is neither Permanent nor Job Order.',
                'Employees with no department assigned are grouped under "Unassigned".',
            ]);
        });
    }

    // ────────────────────────── Training Report ──────────────────────────

    public function training(Request $request)
    {
        return $this->build($request, 'training', function (CsvReportWriter $csv, array $report, array $period) {
            $rows = $report['rows'];
            $totals = $report['totals'];

            $csv->columns([
                'No.', 'Employee ID', 'Employee Name', 'Department / Office',
                'Title of Seminar / Conference / Training Program',
                'Conducted / Sponsored By', 'Date From', 'Date To',
                'Hours Claimed', 'Hours Credited', 'Status',
            ]);

            foreach ($rows as $i => $r) {
                $csv->row([
                    $i + 1,
                    $r['code'],
                    $r['name'],
                    $r['dept'],
                    $r['title'],
                    $r['conductor'],
                    $r['from'],
                    $r['to'],
                    $this->hours($r['hours']),
                    $this->hours($r['credited']),
                    $r['status'],
                ]);
            }

            if ($rows->isEmpty()) {
                $csv->emptyNotice($report['empty']);
            } else {
                $csv->row([
                    '', '', 'TOTAL (' . $rows->count() . ' ' . str('record')->plural($rows->count()) . ')',
                    '', '', '', '', '',
                    $this->hours($totals['hours']),
                    $this->hours($totals['credited']),
                    '',
                ]);
            }

            $csv->summary('Summary', [
                'Training Records:'      => $rows->count(),
                'Verified:'              => $rows->where('status', 'Verified')->count(),
                'Pending Review:'        => $rows->where('status', 'Pending')->count(),
                'Rejected:'              => $rows->where('status', 'Rejected')->count(),
                'Participants:'          => $rows->pluck('code')->unique()->count(),
                'Total Hours Claimed:'   => $this->hours($totals['hours']),
                'Total Hours Credited:'  => $this->hours($totals['credited']),
            ]);

            $csv->summary('Breakdown by Status', $this->countsBy($rows, fn ($r) => $r['status']));

            $csv->summary('Credited Hours per Department / Office',
                $rows->groupBy('dept')
                    ->map(fn (Collection $g) => $g->sum('credited'))
                    ->sortDesc()
                    ->mapWithKeys(fn ($sum, $dept) => [$dept . ':' => $this->hours($sum)])
                    ->all()
            );

            $csv->notes([
                'Filtered by year only — training is not tied to a pay period, so the semi-monthly filter on screen does not narrow this report. Records are those whose start date falls in the fiscal year above.',
                'Hours Credited counts verified submissions only: a rejected or still-pending submission credits 0 hours to CSC PDS Section IV however many it declared.',
                'The Training Verification page exports the fuller review trail — certificate numbers, who verified each entry, and the reason for any rejection.',
            ]);
        });
    }

    // ────────────────────────── Not yet in the schema ──────────────────────────

    public function recruitment(Request $request)
    {
        return $this->build($request, 'recruitment', fn () => null);
    }

    public function performance(Request $request)
    {
        return $this->build($request, 'performance', fn () => null);
    }

    // ────────────────────────── Shared plumbing ──────────────────────────

    /**
     * Letterhead, parameter block, then whatever the tab's own body writes.
     *
     * The head and the tail are identical on all seven files by design — the
     * point of `CsvReportWriter` is that a file found on somebody's laptop a
     * year later identifies itself the same way whichever button produced it.
     */
    private function build(Request $request, string $tab, callable $body)
    {
        try {
            $period = $this->reports->resolvePeriod($request);
            $report = $this->reports->one($tab, $period);

            $fileName = $this->fileName($tab, $report, $period);

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($report, $period, $tab, $body) {
                $csv->letterhead(
                    $report['title'],
                    'Human Resource Management Office · PRIME HRIS',
                    $this->coverage($tab, $period)
                );

                $csv->parameters(
                    $this->parameters($tab, $report, $period),
                    // An unavailable report has no record count to state, and
                    // printing "Total Records: 0" beside "not available" reads
                    // as "nothing happened this period" rather than "this is
                    // not built yet".
                    empty($report['unavailable']) ? $report['rows']->count() : null
                );

                if (!empty($report['unavailable'])) {
                    $this->unavailableBody($csv, $report);

                    return;
                }

                $body($csv, $report, $period);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.reports', ['tab' => $tab] + $request->only(['year', 'month', 'semi']))
                ->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * The body of a report this schema cannot produce.
     *
     * Deliberately not a letterhead over an empty table: a blank grid under
     * "Recruitment Report" reads as "nobody was hired this period", which is a
     * false statement about the municipality's records. The file says in its
     * own words that the capability does not exist yet, and what would have to
     * be recorded before it could.
     */
    private function unavailableBody(CsvReportWriter $csv, array $report): void
    {
        $csv->row(['REPORT STATUS']);
        $csv->row(['Status:', 'NOT AVAILABLE — no data is being recorded for this report']);
        $csv->row(['Reason:', $report['unavailable']]);
        $csv->blank();
        $csv->row(['This file contains no records. It is not a statement that none exist: nothing in PRIME HRIS captures this information yet, so there is nothing for the report to read.']);
        $csv->row(['Once the module is added, this same Export button will produce the full report for the period selected above.']);

        $csv->notes([], containsPersonalData: false);
    }

    /** The coverage line under the report title in the letterhead. */
    private function coverage(string $tab, array $period): string
    {
        return match ($tab) {
            // A roster count, not a pay-period figure — saying it covers
            // "August 1–15" would misdescribe the file.
            'headcount' => 'Personnel on file as of ' . now()->format('F d, Y'),
            'training'  => 'Fiscal Year ' . $period['year'] . ' · Records filed January 1 to December 31, ' . $period['year'],
            default     => 'Pay Period: ' . $period['label']
                . ' (' . $period['start']->format('F d, Y') . ' to ' . $period['end']->format('F d, Y') . ')',
        };
    }

    /**
     * What produced this file.
     *
     * Every filter is printed even where it did not apply, and where it did
     * not apply the file says so — "Not applicable (live snapshot)" rather
     * than a blank cell, so a reader can tell an inapplicable filter from an
     * unwritten one.
     */
    private function parameters(string $tab, array $report, array $period): array
    {
        $semiLabel = $period['semi'] === 1 ? '1st Half (1–15)' : '2nd Half (16–end of month)';

        $base = [
            'Report:'      => $report['title'],
            'Report Tab:'  => AdminReportService::TABS[$tab] ?? $report['title'],
            'Fiscal Year:' => $period['year'],
        ];

        return match ($tab) {
            'headcount' => $base + [
                'Month:'          => 'Not applicable (live snapshot)',
                'Pay Period:'     => 'Not applicable (live snapshot)',
                'Snapshot Taken:' => now()->format('F d, Y g:i A'),
            ],
            'training' => $base + [
                'Month:'          => 'Not applicable (filtered by year)',
                'Pay Period:'     => 'Not applicable (filtered by year)',
                'Period Covered:' => 'January 1 to December 31, ' . $period['year'],
            ],
            default => $base + [
                'Month:'               => $period['start']->format('F'),
                'Semi-Monthly Period:' => $semiLabel,
                'Pay Period:'          => $period['label'],
                'Period Covered:'      => $period['start']->format('F d, Y') . ' to ' . $period['end']->format('F d, Y'),
            ],
        };
    }

    /**
     * `Payroll_Summary_August_1-15_2026.csv` — the report and what it covers,
     * so a folder of these sorts and reads without opening any of them.
     */
    private function fileName(string $tab, array $report, array $period): string
    {
        $scope = match ($tab) {
            'headcount' => 'As_Of_' . now()->format('M_d_Y'),
            'training'  => (string) $period['year'],
            default     => $period['start']->format('M') . '_'
                . ($period['semi'] === 1 ? '1-15' : '16-' . $period['end']->day) . '_' . $period['year'],
        };

        $title = preg_replace('/[^A-Za-z0-9]+/', '_', $report['title']);

        return trim($title, '_') . '_' . $scope . '.csv';
    }

    /**
     * label => count, largest first.
     *
     * @return array<string,int>
     */
    private function countsBy(Collection $items, callable $key): array
    {
        return $items->groupBy($key)
            ->map->count()
            ->sortDesc()
            ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
            ->all();
    }

    /** Plain number, no thousands separator — the column header carries "(PHP)". */
    private function money($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /** Trailing zeros dropped: "16" rather than "16.00", "7.5" kept. */
    private function hours($total): string
    {
        return rtrim(rtrim(number_format((float) $total, 2, '.', ''), '0'), '.') ?: '0';
    }
}
