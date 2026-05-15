<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Models\PayrollDeduction;
use App\Models\DeductionType;

class DeductionService
{
    /**
     * Calculate deduction amount for an employee
     */
    public function calculateDeduction(EmployeeDeduction $employeeDeduction, string $cutoffPeriod, float $basicSalary): float
    {
        $deductionType = $employeeDeduction->deductionType;
        $schedule = $deductionType->schedules()->where('is_active', true)->first();

        if (!$schedule) {
            return 0;
        }

        // Check if deduction applies to this cutoff
        if (!$this->appliesTo($schedule->cutoff_schedule, $cutoffPeriod)) {
            return 0;
        }

        $amount = 0;

        switch ($deductionType->computation_type) {
            case 'PERCENTAGE':
                $amount = $basicSalary * ($deductionType->percentage_rate / 100);
                
                // Apply max amount if set
                if ($deductionType->max_amount && $amount > $deductionType->max_amount) {
                    $amount = $deductionType->max_amount;
                }

                // Split if BOTH_SPLIT
                if ($schedule->cutoff_schedule === 'BOTH_SPLIT') {
                    $amount = $amount / 2;
                }
                break;

            case 'FIXED':
                $amount = $employeeDeduction->installment_amount ?? $employeeDeduction->amount ?? 0;
                break;

            case 'CUSTOM':
                // Implement custom logic (e.g., withholding tax)
                $amount = $this->calculateWithholdingTax($basicSalary, $cutoffPeriod);
                break;
        }

        return round($amount, 2);
    }

    /**
     * Check if deduction applies to cutoff period
     */
    private function appliesTo(string $schedule, string $cutoffPeriod): bool
    {
        return match($schedule) {
            '1ST_ONLY' => $cutoffPeriod === '1ST',
            '2ND_ONLY' => $cutoffPeriod === '2ND',
            'BOTH_SPLIT', 'BOTH_FULL' => true,
            default => false,
        };
    }

    /**
     * Calculate withholding tax (placeholder - implement BIR tax table)
     */
    private function calculateWithholdingTax(float $basicSalary, string $cutoffPeriod): float
    {
        // TODO: Implement BIR tax table computation
        // This is a placeholder
        return 0;
    }

    /**
     * Process all deductions for an employee in a cutoff period
     */
    public function processEmployeeDeductions(int $employeeId, string $cutoffPeriod, float $basicSalary, ?int $payrollId = null): array
    {
        $deductions = EmployeeDeduction::where('employee_id', $employeeId)
            ->where('status', 'ACTIVE')
            ->with('deductionType.schedules')
            ->get();

        $processed = [];

        foreach ($deductions as $deduction) {
            $amount = $this->calculateDeduction($deduction, $cutoffPeriod, $basicSalary);

            if ($amount > 0) {
                $payrollDeduction = PayrollDeduction::create([
                    'payroll_id' => $payrollId,
                    'employee_id' => $employeeId,
                    'employee_deduction_id' => $deduction->id,
                    'deduction_type_id' => $deduction->deduction_type_id,
                    'cutoff_period' => $cutoffPeriod,
                    'amount_deducted' => $amount,
                    'computation_details' => [
                        'basic_salary' => $basicSalary,
                        'rate' => $deduction->deductionType->percentage_rate,
                        'schedule' => $deduction->deductionType->schedules()->first()->cutoff_schedule ?? null,
                    ],
                    'deduction_date' => now(),
                ]);

                // Update loan balance if applicable
                if ($deduction->deductionType->category === 'LOAN') {
                    $this->updateLoanBalance($deduction, $amount);
                }

                $processed[] = $payrollDeduction;
            }
        }

        return $processed;
    }

    /**
     * Update loan balance after deduction
     */
    private function updateLoanBalance(EmployeeDeduction $deduction, float $amount): void
    {
        $deduction->remaining_balance -= $amount;

        if ($deduction->remaining_balance <= 0) {
            $deduction->status = 'COMPLETED';
            $deduction->end_date = now();
            $deduction->remaining_balance = 0;
        }

        $deduction->save();
    }

    /**
     * Get total deductions for an employee in a cutoff period
     */
    public function getTotalDeductions(int $employeeId, string $cutoffPeriod, float $basicSalary): float
    {
        $deductions = EmployeeDeduction::where('employee_id', $employeeId)
            ->where('status', 'ACTIVE')
            ->with('deductionType.schedules')
            ->get();

        $total = 0;

        foreach ($deductions as $deduction) {
            $total += $this->calculateDeduction($deduction, $cutoffPeriod, $basicSalary);
        }

        return round($total, 2);
    }
}
