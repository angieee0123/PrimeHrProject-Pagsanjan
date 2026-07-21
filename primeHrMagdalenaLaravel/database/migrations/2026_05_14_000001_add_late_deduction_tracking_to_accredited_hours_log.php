<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `accredited_hours_log` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('accredited_hours_log')) {
            return;
        }

        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->boolean('late_deducted_from_leave')->default(false)->after('late_minutes');
            $table->string('late_deduction_leave_type', 10)->nullable()->after('late_deducted_from_leave');
        });
    }

    public function down(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `accredited_hours_log` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('accredited_hours_log')) {
            return;
        }

        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->dropColumn(['late_deducted_from_leave', 'late_deduction_leave_type']);
        });
    }
};
