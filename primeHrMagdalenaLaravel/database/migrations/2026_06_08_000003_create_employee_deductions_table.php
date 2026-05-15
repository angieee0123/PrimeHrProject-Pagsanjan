<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('deduction_type_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('remaining_balance', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->enum('status', ['ACTIVE', 'COMPLETED', 'SUSPENDED'])->default('ACTIVE');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_deductions');
    }
};
