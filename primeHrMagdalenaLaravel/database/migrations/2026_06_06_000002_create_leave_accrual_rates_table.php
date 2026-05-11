<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_accrual_rates', function (Blueprint $table) {
            $table->id();
            $table->string('leave_code', 10);
            $table->foreign('leave_code')->references('leave_code')->on('leave_types_config')->cascadeOnDelete();
            $table->decimal('days_of_service_required', 5, 2)->default(1.00)->comment('Days of service needed to earn credits (e.g., 1 day, 30 days)');
            $table->decimal('credits_earned_per_period', 8, 4)->default(0.0000)->comment('Credits earned per service period (e.g., 0.0417 per day for VL/SL)');
            $table->enum('accrual_frequency', ['daily', 'monthly', 'yearly'])->default('monthly')->comment('How often credits are earned');
            $table->date('effective_date')->comment('When this rate becomes effective');
            $table->date('end_date')->nullable()->comment('When this rate expires (null = current)');
            $table->boolean('is_active')->default(true)->comment('Whether this rate is currently active');
            $table->text('notes')->nullable()->comment('CSC memo or policy reference');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_accrual_rates');
    }
};
