<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_computations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('payroll_type', ['monthly', 'semi-monthly', 'weekly'])->default('monthly');
            $table->decimal('monthly_rate', 12, 2);
            $table->decimal('daily_rate', 12, 2);
            $table->decimal('hourly_rate', 12, 2);
            $table->unsignedSmallInteger('total_days_present')->default(0);
            $table->unsignedSmallInteger('total_days_absent')->default(0);
            $table->decimal('total_hours_worked', 8, 2)->default(0);
            $table->decimal('total_accredited_hours', 8, 2)->default(0);
            $table->unsignedSmallInteger('total_late_minutes')->default(0);
            $table->unsignedSmallInteger('total_undertime_minutes')->default(0);
            $table->unsignedSmallInteger('total_ot_minutes')->default(0);
            $table->decimal('basic_pay', 12, 2)->default(0);
            $table->decimal('ot_pay', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('undertime_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'paid'])->default('draft');
            $table->foreignId('computed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_start', 'period_end']);
            $table->index('status');
            $table->index(['employee_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_computations');
    }
};
