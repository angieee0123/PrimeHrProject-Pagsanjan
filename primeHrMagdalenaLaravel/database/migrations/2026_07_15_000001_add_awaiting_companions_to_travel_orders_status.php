<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Travel orders filed with companions start in 'awaiting_companions' until every
 * invited companion has accepted/rejected and the filer forwards the request to
 * HR/admin (which moves it to 'pending').
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
            DB::statement("ALTER TYPE travel_orders_status ADD VALUE IF NOT EXISTS 'awaiting_companions'");
        } else {
            DB::statement("ALTER TABLE travel_orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled', 'disapproved', 'awaiting_companions') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        // Postgres can't drop a single enum value; MySQL would need existing
        // 'awaiting_companions' rows reassigned first. Not reversible in place.
    }
};
