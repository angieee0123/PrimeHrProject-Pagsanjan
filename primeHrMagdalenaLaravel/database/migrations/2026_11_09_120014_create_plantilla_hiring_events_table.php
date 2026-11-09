<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One hiring/appointment fact attached to a plantilla item's history.
 *
 * Ported from `class PlantillaHiringEvent` (native port, staff-only scope).
 *
 * Records that an attested appointment filled this item, on a date, for a named
 * appointee — the "Previous: Vacant → New: Filled" entry HR reads on the item's
 * timeline.
 *
 * Deliberately NOT a `plantilla_item_versions` row. Versions describe the
 * *position* and drive `current_version`; an appointment changes *occupancy*,
 * and the version table's `uq_item_version_effective` would forbid two same-day
 * hires against one multi-slot item. A side table keeps position history pure
 * while the timeline merges both reads.
 *
 * `appointment_id` is UNIQUE and is the logical event identifier: reprocessing
 * the same appointment returns the existing row instead of inserting a
 * duplicate, so retries and repeated attestations are safe. It is a plain
 * column, not a foreign key, exactly as in the reference — the event is keyed on
 * the appointment, not owned by it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_hiring_events', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('item_id')->constrained('plantilla_items');
            $table->string('appointment_id', 36)->unique('uq_plantilla_hiring_event_appointment');
            $table->foreignUuid('vacancy_id')->nullable()->constrained('vacancies')->nullOnDelete();

            // HR-typed legal-name snapshot — never re-derived from the
            // application, whose name is a layout guess.
            $table->string('appointee_name');
            // Position title as it stood on the effective date.
            $table->string('position_title')->nullable();
            $table->string('nature', 30)->nullable();
            // The business date the timeline orders by, independent of
            // created_at.
            $table->date('effective_date');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();

            // The timeline read: this item's hiring events, in order.
            $table->index(['item_id', 'effective_date'], 'idx_plantilla_hiring_item_effective');
            $table->index('effective_date', 'idx_plantilla_hiring_effective');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_hiring_events');
    }
};
