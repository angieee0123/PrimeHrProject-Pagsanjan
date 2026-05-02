<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accredited_hours_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendance')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('set null');
            $table->date('attendance_date');
            
            // Scheduled times (from employee's schedule)
            $table->time('scheduled_am_in')->nullable();
            $table->time('scheduled_am_out')->nullable();
            $table->time('scheduled_pm_in')->nullable();
            $table->time('scheduled_pm_out')->nullable();
            
            // Actual attendance times
            $table->time('actual_am_in')->nullable();
            $table->time('actual_am_out')->nullable();
            $table->time('actual_pm_in')->nullable();
            $table->time('actual_pm_out')->nullable();
            $table->time('actual_ot_in')->nullable();
            $table->time('actual_ot_out')->nullable();
            
            // Computation breakdown (in minutes)
            $table->unsignedSmallInteger('am_accredited_minutes')->default(0);
            $table->unsignedSmallInteger('pm_accredited_minutes')->default(0);
            $table->unsignedSmallInteger('ot_minutes')->default(0);
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('undertime_minutes')->default(0);
            $table->unsignedSmallInteger('total_accredited_minutes')->default(0);
            $table->unsignedSmallInteger('total_actual_minutes')->default(0);
            
            // Flags
            $table->boolean('am_grace_applied')->default(false);
            $table->boolean('pm_grace_applied')->default(false);
            
            // Metadata
            $table->text('computation_notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accredited_hours_log');
    }
};
