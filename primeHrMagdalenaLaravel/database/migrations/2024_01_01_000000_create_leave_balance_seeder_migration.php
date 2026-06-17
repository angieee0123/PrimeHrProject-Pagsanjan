<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration ensures all employees have leave balances for the current year
        DB::statement('
            INSERT INTO leave_balances (employee_id, leave_code, year, total_credits, used_credits, pending_credits, available_credits, carried_over, created_at, updated_at)
            SELECT 
                e.id,
                lt.leave_code,
                YEAR(NOW()),
                COALESCE(ar.annual_credits, 0),
                0,
                0,
                COALESCE(ar.annual_credits, 0),
                0,
                NOW(),
                NOW()
            FROM employees e
            CROSS JOIN leave_types_config lt
            LEFT JOIN accrual_rates ar ON lt.leave_code = ar.leave_code
            LEFT JOIN leave_balances lb ON e.id = lb.employee_id 
                AND lt.leave_code = lb.leave_code 
                AND lb.year = YEAR(NOW())
            WHERE lt.is_active = 1
                AND e.created_at <= NOW()
                AND lb.id IS NULL
            ON DUPLICATE KEY UPDATE updated_at = NOW()
        ');
    }

    public function down(): void
    {
        // No rollback needed
    }
};
