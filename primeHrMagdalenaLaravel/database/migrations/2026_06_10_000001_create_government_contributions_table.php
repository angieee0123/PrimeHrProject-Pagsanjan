<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table for GSIS, PhilHealth, Pag-IBIG contribution rates
        Schema::create('contribution_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['GSIS', 'PhilHealth', 'PagIBIG', 'Tax'])->index();
            $table->decimal('salary_from', 12, 2)->nullable();
            $table->decimal('salary_to', 12, 2)->nullable();
            $table->decimal('employee_rate', 8, 4)->nullable(); // Percentage (e.g., 9.00 for 9%)
            $table->decimal('employer_rate', 8, 4)->nullable(); // Percentage
            $table->decimal('fixed_employee', 10, 2)->nullable(); // Fixed amount
            $table->decimal('fixed_employer', 10, 2)->nullable(); // Fixed amount
            $table->decimal('additional_rate', 8, 4)->nullable(); // For tax brackets
            $table->decimal('base_tax', 10, 2)->nullable(); // For tax computation
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->timestamps();
            
            $table->index(['type', 'is_active', 'effective_date']);
        });

        // Table for employee loans
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('loan_type', ['GSIS', 'PagIBIG', 'Salary', 'Emergency', 'Other']);
            $table->string('loan_number')->nullable();
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_rate', 8, 4)->default(0);
            $table->decimal('monthly_amortization', 10, 2);
            $table->integer('term_months');
            $table->integer('months_paid')->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Active', 'Completed', 'Suspended', 'Cancelled'])->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'status']);
        });

        // Table for other deductions (union, coop, insurance, etc.)
        Schema::create('other_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('deduction_type'); // Union Dues, Cooperative, Insurance, etc.
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['Monthly', 'Semi-Monthly', 'One-Time'])->default('Monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['employee_id', 'is_active']);
        });

        // Add columns to daily_salary_computations table
        Schema::table('daily_salary_computations', function (Blueprint $table) {
            $table->decimal('gsis_contribution', 10, 2)->default(0)->after('undertime_deduction');
            $table->decimal('philhealth_contribution', 10, 2)->default(0)->after('gsis_contribution');
            $table->decimal('pagibig_contribution', 10, 2)->default(0)->after('philhealth_contribution');
            $table->decimal('withholding_tax', 10, 2)->default(0)->after('pagibig_contribution');
            $table->decimal('loan_deductions', 10, 2)->default(0)->after('withholding_tax');
            $table->decimal('other_deductions', 10, 2)->default(0)->after('loan_deductions');
            $table->decimal('total_deductions', 10, 2)->default(0)->after('other_deductions');
            $table->decimal('net_pay', 10, 2)->default(0)->after('total_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('daily_salary_computations', function (Blueprint $table) {
            $table->dropColumn([
                'gsis_contribution',
                'philhealth_contribution',
                'pagibig_contribution',
                'withholding_tax',
                'loan_deductions',
                'other_deductions',
                'total_deductions',
                'net_pay'
            ]);
        });
        
        Schema::dropIfExists('other_deductions');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('contribution_rates');
    }
};
