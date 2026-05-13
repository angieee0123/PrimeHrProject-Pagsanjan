<?php

namespace App\Services;

use App\Models\AccreditedHoursLog;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LateDeductionService
{
    public function processLateDeduction(AccreditedHoursLog $log): void
    {
        if ($log->late_minutes <= 0 || $log->late_deducted_from_leave) {
            return;
        }

        DB::transaction(function () use ($log) {
            $lateMinutes = $log->late_minutes;
            $lateDays = $lateMinutes / 1440; // Convert minutes to days (1440 minutes = 24 hours)
            $employeeId = $log->employee_id;
            $year = date('Y', strtotime($log->created_at));

            $vlBalance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_code', 'VL')
                ->where('year', $year)
                ->first();

            $slBalance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_code', 'SL')
                ->where('year', $year)
                ->first();

            $remainingLateDays = $lateDays;
            $deductedFromLeave = false;
            $leaveTypes = [];

            // Try to deduct from VL first
            if ($vlBalance && $vlBalance->available_credits > 0) {
                $deductAmount = min($vlBalance->available_credits, $remainingLateDays);
                $this->deductFromLeave($vlBalance, $deductAmount, $log, 'VL', false);
                $remainingLateDays -= $deductAmount;
                $deductedFromLeave = true;
                $leaveTypes[] = 'VL';
            }

            // If still have remaining late, try SL
            if ($remainingLateDays > 0 && $slBalance && $slBalance->available_credits > 0) {
                $deductAmount = min($slBalance->available_credits, $remainingLateDays);
                $this->deductFromLeave($slBalance, $deductAmount, $log, 'SL', false);
                $remainingLateDays -= $deductAmount;
                $deductedFromLeave = true;
                $leaveTypes[] = 'SL';
            }

            // Update log based on coverage
            if ($remainingLateDays <= 0) {
                // Fully covered by leave - credit full 8 hours
                $log->update([
                    'total_accredited_minutes' => 480,
                    'late_deducted_from_leave' => true,
                    'late_deduction_leave_type' => implode('+', $leaveTypes) . ' (full)'
                ]);
                
                if ($log->attendance) {
                    $log->attendance->update(['accredited_hours' => 480]);
                }
            } else {
                // Partially covered - deduct remaining from accredited hours
                $remainingLateMinutes = round($remainingLateDays * 1440);
                $newAccreditedMinutes = max(0, $log->total_accredited_minutes - $remainingLateMinutes);
                
                $log->update([
                    'total_accredited_minutes' => $newAccreditedMinutes,
                    'late_deducted_from_leave' => $deductedFromLeave,
                    'late_deduction_leave_type' => $deductedFromLeave ? implode('+', $leaveTypes) . ' (partial)' : null
                ]);
                
                if ($log->attendance) {
                    $log->attendance->update(['accredited_hours' => $newAccreditedMinutes]);
                }
            }
        });
    }

    private function deductFromLeave(LeaveBalance $balance, float $amount, AccreditedHoursLog $log, string $leaveType, bool $updateLog = true): void
    {
        $balanceBefore = $balance->available_credits;
        
        $balance->used_credits += $amount;
        $balance->available_credits -= $amount;
        $balance->save();

        LeaveTransaction::create([
            'employee_id' => $balance->employee_id,
            'leave_code' => $balance->leave_code,
            'year' => $balance->year,
            'transaction_type' => 'debit',
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balance->available_credits,
            'reference_type' => 'manual_adjustment',
            'reference_id' => $log->id,
            'transaction_date' => date('Y-m-d'),
            'processed_by' => auth()->id(),
            'remarks' => "Late deduction: {$log->late_minutes} minutes (" . number_format($amount, 6, '.', '') . " days) from attendance on " . date('Y-m-d', strtotime($log->created_at))
        ]);

        // Only update log if this is the final deduction (for backward compatibility)
        if ($updateLog) {
            // Credit full 8 hours (480 minutes) when late is fully deducted from leave
            $log->update([
                'total_accredited_minutes' => 480,
                'late_deducted_from_leave' => true,
                'late_deduction_leave_type' => $leaveType
            ]);
            
            // Also update the attendance record's accredited_hours
            if ($log->attendance) {
                $log->attendance->update(['accredited_hours' => 480]);
            }
        }
    }
}
