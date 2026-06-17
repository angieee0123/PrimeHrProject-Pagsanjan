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
     * This aggregates credit/debit transactions (from migration) and ledger entries
     */
    public static function syncLeaveCreditsForYear(int $employeeId, int $year, string $leaveCode): void
    {
        // Get all credit and debit transactions (from migrations)
        $transactions = LeaveTransaction::where('employee_id', $employeeId)
            ->where('leave_code', $leaveCode)
            ->where('year', $year)
            ->whereIn('transaction_type', ['credit', 'debit'])
            ->orderBy('transaction_date')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        $totalCredits = 0;
        $usedCredits = 0;
        $availableCredits = 0;

        // Aggregate amounts from transactions
        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'credit') {
                $totalCredits += $transaction->amount;
                $availableCredits += $transaction->amount;
            } elseif ($transaction->transaction_type === 'debit') {
                $usedCredits += abs($transaction->amount);
                $availableCredits += $transaction->amount; // amount is negative
            }
        }

        // Use the last transaction's balance_after as the final available credits (most accurate)
        $lastTransaction = $transactions->last();
        if ($lastTransaction) {
            $availableCredits = (float) $lastTransaction->balance_after;
        }

        // Update or create the leave balance record
        LeaveBalance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_code' => $leaveCode,
                'year' => $year,
            ],
            [
                'total_credits' => max(0, $totalCredits),
                'used_credits' => max(0, $usedCredits),
                'pending_credits' => 0,
                'available_credits' => max(0, $availableCredits),
                'carried_over' => 0,
            ]
        );
    }

    /**
     * Compute all leave credits for an employee across all years
     */
    public static function syncAllLeaveCreditsForEmployee(int $employeeId): void
    {
        // Get all unique (year, leave_code) combinations from credit/debit transactions
        $transactions = LeaveTransaction::where('employee_id', $employeeId)
            ->whereIn('transaction_type', ['credit', 'debit'])
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
