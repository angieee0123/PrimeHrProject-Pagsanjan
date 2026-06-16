<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveCreditsComputationService
{
    /**
     * Compute and sync leave credits for an employee for a specific year based on transactions
     * This aggregates all ledger_entry transactions to get totals, used, and available credits
     */
    public static function syncLeaveCreditsForYear(int $employeeId, int $year, string $leaveCode): void
    {
        $transactions = LeaveTransaction::where('employee_id', $employeeId)
            ->where('leave_code', $leaveCode)
            ->where('year', $year)
            ->where('transaction_type', 'ledger_entry')
            ->orderBy('transaction_date')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        // Get the last ledger entry to determine the final balances
        $lastTransaction = $transactions->last();

        $totalCredits = 0;
        $usedCredits = 0;

        // Process each transaction to accumulate earned and used amounts
        foreach ($transactions as $transaction) {
            // Parse remarks to extract earned and used amounts
            // Format: "[LEDGER] 08/01/2012 | Earned: 1.25, Used: 0, Balance: 1.25 | Notes: "
            if (preg_match('/Earned:\s*([\d.]+),\s*Used:\s*([\d.]+)/', $transaction->remarks, $matches)) {
                $earned = (float) $matches[1];
                $used = (float) $matches[2];
                $totalCredits += $earned;
                $usedCredits += $used;
            }
        }

        // Available credits is the final balance from the last ledger entry
        $availableCredits = (float) $lastTransaction->balance_after;

        // Update or create the leave balance record
        $leaveBalance = LeaveBalance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_code' => $leaveCode,
                'year' => $year,
            ],
            [
                'total_credits' => $totalCredits,
                'used_credits' => $usedCredits,
                'pending_credits' => 0,
                'available_credits' => $availableCredits,
                'carried_over' => 0,
            ]
        );
    }

    /**
     * Compute all leave credits for an employee across all years
     */
    public static function syncAllLeaveCreditsForEmployee(int $employeeId): void
    {
        // Get all unique (year, leave_code) combinations from transactions
        $transactions = LeaveTransaction::where('employee_id', $employeeId)
            ->where('transaction_type', 'ledger_entry')
            ->select('year', 'leave_code')
            ->distinct()
            ->get();

        foreach ($transactions as $transaction) {
            self::syncLeaveCreditsForYear($employeeId, $transaction->year, $transaction->leave_code);
        }
    }

    /**
     * Get leave balances for an employee in the current year
     */
    public static function getEmployeeCurrentYearBalances(int $employeeId): array
    {
        $year = now()->year;

        $balances = LeaveBalance::where('employee_id', $employeeId)
            ->where('year', $year)
            ->with('leaveType')
            ->get()
            ->map(fn($balance) => [
                'leave_code' => $balance->leave_code,
                'leave_name' => $balance->leaveType->leave_name ?? 'Unknown',
                'total_credits' => (float) $balance->total_credits,
                'used_credits' => (float) $balance->used_credits,
                'pending_credits' => (float) $balance->pending_credits,
                'available_credits' => (float) $balance->available_credits,
                'is_accrued' => $balance->leaveType->is_accrued ?? false,
            ])
            ->toArray();

        return $balances;
    }
}
