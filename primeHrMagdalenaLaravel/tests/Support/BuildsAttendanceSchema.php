<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The tables an attendance punch touches, built by hand on the in-memory
 * SQLite connection.
 *
 * The project's own migrations cannot run on the test connection — see
 * CLAUDE.md — so RefreshDatabase is not available and each test builds what it
 * needs. Shared here because the punch pipeline reaches a long way past
 * `attendance`: through accreditation into the daily salary figure and leave
 * balances, and every one of those tables has to exist for a single scan to
 * complete.
 */
trait BuildsAttendanceSchema
{
    protected function createAttendanceSchema(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('photo')->nullable();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('monthly_rate', 12, 2)->default(0);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('am_in')->nullable();
            $table->string('am_out')->nullable();
            $table->string('pm_in')->nullable();
            $table->string('pm_out')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('am_in', 10)->nullable();
            $table->string('am_out', 10)->nullable();
            $table->string('pm_in', 10)->nullable();
            $table->string('pm_out', 10)->nullable();
            $table->string('ot_in', 10)->nullable();
            $table->string('ot_out', 10)->nullable();
            $table->integer('accredited_hours')->nullable();
            $table->integer('total_hours')->nullable();
            $table->string('attendance_type')->default('REGULAR');
            $table->text('remarks')->nullable();
        });

        Schema::create('attendance_punches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->date('date');
            $table->string('slot');
            $table->dateTime('punched_at');
            $table->string('source')->default('qr_scan');
            $table->string('device_label')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->string('previous_value', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_exemptions', function (Blueprint $table) {
            $table->id();
            $table->string('exemption_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_name')->nullable();
            $table->boolean('exempt_from_abandoned')->default(true);
            $table->boolean('exempt_from_incomplete')->default(true);
            $table->text('reason')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('am_in_not_required')->default(false);
            $table->boolean('am_out_not_required')->default(false);
            $table->boolean('pm_in_not_required')->default(false);
            $table->boolean('pm_out_not_required')->default(false);
            $table->boolean('auto_fill_am_out')->default(true);
            $table->boolean('auto_fill_pm_in')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('pass_slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('time_out')->nullable();
            $table->string('time_in')->nullable();
            $table->string('status')->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('accredited_hours_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->integer('am_accredited_minutes')->default(0);
            $table->integer('pm_accredited_minutes')->default(0);
            $table->integer('ot_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->boolean('late_deducted_from_leave')->default(false);
            $table->string('late_deduction_leave_type', 10)->nullable();
            $table->integer('undertime_minutes')->default(0);
            $table->boolean('undertime_deducted_from_leave')->default(false);
            $table->string('undertime_deduction_leave_type', 10)->nullable();
            $table->integer('lwop_minutes')->default(0);
            $table->boolean('requires_salary_deduction')->default(false);
            $table->integer('total_accredited_minutes')->default(0);
            $table->integer('total_actual_minutes')->default(0);
            $table->boolean('am_grace_applied')->default(false);
            $table->boolean('pm_grace_applied')->default(false);
            $table->text('computation_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_salary_computations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('accredited_hours_log_id');
            $table->date('work_date');
            $table->decimal('monthly_rate', 12, 2)->default(0);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('daily_basic_pay', 12, 2)->default(0);
            $table->decimal('ot_pay', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('undertime_deduction', 12, 2)->default(0);
            $table->decimal('daily_gross_pay', 12, 2)->default(0);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_rest_day')->default(false);
            $table->string('holiday_type')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code', 10);
            $table->integer('year');
            $table->decimal('total_credits', 10, 6)->default(0);
            $table->decimal('used_credits', 10, 6)->default(0);
            $table->decimal('pending_credits', 10, 6)->default(0);
            $table->decimal('available_credits', 10, 6)->default(0);
            $table->decimal('carried_over', 10, 6)->default(0);
            $table->timestamps();
        });

        Schema::create('leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code', 10)->nullable();
            $table->string('transaction_type')->nullable();
            $table->decimal('credits', 10, 6)->default(0);
            $table->decimal('balance_before', 10, 6)->default(0);
            $table->decimal('balance_after', 10, 6)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->date('transaction_date')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });
    }

    /** The `users` table, for tests that authenticate a caller. */
    protected function createUsersSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->nullable();
            // EnsureEmailIsVerifiedForArea gates every admin/, mayor/ and
            // employee/ path. Without this column `hasVerifiedEmail()` reads a
            // missing attribute and is false forever, so a test caller is
            // bounced to the verification notice before reaching a controller
            // — the same drift the `restore_email_verified_at_on_users`
            // migration repaired on the live database.
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();
        });
    }
}
