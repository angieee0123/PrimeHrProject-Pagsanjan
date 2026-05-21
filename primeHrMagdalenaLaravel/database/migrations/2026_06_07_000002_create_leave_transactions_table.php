<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code', 10);
            $table->integer('year')->comment('Year this transaction applies to');
            $table->enum('transaction_type', ['credit', 'debit', 'pending', 'reversal', 'adjustment'])->comment('Type of transaction');
            $table->decimal('amount', 10, 6)->comment('Number of days (positive for credit, negative for debit)');
            $table->decimal('balance_before', 10, 6)->comment('Available balance before transaction');
            $table->decimal('balance_after', 10, 6)->comment('Available balance after transaction');
            $table->enum('reference_type', ['accrual', 'leave_application', 'manual_adjustment', 'carryover', 'initialization'])->comment('What triggered this transaction');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of related record (e.g., leave_application_id)');
            $table->date('transaction_date');
            $table->unsignedBigInteger('processed_by')->nullable()->comment('User ID who processed this transaction');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_code')->references('leave_code')->on('leave_types_config')->onDelete('restrict');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['employee_id', 'leave_code', 'year']);
            $table->index('transaction_date');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_transactions');
    }
};
