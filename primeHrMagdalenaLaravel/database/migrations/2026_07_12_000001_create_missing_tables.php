<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recreates tables that exist in the live database but had no CREATE migration
 * in the repository (they were created manually / by since-deleted migrations,
 * or shipped as a vendor migration that was never published — e.g. Sanctum's
 * personal_access_tokens). Without these, a fresh install is missing tables the
 * app depends on (API tokens, user settings, chatbot history, loans, payroll
 * deductions).
 *
 * Every block is guarded by hasTable(), so this is a no-op on the existing
 * database where the tables are already present. Created in FK-dependency order.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sanctum API tokens (no FK).
        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('tokenable_type');
                $table->unsignedBigInteger('tokenable_id');
                $table->index(['tokenable_type', 'tokenable_id']);
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        // Per-user notification / session preferences.
        if (! Schema::hasTable('user_settings')) {
            Schema::create('user_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique('uq_user');
                $table->boolean('notif_payslip')->default(true);
                $table->boolean('notif_leave')->default(true);
                $table->boolean('notif_dtr')->default(true);
                $table->boolean('notif_attendance')->default(false);
                $table->boolean('notif_email_digest')->default(true);
                $table->integer('session_timeout')->default(30);
                $table->boolean('two_factor')->default(false);
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // Chatbot conversation log.
        if (! Schema::hasTable('chat_history')) {
            Schema::create('chat_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('session_id')->nullable()->index();
                $table->longText('question');
                $table->longText('response');
                $table->enum('question_type', ['database', 'system', 'greeting', 'error'])->nullable()->default('system')->index();
                $table->json('follow_up_questions')->nullable();
                $table->json('codebase_files_used')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent()->index();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
                $table->index(['user_id', 'session_id'], 'idx_user_session');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // Employee loans (GSIS / Pag-IBIG / bank).
        if (! Schema::hasTable('employee_loans')) {
            Schema::create('employee_loans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->enum('loan_type', ['GSIS_CONSO', 'GFAL', 'PAGIBIG_MPL', 'LBP', 'UCPB']);
                $table->decimal('principal_amount', 12, 2);
                $table->decimal('monthly_amortization', 10, 2);
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'completed', 'suspended'])->default('active');
                $table->timestamps();
                $table->foreign('employee_id')->references('id')->on('employees');
            });
        }

        // Per-cutoff payroll deduction snapshot.
        if (! Schema::hasTable('payroll_deductions')) {
            Schema::create('payroll_deductions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('period_start');
                $table->date('period_end');
                $table->enum('cutoff', ['1-15', '16-31']);
                $table->decimal('basic_salary', 10, 2);
                $table->decimal('gsis_personal', 10, 2)->default(0);
                $table->decimal('philhealth_personal', 10, 2)->default(0);
                $table->decimal('pagibig_personal', 10, 2)->default(0);
                $table->decimal('withholding_tax', 10, 2)->default(0);
                $table->decimal('total_loans', 10, 2)->default(0);
                $table->decimal('gross_pay', 10, 2);
                $table->decimal('total_deductions', 10, 2);
                $table->decimal('net_pay', 10, 2);
                $table->boolean('net_pay_protected')->default(false);
                $table->timestamps();
                $table->foreign('employee_id')->references('id')->on('employees');
            });
        }

        // Line items linking a payroll deduction to the loans it paid.
        if (! Schema::hasTable('deduction_loan_items')) {
            Schema::create('deduction_loan_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_deduction_id');
                $table->unsignedBigInteger('employee_loan_id');
                $table->decimal('amount_due', 10, 2);
                $table->decimal('amount_deducted', 10, 2);
                $table->boolean('skipped_due_to_threshold')->default(false);
                $table->timestamps();
                $table->foreign('payroll_deduction_id')->references('id')->on('payroll_deductions')->cascadeOnDelete();
                $table->foreign('employee_loan_id')->references('id')->on('employee_loans');
            });
        }
    }

    public function down(): void
    {
        // Drop in reverse FK order. Guarded so it's safe regardless of state.
        Schema::dropIfExists('deduction_loan_items');
        Schema::dropIfExists('payroll_deductions');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('chat_history');
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('personal_access_tokens');
    }
};
