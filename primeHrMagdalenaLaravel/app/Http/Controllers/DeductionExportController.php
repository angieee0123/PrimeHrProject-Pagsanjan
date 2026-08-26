<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Services\CsvReportWriter;
use Illuminate\Http\Request;

/**
 * "Export" on the Deductions & Loans page — one method per tab.
 *
 * Five of the page's six tabs export; the sixth deliberately does not.
 *
 * - **Deduction Types** and **Loan Types** are configuration registries: what
 *   the municipality deducts and on what basis. Neither names anybody, so
 *   `notes()` is told to leave the RA 10173 warning off — a privacy warning
 *   printed on a file carrying no personal data is how a real one stops being
 *   read. Deduction Types had a button wired to nothing; Loan Types had no
 *   button at all.
 * - **Employee Deductions**, **Loans** and **Schedules** carry named records.
 *   All three already exported, but as a bare grid of values with no title, no
 *   municipality, no note of which filters produced them and no totals — and
 *   they ignored the toolbar entirely, so a file exported from a filtered
 *   screen silently contained every row in the system.
 * - **Transactions** has nothing to export. `deduction_transactions` exists
 *   with the right columns but nothing in this system ever writes to it —
 *   `DeductionController::index()` hard-codes `transactions_this_month => 0`
 *   for the same reason. An Export button there would hand out a letterhead
 *   over an empty table, which reads as "no deductions were taken this year"
 *   rather than "this feature is not built yet". Its button is removed until
 *   a payroll run populates the table.
 *
 * Filters are read from the toolbar and printed back into each file's
 * parameter block — every one of them, spelled "All Departments" rather than
 * left blank, so a reader can tell "this covers everything" from "this cell
 * did not get written".
 */
