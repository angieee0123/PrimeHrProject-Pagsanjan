<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old table
        Schema::dropIfExists('daily_salary_computations');
        
        // Create optimized table
        Schema::create('daily_salary_computations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('accredited_hours_log_id')->unique()->constrained('accredited_hours_log')->onDelete('cascade');
            $table->date('work_date');
            
            // Salary rates
            $table->decimal('monthly_rate', 12, 2);
            $table->decimal('daily_rate', 12, 2);
            $table->decimal('hourly_rate', 12, 2);
            
            // Computed pay components
            $table->decimal('daily_basic_pay', 12, 2)->default(0);
            $table->decimal('ot_pay', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('undertime_deduction', 12, 2)->default(0);
            $table->decimal('daily_gross_pay', 12, 2)->default(0);
            
            // Status flags
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_rest_day')->default(false);
            $table->enum('holiday_type', ['regular', 'special'])->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('work_date');
            $table->index(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_salary_computations');
    }
};
