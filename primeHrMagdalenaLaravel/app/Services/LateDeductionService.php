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
            $lateDays = round($lateMinutes / 480, 4);
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

            if ($vlBalance && $vlBalance->available_credits >= $lateDays) {
                $this->deductFromLeave($vlBalance, $lateDays, $log, 'VL');
            } elseif ($vlBalance && $vlBalance->available_credits > 0) {
                $remaining = $lateDays - $vlBalance->available_credits;
                $this->deductFromLeave($vlBalance, $vlBalance->available_credits, $log, 'VL');
                
                if ($slBalance && $slBalance->available_credits >= $remaining) {
                    $this->deductFromLeave($slBalance, $remaining, $log, 'SL');
                } elseif ($slBalance && $slBalance->available_credits > 0) {
                    $this->deductFromLeave($slBalance, $slBalance->available_credits, $log, 'SL');
                    $log->update(['late_deducted_from_leave' => false]);
                } else {
                    $log->update(['late_deducted_from_leave' => false]);
                }
            } elseif ($slBalance && $slBalance->available_credits >= $lateDays) {
                $this->deductFromLeave($slBalance, $lateDays, $log, 'SL');
            } elseif ($slBalance && $slBalance->available_credits > 0) {
                $this->deductFromLeave($slBalance, $slBalance->available_credits, $log, 'SL');
                $log->update(['late_deducted_from_leave' => false]);
            } else {
                $log->update(['late_deducted_from_leave' => false]);
            }
        });
    }

    private function deductFromLeave(LeaveBalance $balance, float $amount, AccreditedHoursLog $log, string $leaveType): void
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
            'remarks' => "Late deduction: {$log->late_minutes} minutes (" . round($amount, 4) . " days) from attendance on " . date('Y-m-d', strtotime($log->created_at))
        ]);

        // Credit full 8 hours (480 minutes) when late is deducted from leave
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
