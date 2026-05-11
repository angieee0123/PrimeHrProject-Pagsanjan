<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('leave_code', 10);
            $table->foreign('leave_code')->references('leave_code')->on('leave_types_config')->cascadeOnDelete();
            $table->year('year')->comment('Calendar year for this balance');
            $table->decimal('total_credits', 8, 2)->default(0)->comment('Total credits allocated for the year');
            $table->decimal('used_credits', 8, 2)->default(0)->comment('Credits already used/consumed');
            $table->decimal('pending_credits', 8, 2)->default(0)->comment('Credits in pending leave requests');
            $table->decimal('available_credits', 8, 2)->default(0)->comment('Remaining available credits');
            $table->decimal('carried_over', 8, 2)->default(0)->comment('Credits carried over from previous year (for VL/SL)');
            $table->timestamps();
            
            $table->unique(['employee_id', 'leave_code', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
