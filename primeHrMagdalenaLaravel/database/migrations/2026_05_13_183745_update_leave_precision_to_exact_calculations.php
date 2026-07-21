<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update decimal precision from (8,2) to (10,6) for exact leave calculations.
     * This ensures that late deductions like 60 minutes = 0.125000 days are stored accurately.
     */
    public function up(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `leave_balances` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('leave_balances')) {
            return;
        }

        // Update leave_balances table
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('total_credits', 10, 6)->default(0)->change();
            $table->decimal('used_credits', 10, 6)->default(0)->change();
            $table->decimal('pending_credits', 10, 6)->default(0)->change();
            $table->decimal('available_credits', 10, 6)->default(0)->change();
            $table->decimal('carried_over', 10, 6)->default(0)->change();
        });

        // Update leave_transactions table
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 6)->change();
            $table->decimal('balance_before', 10, 6)->change();
            $table->decimal('balance_after', 10, 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `leave_balances` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('leave_balances')) {
            return;
        }

        // Revert leave_balances table
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('total_credits', 8, 2)->default(0)->change();
            $table->decimal('used_credits', 8, 2)->default(0)->change();
            $table->decimal('pending_credits', 8, 2)->default(0)->change();
            $table->decimal('available_credits', 8, 2)->default(0)->change();
            $table->decimal('carried_over', 8, 2)->default(0)->change();
        });

        // Revert leave_transactions table
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
            $table->decimal('balance_before', 8, 2)->change();
            $table->decimal('balance_after', 8, 2)->change();
        });
    }
};
