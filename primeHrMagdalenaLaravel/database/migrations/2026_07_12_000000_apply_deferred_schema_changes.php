<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catch-up migration for a set of ALTER migrations whose original filename
 * timestamps place them *before* the CREATE migrations of the tables they
 * modify (the CREATE migrations were re-scaffolded to later 2026 dates). On a
 * fresh install those ALTERs are now guarded to skip (their table doesn't exist
 * yet); this migration — dated after every CREATE — re-applies their net effect
 * so a fresh database ends up matching the live schema.
 *
 * Every step is idempotent (guarded by hasColumn / column-shape), so on an
 * existing database where those ALTERs already ran, this migration is a no-op.
 *
 * MySQL-targeted (the app's database). Skipped on SQLite, which cannot drop a
 * primary key or change a column type in place and is not a deployment target.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // 1) leave_types_config: leave_code PRIMARY  ->  id PRIMARY + leave_code UNIQUE.
        //    Done as one atomic ALTER so leave_code keeps an index throughout and the
        //    FKs pointing at it (leave_applications / leave_balances / leave_transactions)
        //    stay valid during the swap.
        if (Schema::hasTable('leave_types_config') && ! Schema::hasColumn('leave_types_config', 'id')) {
            DB::statement(
                'ALTER TABLE leave_types_config '
                . 'DROP PRIMARY KEY, '
                . 'ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST, '
                . 'ADD UNIQUE KEY leave_types_config_leave_code_unique (leave_code)'
            );
        }

        // 2) leave_accrual_rates: leave_code  ->  leave_type_id (FK to leave_types_config.id).
        if (Schema::hasTable('leave_accrual_rates') && Schema::hasColumn('leave_accrual_rates', 'leave_code')) {
            Schema::table('leave_accrual_rates', function (Blueprint $t) {
                $t->dropForeign(['leave_code']);
                $t->dropColumn('leave_code');
            });
            Schema::table('leave_accrual_rates', function (Blueprint $t) {
                $t->unsignedBigInteger('leave_type_id')->after('id');
                $t->foreign('leave_type_id')->references('id')->on('leave_types_config')->cascadeOnDelete();
            });
        }

        // 3) attendance: time columns -> varchar(10); + attendance_type, remarks.
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $t) {
                foreach (['am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out'] as $c) {
                    $t->string($c, 10)->nullable()->change();
                }
            });
            if (! Schema::hasColumn('attendance', 'attendance_type')) {
                Schema::table('attendance', function (Blueprint $t) {
                    $t->enum('attendance_type', ['REGULAR', 'LEAVE', 'TRAVEL_ORDER', 'HOLIDAY', 'ABSENT'])
                        ->default('REGULAR')->after('total_hours');
                });
            }
            if (! Schema::hasColumn('attendance', 'remarks')) {
                Schema::table('attendance', function (Blueprint $t) {
                    $t->text('remarks')->nullable()->after('attendance_type');
                });
            }
        }

        // 4) accredited_hours_log: deferred late/undertime/LWOP tracking columns.
        if (Schema::hasTable('accredited_hours_log')) {
            $this->addColumnIfMissing('accredited_hours_log', 'late_deducted_from_leave',
                fn (Blueprint $t) => $t->boolean('late_deducted_from_leave')->default(false));
            $this->addColumnIfMissing('accredited_hours_log', 'late_deduction_leave_type',
                fn (Blueprint $t) => $t->string('late_deduction_leave_type', 50)->nullable());
            $this->addColumnIfMissing('accredited_hours_log', 'lwop_minutes',
                fn (Blueprint $t) => $t->integer('lwop_minutes')->default(0));
            $this->addColumnIfMissing('accredited_hours_log', 'requires_salary_deduction',
                fn (Blueprint $t) => $t->boolean('requires_salary_deduction')->default(false));
            $this->addColumnIfMissing('accredited_hours_log', 'undertime_deducted_from_leave',
                fn (Blueprint $t) => $t->boolean('undertime_deducted_from_leave')->default(false));
            $this->addColumnIfMissing('accredited_hours_log', 'undertime_deduction_leave_type',
                fn (Blueprint $t) => $t->string('undertime_deduction_leave_type', 50)->nullable());
        }

        // 5) leave_balances: decimal(8,2) -> decimal(10,6) for exact leave math.
        if (Schema::hasTable('leave_balances')) {
            Schema::table('leave_balances', function (Blueprint $t) {
                foreach (['total_credits', 'used_credits', 'pending_credits', 'available_credits', 'carried_over'] as $c) {
                    $t->decimal($c, 10, 6)->default(0)->change();
                }
            });
        }

        // 6) leave_transactions: decimal(8,2) -> decimal(10,6). reference_type is left
        //    as-is (the live schema keeps it an enum; the historical varchar change had
        //    no net effect).
        if (Schema::hasTable('leave_transactions')) {
            Schema::table('leave_transactions', function (Blueprint $t) {
                foreach (['amount', 'balance_before', 'balance_after'] as $c) {
                    $t->decimal($c, 10, 6)->change();
                }
            });
        }

        // 7) deduction_types: + deducted_from_employee.
        if (Schema::hasTable('deduction_types') && ! Schema::hasColumn('deduction_types', 'deducted_from_employee')) {
            Schema::table('deduction_types', function (Blueprint $t) {
                $t->boolean('deducted_from_employee')->default(true)->after('is_active');
            });
        }
    }

    /**
     * Repair migration — the forward state is the canonical one. Rolling back the
     * primary-key/foreign-key swaps would risk the dependent tables, so down() is
     * intentionally a no-op.
     */
    public function down(): void
    {
        // no-op
    }

    private function addColumnIfMissing(string $table, string $column, \Closure $definition): void
    {
        if (! Schema::hasColumn($table, $column)) {
            Schema::table($table, $definition);
        }
    }
};
