<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Small helpers for raw SQL fragments that differ between MySQL (used
 * locally) and Postgres (used in production), so callers don't need to
 * branch on the driver themselves.
 */
class SqlCompat
{
    protected static function isPgsql(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    public static function year(string $column): string
    {
        return static::isPgsql() ? "EXTRACT(YEAR FROM $column)" : "YEAR($column)";
    }

    public static function month(string $column): string
    {
        return static::isPgsql() ? "EXTRACT(MONTH FROM $column)" : "MONTH($column)";
    }

    public static function day(string $column): string
    {
        return static::isPgsql() ? "EXTRACT(DAY FROM $column)" : "DAY($column)";
    }

    /**
     * SQL fragment that is true when $column falls on a Saturday or Sunday.
     */
    public static function isWeekend(string $column): string
    {
        return static::isPgsql()
            ? "EXTRACT(DOW FROM $column) IN (0, 6)"
            : "DAYOFWEEK($column) IN (1, 7)";
    }

    /**
     * SQL fragment that is true when $column falls on a weekday.
     */
    public static function isNotWeekend(string $column): string
    {
        return static::isPgsql()
            ? "EXTRACT(DOW FROM $column) NOT IN (0, 6)"
            : "DAYOFWEEK($column) NOT IN (1, 7)";
    }
}
