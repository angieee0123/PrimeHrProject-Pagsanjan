<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\LeaveAccrualRate;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use App\Models\SalaryComputation;
use App\Services\CsvReportWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The six "Export" buttons on Admin → Leave & Benefits.
 *
 * Each tab shows a different thing, so each exports a different file: a shared
 * "export this page" endpoint would either dump one tab's columns under
 * another tab's title, or fall back to whatever all six have in common, which
 * is nothing. What they *do* share is the letterhead, and that lives in
 * `CsvReportWriter` rather than being pasted six times.
 *
 * Every export re-runs its tab's query server-side instead of scraping the
 * rendered table. Four of these tabs paginate and four filter in the browser,
 * so a table-scraping export would quietly hand back page 1 of 12 — the file
 * covers everything the filters select, which is the whole point of Export.
 */
class LeaveBenefitsExportController extends Controller
{
    /** Filenames lead with the report so a folder of exports sorts by kind. */
    private function fileName(string $report): string
    {
        return $report . '_' . now()->format('M_d_Y') . '.csv';
    }

    // ─────────────────────────────────────────────────────────────────
    //  1 · Leave Requests
    // ─────────────────────────────────────────────────────────────────

    public function leaveRequests(Request $request)
    {
        $dateFrom   = $request->get('date_from');
        $dateTo     = $request->get('date_to');
        $department = $request->get('department');
        $leaveType  = $request->get('leave_type');
        $status     = $request->get('status');

        $applications = LeaveApplication::with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation',
                'leaveType',
                'approvedBy.employee',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function (LeaveApplication $application) use ($dateFrom, $dateTo, $department, $leaveType, $status) {
                $detail = $application->employee->employmentDetail ?? null;

                if ($department && ($detail->departmentRelation->name ?? null) !== $department) {
                    return false;
                }

                if ($leaveType && ($application->leaveType->leave_name ?? null) !== $leaveType) {
                    return false;
                }

                if ($status && strtolower($status) !== strtolower((string) $application->status)) {
                    return false;
                }

                // A leave that *overlaps* the window belongs in it: a request
                // running Jun 28 → Jul 3 is part of both months, and filtering
                // on start_date alone would drop it from the July report.
                if ($dateFrom && $application->end_date->format('Y-m-d') < $dateFrom) {
                    return false;
                }
                if ($dateTo && $application->start_date->format('Y-m-d') > $dateTo) {
                    return false;
                }

                return true;
            })
            ->values();

