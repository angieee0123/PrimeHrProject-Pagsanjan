<?php

namespace App\Services;

use App\Models\DailySalaryComputation;
use Illuminate\Support\Collection;

/**
 * The Payroll Register's rows — who was paid, for which days, and what came
 * off the top.
 *
 * This used to be ~230 lines inline in `PayrollController::index()`, which is
 * why the register's Export button could not be wired to anything: the only
 * way to reproduce what the screen shows was to re-run the controller action
 * that renders it. The alternative — an endpoint that scrapes the rendered
 * table — is what caps a file at the columns the screen happens to show, and
 * the register deliberately shows seven of them.
 *
 * So the computation lives here and both callers read it: the page renders
 * these rows, and `PayrollExportController::register()` writes the same rows
 * to CSV. A figure in the file cannot disagree with the figure above it on
 * screen, because there is only one place that works it out.
 *
 * The deduction arithmetic is the part that most needed one owner. A
 * deduction's amount depends on its category, its computation type, its base
 * salary type, *and* the cutoff schedule in force for that employee — four
 * branches that were written out twice (once for the employee/monthly view,
 * once for the daily view) and would have been written a third time by any
 * export that rebuilt them.
 */
class PayrollRegisterService
{
    /**
     * Daily view prorates a monthly figure across the working days in a month
     * — the same divisor `DailySalaryComputation::WORKING_DAYS_PER_MONTH`
     * states for the daily rate. A monthly contribution charged in full
     * against one day's pay would read as a 100% deduction.
     */
    private const WORKING_DAYS_PER_MONTH = 22;

