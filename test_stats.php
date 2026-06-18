<?php
require __DIR__ . '/primeHrMagdalenaLaravel/vendor/autoload.php';

$app = require_once __DIR__ . '/primeHrMagdalenaLaravel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$employeeId = 8;

echo "=== RAW QUERY RESULT ===\n";
$allDebits = DB::table('leave_transactions')
    ->where('employee_id', $employeeId)
    ->where('transaction_type', 'debit')
    ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month_year, leave_code, reference_type, COUNT(*) as count')
    ->groupByRaw('DATE_FORMAT(transaction_date, "%Y-%m"), leave_code, reference_type')
    ->orderByRaw('DATE_FORMAT(transaction_date, "%Y-%m") DESC')
    ->get();

foreach ($allDebits as $row) {
    echo "Month: {$row->month_year} | Code: {$row->leave_code} | RefType: {$row->reference_type} | Count: {$row->count}\n";
}

echo "\n=== BUILDING STATS ARRAY ===\n";
$leaveStatsHistory = [];
foreach ($allDebits as $row) {
    if (!isset($leaveStatsHistory[$row->month_year])) {
        $leaveStatsHistory[$row->month_year] = ['leaves_by_type' => [], 'tardiness_count' => 0];
    }
    if ($row->reference_type === 'tardiness_deduction') {
        $leaveStatsHistory[$row->month_year]['tardiness_count'] += $row->count;
    } else {
        $code = $row->leave_code;
        $leaveStatsHistory[$row->month_year]['leaves_by_type'][$code] = ($leaveStatsHistory[$row->month_year]['leaves_by_type'][$code] ?? 0) + $row->count;
    }
}

echo "\n=== DECEMBER 2023 STATS ===\n";
if (isset($leaveStatsHistory['2023-12'])) {
    echo "Leaves by type:\n";
    foreach ($leaveStatsHistory['2023-12']['leaves_by_type'] as $code => $count) {
        echo "  {$code}: {$count}\n";
    }
    echo "Tardiness count: {$leaveStatsHistory['2023-12']['tardiness_count']}\n";
} else {
    echo "No stats for 2023-12\n";
}

echo "\n=== ALL SL DEBITS FOR DEC 2023 ===\n";
$slDebits = DB::table('leave_transactions')
    ->where('employee_id', $employeeId)
    ->where('transaction_type', 'debit')
    ->where('leave_code', 'SL')
    ->whereYear('transaction_date', 2023)
    ->whereMonth('transaction_date', 12)
    ->get(['id', 'transaction_date', 'reference_type', 'amount', 'remarks']);

foreach ($slDebits as $row) {
    echo "ID: {$row->id} | Date: {$row->transaction_date} | RefType: {$row->reference_type} | Amount: {$row->amount}\n";
}
echo "Total SL debits: " . count($slDebits) . "\n";
