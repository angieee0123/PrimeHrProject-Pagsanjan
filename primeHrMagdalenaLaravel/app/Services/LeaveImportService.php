<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

    /**
     * Parse Pagsanjan leave records in Notes column (Column B)
     * Format examples:
     * - "VL1" = 1 day vacation leave used
     * - "SL1" = 1 day sick leave used
     * - "FL1" or "FL2" = 1-2 days forced leave
     * - "T(0-2-10)" = Tardiness: 0 hours, 2 minutes, 10 seconds
     * - "VL1/T(0-1-2)" = 1 day VL + Tardiness combined
     * - "ML1" = Maternity leave
     * - "PL1" = Paternity leave
     */

    /**
     * Parse Pagsanjan-style leave ledger Excel files.
     * Structure:
     * Row 1-5: Header info (Name, Position, etc.)
     * Row 6+: Data rows
     * Columns: A=Month/Year, B=Notes, D=VL Earned, F=VL Used, H=SL Earned, J=SL Used, M=VL Balance, N=SL Balance
     */
    public static function parseExcelFile(string $filePath): array
    {
        try {
            $worksheet = IOFactory::load($filePath)->getActiveSheet();
            $startRow = self::detectDataStartRow($worksheet);
            $baseYear = self::detectBaseYear($worksheet) ?? (int) date('Y');

            $records = [];
            $lastMonthNum = null;
            $year = $baseYear;
            $highestRow = $worksheet->getHighestDataRow();
            $emptyRowStreak = 0;

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $monthYear = trim((string) self::getCellValue($worksheet, 'A', $row));

                if ($monthYear === '') {
                    if (!empty($records)) {
                        $emptyRowStreak++;
                        if ($emptyRowStreak >= 3) {
                            break;
                        }
                    }
                    continue;
                }

                $emptyRowStreak = 0;

                // Check if this is a year header
                if (self::isYearHeader($monthYear)) {
                    $year = (int) $monthYear;
                    $lastMonthNum = null;
                    continue;
                }

                if (is_numeric($monthYear)) {
                    continue;
                }

                // Parse month name
                $parsedDate = self::parseMonthYear($monthYear);
                if ($parsedDate) {
                    $year = (int) $parsedDate->year;
                    $monthNum = (int) $parsedDate->month;
                    $lastMonthNum = $monthNum;
                } else {
                    $monthNum = self::parseMonthName($monthYear);
                    if (!$monthNum) {
                        continue;
                    }

                    // If month number is less than last month, increment year
                    if ($lastMonthNum !== null && $monthNum <= $lastMonthNum) {
                        $year++;
                    }

                    $lastMonthNum = $monthNum;
                }

                // Parse notes column to extract leave types and tardiness
                $notesRaw = trim((string) self::getCellValue($worksheet, 'B', $row));
                $parsedNotes = self::parseNotesColumn($notesRaw);

                $records[] = [
                    'month_year' => $monthYear,
                    'year' => $year,
                    'month' => $monthNum,
                    'transaction_date' => Carbon::create($year, $monthNum, 1)->toDateString(),
                    'notes_raw' => $notesRaw,
                    'leave_types' => $parsedNotes['leave_types'],  // Array of leave type deductions
                    'tardiness' => $parsedNotes['tardiness'],      // Tardiness in minutes
                    'vacation_leave_earned' => self::toFloat(self::getCellValue($worksheet, 'D', $row)),
                    'vacation_leave_used' => self::toFloat(self::getCellValue($worksheet, 'F', $row)),
                    'sick_leave_earned' => self::toFloat(self::getCellValue($worksheet, 'H', $row)),
                    'sick_leave_used' => self::toFloat(self::getCellValue($worksheet, 'J', $row)),
                    'vl_balance' => self::toFloat(self::getCellValue($worksheet, 'M', $row)),
                    'sl_balance' => self::toFloat(self::getCellValue($worksheet, 'N', $row)),
                ];
            }

            if (empty($records)) {
                return [
                    'success' => false,
                    'message' => 'No leave records found in the Excel file. Please check that data starts from row 6 with month names in column A.',
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

    /**
     * Parse Notes column for leave types and tardiness
     * Returns: ['leave_types' => [...], 'tardiness' => minutes]
     */
    private static function parseNotesColumn(string $notes): array
    {
        $result = [
            'leave_types' => [],
            'tardiness' => 0,
        ];

        if (empty($notes)) {
            return $result;
        }

        // Split by forward slash for combined entries (e.g., "VL1/T(0-1-2)")
        $parts = explode('/', trim($notes));

        foreach ($parts as $part) {
            $part = trim($part);

            // Check for Tardiness pattern: T(hours-minutes-seconds)
            if (preg_match('/^T\((\d+)-(\d+)-(\d+)\)$/', $part, $matches)) {
                $hours = (int) $matches[1];
                $minutes = (int) $matches[2];
                $seconds = (int) $matches[3];

                // Convert to total minutes: hours*60 + minutes + seconds/60
                $totalMinutes = ($hours * 60) + $minutes + ($seconds / 60);
                $result['tardiness'] += $totalMinutes;
            }

            // Check for Leave patterns: VL1, SL1, FL1, FL2, ML1, PL1, etc.
            elseif (preg_match('/^([A-Z]+)(\d+)$/', $part, $matches)) {
                $leaveCode = $matches[1];
                $days = (int) $matches[2];

                // Map codes to system leave types
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

    /**
     * Map Pagsanjan leave codes to system leave codes
     */
    private static function mapLeaveCode(string $code): ?string
    {
        $mapping = [
            'VL' => 'VL',  // Vacation Leave
            'SL' => 'SL',  // Sick Leave
            'FL' => 'FL',  // Forced Leave
            'ML' => 'ML',  // Maternity Leave
            'PL' => 'PL',  // Paternity Leave
            'BL' => 'BL',  // Birthday Leave
            'AL' => 'AL',  // Annual Leave
        ];

        return $mapping[$code] ?? null;
    }

    /**
     * Import parsed records: monthly transactions + final balances per year.
     * Records are set to first day of month for consistent date tracking.
     */
    public static function importLeaveRecords(int $employeeId, array $records): array
    {
        DB::beginTransaction();

        try {
            \App\Models\Employee::findOrFail($employeeId);

            $leaveTypes = LeaveType::whereIn('leave_code', ['VL', 'SL', 'FL', 'ML', 'PL', 'BL', 'AL'])
                ->get()
                ->keyBy('leave_code');

            if ($leaveTypes->isEmpty()) {
                throw new \RuntimeException('At least VL and SL leave types must exist before importing records.');
            }

            $importedCount = 0;
            $errors = [];
            $recordsByYear = collect($records)->groupBy('year');

            foreach ($recordsByYear as $year => $yearRecords) {
                /** @var Collection $yearRecords */
                $year = (int) $year;

                // Import VL and SL from earned/used columns (existing logic)
                foreach (['VL' => 'vacation', 'SL' => 'sick'] as $leaveCode => $prefix) {
                    if (!isset($leaveTypes[$leaveCode])) {
                        continue;
                    }

                    $earnedKey = "{$prefix}_leave_earned";
                    $usedKey = "{$prefix}_leave_used";
                    $balanceKey = $leaveCode === 'VL' ? 'vl_balance' : 'sl_balance';

                    $hasData = $yearRecords->contains(function (array $record) use ($earnedKey, $usedKey, $balanceKey) {
                        return ($record[$earnedKey] ?? 0) != 0
                            || ($record[$usedKey] ?? 0) != 0
                            || ($record[$balanceKey] ?? 0) != 0;
                    });

                    if (!$hasData) {
                        continue;
                    }

                    try {
                        $importedCount += self::importLeaveTypeForYear(
                            $employeeId,
                            $leaveCode,
                            $year,
                            $yearRecords,
                            $earnedKey,
                            $usedKey,
                            $balanceKey
                        );
                    } catch (\Exception $e) {
                        $errors[] = "Error importing {$leaveCode} for {$year}: " . $e->getMessage();
                    }
                }

                // Import leave types and tardiness from Notes column (NEW)
                try {
                    $importedCount += self::importNotesColumnData(
                        $employeeId,
                        $year,
                        $yearRecords,
                        $leaveTypes
                    );
                } catch (\Exception $e) {
                    $errors[] = "Error importing notes data for {$year}: " . $e->getMessage();
                }
            }

            if ($importedCount === 0) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => empty($errors)
                        ? 'No leave data could be imported from the file.'
                        : implode(' ', $errors),
                ];
            }

            DB::commit();

            $years = $recordsByYear->keys()->sort()->implode(', ');
            $message = "Successfully imported {$importedCount} leave record(s) for year(s): {$years}.";
            if (!empty($errors)) {
                $message .= ' ' . count($errors) . ' warning(s) occurred.';
            }

            return [
                'success' => true,
                'message' => $message,
                'imported_count' => $importedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import leave types and tardiness from Notes column (Column B)
     */
    private static function importNotesColumnData(
        int $employeeId,
        int $year,
        Collection $yearRecords,
        Collection $leaveTypes
    ): int {
        $importedCount = 0;

        foreach ($yearRecords as $record) {
            $leaveTypesData = $record['leave_types'] ?? [];
            $tardiness = (int) ($record['tardiness'] ?? 0);
            $transactionDate = $record['transaction_date'] ?? now()->toDateString();
            $monthLabel = $record['month_year'] ?? '';

            // Import leave types used from notes (VL1, SL1, FL1, etc.)
            foreach ($leaveTypesData as $leaveTypeData) {
                $code = $leaveTypeData['code'];
                $days = $leaveTypeData['days'];
                $original = $leaveTypeData['original'];

                if (!isset($leaveTypes[$code])) {
                    continue;
                }

                // Get or create leave balance
                $leaveBalance = LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'leave_code' => $code,
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

                if ($days > 0) {
                    $balanceBefore = $leaveBalance->available_credits;
                    $leaveBalance->used_credits += $days;
                    $leaveBalance->available_credits -= $days;
                    $leaveBalance->save();

                    LeaveTransaction::create([
                        'employee_id' => $employeeId,
                        'leave_code' => $code,
                        'year' => $year,
                        'transaction_type' => 'debit',
                        'amount' => $days,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $leaveBalance->available_credits,
                        'reference_type' => 'leave_import',
                        'reference_id' => null,
                        'transaction_date' => $transactionDate,
                        'processed_by' => auth()->id(),
                        'remarks' => "[IMPORT] Used {$days} {$code} ({$original}) - {$monthLabel}",
                    ]);

                    $importedCount++;
                }
            }

            // Import tardiness if present
            if ($tardiness > 0) {
                // Tardiness is typically deducted from VL first, then SL
                $remainingTardiness = $tardiness;

                // Try VL first
                $vlBalance = LeaveBalance::where('employee_id', $employeeId)
                    ->where('leave_code', 'VL')
                    ->where('year', $year)
                    ->first();

                if ($vlBalance && $vlBalance->available_credits > 0) {
                    // Convert minutes to days: tardiness / 480
                    $tardinessDays = round($remainingTardiness / 480, 6);
                    $deductAmount = min($vlBalance->available_credits, $tardinessDays);

                    $balanceBefore = $vlBalance->available_credits;
                    $vlBalance->used_credits += $deductAmount;
                    $vlBalance->available_credits -= $deductAmount;
                    $vlBalance->save();

                    LeaveTransaction::create([
                        'employee_id' => $employeeId,
                        'leave_code' => 'VL',
                        'year' => $year,
                        'transaction_type' => 'debit',
                        'amount' => $deductAmount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $vlBalance->available_credits,
                        'reference_type' => 'leave_import',
                        'reference_id' => null,
                        'transaction_date' => $transactionDate,
                        'processed_by' => auth()->id(),
                        'remarks' => "[IMPORT] Tardiness deduction: {$remainingTardiness} minutes ({$deductAmount} days) from VL - {$monthLabel}",
                    ]);

                    $importedCount++;
                    $remainingTardiness -= ($deductAmount * 480);
                }

                // Try SL if remaining tardiness
                if ($remainingTardiness > 0) {
                    $slBalance = LeaveBalance::where('employee_id', $employeeId)
                        ->where('leave_code', 'SL')
                        ->where('year', $year)
                        ->first();

                    if ($slBalance && $slBalance->available_credits > 0) {
                        $tardinessDays = round($remainingTardiness / 480, 6);
                        $deductAmount = min($slBalance->available_credits, $tardinessDays);

                        $balanceBefore = $slBalance->available_credits;
                        $slBalance->used_credits += $deductAmount;
                        $slBalance->available_credits -= $deductAmount;
                        $slBalance->save();

                        LeaveTransaction::create([
                            'employee_id' => $employeeId,
                            'leave_code' => 'SL',
                            'year' => $year,
                            'transaction_type' => 'debit',
                            'amount' => $deductAmount,
                            'balance_before' => $balanceBefore,
                            'balance_after' => $slBalance->available_credits,
                            'reference_type' => 'leave_import',
                            'reference_id' => null,
                            'transaction_date' => $transactionDate,
                            'processed_by' => auth()->id(),
                            'remarks' => "[IMPORT] Tardiness deduction: {$remainingTardiness} minutes ({$deductAmount} days) from SL - {$monthLabel}",
                        ]);

                        $importedCount++;
                    }
                }
            }
        }

        return $importedCount;
    }

    private static function importLeaveTypeForYear(
        int $employeeId,
        string $leaveCode,
        int $year,
        Collection $yearRecords,
        string $earnedKey,
        string $usedKey,
        string $balanceKey
    ): int {
        $importedCount = 0;
        $totalEarned = round((float) $yearRecords->sum($earnedKey), 6);
        $totalUsed = round((float) $yearRecords->sum($usedKey), 6);
        $lastRecord = $yearRecords->last();
        $finalBalance = round((float) ($lastRecord[$balanceKey] ?? 0), 6);

        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_code' => $leaveCode,
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

        $runningBalance = (float) $leaveBalance->available_credits;

        foreach ($yearRecords as $record) {
            $earned = (float) ($record[$earnedKey] ?? 0);
            $used = (float) ($record[$usedKey] ?? 0);
            $monthLabel = trim($record['month_year'] ?? '');
            $transactionDate = $record['transaction_date'] ?? now()->toDateString();

            if ($earned > 0) {
                $balanceBefore = $runningBalance;
                $runningBalance = round($runningBalance + $earned, 6);

                LeaveTransaction::create([
                    'employee_id' => $employeeId,
                    'leave_code' => $leaveCode,
                    'year' => $year,
                    'transaction_type' => 'credit',
                    'amount' => $earned,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $runningBalance,
                    'reference_type' => 'leave_import',
                    'reference_id' => null,
                    'transaction_date' => $transactionDate,
                    'processed_by' => auth()->id(),
                    'remarks' => "[IMPORT] Earned {$earned} credits ({$monthLabel})",
                ]);
                $importedCount++;
            }

            if ($used > 0) {
                $balanceBefore = $runningBalance;
                $runningBalance = round($runningBalance - $used, 6);

                LeaveTransaction::create([
                    'employee_id' => $employeeId,
                    'leave_code' => $leaveCode,
                    'year' => $year,
                    'transaction_type' => 'debit',
                    'amount' => $used,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $runningBalance,
                    'reference_type' => 'leave_import',
                    'reference_id' => null,
                    'transaction_date' => $transactionDate,
                    'processed_by' => auth()->id(),
                    'remarks' => "[IMPORT] Used {$used} credits ({$monthLabel})",
                ]);
                $importedCount++;
            }
        }

        $leaveBalance->update([
            'total_credits' => $totalEarned,
            'used_credits' => $totalUsed,
            'available_credits' => $finalBalance,
            'carried_over' => 0,
        ]);

        LeaveTransaction::create([
            'employee_id' => $employeeId,
            'leave_code' => $leaveCode,
            'year' => $year,
            'transaction_type' => 'adjustment',
            'amount' => $finalBalance,
            'balance_before' => $runningBalance,
            'balance_after' => $finalBalance,
            'reference_type' => 'leave_import',
            'reference_id' => null,
            'transaction_date' => now()->toDateString(),
            'processed_by' => auth()->id(),
            'remarks' => "[IMPORT] Final {$leaveCode} balance for {$year} set to {$finalBalance}",
        ]);
        $importedCount++;

        return $importedCount;
    }

    private static function detectDataStartRow(Worksheet $worksheet): int
    {
        for ($row = 1; $row <= 20; $row++) {
            $value = trim((string) self::getCellValue($worksheet, 'A', $row));

            if (self::isYearHeader($value)) {
                return $row + 1;
            }

            if ($value !== '' && (self::parseMonthName($value) || self::parseMonthYear($value))) {
                return $row;
            }
        }

        return 6;
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
        return $worksheet->getCell($column . $row)->getCalculatedValue();
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

        return self::MONTH_NAMES[$normalized] ?? null;
    }

    private static function parseMonthYear(string $monthYear): ?Carbon
    {
        $monthYear = trim($monthYear);
        $formats = ['F Y', 'M Y', 'F', 'M', 'Y'];

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
