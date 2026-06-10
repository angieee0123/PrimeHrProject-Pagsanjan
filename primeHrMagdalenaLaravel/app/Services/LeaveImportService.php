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
     * Parse Pagsanjan-style leave ledger Excel files.
     * Data rows: A=Month, B=Notes, D=VL Earned, F=VL Used, H=SL Earned, J=SL Used, L=Balance, M=VL Balance, N=SL Balance
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

                if (self::isYearHeader($monthYear)) {
                    $year = (int) $monthYear;
                    $lastMonthNum = null;
                    continue;
                }

                if (is_numeric($monthYear)) {
                    continue;
                }

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

                    if ($lastMonthNum !== null && $monthNum <= $lastMonthNum) {
                        $year++;
                    }

                    $lastMonthNum = $monthNum;
                }

                $records[] = [
                    'month_year' => $monthYear,
                    'year' => $year,
                    'month' => $monthNum,
                    'transaction_date' => Carbon::create($year, $monthNum, 1)->toDateString(),
                    'notes' => trim((string) self::getCellValue($worksheet, 'B', $row)),
                    'vacation_leave_earned' => self::toFloat(self::getCellValue($worksheet, 'D', $row)),
                    'vacation_leave_used' => self::toFloat(self::getCellValue($worksheet, 'F', $row)),
                    'sick_leave_earned' => self::toFloat(self::getCellValue($worksheet, 'H', $row)),
                    'sick_leave_used' => self::toFloat(self::getCellValue($worksheet, 'J', $row)),
                    'balance' => self::toFloat(self::getCellValue($worksheet, 'L', $row)),
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
     * Import parsed records: monthly transactions + final balances per year.
     */
    public static function importLeaveRecords(int $employeeId, array $records): array
    {
        DB::beginTransaction();

        try {
            \App\Models\Employee::findOrFail($employeeId);

            $leaveTypes = LeaveType::whereIn('leave_code', ['VL', 'SL'])->get()->keyBy('leave_code');
            if ($leaveTypes->isEmpty()) {
                throw new \RuntimeException('VL and SL leave types must exist before importing records.');
            }

            $importedCount = 0;
            $errors = [];
            $recordsByYear = collect($records)->groupBy('year');

            foreach ($recordsByYear as $year => $yearRecords) {
                /** @var Collection<int, array<string, mixed>> $yearRecords */
                $year = (int) $year;

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
            $notes = trim($record['notes'] ?? '');
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
                    'remarks' => self::buildRemark('Earned', $monthLabel, $notes, $earned),
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
                    'remarks' => self::buildRemark('Used', $monthLabel, $notes, $used),
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
            'remarks' => "[IMPORT] Final {$leaveCode} balance for {$year} set to {$finalBalance} (earned: {$totalEarned}, used: {$totalUsed})",
        ]);
        $importedCount++;

        return $importedCount;
    }

    private static function buildRemark(string $action, string $monthLabel, string $notes, float $amount): string
    {
        $remark = "[IMPORT] {$action} {$amount} credits";
        if ($monthLabel !== '') {
            $remark .= " ({$monthLabel})";
        }
        if ($notes !== '') {
            $remark .= " - {$notes}";
        }

        return $remark;
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
