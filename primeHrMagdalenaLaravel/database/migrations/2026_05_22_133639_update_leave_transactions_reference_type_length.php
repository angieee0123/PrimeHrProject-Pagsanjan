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
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `leave_transactions` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('leave_transactions')) {
            return;
        }

        Schema::table('leave_transactions', function (Blueprint $table) {
            // Increase reference_type column length to accommodate 'attendance_correction_reversal'
            $table->string('reference_type', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `leave_transactions` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('leave_transactions')) {
            return;
        }

        Schema::table('leave_transactions', function (Blueprint $table) {
            // Revert to original length if needed
            $table->string('reference_type', 30)->nullable()->change();
        });
    }
};
