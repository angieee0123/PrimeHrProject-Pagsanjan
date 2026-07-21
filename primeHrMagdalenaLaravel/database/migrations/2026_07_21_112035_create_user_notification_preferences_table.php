<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Opt-out model: every column defaults true so behavior for existing
            // users is unchanged until they explicitly disable a category.
            $table->boolean('leave_requests')->default(true);
            $table->boolean('training_submissions')->default(true);
            $table->boolean('travel_orders')->default(true);
            $table->boolean('employee_requests')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
