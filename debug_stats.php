<?php
require 'primeHrMagdalenaLaravel/bootstrap/app.php';
$app = require 'primeHrMagdalenaLaravel/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$transactions = DB::table('leave_transactions')
    ->where('employee_id', 8)
    ->whereYear('transaction_date', 2023)
    ->whereMonth('transaction_date', 12)
    ->orderBy('id')
    ->get();

echo "=== ALL TRANSACTIONS FOR EMPLOYEE 8 - DECEMBER 2023 ===\n";
foreach ($transactions as $t) {
    echo "ID: {$t->id} | Code: {$t->leave_code} | Type: {$t->transaction_type} | RefType: {$t->reference_type} | Amount: {$t->amount} | Remarks: {$t->remarks}\n";
}

echo "\n=== DEBIT COUNT BY LEAVE CODE ===\n";
$debits = DB::table('leave_transactions')
    ->where('employee_id', 8)
    ->where('transaction_type', 'debit')
    ->where('reference_type', '!=', 'tardiness_deduction')
    ->whereYear('transaction_date', 2023)
    ->whereMonth('transaction_date', 12)
    ->selectRaw('leave_code, COUNT(*) as count')
    ->groupBy('leave_code')
    ->get();

foreach ($debits as $d) {
    echo "{$d->leave_code}: {$d->count}\n";
}

echo "\n=== TARDINESS DEBIT COUNT ===\n";
$tardiness = DB::table('leave_transactions')
    ->where('employee_id', 8)
    ->where('transaction_type', 'debit')
    ->where('reference_type', 'tardiness_deduction')
    ->whereYear('transaction_date', 2023)
    ->whereMonth('transaction_date', 12)
    ->count();

echo "Tardiness count: {$tardiness}\n";
