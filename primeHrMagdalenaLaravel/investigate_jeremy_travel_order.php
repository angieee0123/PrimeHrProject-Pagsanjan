<?php

/**
 * Investigate Jeremy Pogi's Travel Order Issue - May 25, 2026
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\TravelOrder;
use Carbon\Carbon;

echo "==============================================\n";
echo "Jeremy Pogi Travel Order Investigation - May 25, 2026\n";
echo "==============================================\n\n";

try {
    // Find Jeremy Pogi
    $jeremy = Employee::where('first_name', 'LIKE', '%Jeremy%')
        ->orWhere('last_name', 'LIKE', '%Pogi%')
        ->orWhere('first_name', 'LIKE', '%JEREMY%')
        ->orWhere('last_name', 'LIKE', '%POGI%')
        ->first();

    if (!$jeremy) {
        echo "Jeremy Pogi not found. Searching for similar names...\n";
        $employees = Employee::where('first_name', 'LIKE', '%Jeremy%')
            ->orWhere('last_name', 'LIKE', '%Pogi%')
            ->get();
        
        if ($employees->count() > 0) {
            echo "Found similar employees:\n";
            foreach ($employees as $emp) {
                echo "  ID: {$emp->id}, Name: {$emp->first_name} {$emp->last_name}\n";
            }
        } else {
            echo "No employees found with Jeremy or Pogi in their name.\n";
        }
        exit(1);
    }

    echo "Found Jeremy: ID {$jeremy->id}, Name: {$jeremy->first_name} {$jeremy->last_name}\n\n";

    // Check attendance for May 25, 2026
    $targetDate = '2026-05-25';
    echo "=== ATTENDANCE RECORD FOR MAY 25, 2026 ===\n";
    
    $attendance = Attendance::where('employee_id', $jeremy->id)
        ->where('date', $targetDate)
        ->first();

    if ($attendance) {
        echo "✓ Attendance record found:\n";
        echo "  Date: {$attendance->date}\n";
        echo "  AM In: " . ($attendance->am_in ?? 'NULL') . "\n";
        echo "  AM Out: " . ($attendance->am_out ?? 'NULL') . "\n";
        echo "  PM In: " . ($attendance->pm_in ?? 'NULL') . "\n";
        echo "  PM Out: " . ($attendance->pm_out ?? 'NULL') . "\n";
        echo "  Accredited Hours: {$attendance->accredited_hours}\n";
        echo "  Total Hours: {$attendance->total_hours}\n";
        echo "  Attendance Type: " . ($attendance->attendance_type ?? 'NULL') . "\n";
        echo "  Remarks: " . ($attendance->remarks ?? 'NULL') . "\n";
        
        // Check if this looks like a travel order attendance
        if ($attendance->attendance_type === 'TRAVEL_ORDER') {
            echo "  ✓ This is a TRAVEL_ORDER attendance record\n";
        } else if (is_null($attendance->am_in) && is_null($attendance->pm_in) && $attendance->accredited_hours == 480) {
            echo "  ⚠️  This looks like a travel order record but attendance_type is not set\n";
        } else {
            echo "  ℹ️  This appears to be a regular attendance record\n";
        }
    } else {
        echo "✗ No attendance record found for May 25, 2026\n";
    }

    echo "\n=== TRAVEL ORDERS COVERING MAY 25, 2026 ===\n";
    
    $travelOrders = TravelOrder::where('employee_id', $jeremy->id)
        ->where('travel_date', '<=', $targetDate)
        ->where('return_date', '>=', $targetDate)
        ->get();

    if ($travelOrders->count() > 0) {
        foreach ($travelOrders as $order) {
            echo "✓ Travel Order Found:\n";
            echo "  ID: {$order->id}\n";
            echo "  Order Number: {$order->order_number}\n";
            echo "  Destination: {$order->destination}\n";
            echo "  Purpose: {$order->purpose}\n";
            echo "  Travel Date: {$order->travel_date}\n";
            echo "  Return Date: {$order->return_date}\n";
            echo "  Duration: {$order->duration} days\n";
            echo "  Status: {$order->status}\n";
            echo "  Approved At: " . ($order->approved_at ?? 'NULL') . "\n";
            echo "  Approved By: " . ($order->approved_by ?? 'NULL') . "\n";
            echo "\n";
        }
    } else {
        echo "✗ No travel orders found covering May 25, 2026\n";
    }

    // Check all recent travel orders
    echo "=== ALL TRAVEL ORDERS (May 2026) ===\n";
    
    $allOrders = TravelOrder::where('employee_id', $jeremy->id)
        ->where(function($query) {
            $query->whereBetween('travel_date', ['2026-05-01', '2026-05-31'])
                  ->orWhereBetween('return_date', ['2026-05-01', '2026-05-31']);
        })
        ->orderBy('travel_date')
        ->get();

    if ($allOrders->count() > 0) {
        foreach ($allOrders as $order) {
            echo "  {$order->order_number} | {$order->travel_date} to {$order->return_date} | Status: {$order->status}";
            if ($order->approved_at) {
                echo " | Approved: {$order->approved_at}";
            }
            echo " | Destination: {$order->destination}\n";
        }
    } else {
        echo "  No travel orders found for May 2026\n";
    }

    // Check system configuration
    echo "\n=== SYSTEM CHECK ===\n";
    
    // Check if travel_orders table exists
    try {
        $orderCount = TravelOrder::count();
        echo "✓ travel_orders table exists ({$orderCount} total records)\n";
    } catch (\Exception $e) {
        echo "✗ travel_orders table missing or inaccessible\n";
    }
    
    // Check if attendance columns exist
    $columns = \DB::select("SHOW COLUMNS FROM attendance");
    $columnNames = array_column($columns, 'Field');
    
    if (in_array('attendance_type', $columnNames)) {
        echo "✓ attendance_type column exists\n";
    } else {
        echo "✗ attendance_type column missing - run migrations\n";
    }
    
    if (in_array('remarks', $columnNames)) {
        echo "✓ remarks column exists\n";
    } else {
        echo "✗ remarks column missing - run migrations\n";
    }

    // Check if TravelOrderObserver is working
    echo "\n=== OBSERVER CHECK ===\n";
    
    // Look for any TRAVEL_ORDER attendance records in the system
    $travelOrderAttendance = Attendance::where('attendance_type', 'TRAVEL_ORDER')->count();
    echo "Total TRAVEL_ORDER attendance records in system: {$travelOrderAttendance}\n";
    
    if ($travelOrderAttendance == 0) {
        echo "⚠️  No TRAVEL_ORDER attendance records found - Observer may not be working\n";
    }

    echo "\n=== DIAGNOSIS ===\n";
    
    if (!$attendance && $travelOrders->count() > 0) {
        $approvedOrder = $travelOrders->where('status', 'approved')->first();
        if ($approvedOrder) {
            echo "❌ ISSUE FOUND: Travel order is approved but no attendance record created\n";
            echo "   This indicates the TravelOrderObserver is not working\n";
            echo "   Possible causes:\n";
            echo "   1. Observer not registered in AppServiceProvider\n";
            echo "   2. Observer code has errors\n";
            echo "   3. Database transaction rollback\n";
            echo "   4. Weekend date (observer skips weekends)\n";
            
            // Check if May 25, 2026 is a weekend
            $date = Carbon::parse($targetDate);
            echo "   5. May 25, 2026 is a " . $date->format('l') . "\n";
            if ($date->isWeekend()) {
                echo "      ✓ This is a weekend - Observer correctly skipped it\n";
            }
        } else {
            echo "ℹ️  Travel order exists but not approved - no attendance record expected\n";
        }
    } else if ($attendance && $travelOrders->count() == 0) {
        echo "ℹ️  Attendance exists but no travel order found - may be leave or manual entry\n";
    } else if (!$attendance && $travelOrders->count() == 0) {
        echo "ℹ️  No attendance or travel order records found for this date\n";
    } else {
        echo "✓ Both attendance and travel order records found\n";
        
        // Check if they match
        $approvedOrder = $travelOrders->where('status', 'approved')->first();
        if ($approvedOrder && $attendance->attendance_type === 'TRAVEL_ORDER') {
            echo "✓ Travel order and attendance records are properly linked\n";
        } else if ($approvedOrder && $attendance->attendance_type !== 'TRAVEL_ORDER') {
            echo "⚠️  Travel order approved but attendance type is not TRAVEL_ORDER\n";
        }
    }

} catch (\Exception $e) {
    echo "\n✗ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\nInvestigation completed.\n";