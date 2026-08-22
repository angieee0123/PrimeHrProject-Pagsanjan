<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs schema drift: `users.email_verified_at` is created by
 * `0001_01_01_000000_create_users_table` and dropped by no migration, yet it
 * was missing from the live `primehrismagdalena` database — removed by hand at
 * some point.
 *
 * `User` implements `MustVerifyEmail`, so without the column
 * `hasVerifiedEmail()` reads a non-existent attribute and returns false for
 * everyone, permanently. That made the `verified` group in `routes/web.php`
 * (employee dashboard, attendance, payslip, leave) unreachable for every
 * employee, and `markEmailAsVerified()` fail with SQLSTATE[42S22] — so the
 * link in the verification email could never succeed either.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        // Every account that exists now was created while verification could
        // not complete, so none of them can ever have been verified. Leaving
        // them NULL would lock current staff out of pages they use today —
        // a regression, not a tightening. Accounts registered from here on
        // start NULL and must verify.
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Deliberately not reversed. The column belongs to the canonical
        // schema in `0001_01_01_000000_create_users_table`; this migration
        // only repairs drift from it. Dropping it here would re-break
        // verification and discard the verification timestamps.
    }
};
