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
        // `deduction_types` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('deduction_types')) {
            return;
        }

        Schema::table('deduction_types', function (Blueprint $table) {
            $table->boolean('deducted_from_employee')->default(true)->after('is_active')
                ->comment('True if deducted from employee salary, False if employer/government share only (for record-keeping)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deferred-safe: on a fresh install this ALTER is ordered before
        // `deduction_types` is created; skip so migrate runs, then a later catch-up migration
        // (…apply_deferred_schema_changes) applies the final schema.
        if (! Schema::hasTable('deduction_types')) {
            return;
        }

        Schema::table('deduction_types', function (Blueprint $table) {
            $table->dropColumn('deducted_from_employee');
        });
    }
};
