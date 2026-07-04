<?php

namespace App\Services;

use App\Models\AccreditedHoursLog;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CscTimeConversionService;

class UndertimeDeductionService
{
    public function processUndertimeDeduction(AccreditedHoursLog $log): void
    {
        if ($log->undertime_minutes <= 0 || $log->undertime_deducted_from_leave) {
            return;
        }

        DB::transaction(function () use ($log) {
            $undertimeMinutes = $log->undertime_minutes;
            $undertimeDays = CscTimeConversionService::convertMinutesToDays($undertimeMinutes); // CSC standard: 480 minutes = 1 work day
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

            $remainingUndertimeDays = $undertimeDays;
            $deductedFromLeave = false;
            $leaveTypes = [];
            $totalCoveredMinutes = 0;  // Track covered minutes directly

            // Try to deduct from VL first
            if ($remainingUndertimeDays > 0 && $vlBalance && $vlBalance->available_credits > 0) {
                $deductAmount = min($vlBalance->available_credits, $remainingUndertimeDays);
                $this->deductFromLeave($vlBalance, $deductAmount, $log, 'VL', false);
                $remainingUndertimeDays -= $deductAmount;
                $totalCoveredMinutes += (int)($deductAmount * 480);
                $deductedFromLeave = true;
                $leaveTypes[] = 'VL';
            }

            // If still have remaining undertime, try SL
            if ($remainingUndertimeDays > 0 && $slBalance && $slBalance->available_credits > 0) {
                $deductAmount = min($slBalance->available_credits, $remainingUndertimeDays);
                $this->deductFromLeave($slBalance, $deductAmount, $log, 'SL', false);
                $remainingUndertimeDays -= $deductAmount;
                $totalCoveredMinutes += (int)($deductAmount * 480);
                $deductedFromLeave = true;
                $leaveTypes[] = 'SL';
            }

            // Uncovered undertime minutes become LWOP
            $undertimeLwopMinutes = max(0, $undertimeMinutes - $totalCoveredMinutes);

            // Preserve late LWOP: only reduce it by what undertime leave actually covered
            $lateLwop = max(0, ($log->lwop_minutes ?? 0) - $totalCoveredMinutes);
            $newLwop  = $lateLwop + $undertimeLwopMinutes;

            // Accredited = current accredited - uncovered undertime
            // (late service may have already set it to 480 if late was fully covered)
            $newAccreditedMinutes = max(0, $log->total_accredited_minutes - max(0, $undertimeLwopMinutes));

            $log->update([
                'total_accredited_minutes'       => $newAccreditedMinutes,
                'undertime_deducted_from_leave'  => $deductedFromLeave,
                'undertime_deduction_leave_type' => $deductedFromLeave
                    ? implode('+', $leaveTypes) . ($undertimeLwopMinutes <= 0 ? ' (full)' : ' (partial)')
                    : null,
                'lwop_minutes'              => $newLwop,
                'requires_salary_deduction' => $newLwop > 0,
            ]);

            if ($log->attendance) {
                $log->attendance->update(['accredited_hours' => $newAccreditedMinutes]);
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
            'remarks' => "Undertime deduction: {$log->undertime_minutes} minutes (" . number_format($amount, 6, '.', '') . " days) from attendance on " . date('Y-m-d', strtotime($log->created_at))
        ]);

        // Only update log if this is the final deduction (for backward compatibility)
        if ($updateLog) {
            // Credit full 8 hours (480 minutes) when undertime is fully deducted from leave
            $log->update([
                'total_accredited_minutes' => 480,
                'undertime_deducted_from_leave' => true,
                'undertime_deduction_leave_type' => $leaveType
            ]);
            
            // Also update the attendance record's accredited_hours
            if ($log->attendance) {
                $log->attendance->update(['accredited_hours' => 480]);
            }
        }
    }
}
