<?php

namespace App\Http\Controllers;

use App\Models\DailySalaryComputation;
use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\SalaryComputation;
use App\Services\CsvReportWriter;
use App\Services\PayrollRegisterService;
use Illuminate\Http\Request;

/**
 * "Export" on the Payroll page — one method per tab, the same rule the
 * Departments, Leave & Benefits, Travel Order and Pass Slip exports follow.
 *
 * Three files, because the page answers three different questions:
 *
 * - **Payroll Register** — what the current filters cover. This button used to
 *   be rendered, styled, clickable and wired to nothing at all.
 * - **Payslip Management** — the payslips already generated and where each one
 *   stands in the approval queue.
 * - **Generate Payroll** — the run that was just computed, as a disbursement
 *   register with a column per deduction type and a totals line to check a
 *   printout against.
 *
 * The last two existed but handed out a bare grid of values. The register run
 * printed a letterhead reading "MUNICIPAL GOVERNMENT OF PAGSANJAN" — a literal,
 * in a Magdalena deployment, which is exactly the drift `CsvReportWriter`
 * exists to end. The office identity is now read from `SiteContentService`
 * like every other export's.
 *
 * A note on the money columns: every peso figure is written as a plain number
 * (`12345.67`), never "₱12,345.67". The column header carries the currency, so
 * a spreadsheet can total the column instead of reading it as text — which is
 * the first thing anyone opening a payroll register tries to do.
 */
class PayrollExportController extends Controller
{
    /**
     * Payroll Register tab → what the toolbar filters currently cover.
     *
     * The endpoint re-runs the register server-side through
     * `PayrollRegisterService`, the same object the page renders from. It
     * never scrapes the table on screen, which is what would cap the file at
     * the seven columns the register deliberately shows — the file carries the
     * full breakdown behind each of those figures, which is the reason to
     * export it rather than screenshot it.
     */
    public function register(Request $request, PayrollRegisterService $registerService)
    {
        try {
            $filters = [
                'start_date'        => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
                'end_date'          => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
                'department'        => $request->input('department'),
                'employment_status' => $request->input('employment_status'),
                'employee_name'     => $request->input('employee_name'),
                'status'            => $request->input('status'),
                'view_mode'         => $request->input('view_mode', 'daily'),
            ];

            $register  = $registerService->build($filters);
            $records   = $register['records'];
            $codes     = $register['deductionTypes'];
            $startDate = $register['start_date'];
            $endDate   = $register['end_date'];
            $viewMode  = $register['view_mode'];

            $typeNames = DeductionType::whereIn('code', $codes)->pluck('name', 'code');

            $viewLabel = match ($viewMode) {
                'employee' => 'By Employee (one row per employee)',
                'monthly'  => 'Monthly Summary (one row per employee)',
                default    => 'Daily (one row per employee per day)',
            };

            $fileName = 'Payroll_Register_'
                . date('M_d_Y', strtotime($startDate)) . '_to_'
                . date('M_d_Y', strtotime($endDate)) . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $records, $codes, $typeNames, $startDate, $endDate, $viewMode, $viewLabel, $filters
            ) {
                $csv->letterhead(
                    'Payroll Register',
                    'Human Resource Management Office · PRIME HRIS',
                    date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate))
                );

