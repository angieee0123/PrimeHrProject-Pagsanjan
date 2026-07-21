<?php

namespace App\Services;

use App\Models\AccreditedHoursLog;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Service to handle leave balance recalculation when attendance records are corrected.
 * 
 * When an admin edits attendance times (am_in, am_out, pm_in, pm_out), this service:
 * 1. Identifies previous leave deductions (late/undertime) from the old attendance
 * 2. Reverses those deductions by crediting back the leave balances
 * 3. Applies new deductions based on the corrected attendance
 * 4. Records all adjustments in the transaction history with clear remarks
 */
class AttendanceCorrectionLeaveRecalculationService
{
    private LateDeductionService $lateDeductionService;
    private UndertimeDeductionService $undertimeDeductionService;

    public function __construct()
    {
        $this->lateDeductionService = new LateDeductionService();
        $this->undertimeDeductionService = new UndertimeDeductionService();
    }

    /**
     * Recalculate leave deductions when attendance is corrected.
     * 
     * @param AccreditedHoursLog $oldLog The previous accredited hours log (before correction)
     * @param AccreditedHoursLog $newLog The new accredited hours log (after correction)
     * @return array Summary of the recalculation
     */
    public function recalculateLeaveDeductions(AccreditedHoursLog $oldLog, AccreditedHoursLog $newLog): array
    {
        return DB::transaction(function () use ($oldLog, $newLog) {
            $summary = [
                'reversed_transactions' => [],
                'new_transactions' => [],
                'net_change' => [],
            ];

            // Step 1: Reverse previous leave deductions if they existed
            if ($oldLog->late_deducted_from_leave || $oldLog->undertime_deducted_from_leave) {
                $summary['reversed_transactions'] = $this->reversePreviousDeductions($oldLog);
            }

            // Step 2: Reset the flags on the old log to prevent double processing
            $oldLog->update([
                'late_deducted_from_leave' => false,
                'undertime_deducted_from_leave' => false,
                'late_deduction_leave_type' => null,
                'undertime_deduction_leave_type' => null,
            ]);

            // Step 3: Apply new deductions based on corrected attendance
            // Reset flags on new log first to ensure fresh processing
            $newLog->update([
                'late_deducted_from_leave' => false,
                'undertime_deducted_from_leave' => false,
                'late_deduction_leave_type' => null,
                'undertime_deduction_leave_type' => null,
            ]);

            // Process late deduction
            if ($newLog->late_minutes > 0) {
                $this->lateDeductionService->processLateDeduction($newLog);
                $summary['new_transactions']['late'] = [
                    'minutes' => $newLog->late_minutes,
                    'days' => CscTimeConversionService::convertMinutesToDays($newLog->late_minutes),
                    'deducted_from_leave' => $newLog->late_deducted_from_leave,
                    'leave_type' => $newLog->late_deduction_leave_type,
                ];
            }

            // Process undertime deduction
            if ($newLog->undertime_minutes > 0) {
                $this->undertimeDeductionService->processUndertimeDeduction($newLog);
                $summary['new_transactions']['undertime'] = [
                    'minutes' => $newLog->undertime_minutes,
                    'days' => CscTimeConversionService::convertMinutesToDays($newLog->undertime_minutes),
                    'deducted_from_leave' => $newLog->undertime_deducted_from_leave,
                    'leave_type' => $newLog->undertime_deduction_leave_type,
                ];
            }

            // Step 4: Calculate net change for reporting
            $summary['net_change'] = $this->calculateNetChange($summary);

            return $summary;
        });
    }

