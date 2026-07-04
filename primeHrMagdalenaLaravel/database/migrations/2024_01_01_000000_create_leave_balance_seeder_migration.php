<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: this used to backfill leave_balances from a table named
        // `accrual_rates`, which has never existed in this schema (only
        // `leave_accrual_rates` does), and it ran before `employees`,
        // `leave_types_config`, and `leave_balances` even existed. Dead code
        // on both MySQL and Postgres; left as a no-op rather than guessing
        // at the intended backfill logic.
    }

    public function down(): void
    {
        // No rollback needed
    }
};
