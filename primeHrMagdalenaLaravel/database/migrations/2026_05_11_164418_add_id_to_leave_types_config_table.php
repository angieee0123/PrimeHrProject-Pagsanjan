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
        // `leave_types_config` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('leave_types_config')) {
            return;
        }

        Schema::table('leave_types_config', function (Blueprint $table) {
            $table->dropPrimary('leave_code');
            $table->id()->first();
            $table->unique('leave_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `leave_types_config` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('leave_types_config')) {
            return;
        }

        Schema::table('leave_types_config', function (Blueprint $table) {
            $table->dropUnique(['leave_code']);
            $table->dropColumn('id');
            $table->primary('leave_code');
        });
    }
};
