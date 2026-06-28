<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrentDayAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Get today or next Monday if today is Sunday
        $targetDate = Carbon::today();
        
        if ($targetDate->dayOfWeek === 0) {
            // If Sunday, move to Monday
            $targetDate->addDay();
            $this->command->info('Today is Sunday. Generating attendance for Monday: ' . $targetDate->format('Y-m-d'));
        } else {
            $this->command->info('Generating attendance for today: ' . $targetDate->format('Y-m-d'));
        }
        
        $dateString = $targetDate->format('Y-m-d');
        
        // Get all active employees
        $employees = DB::table('employees')->get();
        
        if ($employees->isEmpty()) {
            $this->command->warn('No employees found in the database.');
            return;
        }
        
        $attendanceRecords = [];
        
        foreach ($employees as $employee) {
            // Check if attendance already exists
            $exists = DB::table('attendance')
                ->where('employee_id', $employee->id)
                ->where('date', $dateString)
                ->exists();
            
            if ($exists) {
                continue; // Skip if already has attendance
            }
            
            // Generate random attendance patterns
            $pattern = rand(1, 100);
            
            if ($pattern <= 80) {
                // 80% - Present with normal time
                $amIn = $this->randomTime('07:30:00', '08:10:00');
                $amOut = '12:00:00';
                $pmIn = '13:00:00';
                $pmOut = $this->randomTime('17:00:00', '17:30:00');
            } elseif ($pattern <= 88) {
                // 8% - Late arrival
                $amIn = $this->randomTime('08:16:00', '09:30:00');
                $amOut = '12:00:00';
                $pmIn = '13:00:00';
                $pmOut = $this->randomTime('17:00:00', '17:30:00');
            } elseif ($pattern <= 95) {
                // 7% - Early birds (6:30 - 7:29)
                $amIn = $this->randomTime('06:30:00', '07:29:00');
                $amOut = '12:00:00';
                $pmIn = '13:00:00';
                $pmOut = $this->randomTime('17:00:00', '17:30:00');
            } else {
                // 5% - Absent (no record)
                continue;
            }
            
            $attendanceRecords[] = [
                'employee_id' => $employee->id,
                'date' => $dateString,
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
            $this->command->info('Successfully inserted ' . count($attendanceRecords) . ' attendance records for ' . $dateString);
            
            // Show top 5 early birds
            $this->command->info('');
            $this->command->info('Top 5 Early Birds:');
            $earlyBirds = DB::table('attendance')
                ->join('employees', 'attendance.employee_id', '=', 'employees.id')
                ->where('attendance.date', $dateString)
                ->orderBy('attendance.am_in')
                ->limit(5)
                ->select('employees.first_name', 'employees.last_name', 'attendance.am_in')
                ->get();
            
            $rank = 1;
            foreach ($earlyBirds as $bird) {
                $this->command->info($rank . '. ' . $bird->first_name . ' ' . $bird->last_name . ' - ' . $bird->am_in);
                $rank++;
            }
        } else {
            $this->command->info('All employees already have attendance records for ' . $dateString);
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