    /**
     * Build the register.
     *
     * @param  array<string,mixed> $filters start_date, end_date, department,
     *                                      employment_status, employee_name,
     *                                      status, view_mode
     * @return array{records: Collection, deductionTypes: Collection, start_date: string, end_date: string, view_mode: string}
     */
    public function build(array $filters): array
    {
        $startDate        = ($filters['start_date'] ?? null) ?: now()->startOfMonth()->format('Y-m-d');
        $endDate          = ($filters['end_date'] ?? null) ?: now()->endOfMonth()->format('Y-m-d');
        $department       = $filters['department'] ?? null;
        $employmentStatus = $filters['employment_status'] ?? null;
        $employeeName     = $filters['employee_name'] ?? null;
        $status           = $filters['status'] ?? null;
        $viewMode         = ($filters['view_mode'] ?? null) ?: 'daily';

        // Which half of the month the period starts in decides whether a
        // 1ST_ONLY / 2ND_ONLY deduction is charged at all.
        $isCutoff1st = (int) date('d', strtotime($startDate)) <= 15;

        $query = DailySalaryComputation::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'employee.deductions' => function ($q) use ($endDate) {
                $q->where('status', 'ACTIVE')
                  ->where('start_date', '<=', $endDate)
                  ->where(function ($query) use ($endDate) {
                      $query->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
                  })
                  ->with('deductionType.schedules');
            },
            'accreditedHoursLog',
        ])
        ->whereBetween('work_date', [$startDate, $endDate])
        ->orderBy('work_date', 'asc')
        ->orderBy('employee_id');

        if ($department) {
            $query->whereHas('employee.employmentDetail.departmentRelation', function ($q) use ($department) {
                $q->where('name', $department);
            });
        }

        if ($employmentStatus) {
            $query->whereHas('employee.employmentDetail', function ($q) use ($employmentStatus) {
                $q->where('employment_status', $employmentStatus);
            });
        }

        if ($employeeName) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->whereRaw("CONCAT(first_name, ' ', COALESCE(CONCAT(SUBSTRING(middle_name, 1, 1), '. '), ''), last_name) = ?", [$employeeName]);
            });
        }

        $dailyComputations = $query->get();

        $records = ($viewMode === 'employee' || $viewMode === 'monthly')
            ? $this->groupedByEmployee($dailyComputations, $isCutoff1st)
            : $this->oneRowPerDay($dailyComputations, $isCutoff1st);

        if ($status) {
            $records = $records->filter(fn ($r) => $r['status'] === $status)->values();
        }

        return [
            'records'        => $records,
            'deductionTypes' => $this->deductionCodesPresent($records),
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'view_mode'      => $viewMode,
        ];
    }

    /** Employee / Monthly view — one row per employee, days summed. */
    private function groupedByEmployee(Collection $computations, bool $isCutoff1st): Collection
    {
        return $computations->groupBy('employee_id')->map(function ($records) use ($isCutoff1st) {
            $employee = $records->first()->employee;

            $totalBasicPay           = $records->sum('daily_basic_pay');
            $totalOtPay              = $records->sum('ot_pay');
            $totalLateDeduction      = $records->sum('late_deduction');
            $totalUndertimeDeduction = $records->sum('undertime_deduction');

            $deductions = $this->deductionsFor(
                $employee,
                $isCutoff1st,
                (float) $totalBasicPay,
                (float) $totalOtPay,
                false
            );

            return [
                'id'                  => $employee->employee_id ?? 'N/A',
                'name'                => $this->displayName($employee),
                'position'            => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'dept'                => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'photo'               => $employee->photo,
                'work_date'           => null,
                'daily_rate'          => $records->first()->daily_rate ?? 0,
                'basic'               => $totalBasicPay,
                'ot_pay'              => $totalOtPay,
                'late_deduction'      => $totalLateDeduction,
                'undertime_deduction' => $totalUndertimeDeduction,
                'deductions'          => $deductions,
                // A day that computed no gross pay has not been run through
                // payroll yet, so the whole employee row reads Pending.
                'status'              => $records->every(fn ($r) => $r->daily_gross_pay > 0) ? 'Processed' : 'Pending',
                'days_count'          => $records->count(),
            ];
        })->values();
    }

    /** Daily view — one row per employee per worked day. */
    private function oneRowPerDay(Collection $computations, bool $isCutoff1st): Collection
    {
        return $computations->map(function ($record) use ($isCutoff1st) {
            $employee = $record->employee;

            $deductions = $this->deductionsFor(
                $employee,
                $isCutoff1st,
                (float) $record->daily_basic_pay,
                (float) $record->ot_pay,
                true
            );

            return [
                'id'                  => $employee->employee_id ?? 'N/A',
                'name'                => $this->displayName($employee),
                'position'            => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'dept'                => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'photo'               => $employee->photo,
                'work_date'           => $record->work_date,
                'daily_rate'          => $record->daily_rate,
                'basic'               => $record->daily_basic_pay,
                'ot_pay'              => $record->ot_pay,
                'late_deduction'      => $record->late_deduction,
                'undertime_deduction' => $record->undertime_deduction,
                'deductions'          => $deductions,
                'status'              => $record->daily_gross_pay > 0 ? 'Processed' : 'Pending',
                'days_count'          => null,
            ];
        });
    }

    /**
     * Every employee-borne deduction that applies to this row, keyed by code.
     *
     * `$prorateToDay` is the only difference between the two views: a daily
     * row carries one day's share of a monthly contribution, an employee row
     * carries the period's.
     *
     * Employer/government shares are skipped throughout — `deducted_from_employee`
     * false means the municipality pays it, so it never comes off a payslip.
     *
     * @return array<string,float>
     */
    private function deductionsFor($employee, bool $isCutoff1st, float $basicPay, float $otPay, bool $prorateToDay): array
    {
        $deductions = [];
        $divisor    = $prorateToDay ? self::WORKING_DAYS_PER_MONTH : 1;

        foreach ($employee->deductions as $deduction) {
            $type = $deduction->deductionType;

            if (!$type || !$type->deducted_from_employee) {
                continue;
            }

            $amount = 0;

            if ($type->category === 'MANDATORY') {
                if ($type->computation_type === 'PERCENTAGE') {
                    // A percentage already scales with the pay it is taken
                    // from, so only a MONTHLY base needs the day divisor.
                    $base = match ($type->base_salary_type) {
                        'GROSS'   => $basicPay + $otPay,
                        'MONTHLY' => ($employee->employmentDetail?->designationRelation?->monthly_rate ?? 0) / $divisor,
                        default   => $basicPay,
                    };

                    $amount = $base * ($type->percentage_rate / 100);
                } elseif ($type->computation_type === 'FIXED') {
                    // FIXED stores its amount on percentage_rate — the column
                    // is misnamed, not the value.
                    $amount = ($type->percentage_rate ?? $deduction->amount ?? 0) / $divisor;
                } else {
                    $amount = ($deduction->amount ?? 0) / $divisor;
                }
            } elseif ($type->category === 'LOAN') {
                $amount = ($deduction->installment_amount ?? 0) / $divisor;
            }

            $deductions[$type->code] = $this->applyCutoff(
                (float) $amount,
                $this->cutoffScheduleFor($deduction),
                $isCutoff1st
            );
        }

        return $deductions;
    }

    /**
     * The employee's own schedule wins over the deduction type's default —
     * that override is the whole point of the Schedules tab.
     */
    private function cutoffScheduleFor($deduction): string
    {
        if ($deduction->custom_cutoff_schedule) {
            return $deduction->custom_cutoff_schedule;
        }

        return $deduction->deductionType->schedules->first()?->cutoff_schedule ?? 'BOTH_SPLIT';
    }

    /** How much of a period's deduction falls in *this* cutoff. */
    private function applyCutoff(float $amount, string $schedule, bool $isCutoff1st): float
    {
        return match ($schedule) {
            '1ST_ONLY'  => $isCutoff1st ? $amount : 0,
            '2ND_ONLY'  => $isCutoff1st ? 0 : $amount,
            'BOTH_FULL' => $amount,
            default     => $amount / 2, // BOTH_SPLIT
        };
    }

    /** "Juan D. Dela Cruz" — the form the register and the CSV both print. */
    private function displayName($employee): string
    {
        $middle = $employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '';

        return trim($employee->first_name . ' ' . $middle . $employee->last_name);
    }

    /**
     * The deduction columns this particular register needs.
     *
     * Derived from the rows rather than from `deduction_types`, so a filter
     * that excludes every employee carrying a GSIS loan does not print an
     * empty GSIS column.
     */
    private function deductionCodesPresent(Collection $records): Collection
    {
        $codes = collect();

        foreach ($records as $record) {
            foreach (array_keys($record['deductions'] ?? []) as $code) {
                if (!$codes->contains($code)) {
                    $codes->push($code);
                }
            }
        }

        return $codes;
    }
}