                $csv->parameters([
                    'Period Covered:'      => date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate)),
                    'Cutoff:'              => (int) date('d', strtotime($startDate)) <= 15 ? '1st Cutoff' : '2nd Cutoff',
                    'Pay Date:'            => date('F d, Y', strtotime($endDate)),
                    'Employee:'            => $filters['employee_name'] ?: 'All Employees',
                    'Department / Office:' => $filters['department'] ?: 'All Departments',
                    'Employment Type:'     => $filters['employment_status'] ?: 'All Employment Types',
                    'Record Status:'       => $filters['status'] ?: 'All Status',
                    'View:'                => $viewLabel,
                ], $records->count());

                // The daily view identifies a row by its date; the employee
                // and monthly views by how many days it sums. Printing both
                // would leave one of them blank on every row.
                $columns = ['No.', 'Employee ID', 'Employee Name', 'Position', 'Department / Office'];
                $columns[] = $viewMode === 'daily' ? 'Work Date' : 'Days Worked';
                $columns[] = 'Daily Rate (PHP)';
                $columns[] = 'Basic Pay (PHP)';
                $columns[] = 'Overtime Pay (PHP)';
                $columns[] = 'Gross Pay (PHP)';
                $columns[] = 'Late Deduction (PHP)';
                $columns[] = 'Undertime Deduction (PHP)';

                foreach ($codes as $code) {
                    $columns[] = ($typeNames[$code] ?? $code) . ' (PHP)';
                }

                $columns[] = 'Total Deductions (PHP)';
                $columns[] = 'Net Pay (PHP)';
                $columns[] = 'Status';

                $csv->columns($columns);

                $totals = [
                    'basic'      => 0.0,
                    'ot'         => 0.0,
                    'gross'      => 0.0,
                    'late'       => 0.0,
                    'undertime'  => 0.0,
                    'deductions' => 0.0,
                    'net'        => 0.0,
                    'days'       => 0,
                    'byCode'     => array_fill_keys($codes->all(), 0.0),
                ];

                foreach ($records as $index => $record) {
                    $gross          = (float) $record['basic'] + (float) $record['ot_pay'];
                    $otherDeduction = array_sum($record['deductions'] ?? []);
                    $deductionTotal = (float) $record['late_deduction'] + (float) $record['undertime_deduction'] + $otherDeduction;
                    $net            = $gross - $deductionTotal;
                    $days           = $record['days_count'] ?? 1;

                    $row = [
                        $index + 1,
                        $record['id'],
                        $record['name'],
                        $record['position'],
                        $record['dept'],
                        $viewMode === 'daily'
                            ? ($record['work_date'] ? date('Y-m-d', strtotime($record['work_date'])) : '')
                            : $days,
                        $this->money($record['daily_rate']),
                        $this->money($record['basic']),
                        $this->money($record['ot_pay']),
                        $this->money($gross),
                        $this->money($record['late_deduction']),
                        $this->money($record['undertime_deduction']),
                    ];

                    foreach ($codes as $code) {
                        $amount = $record['deductions'][$code] ?? 0;
                        $row[] = $this->money($amount);
                        $totals['byCode'][$code] += (float) $amount;
                    }

                    $row[] = $this->money($deductionTotal);
                    $row[] = $this->money($net);
                    $row[] = $record['status'];

                    $csv->row($row);

                    $totals['basic']     += (float) $record['basic'];
                    $totals['ot']        += (float) $record['ot_pay'];
                    $totals['gross']     += $gross;
                    $totals['late']      += (float) $record['late_deduction'];
                    $totals['undertime'] += (float) $record['undertime_deduction'];
                    $totals['deductions'] += $deductionTotal;
                    $totals['net']       += $net;
                    $totals['days']      += (int) $days;
                }

                if ($records->isEmpty()) {
                    $csv->emptyNotice('No payroll records matched the filters above. Payroll must be generated for the period before it appears here.');
                }

                // A totals line sitting directly under the table, in the
                // table's own columns — a register is checked column by
                // column against a printout, and a summary block below it
                // cannot be read that way.
                if ($records->isNotEmpty()) {
                    $totalRow = ['', '', 'TOTAL', '', '', $viewMode === 'daily' ? '' : $totals['days'], ''];
                    $totalRow[] = $this->money($totals['basic']);
                    $totalRow[] = $this->money($totals['ot']);
                    $totalRow[] = $this->money($totals['gross']);
                    $totalRow[] = $this->money($totals['late']);
                    $totalRow[] = $this->money($totals['undertime']);

                    foreach ($codes as $code) {
                        $totalRow[] = $this->money($totals['byCode'][$code]);
                    }

                    $totalRow[] = $this->money($totals['deductions']);
                    $totalRow[] = $this->money($totals['net']);
                    $totalRow[] = '';

                    $csv->row($totalRow);
                }

                $csv->summary('Payroll Summary', [
                    'Records in File:'                => $records->count(),
                    'Employees Covered:'              => $records->pluck('id')->unique()->count(),
                    'Total Days Paid:'                => $totals['days'],
                    'Total Basic Pay (PHP):'          => number_format($totals['basic'], 2),
                    'Total Overtime Pay (PHP):'       => number_format($totals['ot'], 2),
                    'Gross Payroll (PHP):'            => number_format($totals['gross'], 2),
                    'Total Late Deduction (PHP):'     => number_format($totals['late'], 2),
                    'Total Undertime Deduction (PHP):' => number_format($totals['undertime'], 2),
                    'Total Deductions (PHP):'         => number_format($totals['deductions'], 2),
                    'Total Net Pay (PHP):'            => number_format($totals['net'], 2),
                    'Processed Records:'              => $records->where('status', 'Processed')->count(),
                    'Pending Records:'                => $records->where('status', 'Pending')->count(),
                ]);

                $csv->summary('Deductions by Type (PHP)',
                    $codes->mapWithKeys(fn ($code) => [
                        ($typeNames[$code] ?? $code) . ':' => number_format($totals['byCode'][$code], 2),
                    ])->all()
                );

                $csv->summary('Breakdown by Department / Office (Net Pay, PHP)',
                    $records->groupBy('dept')
                        ->map(fn ($rows) => $rows->sum(fn ($r) => (float) $r['basic'] + (float) $r['ot_pay']
                            - (float) $r['late_deduction'] - (float) $r['undertime_deduction'] - array_sum($r['deductions'] ?? [])))
                        ->sortDesc()
                        ->mapWithKeys(fn ($net, $dept) => [$dept . ':' => number_format($net, 2)])
                        ->all()
                );

                $csv->notes([
                    'Amounts are in Philippine Peso and written unformatted so the columns can be totalled in a spreadsheet.',
                    'Gross Pay is Basic Pay plus Overtime Pay. Net Pay is Gross Pay less every deduction column in this file.',
                    'Only deductions borne by the employee are shown; employer and government shares are excluded.',
                    'Deduction amounts reflect the cutoff schedule in force for each employee, including any per-employee override set under Deductions > Schedules.',
                    'A record reads Pending until payroll has computed gross pay for every day it covers.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.payroll')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Payslip Management tab → the generated payslips and their approval state.
     *
     * A different subject from the register: the register is a period's
     * computation, this is the queue of documents produced from it, which is
     * why "Status" here means draft/approved/rejected rather than
     * processed/pending.
     */
    public function payslips(Request $request)
    {
        try {
            $status = trim((string) $request->input('status'));

            $query = SalaryComputation::with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation',
            // The same two keys `PayrollController::index()` paginates by, so
            // the file lists the payslips in the order the tab shows them.
            ])->orderBy('period_end', 'desc')->orderBy('created_at', 'desc');

            // "Pending" on screen means pending *or* draft: `status` defaults to
            // 'draft' on this table, and `filterPayslips()` normalises 'draft'
            // to 'pending' before comparing, so the Pending/Draft option shows
            // both. A bare `where('status', 'pending')` here therefore exported
            // none of the rows the user was looking at — under a parameter block
            // that named itself "Pending / Draft", so the file claimed a
            // coverage it did not have. The export follows the screen's rule.
            if ($status !== '') {
                $query->whereIn('status', $this->statusesFor($status));
            }

            $computations = $query->get();

            $statusLabel = match (mb_strtolower($status)) {
                ''         => 'All Status',
                'pending'  => 'Pending / Draft',
                default    => ucfirst($status),
            };

            $fileName = 'Payslips_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use ($computations, $statusLabel) {
                $periodFrom = $computations->min('period_start');
                $periodTo   = $computations->max('period_end');

                $csv->letterhead(
                    'Payslip Register',
                    'Human Resource Management Office · PRIME HRIS',
                    $computations->isNotEmpty()
                        ? 'Payslips covering ' . $periodFrom->format('F d, Y') . ' to ' . $periodTo->format('F d, Y')
                        : 'Payslips on file as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Payslip Status:'  => $statusLabel,
                    'Earliest Period:' => $computations->isNotEmpty() ? $periodFrom->format('F d, Y') : '—',
                    'Latest Period:'   => $computations->isNotEmpty() ? $periodTo->format('F d, Y') : '—',
                ], $computations->count());

                $csv->columns([
                    'No.', 'Employee ID', 'Employee Name', 'Department / Office', 'Position',
                    'Period Start', 'Period End', 'Basic Pay (PHP)', 'Overtime Pay (PHP)',
                    'Late Deduction (PHP)', 'Undertime Deduction (PHP)', 'Other Deductions (PHP)',
                    'Gross Pay (PHP)', 'Total Deductions (PHP)', 'Net Pay (PHP)', 'Payslip Status',
                ]);

                foreach ($computations as $index => $comp) {
                    $totalDeductions = (float) $comp->late_deduction
                        + (float) $comp->undertime_deduction
                        + (float) $comp->other_deductions;

                    $csv->row([
                        $index + 1,
                        $comp->employee->employee_id ?? 'N/A',
                        trim(($comp->employee->first_name ?? '') . ' ' . ($comp->employee->last_name ?? '')),
                        $comp->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                        $comp->employee->employmentDetail->designationRelation->title ?? 'N/A',
                        CsvReportWriter::date($comp->period_start),
                        CsvReportWriter::date($comp->period_end),
                        $this->money($comp->basic_pay),
                        $this->money($comp->ot_pay),
                        $this->money($comp->late_deduction),
                        $this->money($comp->undertime_deduction),
                        $this->money($comp->other_deductions),
                        $this->money($comp->gross_pay),
                        $this->money($totalDeductions),
                        $this->money($comp->net_pay),
                        ucfirst((string) $comp->status),
                    ]);
                }

                if ($computations->isEmpty()) {
                    $csv->emptyNotice('No payslips matched the filter above.');
                }

                $csv->summary('Summary', [
                    'Payslips in File:'         => $computations->count(),
                    'Employees Covered:'        => $computations->pluck('employee_id')->unique()->count(),
                    'Total Basic Pay (PHP):'    => number_format((float) $computations->sum('basic_pay'), 2),
                    'Total Overtime Pay (PHP):' => number_format((float) $computations->sum('ot_pay'), 2),
                    'Total Gross Pay (PHP):'    => number_format((float) $computations->sum('gross_pay'), 2),
                    'Total Deductions (PHP):'   => number_format(
                        (float) $computations->sum('late_deduction')
                        + (float) $computations->sum('undertime_deduction')
                        + (float) $computations->sum('other_deductions'), 2),
                    'Total Net Pay (PHP):'      => number_format((float) $computations->sum('net_pay'), 2),
                ]);

                $csv->summary('Breakdown by Payslip Status',
                    $computations->groupBy(fn ($c) => ucfirst((string) $c->status))
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->notes([
                    'Amounts are in Philippine Peso and written unformatted so the columns can be totalled in a spreadsheet.',
                    'Payslip Status is the approval state of the document, not whether payroll has been computed.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.payroll', ['tab' => 'payslips'])->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate Payroll tab → the run that was just computed, as a
     * disbursement register.
     *
     * Unlike `register()`, this covers every employee the generation filters
     * selected — including anyone whose period produced no computed day, who
     * is listed with the zeroes that say so rather than dropped silently.
     */
    public function generated(Request $request)
    {
        try {
            $startDate        = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate          = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $payDate          = $request->input('pay_date') ?: $endDate;
            $department       = $request->input('department');
            $employmentStatus = $request->input('employment_status');

            $employeesQuery = Employee::with([
                'employmentDetail.departmentRelation',
                'employmentDetail.designationRelation',
                'deductions' => function ($q) use ($endDate) {
                    $q->where('status', 'ACTIVE')
                      ->where('start_date', '<=', $endDate)
                      ->where(function ($query) use ($endDate) {
                          $query->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
                      })
                      ->with('deductionType');
                },
            ])->orderBy('last_name')->orderBy('first_name');

            if ($department) {
                $employeesQuery->whereHas('employmentDetail.departmentRelation', function ($q) use ($department) {
                    $q->where('name', $department);
                });
            }

            if ($employmentStatus) {
                $employeesQuery->whereHas('employmentDetail', function ($q) use ($employmentStatus) {
                    $q->where('employment_status', $employmentStatus);
                });
            }

            $employees = $employeesQuery->get();

            // The deduction columns this run needs — derived from the
            // employees selected, so a run covering one office does not print
            // an empty column for a loan nobody in it carries.
            $codes = [];
            $names = [];
            foreach ($employees as $employee) {
                foreach ($employee->deductions as $deduction) {
                    $type = $deduction->deductionType;

                    // Employer and government shares never come off a payslip.
                    if (!$type || !$type->deducted_from_employee) {
                        continue;
                    }

                    if (!in_array($type->code, $codes, true)) {
                        $codes[] = $type->code;
                        $names[$type->code] = $type->name;
                    }
                }
            }

            $fileName = 'Payroll_Run_'
                . date('M_d_Y', strtotime($startDate)) . '_to_'
                . date('M_d_Y', strtotime($endDate)) . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $employees, $codes, $names, $startDate, $endDate, $payDate, $department, $employmentStatus
            ) {
                $csv->letterhead(
                    'Payroll Register — Generated Run',
                    'Human Resource Management Office · PRIME HRIS',
                    date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate))
                );

                $csv->parameters([
                    'Period Covered:'      => date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate)),
                    'Cutoff:'              => (int) date('d', strtotime($startDate)) <= 15 ? '1st Cutoff' : '2nd Cutoff',
                    'Pay Date:'            => date('F d, Y', strtotime($payDate)),
                    'Department / Office:' => $department ?: 'All Departments',
                    'Employment Type:'     => $employmentStatus ?: 'All Employment Types',
                ]);

                $columns = [
                    'No.', 'Employee ID', 'Employee Name', 'Position', 'Department / Office',
                    'Days Worked', 'Daily Rate (PHP)', 'Basic Pay (PHP)', 'Overtime Pay (PHP)',
                    'Gross Pay (PHP)', 'Late Deduction (PHP)', 'Undertime Deduction (PHP)',
                ];

                foreach ($codes as $code) {
                    $columns[] = ($names[$code] ?? $code) . ' (PHP)';
                }

                $columns[] = 'Total Deductions (PHP)';
                $columns[] = 'Net Pay (PHP)';

                $csv->columns($columns);

                $totals = [
                    'basic' => 0.0, 'ot' => 0.0, 'gross' => 0.0, 'late' => 0.0,
                    'undertime' => 0.0, 'deductions' => 0.0, 'net' => 0.0, 'days' => 0,
                    'byCode' => array_fill_keys($codes, 0.0),
                ];

                $rowNum   = 0;
                $netByDept = [];

                foreach ($employees as $employee) {
                    $computations = DailySalaryComputation::where('employee_id', $employee->id)
                        ->whereBetween('work_date', [$startDate, $endDate])
                        ->get();

                    if ($computations->isEmpty()) {
                        continue;
                    }

                    $basicPay   = (float) $computations->sum('daily_basic_pay');
                    $otPay      = (float) $computations->sum('ot_pay');
                    $late       = (float) $computations->sum('late_deduction');
                    $undertime  = (float) $computations->sum('undertime_deduction');
                    $daysWorked = $computations->count();
                    $dailyRate  = (float) ($computations->first()->daily_rate ?? 0);

                    $deductions = array_fill_keys($codes, 0.0);

                    foreach ($employee->deductions as $deduction) {
                        $type = $deduction->deductionType;

                        if (!$type || !$type->deducted_from_employee) {
                            continue;
                        }

                        if ($type->category === 'MANDATORY') {
                            if ($type->computation_type === 'PERCENTAGE') {
                                $base = match ($type->base_salary_type) {
                                    'GROSS'   => $basicPay + $otPay,
                                    'MONTHLY' => (float) ($employee->employmentDetail?->designationRelation?->monthly_rate ?? 0),
                                    default   => $basicPay,
                                };

                                $deductions[$type->code] = $base * ($type->percentage_rate / 100);
                            } elseif ($type->computation_type === 'FIXED') {
                                // FIXED stores its amount on percentage_rate —
                                // the column is misnamed, not the value.
                                $deductions[$type->code] = (float) ($type->percentage_rate ?? $deduction->amount ?? 0);
                            } else {
                                $deductions[$type->code] = (float) ($deduction->amount ?? 0);
                            }
                        } elseif ($type->category === 'LOAN') {
                            $deductions[$type->code] = (float) ($deduction->installment_amount ?? 0);
                        }
                    }

                    $gross          = $basicPay + $otPay;
                    $totalDeduction = $late + $undertime + array_sum($deductions);
                    $net            = $gross - $totalDeduction;
                    $dept           = $employee->employmentDetail?->departmentRelation?->name ?? 'Unassigned';

                    $row = [
                        ++$rowNum,
                        $employee->employee_id ?? 'N/A',
                        trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                        $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                        $dept,
                        $daysWorked,
                        $this->money($dailyRate),
                        $this->money($basicPay),
                        $this->money($otPay),
                        $this->money($gross),
                        $this->money($late),
                        $this->money($undertime),
                    ];

                    foreach ($codes as $code) {
                        $row[] = $this->money($deductions[$code]);
                        $totals['byCode'][$code] += $deductions[$code];
                    }

                    $row[] = $this->money($totalDeduction);
                    $row[] = $this->money($net);

                    $csv->row($row);

                    $totals['basic']      += $basicPay;
                    $totals['ot']         += $otPay;
                    $totals['gross']      += $gross;
                    $totals['late']       += $late;
                    $totals['undertime']  += $undertime;
                    $totals['deductions'] += $totalDeduction;
                    $totals['net']        += $net;
                    $totals['days']       += $daysWorked;

                    $netByDept[$dept] = ($netByDept[$dept] ?? 0) + $net;
                }

                if ($rowNum === 0) {
                    $csv->emptyNotice('No computed payroll days fall in this period for the employees selected. Generate payroll for the period before exporting it.');
                } else {
                    $totalRow = ['', '', 'TOTAL', '', '', $totals['days'], ''];
                    $totalRow[] = $this->money($totals['basic']);
                    $totalRow[] = $this->money($totals['ot']);
                    $totalRow[] = $this->money($totals['gross']);
                    $totalRow[] = $this->money($totals['late']);
                    $totalRow[] = $this->money($totals['undertime']);

                    foreach ($codes as $code) {
                        $totalRow[] = $this->money($totals['byCode'][$code]);
                    }

                    $totalRow[] = $this->money($totals['deductions']);
                    $totalRow[] = $this->money($totals['net']);

                    $csv->row($totalRow);
                }

                $csv->summary('Payroll Summary', [
                    'Employees Paid:'           => $rowNum,
                    'Total Days Paid:'          => $totals['days'],
                    'Total Basic Pay (PHP):'    => number_format($totals['basic'], 2),
                    'Total Overtime Pay (PHP):' => number_format($totals['ot'], 2),
                    'Gross Payroll (PHP):'      => number_format($totals['gross'], 2),
                    'Total Deductions (PHP):'   => number_format($totals['deductions'], 2),
                    'Total Net Pay (PHP):'      => number_format($totals['net'], 2),
                ]);

                $csv->summary('Deductions by Type (PHP)',
                    collect($codes)->mapWithKeys(fn ($code) => [
                        ($names[$code] ?? $code) . ':' => number_format($totals['byCode'][$code], 2),
                    ])->all()
                );

                $csv->summary('Net Pay by Department / Office (PHP)',
                    collect($netByDept)->sortDesc()
                        ->mapWithKeys(fn ($net, $dept) => [$dept . ':' => number_format($net, 2)])
                        ->all()
                );

                $csv->notes([
                    'Amounts are in Philippine Peso and written unformatted so the columns can be totalled in a spreadsheet.',
                    'An employee with no computed day in this period is not listed — payroll has nothing to disburse for them.',
                    'Only deductions borne by the employee are shown; employer and government shares are excluded.',
                    'Deductions are charged in full for the period shown; per-cutoff splitting is applied on the Payroll Register.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.payroll')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * The stored statuses one Payslip Management filter option covers.
     *
     * The dropdown offers three options over five stored values: 'draft' is
     * the column's default and reads as Pending on screen, so the option
     * labelled "Pending/Draft" has to match both. Everything else maps to
     * itself. Kept as one method so the query and the parameter block's label
     * cannot disagree about what "Pending" included.
     *
     * @return list<string>
     */
    private function statusesFor(string $status): array
    {
        return mb_strtolower($status) === 'pending'
            ? ['pending', 'draft']
            : [$status];
    }

    /**
     * A peso figure a spreadsheet can add up.
     *
     * `number_format` with a thousands separator writes "12,345.67", which
     * Excel imports as text — the column then refuses to total, which is the
     * first thing anyone does with a payroll register.
     */
    private function money($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