class DeductionExportController extends Controller
{
    /**
     * Deduction Types tab → the registry of what is deducted and how.
     *
     * "How it is computed" is one sentence on screen, assembled from four
     * columns. The file keeps the sentence *and* the four columns behind it:
     * a spreadsheet is where somebody checks whether a rate is right, and
     * "12.00% of monthly salary" cannot be filtered or sorted on.
     */
    public function deductionTypes(Request $request)
    {
        $category = trim((string) $request->get('category'));
        $status   = trim((string) $request->get('status'));

        try {
            $types = DeductionType::with('schedules')
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->filter(function (DeductionType $type) use ($category, $status) {
                    if ($category !== '' && $type->category !== $category) {
                        return false;
                    }

                    // The toolbar sends "1"/"0"; is_active is cast to bool.
                    if ($status !== '' && (string) (int) $type->is_active !== $status) {
                        return false;
                    }

                    return true;
                })
                ->values();

            $fileName = 'Deduction_Types_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($types, $category, $status) {
                $csv->letterhead(
                    'Deduction Types — Configuration Registry',
                    'Human Resource Management Office · PRIME HRIS',
                    'Registry as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Category:' => $category !== '' ? ucfirst(mb_strtolower($category)) : 'All Categories',
                    'Status:'   => $this->activeLabel($status),
                ], $types->count());

                $csv->columns([
                    'No.', 'Code', 'Deduction', 'Category', 'Borne By',
                    'How It Is Computed', 'Computation Type', 'Rate / Fixed Amount',
                    'Base Salary', 'Cutoff Schedule', 'Employees Assigned', 'Status',
                ]);

                foreach ($types as $index => $type) {
                    $assigned = EmployeeDeduction::where('deduction_type_id', $type->id)
                        ->where('status', 'ACTIVE')
                        ->distinct('employee_id')
                        ->count('employee_id');

                    $csv->row([
                        $index + 1,
                        $type->code,
                        $type->name,
                        ucfirst(mb_strtolower((string) $type->category)),
                        $type->deducted_from_employee ? 'Employee' : 'Employer',
                        $this->computationSentence($type),
                        $type->computation_type ?? '',
                        $this->computationFigure($type),
                        // Only a percentage is taken *of* something. Printing
                        // "salary" beside a flat ₱100 says the amount scales
                        // with pay, which is the opposite of what FIXED means.
                        $type->computation_type === 'PERCENTAGE' ? $this->baseSalaryLabel($type) : '',
                        $this->scheduleLabel($type->schedules->first()?->cutoff_schedule),
                        $assigned,
                        $type->is_active ? 'Active' : 'Inactive',
                    ]);
                }

                if ($types->isEmpty()) {
                    $csv->emptyNotice('No deduction types matched the filters above.');
                }

                $csv->summary('Summary', [
                    'Total Deduction Types:'  => $types->count(),
                    'Active:'                 => $types->where('is_active', true)->count(),
                    'Inactive:'               => $types->where('is_active', false)->count(),
                    'Borne by the Employee:'  => $types->where('deducted_from_employee', true)->count(),
                    'Borne by the Employer:'  => $types->where('deducted_from_employee', false)->count(),
                ]);

                $csv->summary('Breakdown by Category',
                    $types->groupBy(fn (DeductionType $t) => ucfirst(mb_strtolower((string) $t->category)))
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->notes([
                    'Borne By states who pays: an Employer share is funded by the municipality and never appears on a payslip.',
                    'Rate / Fixed Amount is blank where the figure is agreed per employee — every loan type works this way.',
                    'Cutoff Schedule is the type\'s default; an employee may carry an override, shown on the Schedules export.',
                    'Employees Assigned counts employees holding this deduction with an ACTIVE assignment.',
                ], containsPersonalData: false);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.deductions')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /** Employee Deductions tab → every deduction assigned to a named employee. */
    public function employeeDeductions(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $type   = trim((string) $request->get('type'));
        $status = trim((string) $request->get('status'));

        try {
            $deductions = EmployeeDeduction::with([
                    'employee.employmentDetail.departmentRelation',
                    'deductionType',
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function (EmployeeDeduction $d) use ($search, $type, $status) {
                    if (!$d->employee || !$d->deductionType) {
                        return false;
                    }

                    if ($type !== '' && $d->deductionType->category !== $type) {
                        return false;
                    }

                    if ($status !== '' && $d->status !== $status) {
                        return false;
                    }

                    if ($search !== '') {
                        $name = mb_strtolower($d->employee->first_name . ' ' . $d->employee->last_name);
                        if (!str_contains($name, mb_strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();

            $fileName = 'Employee_Deductions_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($deductions, $search, $type, $status) {
                $csv->letterhead(
                    'Employee Deductions',
                    'Human Resource Management Office · PRIME HRIS',
                    'Assignments on file as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Deduction Category:' => $type !== '' ? ucfirst(mb_strtolower($type)) : 'All Types',
                    'Assignment Status:'  => $status !== '' ? ucfirst(mb_strtolower($status)) : 'All Status',
                    'Search Term:'        => $search !== '' ? $search : 'None',
                ], $deductions->count());

                $csv->columns([
                    'No.', 'Employee ID', 'Employee Name', 'Department / Office',
                    'Deduction', 'Code', 'Category', 'Borne By',
                    'Rate / Amount', 'Fixed Amount (PHP)', 'Monthly Installment (PHP)',
                    'Total Amount (PHP)', 'Amount Paid (PHP)', 'Remaining Balance (PHP)',
                    'Start Date', 'End Date', 'Status', 'Remarks',
                ]);

                $totals = ['total' => 0.0, 'paid' => 0.0, 'balance' => 0.0];

                foreach ($deductions as $index => $d) {
                    $totalAmount = (float) ($d->total_amount ?? 0);
                    $balance     = (float) ($d->remaining_balance ?? 0);
                    $paid        = max($totalAmount - $balance, 0);

                    $csv->row([
                        $index + 1,
                        $d->employee->employee_id ?? 'N/A',
                        trim($d->employee->first_name . ' ' . $d->employee->last_name),
                        $d->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                        $d->deductionType->name,
                        $d->deductionType->code,
                        ucfirst(mb_strtolower((string) $d->deductionType->category)),
                        $d->deductionType->deducted_from_employee ? 'Employee' : 'Employer',
                        // The screen shows one "Amount/Balance" cell whose
                        // meaning changes with the category. The file keeps
                        // that label *and* splits the figures into columns a
                        // spreadsheet can total — a percentage and a peso
                        // amount cannot share one numeric column.
                        $this->assignedAmountLabel($d),
                        $d->amount !== null ? $this->money($d->amount) : '',
                        $d->installment_amount !== null ? $this->money($d->installment_amount) : '',
                        $d->total_amount !== null ? $this->money($totalAmount) : '',
                        $d->total_amount !== null ? $this->money($paid) : '',
                        $d->remaining_balance !== null ? $this->money($balance) : '',
                        CsvReportWriter::date($d->start_date, ''),
                        CsvReportWriter::date($d->end_date, 'Open-ended'),
                        $d->status,
                        $d->remarks ?? '',
                    ]);

                    $totals['total']   += $totalAmount;
                    $totals['paid']    += $paid;
                    $totals['balance'] += $balance;
                }

                if ($deductions->isEmpty()) {
                    $csv->emptyNotice('No employee deductions matched the filters above.');
                }

                $csv->summary('Summary', [
                    'Assignments in File:'         => $deductions->count(),
                    'Employees Covered:'           => $deductions->pluck('employee_id')->unique()->count(),
                    'Active:'                      => $deductions->where('status', 'ACTIVE')->count(),
                    'Completed:'                   => $deductions->where('status', 'COMPLETED')->count(),
                    'Suspended:'                   => $deductions->where('status', 'SUSPENDED')->count(),
                    'Total Amount Committed (PHP):' => number_format($totals['total'], 2),
                    'Total Amount Paid (PHP):'     => number_format($totals['paid'], 2),
                    'Total Outstanding (PHP):'     => number_format($totals['balance'], 2),
                ]);

                $csv->summary('Breakdown by Category',
                    $deductions->groupBy(fn (EmployeeDeduction $d) => ucfirst(mb_strtolower((string) $d->deductionType->category)))
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->summary('Breakdown by Department / Office',
                    $deductions->groupBy(fn (EmployeeDeduction $d) => $d->employee->employmentDetail->departmentRelation->name ?? 'Unassigned')
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->notes([
                    'Amounts are in Philippine Peso and written unformatted so the columns can be totalled in a spreadsheet.',
                    'Total Amount, Amount Paid and Remaining Balance apply to loans; a mandatory contribution has no balance to run down.',
                    'Amount Paid is derived as Total Amount less Remaining Balance, not stored on the record.',
                    'An End Date of "Open-ended" means the deduction runs until it is completed or stopped.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.deductions')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Loans tab → the loan ledger.
     *
     * A different report from Employee Deductions even though both read
     * `employee_deductions`: this one exists to answer what is still owed and
     * how long it has left to run, so it carries progress, months remaining,
     * and the per-cutoff split — none of which mean anything for a mandatory
     * contribution.
     */
    public function loans(Request $request)
    {
        $search   = trim((string) $request->get('search'));
        $loanType = trim((string) $request->get('loan_type'));
        $status   = trim((string) $request->get('status'));

        try {
            $loans = EmployeeDeduction::with([
                    'employee.employmentDetail.departmentRelation',
                    'deductionType.schedules',
                ])
                ->whereHas('deductionType', fn ($q) => $q->where('category', 'LOAN'))
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function (EmployeeDeduction $loan) use ($search, $loanType, $status) {
                    if (!$loan->employee || !$loan->deductionType) {
                        return false;
                    }

                    // The toolbar's loan-type select carries deduction_type_id.
                    if ($loanType !== '' && (string) $loan->deduction_type_id !== $loanType) {
                        return false;
                    }

                    if ($status !== '' && $loan->status !== $status) {
                        return false;
                    }

                    if ($search !== '') {
                        $name = mb_strtolower($loan->employee->first_name . ' ' . $loan->employee->last_name);
                        if (!str_contains($name, mb_strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();

            $loanTypeName = $loanType !== ''
                ? (DeductionType::find($loanType)->name ?? 'Unknown loan type')
                : 'All Loan Types';

            $fileName = 'Employee_Loans_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($loans, $search, $loanTypeName, $status) {
                $csv->letterhead(
                    'Employee Loans — Outstanding Balances',
                    'Human Resource Management Office · PRIME HRIS',
                    'Balances as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Loan Type:'   => $loanTypeName,
                    'Loan Status:' => $status !== '' ? ucfirst(mb_strtolower($status)) : 'All Status',
                    'Search Term:' => $search !== '' ? $search : 'None',
                ], $loans->count());

                $csv->columns([
                    'No.', 'Employee ID', 'Employee Name', 'Department / Office',
                    'Loan Type', 'Provider', 'Total Amount (PHP)', 'Amount Paid (PHP)',
                    'Remaining Balance (PHP)', 'Progress (%)', 'Monthly Installment (PHP)',
                    'Cutoff Schedule', '1st Cutoff Amount (PHP)', '2nd Cutoff Amount (PHP)',
                    'Months Remaining', 'Start Date', 'End Date', 'Status', 'Remarks',
                ]);

                $totals = ['total' => 0.0, 'paid' => 0.0, 'balance' => 0.0, 'installment' => 0.0];

                foreach ($loans as $index => $loan) {
                    $totalAmount = (float) ($loan->total_amount ?? 0);
                    $balance     = (float) ($loan->remaining_balance ?? 0);
                    $paid        = max($totalAmount - $balance, 0);
                    $progress    = $totalAmount > 0 ? ($paid / $totalAmount) * 100 : 0;
                    $installment = (float) ($loan->installment_amount ?? 0);
                    $months      = $installment > 0 ? (int) ceil($balance / $installment) : 0;

                    $schedule = $loan->custom_cutoff_schedule
                        ?? $loan->deductionType->schedules->first()?->cutoff_schedule
                        ?? 'BOTH_SPLIT';

                    [$first, $second] = $this->cutoffSplit($installment, $schedule);

                    $csv->row([
                        $index + 1,
                        $loan->employee->employee_id ?? 'N/A',
                        trim($loan->employee->first_name . ' ' . $loan->employee->last_name),
                        $loan->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                        $loan->deductionType->name,
                        $this->providerFor($loan->deductionType->code),
                        $this->money($totalAmount),
                        $this->money($paid),
                        $this->money($balance),
                        number_format($progress, 2, '.', ''),
                        $this->money($installment),
                        $this->scheduleLabel($schedule),
                        $this->money($first),
                        $this->money($second),
                        $months,
                        CsvReportWriter::date($loan->start_date, ''),
                        CsvReportWriter::date($loan->end_date, 'Open-ended'),
                        $loan->status,
                        $loan->remarks ?? '',
                    ]);

                    $totals['total']       += $totalAmount;
                    $totals['paid']        += $paid;
                    $totals['balance']     += $balance;
                    $totals['installment'] += $loan->status === 'ACTIVE' ? $installment : 0;
                }

                if ($loans->isEmpty()) {
                    $csv->emptyNotice('No loans matched the filters above.');
                }

                $active     = $loans->where('status', 'ACTIVE');
                $fullyPaid  = $loans->filter(fn ($l) => (float) ($l->remaining_balance ?? 0) <= 0);

                $csv->summary('Summary', [
                    'Loans in File:'                    => $loans->count(),
                    'Employees Covered:'                => $loans->pluck('employee_id')->unique()->count(),
                    'Active Loans:'                     => $active->count(),
                    'Completed Loans:'                  => $loans->where('status', 'COMPLETED')->count(),
                    'Suspended Loans:'                  => $loans->where('status', 'SUSPENDED')->count(),
                    'Fully Paid (zero balance):'        => $fullyPaid->count(),
                    'Total Loaned (PHP):'               => number_format($totals['total'], 2),
                    'Total Repaid (PHP):'               => number_format($totals['paid'], 2),
                    'Total Outstanding (PHP):'          => number_format($totals['balance'], 2),
                    'Monthly Collection, Active (PHP):' => number_format($totals['installment'], 2),
                ]);

                $csv->summary('Outstanding by Provider (PHP)',
                    $loans->groupBy(fn (EmployeeDeduction $l) => $this->providerFor($l->deductionType->code))
                        ->map(fn ($rows) => $rows->sum(fn ($l) => (float) ($l->remaining_balance ?? 0)))
                        ->sortDesc()
                        ->mapWithKeys(fn ($sum, $label) => [$label . ':' => number_format($sum, 2)])
                        ->all()
                );

                $csv->summary('Outstanding by Department / Office (PHP)',
                    $loans->groupBy(fn (EmployeeDeduction $l) => $l->employee->employmentDetail->departmentRelation->name ?? 'Unassigned')
                        ->map(fn ($rows) => $rows->sum(fn ($l) => (float) ($l->remaining_balance ?? 0)))
                        ->sortDesc()
                        ->mapWithKeys(fn ($sum, $label) => [$label . ':' => number_format($sum, 2)])
                        ->all()
                );

                $csv->notes([
                    'Amounts are in Philippine Peso and written unformatted so the columns can be totalled in a spreadsheet.',
                    'Amount Paid and Progress are derived from Total Amount less Remaining Balance, not stored on the record.',
                    'Months Remaining is the balance divided by the monthly installment, rounded up.',
                    'Provider is inferred from the loan type\'s code — there is no provider column on the deduction type.',
                    'The cutoff columns show how the monthly installment is split across the two payroll cutoffs, including any per-employee override.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.deductions')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Schedules tab → when each employee's deductions are taken.
     *
     * One row per deduction rather than the one row per *employee* the screen
     * shows. The screen's row is a summary with a "Manage Schedule" button
     * behind it; exporting that summary would produce a file saying an
     * employee has four deductions without saying when any of them is taken,
     * which is the only thing this tab is about.
     */
    public function schedules(Request $request)
    {
        $search     = trim((string) $request->get('search'));
        $department = trim((string) $request->get('department'));

        try {
            $employees = Employee::with([
                    'employmentDetail.departmentRelation',
                    'deductions' => fn ($q) => $q->where('status', 'ACTIVE')->with('deductionType.schedules'),
                ])
                ->whereHas('deductions', fn ($q) => $q->where('status', 'ACTIVE'))
                ->orderBy('last_name')
                ->get()
                ->filter(function (Employee $employee) use ($search, $department) {
                    $employeeDept = $employee->employmentDetail->departmentRelation->name ?? 'N/A';

                    if ($department !== '' && $employeeDept !== $department) {
                        return false;
                    }

                    if ($search !== '') {
                        $name = mb_strtolower($employee->first_name . ' ' . $employee->last_name);
                        if (!str_contains($name, mb_strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();

            // Flattened before the file is opened, because `parameters()`
            // prints the record count in the header and this file's record is
            // one *deduction*, not one employee. Counting employees there
            // would print "Total Records: 4" above sixteen rows.
            $rows = [];

            foreach ($employees as $employee) {
                foreach ($employee->deductions as $deduction) {
                    $type = $deduction->deductionType;

                    // Employer shares are not taken from the employee, so
                    // there is no cutoff on which they are deducted.
                    if (!$type || !$type->deducted_from_employee) {
                        continue;
                    }

                    $default  = $type->schedules->first()?->cutoff_schedule;
                    $schedule = $deduction->custom_cutoff_schedule ?: ($default ?: 'BOTH_SPLIT');

                    $rows[] = [
                        'employee'  => $employee,
                        'deduction' => $deduction,
                        'type'      => $type,
                        'schedule'  => $schedule,
                        'isCustom'  => (bool) $deduction->custom_cutoff_schedule,
                    ];
                }
            }

            $fileName = 'Deduction_Schedules_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($rows, $employees, $search, $department) {
                $csv->letterhead(
                    'Deduction Schedules — Cutoff Assignments',
                    'Human Resource Management Office · PRIME HRIS',
                    'Schedules in force as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Department / Office:' => $department !== '' ? $department : 'All Departments',
                    'Search Term:'         => $search !== '' ? $search : 'None',
                    'Deduction Status:'    => 'Active only',
                ], count($rows));

                $csv->columns([
                    'No.', 'Employee ID', 'Employee Name', 'Department / Office',
                    'Deduction', 'Code', 'Category', 'Amount', 'Cutoff Schedule',
                    'When It Is Taken', 'Schedule Source', 'Status',
                ]);

                $rowNum     = 0;
                $custom     = 0;
                $bySchedule = [];

                foreach ($rows as $row) {
                    ['employee' => $employee, 'deduction' => $deduction, 'type' => $type,
                     'schedule' => $schedule, 'isCustom' => $isCustom] = $row;

                    if ($isCustom) {
                        $custom++;
                    }

                    $label = $this->scheduleLabel($schedule);
                    $bySchedule[$label] = ($bySchedule[$label] ?? 0) + 1;

                    $csv->row([
                        ++$rowNum,
                        $employee->employee_id ?? 'N/A',
                        trim($employee->first_name . ' ' . $employee->last_name),
                        $employee->employmentDetail->departmentRelation->name ?? 'N/A',
                        $type->name,
                        $type->code,
                        ucfirst(mb_strtolower((string) $type->category)),
                        $this->assignedAmountLabel($deduction),
                        $label,
                        $this->scheduleExplanation($schedule),
                        $isCustom ? 'Custom (per employee)' : 'Default (deduction type)',
                        $deduction->status,
                    ]);
                }

                if ($rowNum === 0) {
                    $csv->emptyNotice('No active employee-borne deductions matched the filters above.');
                }

                $csv->summary('Summary', [
                    'Scheduled Deductions:'      => $rowNum,
                    'Employees Covered:'         => $employees->count(),
                    'On a Per-Employee Override:' => $custom,
                    'On the Type\'s Default:'     => $rowNum - $custom,
                ]);

                $csv->summary('Breakdown by Cutoff Schedule',
                    collect($bySchedule)->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->notes([
                    'Only ACTIVE deductions borne by the employee are listed — an employer share is not taken on any cutoff.',
                    'Schedule Source says whether the cutoff comes from the deduction type\'s default or an override set for that employee.',
                    'Split across both cutoffs means half the monthly amount is taken on each of the two payroll runs.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.deductions')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Loan Types tab → the registry of loan products that can be assigned.
     *
     * A registry, not a ledger: it says which loans exist and how many people
     * hold each, never who they are. Like the Deduction Types export, it names
     * nobody and so carries no privacy warning.
     */
    public function loanTypes(Request $request)
    {
        $search   = trim((string) $request->get('search'));
        $provider = trim((string) $request->get('provider'));
        $status   = trim((string) $request->get('status'));

        try {
            $loanTypes = DeductionType::with('schedules')
                ->where('category', 'LOAN')
                ->orderBy('is_active', 'desc')
                ->orderBy('name')
                ->get()
                ->filter(function (DeductionType $type) use ($search, $provider, $status) {
                    if ($provider !== '' && $this->providerKey($type->code) !== $provider) {
                        return false;
                    }

                    if ($status !== '' && (string) (int) $type->is_active !== $status) {
                        return false;
                    }

                    if ($search !== '' && !str_contains(mb_strtolower($type->name), mb_strtolower($search))) {
                        return false;
                    }

                    return true;
                })
                ->values();

            $fileName = 'Loan_Types_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($loanTypes, $search, $provider, $status) {
                $csv->letterhead(
                    'Loan Type Registry',
                    'Human Resource Management Office · PRIME HRIS',
                    'Registry as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Provider:'    => $provider !== '' ? $this->providerLabel($provider) : 'All Providers',
                    'Status:'      => $this->activeLabel($status),
                    'Search Term:' => $search !== '' ? $search : 'None',
                ], $loanTypes->count());

                $csv->columns([
                    'No.', 'Code', 'Loan Type', 'Provider', 'Interest Rate (%)',
                    'Maximum Loanable (PHP)', 'Default Cutoff Schedule',
                    'Employees Holding', 'Total Loaned (PHP)', 'Total Outstanding (PHP)', 'Status',
                ]);

                $grandLoaned = 0.0;
                $grandOut    = 0.0;

                foreach ($loanTypes as $index => $type) {
                    $assignments = EmployeeDeduction::where('deduction_type_id', $type->id)->get();
                    $active      = $assignments->where('status', 'ACTIVE');

                    $loaned      = (float) $assignments->sum(fn ($a) => (float) ($a->total_amount ?? 0));
                    $outstanding = (float) $active->sum(fn ($a) => (float) ($a->remaining_balance ?? 0));

                    $grandLoaned += $loaned;
                    $grandOut    += $outstanding;

                    $csv->row([
                        $index + 1,
                        $type->code,
                        $type->name,
                        $this->providerFor($type->code),
                        // Left blank rather than filled with "N/A": these are
                        // null on every loan type in this deployment, and a
                        // blank numeric cell is what a spreadsheet can handle.
                        $type->percentage_rate !== null ? number_format((float) $type->percentage_rate, 2, '.', '') : '',
                        $type->max_amount !== null ? $this->money($type->max_amount) : '',
                        $this->scheduleLabel($type->schedules->first()?->cutoff_schedule),
                        $active->pluck('employee_id')->unique()->count(),
                        $this->money($loaned),
                        $this->money($outstanding),
                        $type->is_active ? 'Active' : 'Inactive',
                    ]);
                }

                if ($loanTypes->isEmpty()) {
                    $csv->emptyNotice('No loan types matched the filters above.');
                }

                $csv->summary('Summary', [
                    'Loan Types in File:'      => $loanTypes->count(),
                    'Active:'                  => $loanTypes->where('is_active', true)->count(),
                    'Inactive:'                => $loanTypes->where('is_active', false)->count(),
                    'Total Loaned (PHP):'      => number_format($grandLoaned, 2),
                    'Total Outstanding (PHP):' => number_format($grandOut, 2),
                ]);

                $csv->summary('Breakdown by Provider',
                    $loanTypes->groupBy(fn (DeductionType $t) => $this->providerFor($t->code))
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->notes([
                    'Amounts are in Philippine Peso and written unformatted so the columns can be totalled in a spreadsheet.',
                    'Interest Rate and Maximum Loanable are blank where the loan type does not set them — the amount and terms are agreed per employee.',
                    'Employees Holding counts distinct employees with an ACTIVE loan of this type; Total Loaned covers every assignment ever made.',
                    'Provider is inferred from the code — there is no provider column on the deduction type.',
                ], containsPersonalData: false);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.deductions')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    // ── Shared vocabulary ────────────────────────────────────────────────
    //
    // These labels appear in more than one of the five files above. Written
    // once so the Loans export and the Schedules export cannot describe the
    // same cutoff differently.

    /** A peso figure a spreadsheet can add up. */
    private function money($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /** "Active" / "Inactive" / "All Status" from the toolbar's "1"/"0"/"". */
    private function activeLabel(string $status): string
    {
        return match ($status) {
            '1'     => 'Active',
            '0'     => 'Inactive',
            default => 'All Status',
        };
    }

    /** The enum the schedule is stored as, in words. */
    private function scheduleLabel(?string $schedule): string
    {
        return match ($schedule) {
            '1ST_ONLY'   => '1st cutoff only',
            '2ND_ONLY'   => '2nd cutoff only',
            'BOTH_FULL'  => 'Both cutoffs, full amount',
            'BOTH_SPLIT' => 'Split across both cutoffs',
            null, ''     => 'Not set',
            default      => $schedule,
        };
    }

    /** What that schedule means for a payroll run. */
    private function scheduleExplanation(?string $schedule): string
    {
        return match ($schedule) {
            '1ST_ONLY'   => 'Taken in full on the 1st cutoff; nothing on the 2nd.',
            '2ND_ONLY'   => 'Taken in full on the 2nd cutoff; nothing on the 1st.',
            'BOTH_FULL'  => 'The full amount is taken on each cutoff.',
            'BOTH_SPLIT' => 'Half the monthly amount is taken on each cutoff.',
            default      => 'Falls back to being split across both cutoffs.',
        };
    }

    /** How a monthly installment lands across the two cutoffs. */
    private function cutoffSplit(float $installment, string $schedule): array
    {
        return match ($schedule) {
            '1ST_ONLY'  => [$installment, 0.0],
            '2ND_ONLY'  => [0.0, $installment],
            'BOTH_FULL' => [$installment, $installment],
            default     => [$installment / 2, $installment / 2],
        };
    }

    /** Provider inferred from the code — there is no provider column. */
    private function providerFor(?string $code): string
    {
        return $this->providerLabel($this->providerKey($code));
    }

    private function providerKey(?string $code): string
    {
        $code = mb_strtoupper((string) $code);

        if (str_contains($code, 'GSIS')) {
            return 'GSIS';
        }

        if (str_contains($code, 'PAGIBIG') || str_contains($code, 'PAG-IBIG')) {
            return 'PAG-IBIG';
        }

        return 'OTHER';
    }

    private function providerLabel(string $key): string
    {
        return match ($key) {
            'GSIS'     => 'GSIS',
            'PAG-IBIG' => 'Pag-IBIG',
            default    => 'Other',
        };
    }

    /**
     * The "Amount/Balance" cell as the page words it — a loan states its
     * installment, a percentage type states its rate, everything else states
     * the amount agreed on the assignment.
     */
    private function assignedAmountLabel(EmployeeDeduction $deduction): string
    {
        $type = $deduction->deductionType;

        if ($type->category === 'LOAN') {
            return number_format((float) ($deduction->installment_amount ?? 0), 2) . ' per month';
        }

        if ($type->computation_type === 'PERCENTAGE' && $type->percentage_rate !== null) {
            return number_format((float) $type->percentage_rate, 2) . '% of ' . $this->baseSalaryLabel($type);
        }

        if ($deduction->amount !== null) {
            return number_format((float) $deduction->amount, 2) . ' fixed';
        }

        return 'Set per employee';
    }

    /** "How it is computed", the sentence the Deduction Types table shows. */
    private function computationSentence(DeductionType $type): string
    {
        if ($type->computation_type === 'PERCENTAGE' && $type->percentage_rate) {
            return number_format((float) $type->percentage_rate, 2) . '% of ' . $this->baseSalaryLabel($type);
        }

        if ($type->max_amount) {
            return number_format((float) $type->max_amount, 2) . ' fixed amount';
        }

        // FIXED with no amount on the type: the figure lives on each
        // employee's assignment, which is true of every loan here.
        return $type->category === 'LOAN'
            ? 'Set per employee — amount agreed per loan'
            : 'Set per employee — entered on assignment';
    }

    /** The figure behind that sentence, as a number a column can total. */
    private function computationFigure(DeductionType $type): string
    {
        if ($type->computation_type === 'PERCENTAGE' && $type->percentage_rate !== null) {
            return number_format((float) $type->percentage_rate, 2, '.', '');
        }

        if ($type->max_amount !== null) {
            return $this->money($type->max_amount);
        }

        return '';
    }

    private function baseSalaryLabel(DeductionType $type): string
    {
        return match ($type->base_salary_type) {
            'MONTHLY' => 'monthly salary',
            'DAILY'   => 'daily rate',
            'ANNUAL'  => 'annual salary',
            'BASIC'   => 'basic pay',
            'GROSS'   => 'gross pay',
            null, ''  => 'salary',
            default   => 'salary',
        };
    }
}
