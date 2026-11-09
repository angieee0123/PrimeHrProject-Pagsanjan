<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How long candidate records are kept, as a centrally-set policy value.
 *
 * Ported from `class RetentionSetting` (native port, staff-only scope).
 *
 * One row per jurisdiction, plus the literal `DEFAULT` row that answers for
 * every jurisdiction without one of its own. Deliberately not per-admin
 * configurable: a retention period is compliance-driven under RA 10173 and CSC
 * records-retention guidance, so the value is defined by HR Compliance/Legal and
 * merely entered here by an HR admin. The RBAC gate on the write endpoint is
 * what enforces that.
 *
 * **`jurisdiction_code` is NOT NULL and unique.** MySQL treats every NULL as
 * distinct in a unique index, so a nullable column would silently permit more
 * than one "system-wide default" row and the fallback lookup would start
 * returning whichever one it happened to see first.
 *
 * The table holds only the *current* value; its change history is
 * `retention_setting_changes`, which is what makes "every change is logged"
 * true. Timestamps here are plain UTC instants, not business-calendar dates:
 * they are point-in-time audit stamps, and the business calendar is for
 * calendar-day comparisons, not for "when was this row written".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('jurisdiction_code', 20)->unique('uq_retention_settings_jurisdiction')->default('DEFAULT');
            $table->smallInteger('retention_months');
            // Free-text pointer to the Legal/Compliance memo this value came
            // from. Distinct from updated_by_id, which is only who typed it in:
            // an auditor asking "on what basis?" needs the authority.
            $table->string('authorization_reference')->nullable();

            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_settings');
    }
};
