<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TodayAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        
        // Get all active employees
        $employees = DB::table('employees')->get();
        
        if ($employees->isEmpty()) {
            $this->command->warn('No employees found in the database.');
            return;
        }
        
        $attendanceRecords = [];
        
        foreach ($employees as $employee) {
            // Check if attendance already exists for today
            $exists = DB::table('attendance')
                ->where('employee_id', $employee->id)
                ->where('date', $today)
                ->exists();
            
            if ($exists) {
                continue; // Skip if already has attendance
            }
            
            // Generate random attendance patterns
            $pattern = rand(1, 100);
            
            if ($pattern <= 85) {
                // 85% - Present with normal time
                $amIn = $this->randomTime('07:30:00', '08:15:00');
                $amOut = '12:00:00';
                $pmIn = '13:00:00';
                $pmOut = $this->randomTime('17:00:00', '17:30:00');
            } elseif ($pattern <= 92) {
                // 7% - Late arrival
                $amIn = $this->randomTime('08:16:00', '09:30:00');
                $amOut = '12:00:00';
                $pmIn = '13:00:00';
                $pmOut = $this->randomTime('17:00:00', '17:30:00');
            } elseif ($pattern <= 97) {
                // 5% - Early birds (7:00 - 7:29)
                $amIn = $this->randomTime('07:00:00', '07:29:00');
                $amOut = '12:00:00';
                $pmIn = '13:00:00';
                $pmOut = $this->randomTime('17:00:00', '17:30:00');
            } else {
                // 3% - Absent (no record)
                continue;
            }
            
            $attendanceRecords[] = [
                'employee_id' => $employee->id,
                'date' => $today,
                'am_in' => $amIn,
                'am_out' => $amOut,
                'pm_in' => $pmIn,
                'pm_out' => $pmOut,
                'ot_in' => null,
                'ot_out' => null,
            ];
        }
        
        if (!empty($attendanceRecords)) {
            DB::table('attendance')->insert($attendanceRecords);
            $this->command->info('Successfully inserted ' . count($attendanceRecords) . ' attendance records for today (' . $today . ')');
        } else {
            $this->command->info('All employees already have attendance records for today.');
        }
    }
    
    /**
     * Generate random time between two times
     */
    private function randomTime(string $start, string $end): string
    {
        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);
        $randomTimestamp = rand($startTimestamp, $endTimestamp);
        
        return date('H:i:s', $randomTimestamp);
    }
}
