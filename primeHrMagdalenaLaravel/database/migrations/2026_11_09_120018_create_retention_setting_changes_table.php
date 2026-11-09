<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only history of every retention-window change.
 *
 * Ported from `class RetentionSettingChange` (native port, staff-only scope).
 *
 * A separate table rather than a richer `audit_log` row: `audit_log` records
 * action/entity/entity_id and nothing else, so an old→new value pair has nowhere
 * to live in it. Encoding the values into the 50-character `action` string would
 * make them unaggregatable.
 *
 * `jurisdiction_code` is denormalized from the setting row so a change stays
 * readable on its own and survives the setting row being deleted.
 * `old_retention_months` is NULL only for the first value ever set for a
 * jurisdiction — there was no previous value, which is a different fact from
 * "the previous value was zero".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_setting_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('setting_id')->constrained('retention_settings');
            $table->string('jurisdiction_code', 20);

            $table->smallInteger('old_retention_months')->nullable();
            $table->smallInteger('new_retention_months');
            $table->string('old_authorization_reference')->nullable();
            $table->string('new_authorization_reference')->nullable();

            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            // Name snapshot, so the history stays readable after the account is
            // removed.
            $table->string('changed_by_name')->nullable();
            $table->dateTime('at')->useCurrent();

            $table->index(['jurisdiction_code', 'at'], 'idx_retention_changes_jurisdiction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_setting_changes');
    }
};
