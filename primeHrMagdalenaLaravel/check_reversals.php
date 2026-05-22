<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LeaveTransaction;

echo "Checking for attendance correction reversal transactions...\n\n";

$reversals = LeaveTransaction::where('employee_id', 8)
    ->where('reference_type', 'attendance_correction_reversal')
    ->get();

echo "Total reversal transactions found: " . $reversals->count() . "\n\n";

if ($reversals->count() > 0) {
    foreach ($reversals as $r) {
        echo "Transaction ID: " . $r->id . "\n";
        echo "Date: " . $r->transaction_date->format('Y-m-d') . "\n";
        echo "Leave Code: " . $r->leave_code . "\n";
        echo "Amount: " . $r->amount . "\n";
        echo "Remarks: " . $r->remarks . "\n";
        echo "-------------------------------------------\n";
    }
} else {
    echo "❌ NO REVERSAL TRANSACTIONS FOUND!\n\n";
    echo "This means the attendance correction did NOT trigger the\n";
    echo "leave balance recalculation service.\n\n";
    echo "The original deductions (152 min late + 18 min undertime) were NOT reversed.\n";
}
