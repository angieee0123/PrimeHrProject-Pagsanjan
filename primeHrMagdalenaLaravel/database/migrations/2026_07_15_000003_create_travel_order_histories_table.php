<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for travel orders: filing, companion invitations/responses,
 * forwarding to HR, approval/disapproval, cancellation.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('travel_order_histories')) {
            return;
        }

        Schema::create('travel_order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained('travel_orders')->onDelete('cascade');
            $table->string('action', 50);
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_order_histories');
    }
};
