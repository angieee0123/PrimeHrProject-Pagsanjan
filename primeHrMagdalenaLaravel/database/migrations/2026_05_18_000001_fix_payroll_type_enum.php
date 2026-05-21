<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter the payroll_type enum to include all valid types
        DB::statement("ALTER TABLE salary_computations MODIFY COLUMN payroll_type ENUM('regular', '13th_month', 'bonus', 'special', 'monthly', 'semi-monthly', 'weekly') NOT NULL DEFAULT 'regular'");
    }

    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE salary_computations MODIFY COLUMN payroll_type ENUM('monthly', 'semi-monthly', 'weekly') NOT NULL DEFAULT 'monthly'");
    }
};