    /**
     * Reverse previous leave deductions by crediting back the leave balances.
     * 
     * @param AccreditedHoursLog $log The log with previous deductions
     * @return array Details of reversed transactions
     */
    private function reversePreviousDeductions(AccreditedHoursLog $log): array
    {
        $reversed = [];
        $employeeId = $log->employee_id;
        $year = date('Y', strtotime($log->created_at));
        $attendanceDate = $log->attendance ? $log->attendance->date : date('Y-m-d', strtotime($log->created_at));

        // Find all previous deduction transactions for this attendance
        $previousTransactions = LeaveTransaction::where('employee_id', $employeeId)
            ->where('reference_type', 'tardiness_deduction')
            ->where('reference_id', $log->id)
            ->where('transaction_type', 'debit')
            ->where('amount', '<', 0)
            ->get();

        foreach ($previousTransactions as $transaction) {
            // Credit back the deducted amount
            $creditAmount = abs($transaction->amount);
            
            $leaveBalance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_code', $transaction->leave_code)
                ->where('year', $year)
                ->first();

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                
                // Reverse the deduction
                $leaveBalance->used_credits -= $creditAmount;
                $leaveBalance->available_credits += $creditAmount;
                $leaveBalance->save();

                // Create reversal transaction
                $reversalTransaction = LeaveTransaction::create([
                    'employee_id' => $employeeId,
                    'leave_code' => $transaction->leave_code,
                    'year' => $year,
                    'transaction_type' => 'reversal',
                    'amount' => $creditAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'attendance_correction_reversal',
                    'reference_id' => $log->id,
                    'transaction_date' => date('Y-m-d'),
                    'processed_by' => Auth::id(),
                    'remarks' => "Reversal of previous {$this->getDeductionType($transaction->remarks)} deduction due to attendance correction on {$attendanceDate}. Original deduction: " . number_format(abs($transaction->amount), 6) . " days.",
                ]);

                $reversed[] = [
                    'leave_code' => $transaction->leave_code,
                    'amount' => $creditAmount,
                    'original_transaction_id' => $transaction->id,
                    'reversal_transaction_id' => $reversalTransaction->id,
                    'type' => $this->getDeductionType($transaction->remarks),
                ];
            }
        }

        return $reversed;
    }

    /**
     * Determine if a transaction was for late or undertime deduction.
     * 
     * @param string $remarks Transaction remarks
     * @return string 'late' or 'undertime'
     */
    private function getDeductionType(string $remarks): string
    {
        if (stripos($remarks, 'late') !== false) {
            return 'late';
        } elseif (stripos($remarks, 'undertime') !== false) {
            return 'undertime';
        }
        return 'attendance';
    }

    /**
     * Calculate net change in leave balances.
     * 
     * @param array $summary Summary of reversed and new transactions
     * @return array Net change per leave type
     */
    private function calculateNetChange(array $summary): array
    {
        $netChange = [];

        // Calculate from reversed transactions (these are credits)
        foreach ($summary['reversed_transactions'] as $reversed) {
            $leaveCode = $reversed['leave_code'];
            if (!isset($netChange[$leaveCode])) {
                $netChange[$leaveCode] = 0;
            }
            $netChange[$leaveCode] += $reversed['amount']; // Positive (credit)
        }

        // Calculate from new transactions (these are debits)
        // We need to fetch the actual new transactions to get the amounts
        // For now, we'll just note that new deductions were applied
        // The actual amounts are in the LeaveTransaction table

        return $netChange;
    }

    /**
     * Get a human-readable summary of the recalculation.
     * 
     * @param array $summary Recalculation summary
     * @return string Human-readable summary
     */
    public function getSummaryMessage(array $summary): string
    {
        $messages = [];

        if (!empty($summary['reversed_transactions'])) {
            $messages[] = "Reversed " . count($summary['reversed_transactions']) . " previous leave deduction(s).";
        }

        if (!empty($summary['new_transactions'])) {
            if (isset($summary['new_transactions']['late'])) {
                $late = $summary['new_transactions']['late'];
                $messages[] = "Applied new late deduction: {$late['minutes']} minutes (" . number_format($late['days'], 6) . " days)" . 
                    ($late['deducted_from_leave'] ? " from {$late['leave_type']}" : " as LWOP");
            }
            if (isset($summary['new_transactions']['undertime'])) {
                $undertime = $summary['new_transactions']['undertime'];
                $messages[] = "Applied new undertime deduction: {$undertime['minutes']} minutes (" . number_format($undertime['days'], 6) . " days)" . 
                    ($undertime['deducted_from_leave'] ? " from {$undertime['leave_type']}" : " as LWOP");
            }
        }

        if (empty($messages)) {
            return "No leave balance adjustments needed.";
        }

        return implode(" ", $messages);
    }
}

