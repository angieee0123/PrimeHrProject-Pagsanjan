<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\SalaryComputation;
use App\Models\User;
use App\Services\CsvReportWriter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeePayslipController extends Controller
{
    /**
     * The two words the Status column shows, and the stored values behind each.
     *
     * `salary_computations.status` is an enum of four — 'draft', 'pending',
     * 'approved', 'paid' — and it *defaults* to 'draft', so a computation
     * nobody has touched is stored as a draft rather than as pending. Draft
     * and pending both mean "not yet settled" everywhere else in this system
     * (`WorkflowAssistantService::payrollPreview()` reads them as one bucket,
     * and `PayrollExportController::statusesFor()` maps the admin dropdown's
     * "Pending / Draft" onto both), so the employee's page groups them the
     * same way.
     *
     * The filter, the badge and the export all read this one map, which is
     * what keeps a filtered table and its download saying the same word.
     */
    private const STATUS_GROUPS = [
        'pending'   => ['draft', 'pending'],
        'processed' => ['approved', 'paid'],
    ];

    private const STATUS_LABELS = [
        'pending'   => 'Pending',
        'processed' => 'Processed',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return view('employee.payslip.employeePayslip', [
                'payslips' => collect(),
                'latestPayslip' => null,
                'filters' => $this->filters($request),
                'stats' => [
                    'latest_net_pay' => 0,
                    'basic_pay' => 0,
                    'total_deductions' => 0,
                    'total_payslips' => 0
                ]
            ]);
        }

        // Load employee relationships for topbar
        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $filters = $this->filters($request);

        // `withQueryString()` so paging to page 2 keeps the filters — without
        // it the toolbar reads "filtered" while page 2 quietly shows
        // everything, which is the state the export would then be asked to
        // match.
        $payslips = $this->filtered($employee, $filters)
            ->paginate(5)
            ->withQueryString();

        // The stat cards describe the employee's record as a whole, not the
        // current filter — "Latest Net Pay" means their latest, and narrowing
        // the table to last March must not restate it as March's.
        $latestPayslip = SalaryComputation::where('employee_id', $employee->id)
            ->orderBy('period_end', 'desc')
            ->first();

        $stats = [
            'latest_net_pay' => $latestPayslip->net_pay ?? 0,
            'basic_pay' => $latestPayslip->basic_pay ?? 0,
            'total_deductions' => ($latestPayslip->late_deduction ?? 0) + ($latestPayslip->undertime_deduction ?? 0) + ($latestPayslip->other_deductions ?? 0),
            'total_payslips' => SalaryComputation::where('employee_id', $employee->id)->count()
        ];

        return view('employee.payslip.employeePayslip', compact('employee', 'payslips', 'latestPayslip', 'stats', 'filters'));
    }

    /**
     * "Export" on the Payslip History toolbar.
     *
     * The button had no handler at all — it rendered, it hovered, and clicking
     * it did nothing. It now streams the register the table is showing, built
     * from `filtered()`, the *same* query `index()` paginates, so the file
     * cannot disagree with the screen about which payslips matched.
     *
     * Pagination is deliberately not applied: the table shows five rows at a
     * time, and a register of "my payslips this year" that stopped at the
     * fifth would be wrong in a way nothing on the file could explain.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return redirect()->route('employee.payslip')
                ->with('error', 'No employee record found.');
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $filters = $this->filters($request);

        try {
            $payslips = $this->filtered($employee, $filters)
                ->get()
                ->filter(fn (SalaryComputation $p) => $this->matchesSearch($p, $filters['search']))
                ->values();

            $fileName = 'Payslip_History_' . $employee->employee_id . '_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $payslips, $employee, $filters
            ) {
                $csv->letterhead(
                    'Payslip History',
                    'Human Resource Management Office · PRIME HRIS',
                    $payslips->isNotEmpty()
                        ? 'Payslips covering ' . CsvReportWriter::longDate($payslips->min('period_start'))
                            . ' to ' . CsvReportWriter::longDate($payslips->max('period_end'))
                        : 'Payslips on file as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Employee:'            => trim($employee->first_name . ' ' . $employee->last_name),
                    'Employee ID:'         => $employee->employee_id,
                    'Position:'            => $employee->employmentDetail->designationRelation->title ?? 'Unassigned',
                    'Department / Office:' => $employee->employmentDetail->departmentRelation->name ?? 'Unassigned',
                    'Period From:'         => $filters['start_date'] ? CsvReportWriter::longDate(Carbon::parse($filters['start_date'])) : 'All Periods',
                    'Period To:'           => $filters['end_date'] ? CsvReportWriter::longDate(Carbon::parse($filters['end_date'])) : 'All Periods',
                    'Status:'              => self::STATUS_LABELS[$filters['status']] ?? 'All Status',
                    'Search Term:'         => $filters['search'] !== '' ? $filters['search'] : 'None',
                ], $payslips->count());

                $csv->columns([
                    'No.', 'Period Start', 'Period End', 'Pay Date', 'Payroll Type',
                    'Monthly Rate (PHP)', 'Daily Rate (PHP)', 'Days Present',
                    'Basic Pay (PHP)', 'Overtime Pay (PHP)', 'Gross Pay (PHP)',
                    'Late Deduction (PHP)', 'Undertime Deduction (PHP)',
                    'Other Deductions (PHP)', 'Total Deductions (PHP)',
                    'Net Pay (PHP)', 'Status',
                ]);

                foreach ($payslips as $index => $p) {
                    $csv->row([
                        $index + 1,
                        CsvReportWriter::date($p->period_start),
                        CsvReportWriter::date($p->period_end),
                        CsvReportWriter::date($p->pay_date ?: $p->period_end),
                        $p->payroll_type ? ucfirst(str_replace('_', ' ', $p->payroll_type)) : '—',
                        $this->money($p->monthly_rate),
                        $this->money($p->daily_rate),
                        $p->total_days_present ?? 0,
                        $this->money($p->basic_pay),
                        $this->money($p->ot_pay),
                        $this->money($this->grossPay($p)),
                        $this->money($p->late_deduction),
                        $this->money($p->undertime_deduction),
                        $this->money($p->other_deductions),
                        $this->money($this->totalDeductions($p)),
                        $this->money($p->net_pay),
                        $this->statusLabel($p),
                    ]);
                }

                if ($payslips->isEmpty()) {
                    $csv->emptyNotice('No payslips matched the filters above.');
                }

                // A totals line in the table's own columns, so the file can be
                // checked against a printout column by column, as well as the
                // summary block below it.
                if ($payslips->isNotEmpty()) {
                    $csv->row([
                        '', 'TOTAL', '', '', '', '', '', $payslips->sum('total_days_present'),
                        $this->money($payslips->sum('basic_pay')),
                        $this->money($payslips->sum('ot_pay')),
                        $this->money($payslips->sum(fn (SalaryComputation $p) => $this->grossPay($p))),
                        $this->money($payslips->sum('late_deduction')),
                        $this->money($payslips->sum('undertime_deduction')),
                        $this->money($payslips->sum('other_deductions')),
                        $this->money($payslips->sum(fn (SalaryComputation $p) => $this->totalDeductions($p))),
                        $this->money($payslips->sum('net_pay')),
                        '',
                    ]);
                }

                $csv->summary('Summary', [
                    'Payslips Covered:'    => $payslips->count(),
                    'Pending:'             => $this->countByStatus($payslips, 'pending'),
                    'Processed:'           => $this->countByStatus($payslips, 'processed'),
                    'Total Days Present:'  => $payslips->sum('total_days_present'),
                    'Total Basic Pay:'     => $this->money($payslips->sum('basic_pay')),
                    'Total Overtime Pay:'  => $this->money($payslips->sum('ot_pay')),
                    'Total Gross Pay:'     => $this->money($payslips->sum(fn (SalaryComputation $p) => $this->grossPay($p))),
                    'Total Deductions:'    => $this->money($payslips->sum(fn (SalaryComputation $p) => $this->totalDeductions($p))),
                    'Total Net Pay:'       => $this->money($payslips->sum('net_pay')),
                ]);

                $csv->notes([
                    'Total Deductions is late + undertime + other deductions, the same figure the Deductions column shows on screen. Other Deductions carries the GSIS, PhilHealth, Pag-IBIG and loan items itemised on each payslip.',
                    'Status "Pending" covers a computation still in draft — payroll has not settled it — and "Processed" covers an approved or paid one.',
                    'Amounts are in Philippine Pesos, written without a thousands separator so a spreadsheet totals the column rather than reading it as text.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('employee.payslip')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function getPayslipDetails($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $payslip = SalaryComputation::where('id', $id)
            ->where('employee_id', $employee->id)
            ->with(['employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation'])
            ->first();

        if (!$payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found'
            ], 404);
        }

        // Parse deduction_breakdown if it's a string
        $deductionBreakdown = $payslip->deduction_breakdown;
        if (is_string($deductionBreakdown)) {
            $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
        } elseif (!is_array($deductionBreakdown)) {
            $deductionBreakdown = [];
        }

        return response()->json([
            'success' => true,
            'payslip' => [
                'employee_name' => $payslip->employee->first_name . ' ' . $payslip->employee->last_name,
                'employee_id' => $payslip->employee->employee_id,
                'department' => $payslip->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                'position' => $payslip->employee->employmentDetail->designationRelation->title ?? 'N/A',
                'period' => $payslip->period_start->format('M d, Y') . ' - ' . $payslip->period_end->format('M d, Y'),
                'pay_date' => $payslip->period_end->format('M d, Y'),
                'monthly_rate' => $payslip->monthly_rate ?? ($payslip->employee->employmentDetail->designationRelation->monthly_rate ?? 0),
                'daily_rate' => $payslip->daily_rate ?? 0,
                'total_days_present' => $payslip->total_days_present ?? 0,
                'basic_pay' => $payslip->basic_pay,
                'ot_pay' => $payslip->ot_pay ?? 0,
                'gross_pay' => $payslip->gross_pay ?? ($payslip->basic_pay + ($payslip->ot_pay ?? 0)),
                'late_deduction' => $payslip->late_deduction ?? 0,
                'undertime_deduction' => $payslip->undertime_deduction ?? 0,
                'other_deductions' => $payslip->other_deductions ?? 0,
                'deduction_breakdown' => $deductionBreakdown,
                'total_deductions' => ($payslip->late_deduction ?? 0) + ($payslip->undertime_deduction ?? 0) + ($payslip->other_deductions ?? 0),
                'net_pay' => $payslip->net_pay,
                'status' => $payslip->status,
                'notes' => $payslip->notes ?? null
            ]
        ]);
    }

    /**
     * The toolbar's state, read once and handed to both the page and the file.
     *
     * Every key is always present, so the export's parameter block can print
     * "All Periods" rather than leave a cell unwritten — a reader has to be
     * able to tell "this covers everything" from "this did not get filled in".
     *
     * @return array{start_date: string, end_date: string, status: string, search: string}
     */
    private function filters(Request $request): array
    {
        $status = strtolower(trim((string) $request->get('status')));

        return [
            'start_date' => $this->dateOrEmpty($request->get('start_date')),
            'end_date'   => $this->dateOrEmpty($request->get('end_date')),
            // Anything unrecognised — including the dropdown's own "" for
            // All Status — narrows nothing, rather than matching nothing.
            'status'     => array_key_exists($status, self::STATUS_GROUPS) ? $status : '',
            'search'     => trim((string) $request->get('search')),
        ];
    }

    /**
     * The one query behind the table and the download.
     *
     * A payslip matches the date range if its period *overlaps* it: a period
     * running 16 July – 15 August belongs in an August register, and an
     * exact-containment test would silently drop it under a heading that says
     * the month is covered.
     */
    private function filtered(Employee $employee, array $filters): Builder
    {
        $query = SalaryComputation::where('employee_id', $employee->id)
            ->orderBy('period_end', 'desc');

        if ($filters['start_date'] !== '') {
            $query->whereDate('period_end', '>=', $filters['start_date']);
        }

        if ($filters['end_date'] !== '') {
            $query->whereDate('period_start', '<=', $filters['end_date']);
        }

        if ($filters['status'] !== '') {
            $query->whereIn('status', self::STATUS_GROUPS[$filters['status']]);
        }

        return $query;
    }

    /**
     * The topbar search box narrows the table by matching each row's rendered
     * text. Matched here against the cells that row is built from — the period
     * in the spelling the table prints, the pay date and the status word — so
     * a narrowed table and its export agree.
     *
     * The box filters only the rows currently paginated onto the screen; the
     * export applies the same term across every matching payslip, which is
     * what "export what I filtered to" means once the filter outlives page 1.
     */
    private function matchesSearch(SalaryComputation $payslip, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $haystack = mb_strtolower(implode(' ', [
            $payslip->period_start?->format('M d') . '-' . $payslip->period_end?->format('d, Y'),
            $payslip->period_start?->format('Y-m-d'),
            $payslip->period_end?->format('M d, Y'),
            $this->statusLabel($payslip),
        ]));

        return str_contains($haystack, mb_strtolower($search));
    }

    /** The word the Status badge shows for this row. */
    private function statusLabel(SalaryComputation $payslip): string
    {
        foreach (self::STATUS_GROUPS as $group => $stored) {
            if (in_array($payslip->status, $stored, true)) {
                return self::STATUS_LABELS[$group];
            }
        }

        return ucfirst((string) $payslip->status);
    }

    private function countByStatus(Collection $payslips, string $group): int
    {
        return $payslips->whereIn('status', self::STATUS_GROUPS[$group])->count();
    }

    /** Stored where payroll wrote it, derived where it did not. */
    private function grossPay(SalaryComputation $payslip): float
    {
        return (float) ($payslip->gross_pay ?? ((float) $payslip->basic_pay + (float) ($payslip->ot_pay ?? 0)));
    }

    /** The same three items the table's Deductions column adds up. */
    private function totalDeductions(SalaryComputation $payslip): float
    {
        return (float) ($payslip->late_deduction ?? 0)
            + (float) ($payslip->undertime_deduction ?? 0)
            + (float) ($payslip->other_deductions ?? 0);
    }

    /**
     * A peso figure a spreadsheet can add up.
     *
     * No thousands separator: "12,345.67" imports as text, and the column then
     * refuses to total, which is the first thing anyone does with a payroll
     * register. The header carries the "(PHP)" instead.
     */
    private function money($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /** A date the toolbar sent, or '' — never a parse error thrown at a page. */
    private function dateOrEmpty($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
