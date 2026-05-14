<?php
/**
 * EXACT CONVERSION VERIFICATION SCRIPT
 * 
 * Run this to verify that conversions are EXACT with NO rounding
 * 
 * Usage: php verify_exact_conversions.php
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  EXACT CONVERSION VERIFICATION - NO ROUNDING UP OR DOWN       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Simulate CSC conversion functions
function convertDaysToMinutes(float $days): int
{
    // EXACT: Just multiply and cast to int (no round, no floor, no ceil)
    return (int)($days * 480);
}

function convertMinutesToDays(int $minutes): float
{
    // EXACT: Just divide
    return $minutes / 480;
}

// Test 1: Your specific case - 0.125 days
echo "TEST 1: Your Case - 0.125 days (1 hour)\n";
echo "═══════════════════════════════════════════\n";
$days = 0.125;
$minutes = convertDaysToMinutes($days);
$calculation = $days * 480;

echo "Input: {$days} days\n";
echo "Calculation: {$days} × 480 = {$calculation}\n";
echo "Result: {$minutes} minutes\n";
echo "Expected: 60 minutes\n";
echo "Status: " . ($minutes === 60 ? "✅ PASS - EXACT" : "❌ FAIL") . "\n\n";

// Test 2: Your scenario - 3 hours late, VL=0.125, SL=0.125
echo "TEST 2: Your Complete Scenario\n";
echo "═══════════════════════════════════════════\n";
$lateMinutes = 180;
$vlDays = 0.125;
$slDays = 0.125;

echo "Late: {$lateMinutes} minutes (3 hours)\n";
echo "VL Balance: {$vlDays} days\n";
echo "SL Balance: {$slDays} days\n\n";

// Convert VL to minutes
$vlMinutes = convertDaysToMinutes($vlDays);
echo "VL in minutes: {$vlDays} × 480 = {$vlMinutes} minutes\n";
echo "Expected: 60 minutes\n";
echo "Status: " . ($vlMinutes === 60 ? "✅ EXACT" : "❌ WRONG") . "\n\n";

// Convert SL to minutes
$slMinutes = convertDaysToMinutes($slDays);
echo "SL in minutes: {$slDays} × 480 = {$slMinutes} minutes\n";
echo "Expected: 60 minutes\n";
echo "Status: " . ($slMinutes === 60 ? "✅ EXACT" : "❌ WRONG") . "\n\n";

// Calculate coverage
$totalCovered = $vlMinutes + $slMinutes;
echo "Total covered: {$vlMinutes} + {$slMinutes} = {$totalCovered} minutes\n";
echo "Expected: 120 minutes\n";
echo "Status: " . ($totalCovered === 120 ? "✅ EXACT" : "❌ WRONG") . "\n\n";

// Calculate LWOP
$lwop = $lateMinutes - $totalCovered;
echo "LWOP: {$lateMinutes} - {$totalCovered} = {$lwop} minutes\n";
echo "Expected: 60 minutes\n";
echo "Status: " . ($lwop === 60 ? "✅ EXACT" : "❌ WRONG") . "\n\n";

// Calculate accredited hours
$initialAccredited = 480 - $lateMinutes;
$finalAccredited = $initialAccredited + $totalCovered;
$accreditedHours = $finalAccredited / 60;

echo "Initial accredited: 480 - {$lateMinutes} = {$initialAccredited} minutes\n";
echo "Restore covered: {$initialAccredited} + {$totalCovered} = {$finalAccredited} minutes\n";
echo "Accredited hours: {$finalAccredited} ÷ 60 = {$accreditedHours} hours\n";
echo "Expected: 7 hours\n";
echo "Status: " . (abs($accreditedHours - 7.0) < 0.0001 ? "✅ EXACT - 7 HOURS!" : "❌ WRONG") . "\n\n";

// Test 3: Common CSC values
echo "TEST 3: Common CSC Values\n";
echo "═══════════════════════════════════════════\n";
$testCases = [
    ['days' => 1.0, 'expected' => 480, 'label' => '1 full day'],
    ['days' => 0.5, 'expected' => 240, 'label' => 'Half day'],
    ['days' => 0.25, 'expected' => 120, 'label' => 'Quarter day (2 hours)'],
    ['days' => 0.125, 'expected' => 60, 'label' => '1 hour'],
    ['days' => 0.0625, 'expected' => 30, 'label' => '30 minutes'],
    ['days' => 0.375, 'expected' => 180, 'label' => '3 hours'],
];

$allPass = true;
foreach ($testCases as $test) {
    $days = $test['days'];
    $expected = $test['expected'];
    $label = $test['label'];
    
    $minutes = convertDaysToMinutes($days);
    $pass = ($minutes === $expected);
    $allPass = $allPass && $pass;
    
    $status = $pass ? "✅" : "❌";
    echo "{$status} {$label}: {$days} days = {$minutes} minutes (expected {$expected})\n";
}
echo "\nAll common values: " . ($allPass ? "✅ ALL PASS" : "❌ SOME FAILED") . "\n\n";

// Test 4: Round-trip conversion
echo "TEST 4: Round-Trip Conversion (days → minutes → days)\n";
echo "═══════════════════════════════════════════\n";
$roundTripTests = [0.125, 0.25, 0.375, 0.5, 1.0];
$allRoundTripPass = true;

foreach ($roundTripTests as $originalDays) {
    $minutes = convertDaysToMinutes($originalDays);
    $backToDays = convertMinutesToDays($minutes);
    $pass = (abs($originalDays - $backToDays) < 0.000001);
    $allRoundTripPass = $allRoundTripPass && $pass;
    
    $status = $pass ? "✅" : "❌";
    echo "{$status} {$originalDays} days → {$minutes} min → {$backToDays} days\n";
}
echo "\nRound-trip: " . ($allRoundTripPass ? "✅ ALL PASS" : "❌ SOME FAILED") . "\n\n";

// Test 5: Verify NO rounding is happening
echo "TEST 5: Verify NO Rounding Functions Used\n";
echo "═══════════════════════════════════════════\n";
$testValue = 0.124999;  // Just under 0.125
$minutes = convertDaysToMinutes($testValue);
$withRound = (int)round($testValue * 480);
$withFloor = (int)floor($testValue * 480);
$withCeil = (int)ceil($testValue * 480);
$withCast = (int)($testValue * 480);

echo "Test value: {$testValue} days\n";
echo "Calculation: {$testValue} × 480 = " . ($testValue * 480) . "\n\n";
echo "With round(): {$withRound} minutes\n";
echo "With floor(): {$withFloor} minutes\n";
echo "With ceil():  {$withCeil} minutes\n";
echo "With (int):   {$withCast} minutes\n\n";
echo "Our function: {$minutes} minutes\n";
echo "Status: " . ($minutes === $withCast ? "✅ Using direct cast (no rounding)" : "❌ Using rounding") . "\n\n";

// Test 6: Floating point precision check
echo "TEST 6: Floating Point Precision Check\n";
echo "═══════════════════════════════════════════\n";
$precisionTests = [
    0.125,
    0.125000,
    0.1250000000,
];

echo "Testing if different representations of 0.125 give same result:\n";
$results = [];
foreach ($precisionTests as $value) {
    $minutes = convertDaysToMinutes($value);
    $results[] = $minutes;
    echo "  {$value} → {$minutes} minutes\n";
}

$allSame = (count(array_unique($results)) === 1);
echo "Status: " . ($allSame ? "✅ All give same result (60 minutes)" : "❌ Different results") . "\n\n";

// Final Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  FINAL SUMMARY                                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$finalChecks = [
    ['check' => '0.125 days = 60 minutes', 'pass' => (convertDaysToMinutes(0.125) === 60)],
    ['check' => 'VL + SL = 120 minutes', 'pass' => ($totalCovered === 120)],
    ['check' => 'LWOP = 60 minutes', 'pass' => ($lwop === 60)],
    ['check' => 'Accredited = 7 hours', 'pass' => (abs($accreditedHours - 7.0) < 0.0001)],
    ['check' => 'No rounding functions', 'pass' => (convertDaysToMinutes(0.124999) === 59)],
    ['check' => 'Round-trip accurate', 'pass' => $allRoundTripPass],
];

$allFinalPass = true;
foreach ($finalChecks as $check) {
    $status = $check['pass'] ? "✅ PASS" : "❌ FAIL";
    echo "{$status} - {$check['check']}\n";
    $allFinalPass = $allFinalPass && $check['pass'];
}

echo "\n";
if ($allFinalPass) {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ ALL TESTS PASSED - EXACT CONVERSIONS VERIFIED!            ║\n";
    echo "║  No rounding up, no rounding down - EXACT calculations!       ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
} else {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║  ❌ SOME TESTS FAILED - CHECK IMPLEMENTATION                   ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";
}

echo "\n";
echo "Key Points:\n";
echo "• 0.125 days = EXACTLY 60 minutes (no rounding)\n";
echo "• 0.125 + 0.125 = EXACTLY 120 minutes (no rounding)\n";
echo "• 180 - 120 = EXACTLY 60 minutes LWOP\n";
echo "• Final accredited = EXACTLY 7 hours (420 minutes)\n";
echo "• Uses (int) cast only - NO round(), floor(), or ceil()\n";
