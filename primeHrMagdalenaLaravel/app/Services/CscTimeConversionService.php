<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * CSC Time Conversion Service
 * 
 * Handles all time conversions according to Civil Service Commission (CSC) standards
 * for Philippine government service.
 * 
 * CSC Standards:
 * - 1 official working day = 8.000 working hours
 * - 0.5 day (Half-day) = 4.000 working hours
 * - 1 hour = 0.125 days (1 / 8)
 * - 1 minute = 0.002083 days (1 / 480) - CSC standard
 * - Working days exclude weekends (Saturday & Sunday) and legal holidays
 */
class CscTimeConversionService
{
    // CSC Constants
    const MINUTES_PER_WORK_DAY = 480;      // 8 hours * 60 minutes
    const HOURS_PER_WORK_DAY = 8;          // Official working hours per day
    const MINUTES_PER_HOUR = 60;
    const DAYS_PER_MINUTE = 0.002083;      // CSC standard (1/480)
    const DAYS_PER_HOUR = 0.125;           // 1/8
    const HOURS_PER_HALF_DAY = 4;
    const MINUTES_PER_HALF_DAY = 240;

    /**
     * Convert days to hours (CSC standard: 1 day = 8 hours)
     * 
     * @param float $days Number of working days
     * @return float Number of hours
     */
    public static function convertDaysToHours(float $days): float
    {
        return $days * self::HOURS_PER_WORK_DAY;
    }

    /**
     * Convert hours to days (CSC standard: 8 hours = 1 day)
     * 
     * @param float $hours Number of hours
     * @return float Number of working days
     */
    public static function convertHoursToDays(float $hours): float
    {
        return $hours / self::HOURS_PER_WORK_DAY;
    }

    /**
     * Convert days to minutes (CSC standard: 1 day = 480 minutes)
     * NO ROUNDING - Uses floor to prevent rounding up
     * 
     * @param float $days Number of working days
     * @return int Number of minutes
     */
    public static function convertDaysToMinutes(float $days): int
    {
        // Use floor instead of round to prevent rounding up
        // 0.125 days = 60 minutes (exact)
        // 0.124999 days = 59.9995 minutes → floor = 59 minutes (not rounded up)
        return (int) floor($days * self::MINUTES_PER_WORK_DAY);
    }

    /**
     * Convert minutes to days (CSC standard: 480 minutes = 1 day)
     * 
     * @param int $minutes Number of minutes
     * @return float Number of working days
     */
    public static function convertMinutesToDays(int $minutes): float
    {
        return $minutes / self::MINUTES_PER_WORK_DAY;
    }

    /**
     * Convert hours to minutes
     * NO ROUNDING - Uses floor to prevent rounding up
     * 
     * @param float $hours Number of hours
     * @return int Number of minutes
     */
    public static function convertHoursToMinutes(float $hours): int
    {
        // Use floor instead of round to prevent rounding up
        return (int) floor($hours * self::MINUTES_PER_HOUR);
    }

    /**
     * Convert minutes to hours
     * 
     * @param int $minutes Number of minutes
     * @return float Number of hours
     */
    public static function convertMinutesToHours(int $minutes): float
    {
        return $minutes / self::MINUTES_PER_HOUR;
    }

    /**
     * Convert minutes to leave credits using CSC standard multiplier
     * Returns negative decimal for deductions (e.g., -0.125 for 1 hour late)
     * 
     * @param int $minutes Number of minutes (tardiness/undertime)
     * @param bool $asNegative Return as negative value for deductions
     * @return float Leave credit equivalent (rounded to 6 decimals)
     */
    public static function convertMinutesToLeaveCredits(int $minutes, bool $asNegative = true): float
    {
        $credits = round($minutes * self::DAYS_PER_MINUTE, 6);
        return $asNegative ? -abs($credits) : $credits;
    }

