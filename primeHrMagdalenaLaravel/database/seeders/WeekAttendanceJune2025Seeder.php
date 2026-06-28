<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeekAttendanceJune2025Seeder extends Seeder
{
    public function run(): void
    {
        // Date range: June 30, 2025 to July 6, 2025 (7 days) + Today
        $startDate = Carbon::parse('2025-06-30');
        $endDate = Carbon::parse('2025-07-06');
        $today = Carbon::today();
        
        // Get all active employees
        $employees = DB::table('employees')->get();
        
        if ($employees->isEmpty()) {
            $this->command->warn('No employees found in the database.');
            return;
        }
        
        $this->command->info('Generating attendance for ' . $employees->count() . ' employees from ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . ' + Today (' . $today->format('Y-m-d') . ')');
        
        $attendanceRecords = [];
        
        // Process June 30 - July 6, 2025
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->dayOfWeek;
            
            // Skip Sundays (0) - assuming Sunday is rest day
            if ($dayOfWeek === 0) {
                $this->command->info('Skipping Sunday: ' . $dateString);
                $currentDate->addDay();
                continue;
            }
            
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
                
                // Saturday - reduced attendance (50% present)
                if ($dayOfWeek === 6 && rand(1, 100) > 50) {
                    continue; // Skip this employee on Saturday
                }
                
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
            
            $currentDate->addDay();
        }
        
        // Process Today's attendance
        $todayString = $today->format('Y-m-d');
        $todayDayOfWeek = $today->dayOfWeek;
        
        if ($todayDayOfWeek !== 0) { // Skip if today is Sunday
            $this->command->info('Generating attendance for TODAY: ' . $todayString);
            
            foreach ($employees as $employee) {
                // Check if attendance already exists for today
                $exists = DB::table('attendance')
                    ->where('employee_id', $employee->id)
                    ->where('date', $todayString)
                    ->exists();
                
                if ($exists) {
                    continue; // Skip if already has attendance
                }
                
                // Generate random attendance patterns for today
                $pattern = rand(1, 100);
                
                // Saturday - reduced attendance (50% present)
                if ($todayDayOfWeek === 6 && rand(1, 100) > 50) {
                    continue; // Skip this employee on Saturday
                }
                
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
                    'date' => $todayString,
                    'am_in' => $amIn,
                    'am_out' => $amOut,
                    'pm_in' => $pmIn,
                    'pm_out' => $pmOut,
                    'ot_in' => null,
                    'ot_out' => null,
                ];
            }
        } else {
            $this->command->info('Today is Sunday - skipping today\'s attendance.');
        }
        
        if (!empty($attendanceRecords)) {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($attendanceRecords, 500);
            foreach ($chunks as $chunk) {
                DB::table('attendance')->insert($chunk);
            }
            $this->command->info('Successfully inserted ' . count($attendanceRecords) . ' attendance records.');
        } else {
            $this->command->info('All attendance records already exist for this period.');
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
