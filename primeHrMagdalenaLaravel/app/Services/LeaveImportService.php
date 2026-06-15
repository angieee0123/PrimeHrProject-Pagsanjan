<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
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
                
                $vlEarned = self::toFloat(self::getCellValue($worksheet, 'D', $row));
                $vlUsed = self::toFloat(self::getCellValue($worksheet, 'F', $row));
                $slEarned = self::toFloat(self::getCellValue($worksheet, 'H', $row));
                $slUsed = self::toFloat(self::getCellValue($worksheet, 'J', $row));
                $vlBalance = self::toFloat(self::getCellValue($worksheet, 'M', $row));
                $slBalance = self::toFloat(self::getCellValue($worksheet, 'N', $row));
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
                $year = (int) $year;

                foreach (['VL' => 'vacation', 'SL' => 'sick'] as $leaveCode => $prefix) {
                    if (!isset($leaveTypes[$leaveCode])) {
                        continue;
                    }

                    $earnedKey = "{$prefix}_leave_earned";
                    $usedKey = "{$prefix}_leave_used";
                    $balanceKey = $leaveCode === 'VL' ? 'vl_balance' : 'sl_balance';

                    $hasData = $yearRecords->contains(function (array $record) use ($earnedKey, $usedKey, $balanceKey) {
                        return ($record[$earnedKey] ?? 0) > 0
                            || ($record[$usedKey] ?? 0) > 0
                            || ($record[$balanceKey] ?? 0) > 0;
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
            $recordYear = (int) ($record['year'] ?? $year);
            $recordMonth = (int) ($record['month'] ?? 1);
            $transactionDate = Carbon::create($recordYear, $recordMonth, 1)->toDateString();
            $monthLabel = $record['month_year'] ?? '';

            foreach ($leaveTypesData as $leaveTypeData) {
                $code = $leaveTypeData['code'];
                $days = $leaveTypeData['days'];
                $original = $leaveTypeData['original'];

                if (!isset($leaveTypes[$code])) {
                    continue;
                }

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

            if ($tardiness > 0) {
                $remainingTardiness = $tardiness;

                $vlBalance = LeaveBalance::where('employee_id', $employeeId)
                    ->where('leave_code', 'VL')
                    ->where('year', $year)
                    ->first();

                if ($vlBalance && $vlBalance->available_credits > 0) {
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
            $recordYear = (int) ($record['year'] ?? $year);
            $recordMonth = (int) ($record['month'] ?? 1);
            $transactionDate = Carbon::create($recordYear, $recordMonth, 1)->toDateString();

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
