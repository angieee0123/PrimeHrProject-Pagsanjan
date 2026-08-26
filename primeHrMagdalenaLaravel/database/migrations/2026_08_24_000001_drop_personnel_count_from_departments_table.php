<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `departments.personnel_count` was a typed-in integer: set once from the Add
 * Department form or a CSV column, and never recomputed. It answered "how many
 * people work here", which is a question `employment_details.department_id`
 * already answers exactly — so the column could only ever drift away from the
 * truth, and it had. Every one of the 26 rows held 0 while 14 employees were
 * assigned across 10 offices, which made the Personnel column, the "Total
 * Personnel" stat card and "Largest Office" all read 0.
 *
 * `DepartmentController::index()` now derives it with
 * `withCount(['employmentDetails as personnel_count'])`. That alias only
 * resolves cleanly once the real column is gone: `select departments.*` plus a
 * subquery of the same name would put two `personnel_count` keys in the same
 * result row, and which one survived into the model would be down to driver
 * behaviour. Dropping the column is what makes the derived figure the only
 * figure.
 *
 * Nothing is lost. Every stored value is 0, so down() restores the column to
 * exactly the state this migration found it in.
 *
 * Note `departments` is in SafeSqlService::ALLOWED_TABLES — the AI assistant
 * could generate SQL against this column and answer "how many people work in
 * the Mayor's Office" with 0. Removing it forces that question onto
 * `employment_details`, where the answer is real.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('personnel_count');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->integer('personnel_count')->default(0)->after('head');
        });
    }
};