    /**
     * Calculate working days between two dates
     * Excludes weekends (Saturday & Sunday) and optionally legal holidays
     * 
     * @param Carbon|string $startDate Start date
     * @param Carbon|string $endDate End date
     * @param array $holidays Array of holiday dates (Y-m-d format)
     * @return int Number of working days
     */
    public static function calculateWorkingDays($startDate, $endDate, array $holidays = []): int
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        $workingDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // Exclude weekends (Saturday = 6, Sunday = 0)
            if (!in_array($current->dayOfWeek, [0, 6])) {
                // Exclude holidays
                if (!in_array($current->format('Y-m-d'), $holidays)) {
                    $workingDays++;
                }
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Calculate total working hours between two dates
     * 
     * @param Carbon|string $startDate Start date
     * @param Carbon|string $endDate End date
     * @param array $holidays Array of holiday dates (Y-m-d format)
     * @return float Total working hours
     */
    public static function calculateWorkingHours($startDate, $endDate, array $holidays = []): float
    {
        $workingDays = self::calculateWorkingDays($startDate, $endDate, $holidays);
        return $workingDays * self::HOURS_PER_WORK_DAY;
    }

    /**
     * Calculate total working minutes between two dates
     * 
     * @param Carbon|string $startDate Start date
     * @param Carbon|string $endDate End date
     * @param array $holidays Array of holiday dates (Y-m-d format)
     * @return int Total working minutes
     */
    public static function calculateWorkingMinutes($startDate, $endDate, array $holidays = []): int
    {
        $workingDays = self::calculateWorkingDays($startDate, $endDate, $holidays);
        return $workingDays * self::MINUTES_PER_WORK_DAY;
    }

    /**
     * Format minutes to human-readable format (e.g., "2 hrs 30 min")
     * 
     * @param int $minutes Number of minutes
     * @return string Formatted string
     */
    public static function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 min';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' . $mins . ' min';
        } elseif ($hours > 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '');
        } else {
            return $mins . ' min';
        }
    }

    /**
     * Format hours to human-readable format (e.g., "8.5 hrs")
     * 
     * @param float $hours Number of hours
     * @param int $decimals Number of decimal places
     * @return string Formatted string
     */
    public static function formatHours(float $hours, int $decimals = 1): string
    {
        return number_format($hours, $decimals) . ' hr' . ($hours != 1 ? 's' : '');
    }

    /**
     * Format days to human-readable format (e.g., "2.5 days")
     * 
     * @param float $days Number of days
     * @param int $decimals Number of decimal places
     * @return string Formatted string
     */
    public static function formatDays(float $days, int $decimals = 2): string
    {
        return number_format($days, $decimals) . ' day' . ($days != 1 ? 's' : '');
    }

    /**
     * Check if a date is a weekend (Saturday or Sunday)
     * 
     * @param Carbon|string $date Date to check
     * @return bool True if weekend
     */
    public static function isWeekend($date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return in_array($carbon->dayOfWeek, [0, 6]); // Sunday = 0, Saturday = 6
    }

    /**
     * Check if a date is a working day (not weekend, not holiday)
     * 
     * @param Carbon|string $date Date to check
     * @param array $holidays Array of holiday dates (Y-m-d format)
     * @return bool True if working day
     */
    public static function isWorkingDay($date, array $holidays = []): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        if (self::isWeekend($carbon)) {
            return false;
        }
        
        if (in_array($carbon->format('Y-m-d'), $holidays)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get array of working dates between two dates
     * 
     * @param Carbon|string $startDate Start date
     * @param Carbon|string $endDate End date
     * @param array $holidays Array of holiday dates (Y-m-d format)
     * @return array Array of Carbon dates
     */
    public static function getWorkingDates($startDate, $endDate, array $holidays = []): array
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        $workingDates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            if (self::isWorkingDay($current, $holidays)) {
                $workingDates[] = $current->copy();
            }
            $current->addDay();
        }

        return $workingDates;
    }

    /**
     * Round minutes to nearest CSC-compliant leave credit increment
     * CSC typically uses 0.125 day increments (1 hour = 0.125 days)
     * 
     * @param int $minutes Number of minutes
     * @return float Rounded leave credits
     */
    public static function roundToLeaveIncrement(int $minutes): float
    {
        // Convert to days
        $days = self::convertMinutesToDays($minutes);
        
        // Round to nearest 0.125 (1 hour increment)
        return round($days * 8) / 8;
    }

    /**
     * Calculate leave deduction for tardiness/undertime
     * Returns array with breakdown of deduction
     * 
     * @param int $lateMinutes Late minutes
     * @param int $undertimeMinutes Undertime minutes
     * @return array Deduction breakdown
     */
    public static function calculateLeaveDeduction(int $lateMinutes, int $undertimeMinutes): array
    {
        $totalMinutes = $lateMinutes + $undertimeMinutes;
        $totalDays = self::convertMinutesToDays($totalMinutes);
        $totalHours = self::convertMinutesToHours($totalMinutes);
        
        return [
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalHours, 4),
            'total_days' => round($totalDays, 6),
            'leave_deduction' => -abs(round($totalDays, 6)), // Negative for deduction
            'formatted' => [
                'minutes' => self::formatMinutes($totalMinutes),
                'hours' => self::formatHours($totalHours),
                'days' => self::formatDays($totalDays),
            ],
        ];
    }

    /**
     * Validate if leave application days match actual working days
     * 
     * @param Carbon|string $startDate Start date
     * @param Carbon|string $endDate End date
     * @param float $requestedDays Requested number of days
     * @param array $holidays Array of holiday dates (Y-m-d format)
     * @return array Validation result
     */
    public static function validateLeaveDays($startDate, $endDate, float $requestedDays, array $holidays = []): array
    {
        $actualWorkingDays = self::calculateWorkingDays($startDate, $endDate, $holidays);
        $isValid = abs($actualWorkingDays - $requestedDays) < 0.01; // Allow small floating point difference
        
        return [
            'is_valid' => $isValid,
            'requested_days' => $requestedDays,
            'actual_working_days' => $actualWorkingDays,
            'difference' => $actualWorkingDays - $requestedDays,
            'message' => $isValid 
                ? 'Leave days match working days' 
                : "Mismatch: Requested {$requestedDays} days but date range has {$actualWorkingDays} working days",
        ];
    }
}
