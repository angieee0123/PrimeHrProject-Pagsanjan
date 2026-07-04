<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A handful of early migrations (dated 2024, before `employees`/`users`
 * existed in fresh-install order) create foreign key columns without the
 * constraint itself, since the referenced tables don't exist yet at that
 * point in a from-scratch migrate. This migration adds those constraints
 * once all referenced tables exist. Safe to add at the end regardless of
 * database driver.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('travel_orders')) {
            Schema::table('travel_orders', function (Blueprint $table) {
                if (! $this->hasForeignKey('travel_orders', 'travel_orders_employee_id_foreign')) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                }
                if (! $this->hasForeignKey('travel_orders', 'travel_orders_approved_by_foreign')) {
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('travel_orders')) {
            Schema::table('travel_orders', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropForeign(['approved_by']);
            });
        }
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'pgsql') {
            $result = $connection->select(
                'select 1 from information_schema.table_constraints where table_name = ? and constraint_name = ?',
                [$table, $constraintName]
            );
        } else {
            $result = $connection->select(
                'select 1 from information_schema.table_constraints where table_schema = database() and table_name = ? and constraint_name = ?',
                [$table, $constraintName]
            );
        }

        return count($result) > 0;
    }
};
