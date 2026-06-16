<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use App\Services\LeaveCreditsComputationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveImportService
{
    private const MONTH_NAMES = [
        'january' => 1, 'jan' => 1,
        'february' => 2, 'feb' => 2,
        'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'may' => 5,
        'june' => 6, 'jun' => 6,
        'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'october' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'december' => 12, 'dec' => 12,
    ];

    private const BALANCE_TOLERANCE = 0.001;

    public static function parseExcelFile(string $filePath): array
    {
        try {
            $worksheet = IOFactory::load($filePath)->getActiveSheet();
            $baseYear = self::detectBaseYear($worksheet) ?? (int) date('Y');

            $records = [];
            $year = $baseYear;
            $highestRow = $worksheet->getHighestDataRow();

            for ($row = 6; $row <= $highestRow; $row++) {
                $cellA = $worksheet->getCell('A' . $row);
                $monthYear = self::getCellDateString($cellA);
                
                $vlEarned = self::toFloat(self::getCellValue($worksheet, 'C', $row));
                $vlUsed = self::toFloat(self::getCellValue($worksheet, 'D', $row));
                $slEarned = self::toFloat(self::getCellValue($worksheet, 'E', $row));
                $slUsed = self::toFloat(self::getCellValue($worksheet, 'F', $row));
                $vlBalance = self::toFloat(self::getCellValue($worksheet, 'G', $row));
                $slBalance = self::toFloat(self::getCellValue($worksheet, 'H', $row));
                $notesRaw = trim((string) self::getCellValue($worksheet, 'B', $row));
                $parsedNotes = self::parseNotesColumn($notesRaw);

                if ($monthYear === '' && $vlEarned == 0 && $vlUsed == 0 && $slEarned == 0 && $slUsed == 0 && $vlBalance == 0 && $slBalance == 0 && empty($parsedNotes['leave_types']) && $parsedNotes['tardiness'] == 0) {
                    continue;
                }

                if (self::isYearHeader($monthYear)) {
                    $year = (int) $monthYear;
                    continue;
                }

                $monthNum = null;
                $recordYear = $year;

                if ($monthYear !== '') {
                    $parsedDate = self::parseMonthYear($monthYear);
                    if ($parsedDate) {
                        $recordYear = (int) $parsedDate->year;
                        $monthNum = (int) $parsedDate->month;
                    } else {
                        $parsed = self::parseMonthName($monthYear);
                        if ($parsed) {
                            $monthNum = $parsed;
                        }
                    }
                }

                if ($monthNum === null) {
                    continue;
                }

                $records[] = [
                    'month_year' => $monthYear,
                    'year' => $recordYear,
                    'month' => $monthNum,
                    'transaction_date' => Carbon::create($recordYear, $monthNum, 1)->toDateString(),
                    'notes_raw' => $notesRaw,
                    'leave_types' => $parsedNotes['leave_types'],
                    'tardiness' => $parsedNotes['tardiness'],
                    'vacation_leave_earned' => $vlEarned,
                    'vacation_leave_used' => $vlUsed,
                    'sick_leave_earned' => $slEarned,
                    'sick_leave_used' => $slUsed,
                    'vl_balance' => $vlBalance,
                    'sl_balance' => $slBalance,
                ];
            }

            if (empty($records)) {
                return [
                    'success' => false,
                    'message' => 'No leave records found in the Excel file. Ensure: (1) Data rows start from row 6 or later, (2) Column A contains month dates (e.g., "8/1/2012") or month names, (3) At least one data row has valid leave values.',
                ];
            }

            return [
                'success' => true,
                'records' => $records,
                'record_count' => count($records),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to parse Excel file: ' . $e->getMessage(),
            ];
        }
    }

    private static function getCellDateString($cell): string
    {
        try {
            $value = $cell->getValue();
            
            if ($value === null || $value === '') {
                return '';
            }

            $dataType = $cell->getDataType();
            
            if ($dataType === 'd' || ($dataType === 'n' && is_numeric($value) && $value > 0 && $value < 60000)) {
                try {
                    if (is_numeric($value)) {
                        $dateTime = ExcelDate::excelToDateTimeObject($value);
                        return $dateTime->format('m/d/Y');
                    }
                } catch (\Exception $e) {
                    // Fall through to return raw value
                }
            }
            
            return trim((string) $value);
        } catch (\Exception $e) {
            return '';
        }
    }

    private static function parseNotesColumn(string $notes): array
    {
        $result = [
            'leave_types' => [],
            'tardiness' => 0,
        ];

        if (empty($notes)) {
            return $result;
        }

        $parts = explode('/', trim($notes));

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/^T\((\d+)-(\d+)-(\d+)\)$/', $part, $matches)) {
                $hours = (int) $matches[1];
                $minutes = (int) $matches[2];
                $seconds = (int) $matches[3];

                $totalMinutes = ($hours * 60) + $minutes + ($seconds / 60);
                $result['tardiness'] += $totalMinutes;
            }

            elseif (preg_match('/^([A-Z]+)(\d+)$/', $part, $matches)) {
                $leaveCode = $matches[1];
                $days = (int) $matches[2];

                $mappedCode = self::mapLeaveCode($leaveCode);
                if ($mappedCode) {
                    $result['leave_types'][] = [
                        'code' => $mappedCode,
                        'days' => $days,
                        'original' => $part,
                    ];
                }
            }
        }

        return $result;
    }

    private static function mapLeaveCode(string $code): ?string
    {
        $mapping = [
            'VL' => 'VL',
            'SL' => 'SL',
            'FL' => 'FL',
            'ML' => 'ML',
            'PL' => 'PL',
            'BL' => 'BL',
            'AL' => 'AL',
        ];

        return $mapping[$code] ?? null;
    }

    public static function importLeaveRecords(int $employeeId, array $records): array
    {
        DB::beginTransaction();

        try {
            \App\Models\Employee::findOrFail($employeeId);

            $leaveTypes = LeaveType::whereIn('leave_code', ['VL', 'SL'])
                ->get()
                ->keyBy('leave_code');

            if ($leaveTypes->isEmpty()) {
                throw new \RuntimeException('VL and SL leave types must exist before importing records.');
            }

            $importedCount = 0;
            $recordsByYear = collect($records)->groupBy('year');
            $validationReport = [];
            $criticalAnomalies = [];

            foreach ($recordsByYear as $year => $yearRecords) {
                $year = (int) $year;
                $yearRecords = $yearRecords->sortBy('month')->values();

                // Validate cross-month balance continuity
                $continuityIssues = self::validateBalanceContinuity($yearRecords);
                if (!empty($continuityIssues)) {
                    $validationReport = array_merge($validationReport, $continuityIssues);
                }

                foreach ($yearRecords as $index => $record) {
                    $recordYear = (int) ($record['year'] ?? $year);
                    $recordMonth = (int) ($record['month'] ?? 1);
                    $transactionDate = Carbon::create($recordYear, $recordMonth, 1)->toDateString();
                    $monthLabel = $record['month_year'] ?? '';

                    $vlEarned = round((float) ($record['vacation_leave_earned'] ?? 0), 6);
                    $vlUsed = round((float) ($record['vacation_leave_used'] ?? 0), 6);
                    $vlBalance = round((float) ($record['vl_balance'] ?? 0), 6);

                    $slEarned = round((float) ($record['sick_leave_earned'] ?? 0), 6);
                    $slUsed = round((float) ($record['sick_leave_used'] ?? 0), 6);
                    $slBalance = round((float) ($record['sl_balance'] ?? 0), 6);

                    $notesRaw = trim($record['notes_raw'] ?? '');
                    $parsedNotes = $record['leave_types'] ?? [];
                    $tardinessMinutes = $record['tardiness'] ?? 0;

                    // Get previous month's balance
                    $previousVlBalance = 0;
                    $previousSlBalance = 0;

                    if ($index > 0) {
                        $previousRecord = $yearRecords[$index - 1];
                        $previousVlBalance = round((float) ($previousRecord['vl_balance'] ?? 0), 6);
                        $previousSlBalance = round((float) ($previousRecord['sl_balance'] ?? 0), 6);
                    }

                    // ====== CRITICAL: TREAT EXCEL AS SOURCE OF TRUTH ======
                    // Compute expected balance and compare with Excel balance
                    $expectedVlBalance = round($previousVlBalance + $vlEarned - $vlUsed, 6);
                    $expectedSlBalance = round($previousSlBalance + $slEarned - $slUsed, 6);

                    // VL Balance Validation
                    $vlAnomaly = self::validateBalance($expectedVlBalance, $vlBalance, self::BALANCE_TOLERANCE);
                    if (!$vlAnomaly['valid']) {
                        $criticalAnomaly = [
                            'type' => 'critical_balance_mismatch',
                            'severity' => 'CRITICAL',
                            'month' => $monthLabel,
                            'leave_code' => 'VL',
                            'computed_balance' => $expectedVlBalance,
                            'excel_balance' => $vlBalance,
                            'difference' => $vlAnomaly['difference'],
                            'previous_balance' => $previousVlBalance,
                            'earned' => $vlEarned,
                            'used' => $vlUsed,
                            'message' => "VL: Excel balance {$vlBalance} doesn't match computed {$expectedVlBalance} (diff: {$vlAnomaly['difference']}). " .
                                       "Previous: {$previousVlBalance} + Earned: {$vlEarned} - Used: {$vlUsed} = {$expectedVlBalance}",
                        ];
                        $criticalAnomalies[] = $criticalAnomaly;
                        
                        // Use Excel balance as source of truth
                        \Log::warning('VL Balance Anomaly', $criticalAnomaly);
                    }

                    // SL Balance Validation
                    $slAnomaly = self::validateBalance($expectedSlBalance, $slBalance, self::BALANCE_TOLERANCE);
                    if (!$slAnomaly['valid']) {
                        $criticalAnomaly = [
                            'type' => 'critical_balance_mismatch',
                            'severity' => 'CRITICAL',
                            'month' => $monthLabel,
                            'leave_code' => 'SL',
                            'computed_balance' => $expectedSlBalance,
                            'excel_balance' => $slBalance,
                            'difference' => $slAnomaly['difference'],
                            'previous_balance' => $previousSlBalance,
                            'earned' => $slEarned,
                            'used' => $slUsed,
                            'message' => "SL: Excel balance {$slBalance} doesn't match computed {$expectedSlBalance} (diff: {$slAnomaly['difference']}). " .
                                       "Previous: {$previousSlBalance} + Earned: {$slEarned} - Used: {$slUsed} = {$expectedSlBalance}",
                        ];
                        $criticalAnomalies[] = $criticalAnomaly;
                        
                        // Use Excel balance as source of truth
                        \Log::warning('SL Balance Anomaly', $criticalAnomaly);
                    }

                    // Process VL transactions - USING EXCEL BALANCE AS SOURCE OF TRUTH
                    if ($vlEarned > 0 || $vlUsed > 0 || ($index === 0 && $vlBalance > 0)) {
                        $importedCount += self::createLeaveTransactions(
                            $employeeId,
                            'VL',
                            $year,
                            $vlEarned,
                            $vlUsed,
                            $previousVlBalance,
                            $vlBalance,  // ← EXCEL BALANCE IS SOURCE OF TRUTH
                            $transactionDate,
                            $monthLabel,
                            $notesRaw,
                            $parsedNotes,
                            $tardinessMinutes,
                            !$vlAnomaly['valid']  // Flag if anomaly detected
                        );

                        // Update VL balance
                        $leaveBalance = LeaveBalance::firstOrCreate(
                            [
                                'employee_id' => $employeeId,
                                'leave_code' => 'VL',
                                'year' => $year,
                            ],
                            [
                                'total_credits' => 0,
                                'used_credits' => 0,
                                'pending_credits' => 0,
                                'available_credits' => 0,
                                'carried_over' => 0,
                            ]
                        );

                        $leaveBalance->total_credits += $vlEarned;
                        $leaveBalance->used_credits += $vlUsed;
                        $leaveBalance->available_credits = $vlBalance;  // ← EXCEL VALUE
                        $leaveBalance->save();
                    }

                    // Process SL transactions - USING EXCEL BALANCE AS SOURCE OF TRUTH
                    if ($slEarned > 0 || $slUsed > 0 || ($index === 0 && $slBalance > 0)) {
                        $importedCount += self::createLeaveTransactions(
                            $employeeId,
                            'SL',
                            $year,
                            $slEarned,
                            $slUsed,
                            $previousSlBalance,
                            $slBalance,  // ← EXCEL BALANCE IS SOURCE OF TRUTH
                            $transactionDate,
                            $monthLabel,
                            $notesRaw,
                            $parsedNotes,
                            $tardinessMinutes,
                            !$slAnomaly['valid']  // Flag if anomaly detected
                        );

                        // Update SL balance
                        $leaveBalance = LeaveBalance::firstOrCreate(
                            [
                                'employee_id' => $employeeId,
                                'leave_code' => 'SL',
                                'year' => $year,
                            ],
                            [
                                'total_credits' => 0,
                                'used_credits' => 0,
                                'pending_credits' => 0,
                                'available_credits' => 0,
                                'carried_over' => 0,
                            ]
                        );

                        $leaveBalance->total_credits += $slEarned;
                        $leaveBalance->used_credits += $slUsed;
                        $leaveBalance->available_credits = $slBalance;  // ← EXCEL VALUE
                        $leaveBalance->save();
                    }
                }
            }

            if ($importedCount === 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No leave data could be imported from the file.',
                ];
            }

            DB::commit();

            // Sync leave balances from imported transactions
            foreach ($recordsByYear as $year => $yearRecords) {
                LeaveCreditsComputationService::syncLeaveCreditsForYear($employeeId, (int) $year, 'VL');
                LeaveCreditsComputationService::syncLeaveCreditsForYear($employeeId, (int) $year, 'SL');
            }

            $years = $recordsByYear->keys()->sort()->implode(', ');
            $message = "Successfully imported {$importedCount} transactions for year(s): {$years}.";

            if (!empty($criticalAnomalies)) {
                $message .= " ⚠️ CRITICAL: " . count($criticalAnomalies) . " balance anomalies detected (using Excel values as source of truth).";
            }

            if (!empty($validationReport)) {
                $message .= " Warning: " . count($validationReport) . " entries had continuity issues.";
            }

            return [
                'success' => true,
                'message' => $message,
                'imported_count' => $importedCount,
                'critical_anomalies' => $criticalAnomalies,
                'validation_report' => $validationReport,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ];
        }
    }

    private static function createLeaveTransactions(
        int $employeeId,
        string $leaveCode,
        int $year,
        float $earned,
        float $used,
        float $previousBalance,
        float $finalBalance,
        string $transactionDate,
        string $monthLabel,
        string $notesRaw,
        array $parsedNotes,
        float $tardinessMinutes,
        bool $hasAnomalies = false
    ): int {
        $transactionCount = 0;
        $currentBalance = $previousBalance;
        $deductionReasons = self::buildDeductionReasons($leaveCode, $parsedNotes, $tardinessMinutes);
        
        // Flag anomalies in remarks
        $anomalyFlag = $hasAnomalies ? '[⚠️ ANOMALY] ' : '';

        // Create CREDIT transaction if earned
        if ($earned > 0) {
            LeaveTransaction::create([
                'employee_id' => $employeeId,
                'leave_code' => $leaveCode,
                'year' => $year,
                'transaction_type' => 'credit',
                'amount' => $earned,
                'balance_before' => $currentBalance,
                'balance_after' => round($currentBalance + $earned, 6),
                'reference_type' => 'leave_import',
                'reference_id' => null,
                'transaction_date' => $transactionDate,
                'processed_by' => auth()->id(),
                'remarks' => "{$anomalyFlag}[IMPORT] {$monthLabel} | Earned: {$earned} days | Notes: {$notesRaw}",
            ]);
            $currentBalance = round($currentBalance + $earned, 6);
            $transactionCount++;
        }

        // Create DEBIT transaction if used
        if ($used > 0) {
            $debitRemarks = "{$anomalyFlag}[IMPORT] {$monthLabel} | Deducted: {$used} days";
            if (!empty($deductionReasons)) {
                $debitRemarks .= " | Reasons: {$deductionReasons}";
            }
            $debitRemarks .= " | Notes: {$notesRaw}";

            LeaveTransaction::create([
                'employee_id' => $employeeId,
                'leave_code' => $leaveCode,
                'year' => $year,
                'transaction_type' => 'debit',
                'amount' => -$used,
                'balance_before' => $currentBalance,
                'balance_after' => round($currentBalance - $used, 6),
                'reference_type' => 'leave_import',
                'reference_id' => null,
                'transaction_date' => $transactionDate,
                'processed_by' => auth()->id(),
                'remarks' => $debitRemarks,
            ]);
            $currentBalance = round($currentBalance - $used, 6);
            $transactionCount++;
        }

        // Create single balance entry if first row with no earned/used but has balance
        if ($transactionCount === 0 && $finalBalance > 0) {
            LeaveTransaction::create([
                'employee_id' => $employeeId,
                'leave_code' => $leaveCode,
                'year' => $year,
                'transaction_type' => 'credit',
                'amount' => $finalBalance,
                'balance_before' => 0,
                'balance_after' => $finalBalance,
                'reference_type' => 'leave_import',
                'reference_id' => null,
                'transaction_date' => $transactionDate,
                'processed_by' => auth()->id(),
                'remarks' => "{$anomalyFlag}[IMPORT] {$monthLabel} | Initial Balance: {$finalBalance} days | Notes: {$notesRaw}",
            ]);
            $transactionCount++;
        }

        return $transactionCount;
    }

    private static function buildDeductionReasons(string $leaveCode, array $parsedNotes, float $tardinessMinutes): string
    {
        $reasons = [];

        foreach ($parsedNotes as $note) {
            if ($note['code'] === $leaveCode) {
                $reasons[] = "{$note['code']}: {$note['days']} day(s)";
            }
        }

        if ($tardinessMinutes > 0) {
            $hours = (int) ($tardinessMinutes / 60);
            $mins = (int) ($tardinessMinutes % 60);
            $reasons[] = "Tardiness: {$hours}h {$mins}m";
        }

        return implode('; ', $reasons);
    }

    private static function validateBalanceContinuity(Collection $yearRecords): array
    {
        $issues = [];
        $vlLeaveTypes = ['VL', 'SL'];

        foreach ($vlLeaveTypes as $leaveCode) {
            for ($i = 0; $i < $yearRecords->count() - 1; $i++) {
                $currentRecord = $yearRecords[$i];
                $nextRecord = $yearRecords[$i + 1];

                $currentBalance = $leaveCode === 'VL' 
                    ? round((float) ($currentRecord['vl_balance'] ?? 0), 6)
                    : round((float) ($currentRecord['sl_balance'] ?? 0), 6);

                $nextBalance = $leaveCode === 'VL'
                    ? round((float) ($nextRecord['vl_balance'] ?? 0), 6)
                    : round((float) ($nextRecord['sl_balance'] ?? 0), 6);

                if ($currentBalance !== $nextBalance) {
                    $gap = abs($currentBalance - $nextBalance);
                    if ($gap > 0.001) {
                        $issues[] = [
                            'type' => 'balance_gap',
                            'leave_code' => $leaveCode,
                            'from_month' => $currentRecord['month_year'] ?? '',
                            'to_month' => $nextRecord['month_year'] ?? '',
                            'gap' => $gap,
                            'message' => "{$leaveCode}: Gap between {$currentRecord['month_year']} and {$nextRecord['month_year']}: {$gap} days",
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    private static function validateBalance(float $expected, float $actual, float $tolerance): array
    {
        $difference = abs($expected - $actual);
        $valid = $difference <= $tolerance;

        return [
            'valid' => $valid,
            'expected' => $expected,
            'actual' => $actual,
            'difference' => $difference,
            'message' => $valid ? 'OK' : "Mismatch: expected {$expected}, got {$actual} (diff: {$difference})",
        ];
    }

    private static function isYearHeader(string $value): bool
    {
        return (bool) preg_match('/^(19|20)\d{2}$/', trim($value));
    }

    private static function detectBaseYear(Worksheet $worksheet): ?int
    {
        for ($row = 1; $row <= 5; $row++) {
            foreach (range('A', 'N') as $column) {
                $value = trim((string) self::getCellValue($worksheet, $column, $row));
                if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches)) {
                    return (int) $matches[0];
                }
            }
        }

        return null;
    }

    private static function getCellValue(Worksheet $worksheet, string $column, int $row): mixed
    {
        $cell = $worksheet->getCell($column . $row);
        return $cell->getCalculatedValue();
    }

    private static function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round((float) $value, 6);
    }

    private static function parseMonthName(string $monthYear): ?int
    {
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $monthYear)));
        
        if (isset(self::MONTH_NAMES[$normalized])) {
            return self::MONTH_NAMES[$normalized];
        }

        foreach (self::MONTH_NAMES as $name => $num) {
            if (strpos($normalized, $name) === 0) {
                return $num;
            }
        }

        return null;
    }

    private static function parseMonthYear(string $monthYear): ?Carbon
    {
        $monthYear = trim($monthYear);
        $formats = [
            'm/d/Y',
            'm/d/y',
            'd/m/Y',
            'd/m/y',
            'M-y',
            'm-y',
            'M/y',
            'm/y',
            'F Y',
            'M Y',
            'F',
            'M',
            'Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $monthYear);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}
