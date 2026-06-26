<?php

// Run this script from the Laravel project root: php seed_attendance.php

require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "Starting attendance seeding...\n";

// Get all employees with appointment dates
$employees = DB::table('employees')
    ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
    ->whereNotNull('employment_details.appointment_date')
    ->select('employees.id', 'employment_details.appointment_date')
    ->get();

$today = Carbon::today();
$totalRecords = 0;
$regularCount = 0;
$absentCount = 0;

foreach ($employees as $employee) {
    echo "Processing Employee ID: {$employee->id}\n";
    
    // Get employee schedule
    $schedule = DB::table('schedules')
        ->where('employee_id', $employee->id)
        ->where(function($query) use ($today) {
            $query->whereNull('start_date')
                  ->orWhere('start_date', '<=', $today);
        })
        ->where(function($query) use ($employee) {
            $query->whereNull('end_date')
                  ->orWhere('end_date', '>=', $employee->appointment_date);
        })
        ->orderBy('start_date', 'desc')
        ->first();
    
    // Default schedule if not found
    $amIn = $schedule->am_in ?? '08:00:00';
    $amOut = $schedule->am_out ?? '12:00:00';
    $pmIn = $schedule->pm_in ?? '13:00:00';
    $pmOut = $schedule->pm_out ?? '17:00:00';
    
    // Random targets for lates and absents (3-5 each)
    $targetLates = rand(3, 5);
    $targetAbsents = rand(3, 5);
    $lateCount = 0;
    $absentCount = 0;
    
    $currentDate = Carbon::parse($employee->appointment_date);
    
    while ($currentDate->lte($today)) {
        // Skip weekends
        if (!$currentDate->isWeekend()) {
            $isLate = false;
            $isAbsent = false;
            
            // Randomly assign lates
            if ($lateCount < $targetLates && rand(1, 100) <= 2) {
                $isLate = true;
                $lateCount++;
            }
            
            // Randomly assign absents
            if (!$isLate && $absentCount < $targetAbsents && rand(1, 100) <= 1.5) {
                $isAbsent = true;
                $absentCount++;
            }
            
            if ($isAbsent) {
                // Insert absent record
                DB::table('attendance')->insert([
                    'employee_id' => $employee->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'attendance_type' => 'ABSENT',
                    'remarks' => 'Seeded data'
                ]);
                $totalRecords++;
                $absentCount++;
            } else {
                // Normal or late attendance
                $amInTime = Carbon::parse($amIn);
                
                if ($isLate) {
                    // Add 10-30 minutes late
                    $amInTime->addMinutes(rand(10, 30));
                } else {
                    // On time or slightly early (±5 minutes)
                    $amInTime->addMinutes(rand(-5, 5));
                }
                
                $amOutTime = Carbon::parse($amOut)->addMinutes(rand(-3, 3));
                $pmInTime = Carbon::parse($pmIn)->addMinutes(rand(-3, 3));
                $pmOutTime = Carbon::parse($pmOut)->addMinutes(rand(-5, 10));
                
                DB::table('attendance')->insert([
                    'employee_id' => $employee->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'am_in' => $amInTime->format('H:i'),
                    'am_out' => $amOutTime->format('H:i'),
                    'pm_in' => $pmInTime->format('H:i'),
                    'pm_out' => $pmOutTime->format('H:i'),
                    'attendance_type' => 'REGULAR'
                ]);
                $totalRecords++;
                $regularCount++;
            }
        }
        
        $currentDate->addDay();
    }
    
    echo "  - Created {$lateCount} lates and {$absentCount} absents\n";
}

echo "\n✅ Attendance seeding completed!\n";
echo "Total records created: {$totalRecords}\n";
echo "Regular attendance: {$regularCount}\n";
echo "Absent records: {$absentCount}\n";

// Display summary by employee
echo "\nSummary by Employee:\n";
$summary = DB::table('attendance')
    ->select('employee_id', 'attendance_type', DB::raw('COUNT(*) as count'))
    ->groupBy('employee_id', 'attendance_type')
    ->orderBy('employee_id')
    ->get();

foreach ($summary as $row) {
    echo "Employee {$row->employee_id} - {$row->attendance_type}: {$row->count}\n";
}
