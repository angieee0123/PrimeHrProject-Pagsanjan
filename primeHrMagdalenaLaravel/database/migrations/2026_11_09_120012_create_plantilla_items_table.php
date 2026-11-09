<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A budgeted position slot — the stable spine of the plantilla.
 *
 * Ported from `class PlantillaItem` (native port, staff-only scope).
 *
 * **The primary key is a surrogate, and that is the whole point.** The obvious
 * key would be the PSI item number, and it is the wrong one: those numbers are
 * not stable across Personnel Services Itemization years and can shift or be
 * reissued at a renumbering. Keyed on the number, one continuous position would
 * appear as two unrelated items either side of a PSI cycle and its history would
 * split in half exactly when somebody needed to read across it. The number lives
 * on `plantilla_item_versions` instead.
 *
 * Almost nothing lives here: title, grade, office and PSI number are all mutable
 * over an item's life, so all are versioned. What remains is the pair of facts
 * that bracket an item's existence — it was created by some authority, and it
 * may one day be abolished by another. A non-null `abolished_date` is a reliable
 * "this slot no longer exists".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Free text: a citation of a document, not a code the system
            // reasons about.
            $table->string('creation_authority')->nullable();
            $table->date('created_date')->nullable();
            $table->date('abolished_date')->nullable()->index('idx_plantilla_items_abolished');
            $table->string('abolition_authority')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_items');
    }
};
