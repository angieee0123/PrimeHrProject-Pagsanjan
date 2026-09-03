<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employees invited as companions on a travel order. Each companion must
 * accept or reject the invitation before the filer can forward the travel
 * order to HR/admin for final approval.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('travel_order_companions')) {
            return;
        }

        Schema::create('travel_order_companions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained('travel_orders')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['travel_order_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_order_companions');
    }
};
