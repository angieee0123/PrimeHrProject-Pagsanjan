<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One effective-dated description of a plantilla item.
 *
 * Ported from `class PlantillaItemVersion` (native port, staff-only scope).
 *
 * Every attribute that can change without the position ceasing to be itself
 * lives here, with the date the change took effect and the reason. The item's
 * current description is the latest version whose `effective_date` has arrived;
 * its history is all of them in order. An append-only sequence of versions *is*
 * the history — there is deliberately no separate history table.
 *
 * Two distinct mechanisms produce rows, and conflating them was the modelling
 * trap this design avoids:
 *   - RECLASSIFIED — the item keeps its number while title and grade change,
 *     and the incumbent does not move.
 *   - RENUMBERED — a PSI cycle reissues the item under a different number, and
 *     nothing about the position changed.
 *
 * **No `end_date` column.** A version ends when the next one begins, so storing
 * it would duplicate `effective_date` on the following row and create a second
 * thing to keep consistent. The final version has no end: the item's own
 * `abolished_date` is what ends it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_item_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('item_id')->constrained('plantilla_items');
            // Indexed and NOT unique: the same number legitimately appears on
            // every version of one item that kept it, and may be reissued to a
            // different item after a renumbering. Lookup by a *historical*
            // number is a supported search.
            $table->string('psi_item_no', 100);
            $table->string('position_title');
            $table->smallInteger('salary_grade')->nullable();
            $table->string('office');

            $table->date('effective_date');
            // A code from the plantilla service's controlled change-reason list —
            // "how many items were reclassified this year" is a real question and
            // free text cannot answer it.
            $table->string('change_reason', 30);
            // The DBM circular / ordinance authorising this particular change,
            // distinct from the item's creation_authority.
            $table->string('authority_reference')->nullable();
            $table->string('note', 500)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();

            // Two versions of one item cannot take effect on the same day —
            // "which one is current?" would have no answer, and the timeline
            // would render a coin toss. This is the consistency guarantee the
            // read path depends on, so it is a constraint, not a service check.
            $table->unique(['item_id', 'effective_date'], 'uq_item_version_effective');
            $table->index(['item_id', 'effective_date'], 'idx_item_versions_item_effective');
            $table->index('psi_item_no', 'idx_item_versions_psi_item_no');
            $table->index('effective_date', 'idx_item_versions_effective');
            $table->index('change_reason', 'idx_item_versions_change_reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_item_versions');
    }
};