        return CsvReportWriter::download($this->fileName('Leave_Requests'), function (CsvReportWriter $csv) use (
            $applications, $dateFrom, $dateTo, $department, $leaveType, $status
        ) {
            $csv->letterhead(
                'Leave Applications Report',
                'Human Resource Management Office · PRIME HRIS',
                'Leave Period Covered: ' . $this->describeRange($dateFrom, $dateTo)
            );

            $csv->parameters([
                'Leave Period:' => $this->describeRange($dateFrom, $dateTo),
                'Department:'   => $department ?: 'All Departments',
                'Leave Type:'   => $leaveType ?: 'All Leave Types',
                'Status:'       => $status ? $this->statusLabel($status) : 'All Status',
            ], $applications->count());

            $csv->columns([
                'No.', 'Application No.', 'Date Filed',
                'Employee ID', 'Employee Name', 'Position', 'Department',
                'Leave Code', 'Leave Type',
                'Date From', 'Date To', 'No. of Days',
                'Status', 'Reason / Purpose',
                'Acted On By', 'Date Acted On', 'Remarks',
            ]);

            foreach ($applications as $index => $application) {
                $employee = $application->employee;
                $detail   = $employee->employmentDetail ?? null;

                $csv->row([
                    $index + 1,
                    $application->application_number ?: '—',
                    CsvReportWriter::date($application->created_at),
                    $employee->employee_id ?? '—',
                    $this->employeeName($employee),
                    $detail->designationRelation->title ?? '—',
                    $detail->departmentRelation->name ?? '—',
                    $application->leave_code,
                    $application->leaveType->leave_name ?? '—',
                    CsvReportWriter::date($application->start_date),
                    CsvReportWriter::date($application->end_date),
                    $this->days($application->number_of_days),
                    $this->statusLabel($application->status),
                    $application->reason ?: '—',
                    $this->actorName($application->approvedBy),
                    CsvReportWriter::date($application->approved_at),
                    $application->approver_remarks ?: '—',
                ]);
            }

            if ($applications->isEmpty()) {
                $csv->emptyNotice('No leave applications matched the filters above.');
            }

            $approved = $applications->where('status', 'approved');

            $csv->summary('Summary of Leave Applications', [
                'Total Applications:'  => $applications->count(),
                'Approved:'            => $approved->count(),
                'Pending:'             => $applications->where('status', 'pending')->count(),
                'Disapproved:'         => $applications->where('status', 'rejected')->count(),
                'Cancelled:'           => $applications->whereNotIn('status', ['approved', 'pending', 'rejected'])->count(),
                'Total Approved Days:' => $this->days($approved->sum('number_of_days')),
            ]);

            $csv->summary('Approved Days by Leave Type', $this->tally(
                $approved->groupBy(fn ($a) => $a->leaveType->leave_name ?? $a->leave_code)
                    ->map(fn ($group) => $this->days($group->sum('number_of_days')))
            ));

            $csv->summary('Applications by Department / Office', $this->tally(
                $applications->groupBy(fn ($a) => $a->employee->employmentDetail->departmentRelation->name ?? 'Unassigned')
                    ->map->count()
            ));

            $csv->notes(['Days shown are as filed. Approved applications are the ones charged against leave credits.']);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    //  2 · Transaction History
    // ─────────────────────────────────────────────────────────────────

    public function transactions(Request $request)
    {
        $dateFrom   = $request->get('filter_transaction_date_from');
        $dateTo     = $request->get('filter_transaction_date_to');
        $year       = $request->get('filter_transaction_year');
        $employeeId = $request->get('filter_employee');
        $type       = $request->get('filter_type');
        $leaveCode  = $request->get('filter_leave_code');

        $query = LeaveTransaction::with([
            'employee.employmentDetail.departmentRelation',
            'leaveType',
            'processedBy.employee',
        ]);

        // The tab's own precedence: an explicit range wins over the year
        // picker, which is why the page disables the year select while a range
        // is set.
        if ($dateFrom && $dateTo) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        } elseif ($year) {
            $query->whereYear('transaction_date', $year);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        if ($type) {
            $query->where('transaction_type', $type);
        }
        if ($leaveCode) {
            $query->where('leave_code', $leaveCode);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return CsvReportWriter::download($this->fileName('Leave_Transaction_History'), function (CsvReportWriter $csv) use (
            $transactions, $dateFrom, $dateTo, $year, $employeeId, $type, $leaveCode
        ) {
            $period = $this->describeTransactionPeriod($dateFrom, $dateTo, $year);

            $csv->letterhead(
                'Leave Transaction History',
                'Audit Trail of Leave Credit Movements · PRIME HRIS',
                'Transaction Period: ' . $period
            );

            $csv->parameters([
                'Transaction Period:' => $period,
                'Employee:'           => $this->employeeLabel($employeeId),
                'Transaction Type:'   => $type ? ucfirst($type) : 'All Types',
                'Leave Type:'         => $leaveCode ? $this->leaveTypeLabel($leaveCode) : 'All Leave Types',
            ], $transactions->count());

            $csv->columns([
                'No.', 'Transaction Date',
                'Employee ID', 'Employee Name', 'Department',
                'Leave Code', 'Leave Type', 'Credit Year',
                'Transaction Type', 'Amount (Days)', 'Balance Before (Days)', 'Balance After (Days)',
                'Reference', 'Reference ID', 'Processed By', 'Remarks',
            ]);

            foreach ($transactions as $index => $transaction) {
                $employee = $transaction->employee;

                // Sign, not just size: a debit and a credit of 1.25 days are
                // opposite events and must not read identically in a column
                // somebody is about to sum.
                $amount = (float) $transaction->amount;
                $signed = $transaction->transaction_type === 'debit' ? -abs($amount) : $amount;

                $csv->row([
                    $index + 1,
                    CsvReportWriter::date($transaction->transaction_date),
                    $employee->employee_id ?? '—',
                    $this->employeeName($employee),
                    $employee->employmentDetail->departmentRelation->name ?? '—',
                    $transaction->leave_code,
                    $transaction->leaveType->leave_name ?? '—',
                    $transaction->year ?: '—',
                    ucfirst($transaction->transaction_type),
                    $this->days($signed),
                    $this->days($transaction->balance_before),
                    $this->days($transaction->balance_after),
                    ucwords(str_replace('_', ' ', $transaction->reference_type ?: 'N/A')),
                    $transaction->reference_id ?: '—',
                    $this->actorName($transaction->processedBy),
                    $transaction->remarks ?: '—',
                ]);
            }

            if ($transactions->isEmpty()) {
                $csv->emptyNotice('No leave transactions matched the filters above.');
            }

            $credited = (float) $transactions->whereIn('transaction_type', ['credit', 'adjustment'])
                ->sum(fn ($t) => max((float) $t->amount, 0));
            $debited = (float) $transactions->where('transaction_type', 'debit')
                ->sum(fn ($t) => abs((float) $t->amount));

            $csv->summary('Summary of Credit Movement', [
                'Total Transactions:'  => $transactions->count(),
                'Employees Affected:'  => $transactions->pluck('employee_id')->unique()->count(),
                'Total Days Credited:' => $this->days($credited),
                'Total Days Debited:'  => $this->days($debited),
                'Net Movement (Days):' => $this->days($credited - $debited),
            ]);

            $csv->summary('Transactions by Type', $this->tally(
                $transactions->groupBy(fn ($t) => ucfirst($t->transaction_type))->map->count()
            ));

            $csv->summary('Transactions by Leave Type', $this->tally(
                $transactions->groupBy('leave_code')->map->count()
            ));

            $csv->notes(['A debit is written as a negative amount, so the Amount column sums to the net movement above.']);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    //  3 · Leave Credits
    // ─────────────────────────────────────────────────────────────────

    public function leaveCredits(Request $request)
    {
        $dateFrom   = $request->get('filter_credits_date_from');
        $dateTo     = $request->get('filter_credits_date_to');
        $year       = $request->get('filter_credits_year');
        $employeeId = $request->get('filter_credits_employee');
        $leaveCode  = $request->get('filter_credits_leave_code');
        $creditType = $request->get('filter_credits_type');

        $leaveTypes = LeaveType::all()->keyBy('leave_code');

        $balances = $this->balancesFor($dateFrom, $dateTo, $year)
            ->filter(function (LeaveBalance $balance) use ($employeeId, $leaveCode, $creditType, $leaveTypes) {
                if ($employeeId && (string) $balance->employee_id !== (string) $employeeId) {
                    return false;
                }
                if ($leaveCode && $balance->leave_code !== $leaveCode) {
                    return false;
                }
                if ($creditType) {
                    $isAccrued = (bool) ($leaveTypes[$balance->leave_code]->is_accrued ?? false);
                    if (($creditType === 'accrued') !== $isAccrued) {
                        return false;
                    }
                }

                return true;
            })
            ->values();

        return CsvReportWriter::download($this->fileName('Leave_Credits_Balance'), function (CsvReportWriter $csv) use (
            $balances, $leaveTypes, $dateFrom, $dateTo, $year, $employeeId, $leaveCode, $creditType
        ) {
            $asOf = $this->describeCreditsPeriod($dateFrom, $dateTo, $year);

            $csv->letterhead(
                'Employee Leave Credits Balance',
                'Human Resource Management Office · PRIME HRIS',
                'Balances ' . $asOf
            );

            $csv->parameters([
                'Balances:'    => $asOf,
                'Employee:'    => $this->employeeLabel($employeeId),
                'Leave Type:'  => $leaveCode ? $this->leaveTypeLabel($leaveCode) : 'All Leave Types',
                'Credit Type:' => $creditType ? ucfirst($creditType) : 'All Types',
            ], $balances->count());

            $csv->columns([
                'No.', 'Employee ID', 'Employee Name', 'Department',
                'Leave Code', 'Leave Type', 'Credit Type', 'Balance Year',
                'Total Credits', 'Used', 'Pending', 'Available', 'Carried Over',
                'Utilisation (%)',
            ]);

            foreach ($balances as $index => $balance) {
                $employee = $balance->employee;
                $type     = $leaveTypes[$balance->leave_code] ?? null;

                $total = (float) $balance->total_credits;
                $used  = (float) $balance->used_credits;

                $csv->row([
                    $index + 1,
                    $employee->employee_id ?? '—',
                    $this->employeeName($employee),
                    $employee->employmentDetail->departmentRelation->name ?? '—',
                    $balance->leave_code,
                    $type->leave_name ?? '—',
                    ($type && $type->is_accrued) ? 'Accrued' : 'Fixed',
                    $balance->year,
                    $this->days($total),
                    $this->days($used),
                    $this->days($balance->pending_credits),
                    $this->days($balance->available_credits),
                    $this->days($balance->carried_over),
                    $total > 0 ? number_format(($used / $total) * 100, 1, '.', '') : '0.0',
                ]);
            }

            if ($balances->isEmpty()) {
                $csv->emptyNotice('No leave credit balances matched the filters above.');
            }

            $csv->summary('Summary of Leave Credits', [
                'Balance Records:'      => $balances->count(),
                'Employees Covered:'    => $balances->pluck('employee_id')->unique()->count(),
                'Leave Types Covered:'  => $balances->pluck('leave_code')->unique()->count(),
                'Total Credits Earned:' => $this->days($balances->sum(fn ($b) => (float) $b->total_credits)),
                'Total Credits Used:'   => $this->days($balances->sum(fn ($b) => (float) $b->used_credits)),
                'Total Available:'      => $this->days($balances->sum(fn ($b) => (float) $b->available_credits)),
            ]);

            $csv->summary('Available Credits by Leave Type', $this->tally(
                $balances->groupBy('leave_code')
                    ->map(fn ($group) => $this->days($group->sum(fn ($b) => (float) $b->available_credits)))
            ));

            $csv->notes([
                'Balance Year is the year the row was written, not the year the credits expire. Balances are not '
                . 'rewritten each January, so a current figure can sit under an earlier year.',
            ]);
        });
    }

    /** The tab's three ways of deciding which row is "the" balance. */
    private function balancesFor(?string $dateFrom, ?string $dateTo, ?string $year): Collection
    {
        $with = ['employee.employmentDetail.departmentRelation'];

        if ($dateFrom && $dateTo) {
            return LeaveBalance::with($with)
                ->whereIn('id', function ($query) use ($dateTo) {
                    $query->selectRaw('MAX(id)')
                        ->from('leave_balances')
                        ->where('year', '<=', Carbon::parse($dateTo)->year)
                        ->groupBy('employee_id', 'leave_code');
                })
                ->orderBy('employee_id')->orderBy('leave_code')->get();
        }

        if ($year) {
            return LeaveBalance::with($with)
                ->where('year', $year)
                ->orderBy('employee_id')->orderBy('leave_code')->get();
        }

        return LeaveBalance::with($with)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('leave_balances')
                    ->groupBy('employee_id', 'leave_code');
            })
            ->orderBy('employee_id')->orderBy('leave_code')->get();
    }

    // ─────────────────────────────────────────────────────────────────
    //  4 · Benefits Summary
    // ─────────────────────────────────────────────────────────────────

    /**
     * Mandatory contributions and standing leave credits, per employee.
     *
     * Two things this does not do, both deliberate:
     *
     * It does not export the figures the Benefits Summary tab renders. Those
     * are a hard-coded demonstration array in `LeaveController::index()` — six
     * names that are not in this database. A CSV under the municipality's
     * letterhead is a document somebody may act on, so it reports records.
     *
     * It does not read amounts from `employee_deductions`. A mandatory
     * deduction row there is an *enrolment*: for a PERCENTAGE type its
     * `amount` is null, because the peso figure only exists once payroll
     * computes the period against the employee's salary. Reading it would
     * report every employee as contributing 0.00 while their payslip says
     * otherwise. The figures come from the latest `salary_computations` row's
     * `deduction_breakdown`, and the period that produced them is a column, so
     * nobody reads a half-month contribution as an annual one.
     *
     * The contribution columns are also not hard-coded. The seeded codes
     * (GSIS / PHILHEALTH / PAGIBIG) are not what this municipality actually
     * uses — it splits personal and government share ("GSIS PS", "GSIS GS",
     * "GSIS-SI", "PhilHeath PS", "PAG-IBIG PS", …) — so the columns are read
     * from `deduction_types`, and a mandatory type added later gets a column
     * without this file being touched.
     */
    public function benefits(Request $request)
    {
        $department = $request->get('department');

        $employees = Employee::with([
                'employmentDetail.departmentRelation',
                'employmentDetail.designationRelation',
                'governmentIds',
            ])
            ->orderBy('last_name')->orderBy('first_name')
            ->get()
            ->filter(fn (Employee $e) => !$department
                || ($e->employmentDetail->departmentRelation->name ?? null) === $department)
            ->values();

        // The columns, read from the configuration rather than named here.
        $contributionTypes = DeductionType::where('category', 'MANDATORY')
            ->orderBy('name')
            ->get();

        // One payroll row per employee: the most recent period computed for
        // them. `period_end` first, then id, so two periods filed on the same
        // day still resolve to the one entered last.
        $payslips = SalaryComputation::orderBy('period_end', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('employee_id')
            ->keyBy('employee_id');

        $balances = LeaveBalance::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('leave_balances')
                    ->groupBy('employee_id', 'leave_code');
            })
            ->get()
            ->groupBy('employee_id');

        return CsvReportWriter::download($this->fileName('Employee_Benefits_Summary'), function (CsvReportWriter $csv) use (
            $employees, $contributionTypes, $payslips, $balances, $department
        ) {
            $csv->letterhead(
                'Employee Benefits Summary',
                'Mandatory Contributions & Leave Credits · PRIME HRIS',
                'As of ' . now()->format('F d, Y')
            );

            $csv->parameters([
                'Department:'   => $department ?: 'All Departments',
                'Contributions:' => 'From each employee\'s most recent computed payroll period',
                'Leave Credits:' => 'Latest recorded balance per leave type',
                'As Of:'        => now()->format('F d, Y'),
            ], $employees->count());

            $csv->columns(array_merge(
                [
                    'No.', 'Employee ID', 'Employee Name', 'Position', 'Department',
                    'GSIS No.', 'PhilHealth No.', 'Pag-IBIG No.', 'TIN',
                    'Payroll Period', 'Payroll Status',
                ],
                $contributionTypes->map(fn ($t) => $t->name . ' (PHP)')->all(),
                [
                    'Total Mandatory Deductions (PHP)',
                    'VL Balance (Days)', 'SL Balance (Days)', 'Other Leave Credits (Days)',
                ]
            ));

            $totals = [];
            $totalVl = 0.0;
            $totalSl = 0.0;
            $withPayroll = 0;

            foreach ($employees as $index => $employee) {
                $detail   = $employee->employmentDetail;
                $ids      = $employee->governmentIds->first();
                $payslip  = $payslips[$employee->id] ?? null;
                $credit   = $balances[$employee->id] ?? collect();
                $breakdown = $payslip ? $this->breakdown($payslip) : [];

                if ($payslip) {
                    $withPayroll++;
                }

                $amounts = [];
                $rowTotal = 0.0;

                foreach ($contributionTypes as $type) {
                    // The breakdown is keyed by code, but a row written before
                    // a code was renamed still carries the old key with the
                    // right name, so fall back to matching on the name.
                    $entry = $breakdown[$type->code]
                        ?? collect($breakdown)->firstWhere('name', $type->name)
                        ?? null;

                    $amount = (float) ($entry['amount'] ?? 0);
                    $amounts[] = $payslip ? $this->peso($amount) : '';
                    $rowTotal += $amount;

                    $totals[$type->name] = ($totals[$type->name] ?? 0.0) + $amount;
                }

                $available = fn (string $code) => (float) (optional($credit->firstWhere('leave_code', $code))->available_credits ?? 0);
                $vl    = $available('VL');
                $sl    = $available('SL');
                $other = (float) $credit->whereNotIn('leave_code', ['VL', 'SL'])
                    ->sum(fn ($b) => (float) $b->available_credits);

                $totalVl += $vl;
                $totalSl += $sl;

                $csv->row(array_merge(
                    [
                        $index + 1,
                        $employee->employee_id,
                        $this->employeeName($employee),
                        $detail->designationRelation->title ?? '—',
                        $detail->departmentRelation->name ?? '—',
                        $ids->gsis_no ?? '—',
                        $ids->philhealth_no ?? '—',
                        $ids->pagibig_no ?? '—',
                        $ids->tin_no ?? '—',
                        // An employee with no computed payroll gets a stated
                        // reason, not a row of zeroes that reads as "pays
                        // nothing".
                        $payslip
                            ? CsvReportWriter::date($payslip->period_start) . ' – ' . CsvReportWriter::date($payslip->period_end)
                            : 'No payroll computed',
                        $payslip ? ucfirst((string) $payslip->status) : '—',
                    ],
                    $amounts,
                    [
                        $payslip ? $this->peso($rowTotal) : '',
                        $this->days($vl),
                        $this->days($sl),
                        $this->days($other),
                    ]
                ));
            }

            if ($employees->isEmpty()) {
                $csv->emptyNotice('No employees matched the filters above.');
            }

            $csv->summary('Summary of Benefits', array_merge(
                [
                    'Employees Covered:'          => $employees->count(),
                    'With Computed Payroll:'      => $withPayroll,
                    'Without Computed Payroll:'   => $employees->count() - $withPayroll,
                ],
                collect($totals)->mapWithKeys(fn ($v, $k) => ['Total ' . $k . ':' => $this->peso($v)])->all(),
                [
                    'Total Mandatory Deductions:' => $this->peso(array_sum($totals)),
                    'Total VL Credits Available:' => $this->days($totalVl),
                    'Total SL Credits Available:' => $this->days($totalSl),
                ]
            ));

            $csv->notes([
                'Contribution amounts are taken from each employee\'s most recent computed payroll period, shown per row. '
                . 'They are that period\'s figures — not a monthly, annual, or year-to-date total.',
                'A blank contribution column means no payroll has been computed for that employee yet.',
                'Leave balances are the latest recorded balance per leave type, which may have been written under an earlier year.',
            ]);
        });
    }

    /**
     * `deduction_breakdown` is cast to array, but rows are written
     * double-encoded — the cast then yields the inner JSON string rather than
     * an array. Decode until we actually have one.
     */
    private function breakdown(SalaryComputation $computation): array
    {
        $raw = $computation->deduction_breakdown;

        for ($i = 0; $i < 3 && is_string($raw); $i++) {
            $raw = json_decode($raw, true);
        }

        return is_array($raw) ? $raw : [];
    }

    // ─────────────────────────────────────────────────────────────────
    //  5 · Leave Types
    // ─────────────────────────────────────────────────────────────────

    public function leaveTypes(Request $request)
    {
        $status  = $request->get('status', 'all');
        $accrual = $request->get('accrual', 'all');

        $types = LeaveType::orderBy('leave_code')->get()
            ->filter(function (LeaveType $type) use ($status, $accrual) {
                if ($status !== 'all' && $type->is_active !== ($status === 'active')) {
                    return false;
                }
                if ($accrual !== 'all' && $type->is_accrued !== ($accrual === 'accrued')) {
                    return false;
                }

                return true;
            })
            ->values();

        return CsvReportWriter::download($this->fileName('Leave_Types_Configuration'), function (CsvReportWriter $csv) use (
            $types, $status, $accrual
        ) {
            $csv->letterhead(
                'Leave Types Configuration',
                'Civil Service Commission Leave Benefits · PRIME HRIS',
                'Configuration as of ' . now()->format('F d, Y')
            );

            $csv->parameters([
                'Status:'      => $status === 'all' ? 'All Status' : ucfirst($status),
                'Credit Type:' => $accrual === 'all' ? 'All Types' : ucfirst($accrual),
            ], $types->count());

            $csv->columns([
                'No.', 'Leave Code', 'Leave Type', 'Annual Limit (Days)', 'Credit Type',
                'Cumulative', 'Requires 6 Months Service', 'Monetizable',
                'Requires Attachment', 'Attachment Requirement', 'Reference Document', 'Status',
            ]);

            foreach ($types as $index => $type) {
                $csv->row([
                    $index + 1,
                    $type->leave_code,
                    $type->leave_name,
                    $type->annual_limit > 0 ? $this->days($type->annual_limit) : 'No fixed limit',
                    $type->is_accrued ? 'Accrued' : 'Fixed',
                    $this->yesNo($type->is_cumulative),
                    $this->yesNo($type->requires_6_months),
                    $this->yesNo($type->is_monetizable),
                    $this->yesNo($type->requires_attachment),
                    $type->attachment_info ?: 'Not required',
                    $type->document_path ? basename($type->document_path) : '—',
                    $type->is_active ? 'Active' : 'Inactive',
                ]);
            }

            if ($types->isEmpty()) {
                $csv->emptyNotice('No leave types matched the filters above.');
            }

            $csv->summary('Summary of Leave Types', [
                'Total Leave Types:'  => $types->count(),
                'Active:'             => $types->where('is_active', true)->count(),
                'Inactive:'           => $types->where('is_active', false)->count(),
                'Accrued:'            => $types->where('is_accrued', true)->count(),
                'Fixed:'              => $types->where('is_accrued', false)->count(),
                'Monetizable:'        => $types->where('is_monetizable', true)->count(),
                'Require Attachment:' => $types->where('requires_attachment', true)->count(),
            ]);

            $csv->notes(['This is the live configuration the system applies when an employee files a leave application.'], false);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    //  6 · CSC Daily Accrual
    // ─────────────────────────────────────────────────────────────────

    public function accrualRates(Request $request)
    {
        $status    = $request->get('status', 'all');
        $frequency = $request->get('frequency', 'all');

        $rates = LeaveAccrualRate::with('leaveType')
            ->orderBy('is_active', 'desc')
            ->orderBy('leave_type_id')
            ->orderBy('effective_date', 'desc')
            ->get()
            ->filter(function (LeaveAccrualRate $rate) use ($status, $frequency) {
                if ($status !== 'all' && $rate->is_active !== ($status === 'active')) {
                    return false;
                }
                if ($frequency !== 'all' && $rate->accrual_frequency !== $frequency) {
                    return false;
                }

                return true;
            })
            ->values();

        return CsvReportWriter::download($this->fileName('CSC_Daily_Accrual_Rates'), function (CsvReportWriter $csv) use (
            $rates, $status, $frequency
        ) {
            $csv->letterhead(
                'CSC Daily Accrual Configuration',
                'Leave Credit Earning Rates · PRIME HRIS',
                'Configuration as of ' . now()->format('F d, Y')
            );

            $csv->parameters([
                'Status:'            => $status === 'all' ? 'All Status' : ucfirst($status),
                'Accrual Frequency:' => $frequency === 'all' ? 'All Frequencies' : ucfirst($frequency),
            ], $rates->count());

            $csv->columns([
                'No.', 'Leave Code', 'Leave Type', 'Credit Type',
                'Accrual Frequency', 'Days of Service Required', 'Credits Earned Per Period',
                'Effective Date', 'End Date', 'Status', 'Notes',
            ]);

            foreach ($rates as $index => $rate) {
                $type = $rate->leaveType;

                $csv->row([
                    $index + 1,
                    $type->leave_code ?? '—',
                    $type->leave_name ?? '—',
                    ($type && $type->is_accrued) ? 'Accrued' : 'Fixed',
                    ucfirst($rate->accrual_frequency),
                    number_format((float) $rate->days_of_service_required, 2, '.', ''),
                    number_format((float) $rate->credits_earned_per_period, 4, '.', ''),
                    CsvReportWriter::date($rate->effective_date),
                    CsvReportWriter::date($rate->end_date, 'No end date'),
                    $rate->is_active ? 'Active' : 'Inactive',
                    $rate->notes ?: '—',
                ]);
            }

            if ($rates->isEmpty()) {
                $csv->emptyNotice('No accrual rates matched the filters above.');
            }

            $csv->summary('Summary of Accrual Rates', [
                'Total Rates:'         => $rates->count(),
                'Active:'              => $rates->where('is_active', true)->count(),
                'Inactive:'            => $rates->where('is_active', false)->count(),
                'Leave Types Covered:' => $rates->pluck('leave_type_id')->unique()->count(),
            ]);

            $csv->summary('Rates by Frequency', $this->tally(
                $rates->groupBy(fn ($r) => ucfirst($r->accrual_frequency))->map->count()
            ));

            $csv->notes([
                'CSC standard: VL and SL accrue at 1.25 days per month, or 15 days annually.',
                'Daily equivalent: 1.25 ÷ 30 = 0.042 credits per day of service.',
            ], false);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    //  Shared helpers
    // ─────────────────────────────────────────────────────────────────

    /**
     * Numbers are written plain — 1234.50, never "1,234.50".
     *
     * A thousands separator inside a CSV cell is read back by Excel as text,
     * which turns the column somebody wanted to total into one they cannot.
     */
    private function days($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function peso($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function employeeName($employee): string
    {
        if (!$employee) {
            return 'Unknown employee';
        }

        $name = trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : null,
            $employee->last_name,
            $employee->suffix,
        ])));

        return $name !== '' ? $name : 'Unknown employee';
    }

    /** A user who acted on a record, named by their employee row where there is one. */
    private function actorName($user): string
    {
        if (!$user) {
            return 'System';
        }

        if ($user->employee) {
            return $this->employeeName($user->employee);
        }

        return trim((string) $user->name) ?: 'System';
    }

    /** The toolbar says "Disapproved"; the column stores "rejected". */
    private function statusLabel(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'approved'  => 'Approved',
            'pending'   => 'Pending',
            'rejected'  => 'Disapproved',
            'cancelled' => 'Cancelled',
            default     => ucfirst((string) $status) ?: 'Unknown',
        };
    }

    private function yesNo($value): string
    {
        return $value ? 'Yes' : 'No';
    }

    /** A grouped count, rendered as the summary block's "Label: value" rows. */
    private function tally(Collection $counts): array
    {
        return $counts->sortKeys()
            ->mapWithKeys(fn ($value, $key) => [$key . ':' => $value])
            ->all();
    }

    private function employeeLabel(?string $employeeId): string
    {
        if (!$employeeId) {
            return 'All Employees';
        }

        $employee = Employee::find($employeeId);

        return $employee
            ? $employee->employee_id . ' — ' . $this->employeeName($employee)
            : 'Employee #' . $employeeId;
    }

    private function leaveTypeLabel(string $leaveCode): string
    {
        $type = LeaveType::where('leave_code', $leaveCode)->first();

        return $type ? $type->leave_code . ' — ' . $type->leave_name : $leaveCode;
    }

    private function describeRange(?string $from, ?string $to): string
    {
        if (!$from && !$to) {
            return 'All dates';
        }
        if ($from && $to) {
            return Carbon::parse($from)->format('F d, Y') . ' to ' . Carbon::parse($to)->format('F d, Y');
        }
        if ($from) {
            return 'From ' . Carbon::parse($from)->format('F d, Y');
        }

        return 'Up to ' . Carbon::parse($to)->format('F d, Y');
    }

    private function describeTransactionPeriod(?string $from, ?string $to, ?string $year): string
    {
        if ($from && $to) {
            return $this->describeRange($from, $to);
        }
        if ($year) {
            return 'Calendar Year ' . $year;
        }

        return 'All recorded transactions';
    }

    private function describeCreditsPeriod(?string $from, ?string $to, ?string $year): string
    {
        if ($from && $to) {
            return 'as of ' . Carbon::parse($to)->format('F d, Y');
        }
        if ($year) {
            return 'for Calendar Year ' . $year;
        }

        return 'as most recently recorded';
    }
}
