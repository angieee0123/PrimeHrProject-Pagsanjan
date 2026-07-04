<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * travel_orders.status has always been enum('pending','approved','rejected','cancelled'),
 * missing 'disapproved' even though the disapproval feature (disapproved_by/at/reason
 * columns, TravelOrderController's disapproved-orders tab) already exists and relies on
 * it. Add the missing value so those code paths stop throwing invalid-enum errors.
 */
return new class extends Migration
{
    /**
     * Postgres can't add an enum value and use it in the same transaction,
     * so this migration must not run inside one.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TYPE travel_orders_status ADD VALUE IF NOT EXISTS 'disapproved'");
        } else {
            DB::statement("ALTER TABLE travel_orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled', 'disapproved') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        // Postgres can't drop a single enum value; MySQL would need existing
        // 'disapproved' rows reassigned first. Not reversible in place.
    }
};
