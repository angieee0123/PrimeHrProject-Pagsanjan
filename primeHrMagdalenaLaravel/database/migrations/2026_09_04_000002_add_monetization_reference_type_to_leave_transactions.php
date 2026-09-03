<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * leave_transactions.reference_type is an allow-list enum, and the
     * MonetizationRequest approval writes 'monetization' into it. Without
     * this value MySQL rejects the debit rows and every approval fails.
     * Raw ALTER: Laravel's schema builder cannot modify an enum without
     * doctrine/dbal, and the values must be restated in full.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! Schema::hasTable('leave_transactions')) {
            return;
        }

        DB::statement("ALTER TABLE `leave_transactions` MODIFY `reference_type` ENUM('accrual','leave_application','manual_adjustment','carryover','initialization','leave_import','tardiness_deduction','monetization') NOT NULL COMMENT 'What triggered this transaction'");
    }

    /**
     * Reverse the migrations.
     *
     * MySQL refuses this revert while any 'monetization' row exists rather
     * than silently corrupting it — that refusal is the safe behaviour.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! Schema::hasTable('leave_transactions')) {
            return;
        }

        DB::statement("ALTER TABLE `leave_transactions` MODIFY `reference_type` ENUM('accrual','leave_application','manual_adjustment','carryover','initialization','leave_import','tardiness_deduction') NOT NULL COMMENT 'What triggered this transaction'");
    }
};
