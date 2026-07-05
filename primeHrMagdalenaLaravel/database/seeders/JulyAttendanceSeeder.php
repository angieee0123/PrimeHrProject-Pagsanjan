<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JulyAttendanceSeeder extends Seeder
{
    // Grace period in minutes
    private const GRACE = 5;
    private const WORK_MINUTES = 480; // 8 hours

    public function run(): void
    {
        $start = Carbon::parse('2026-07-04');
        $end   = Carbon::parse('2026-07-20');

        $employees = DB::table('employees')->get();
        $schedules = DB::table('schedules')->get()->keyBy('employee_id');
        $rates     = DB::table('employment_details as ed')
            ->join('designations as d', 'ed.designation_id', '=', 'd.id')
            ->select('ed.employee_id', 'd.monthly_rate')
            ->get()->keyBy('employee_id');

        $now = now();
        $inserted = 0;
        $skipped  = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) continue;

            $dateStr = $date->toDateString();

            foreach ($employees as $employee) {
                $empId = $employee->id;

                // Skip if attendance already exists
                $exists = DB::table('attendance')
                    ->where('employee_id', $empId)
                    ->where('date', $dateStr)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                // Get schedule (default to standard 8-5 if none)
                $sched = $schedules->get($empId);
                $schedAmIn  = $sched ? $sched->am_in  : '08:00:00';
                $schedAmOut = $sched ? $sched->am_out : '12:00:00';
                $schedPmIn  = $sched ? $sched->pm_in  : '13:00:00';
                $schedPmOut = $sched ? $sched->pm_out : '17:00:00';

                // Generate realistic attendance times
                [$amIn, $amOut, $pmIn, $pmOut, $lateMinutes] = $this->generateTimes(
                    $schedAmIn, $schedAmOut, $schedPmIn, $schedPmOut
                );

                // Compute accredited minutes
                $amAccredited = $this->computeAmMinutes($amIn, $schedAmIn, $schedAmOut);
                $pmAccredited = $this->computePmMinutes($pmIn, $schedPmIn, $schedPmOut);
                $totalAccredited = $amAccredited + $pmAccredited;
                $totalActual     = $totalAccredited; // same for regular attendance

                $amGrace = $lateMinutes === 0 && $amIn > $schedAmIn;
                $pmGrace = false;

                // Insert attendance
                $attendanceId = DB::table('attendance')->insertGetId([
                    'employee_id'     => $empId,
                    'date'            => $dateStr,
                    'am_in'           => $amIn,
                    'am_out'          => $amOut,
                    'pm_in'           => $pmIn,
                    'pm_out'          => $pmOut,
                    'ot_in'           => null,
                    'ot_out'          => null,
                    'accredited_hours' => (int) round($totalAccredited / 60),
                    'total_hours'      => (int) round($totalActual / 60),
                    'attendance_type'  => 'REGULAR',
                    'remarks'          => null,
                ]);

                // Get schedule_id
                $scheduleId = $sched ? $sched->id : null;

                // Insert accredited_hours_log
                $logId = DB::table('accredited_hours_log')->insertGetId([
                    'attendance_id'                 => $attendanceId,
                    'employee_id'                   => $empId,
                    'schedule_id'                   => $scheduleId,
                    'am_accredited_minutes'         => $amAccredited,
                    'pm_accredited_minutes'         => $pmAccredited,
                    'ot_minutes'                    => 0,
                    'late_minutes'                  => $lateMinutes,
                    'late_deducted_from_leave'      => false,
                    'late_deduction_leave_type'     => null,
                    'lwop_minutes'                  => 0,
                    'requires_salary_deduction'     => false,
                    'undertime_minutes'             => 0,
                    'undertime_deducted_from_leave' => false,
                    'undertime_deduction_leave_type'=> null,
                    'total_accredited_minutes'      => $totalAccredited,
                    'total_actual_minutes'          => $totalActual,
                    'am_grace_applied'              => $amGrace,
                    'pm_grace_applied'              => $pmGrace,
                    'computation_notes'             => $lateMinutes > 0
                        ? "Late {$lateMinutes} min(s). Deduction: " . round($lateMinutes / self::WORK_MINUTES, 4) . " VL day."
                        : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Insert daily_salary_computations
                $rateRow     = $rates->get($empId);
                $monthlyRate = $rateRow ? (float) $rateRow->monthly_rate : 0;
                $dailyRate   = $monthlyRate > 0 ? round($monthlyRate / 22, 2) : 0;
                $hourlyRate  = $dailyRate > 0 ? round($dailyRate / 8, 2) : 0;

                $dailyBasicPay   = round(($totalAccredited / self::WORK_MINUTES) * $dailyRate, 2);
                $lateDeduction   = $lateMinutes > 0
                    ? round(($lateMinutes / self::WORK_MINUTES) * $dailyRate, 2)
                    : 0;
                $dailyGrossPay   = round($dailyBasicPay - $lateDeduction, 2);

                DB::table('daily_salary_computations')->insert([
                    'employee_id'            => $empId,
                    'accredited_hours_log_id'=> $logId,
                    'work_date'              => $dateStr,
                    'monthly_rate'           => $monthlyRate,
                    'daily_rate'             => $dailyRate,
                    'hourly_rate'            => $hourlyRate,
                    'daily_basic_pay'        => $dailyBasicPay,
                    'ot_pay'                 => 0,
                    'late_deduction'         => $lateDeduction,
                    'undertime_deduction'    => 0,
                    'daily_gross_pay'        => $dailyGrossPay,
                    'is_holiday'             => false,
                    'is_rest_day'            => false,
                    'holiday_type'           => null,
                    'notes'                  => null,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);

                $inserted++;
            }
        }

        $this->command->info("Done! Inserted: {$inserted} | Skipped (already exist): {$skipped}");
    }

    /**
     * Generate realistic AM/PM times and compute late minutes.
     * Returns [amIn, amOut, pmIn, pmOut, lateMinutes]
     */
    private function generateTimes(string $schedAmIn, string $schedAmOut, string $schedPmIn, string $schedPmOut): array
    {
        $roll = rand(1, 100);

        if ($roll <= 10) {
            // 10% late (8:06 - 9:00)
            $amIn = $this->randomTime('08:06:00', '09:00:00');
        } elseif ($roll <= 18) {
            // 8% early bird (7:00 - 7:59)
            $amIn = $this->randomTime('07:00:00', '07:59:00');
        } else {
            // 82% on time (7:30 - 8:05, within grace)
            $amIn = $this->randomTime('07:30:00', '08:05:00');
        }

        $amOut = $schedAmOut; // always 12:00
        $pmIn  = $schedPmIn;  // always 13:00
        $pmOut = $this->randomTime('17:00:00', '17:30:00');

        // Compute late minutes (past grace period of scheduled + 5 min)
        $graceEnd    = $this->addMinutes($schedAmIn, self::GRACE); // 08:05:00
        $lateMinutes = 0;
        if ($amIn > $graceEnd) {
            $lateMinutes = (int) round((strtotime($amIn) - strtotime($graceEnd)) / 60);
        }

        return [$amIn, $amOut, $pmIn, $pmOut, $lateMinutes];
    }

    private function computeAmMinutes(string $amIn, string $schedAmIn, string $schedAmOut): int
    {
        $graceEnd = $this->addMinutes($schedAmIn, self::GRACE);
        $effectiveIn = $amIn > $graceEnd ? $amIn : $schedAmIn;
        $minutes = (int) round((strtotime($schedAmOut) - strtotime($effectiveIn)) / 60);
        return max(0, min($minutes, 240)); // cap at 4 hours (240 min)
    }

    private function computePmMinutes(string $pmIn, string $schedPmIn, string $schedPmOut): int
    {
        $graceEnd = $this->addMinutes($schedPmIn, self::GRACE);
        $effectiveIn = $pmIn > $graceEnd ? $pmIn : $schedPmIn;
        $minutes = (int) round((strtotime($schedPmOut) - strtotime($effectiveIn)) / 60);
        return max(0, min($minutes, 240));
    }

    private function addMinutes(string $time, int $minutes): string
    {
        return date('H:i:s', strtotime($time) + ($minutes * 60));
    }

    private function randomTime(string $start, string $end): string
    {
        return date('H:i:s', rand(strtotime($start), strtotime($end)));
    }
}
