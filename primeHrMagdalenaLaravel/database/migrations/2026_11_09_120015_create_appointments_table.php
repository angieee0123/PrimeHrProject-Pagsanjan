<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The terminal fact of a successful recruitment: this application ended in an
 * appointment to a position, effective on a date.
 *
 * Ported from `class Appointment` (native port, staff-only scope).
 *
 * **This is the seam to Personnel Administration, not a personnel record.** It
 * can observe a hire; it structurally cannot observe a separation. So it owns
 * the *opening* fact of a plantilla occupancy and will never own the closing one.
 *
 * **Why this is not itself the occupancy row.** A CSC attestation can be
 * *disapproved*, retroactively invalidating the appointment. If this row *were*
 * the occupancy row, a disapproval would mean voiding an occupancy period, which
 * breaks the append-only guarantee the plantilla design rests on. Keeping them
 * apart lets attestation status stay mutable here while occupancy stays
 * immutable there.
 *
 * **Everything about the position is a snapshot, deliberately.** A position can
 * be reclassified in place (same number, new title and grade, incumbent
 * unmoved), and item numbers are not stable across PSI years — so a live join to
 * the position's current title would silently rewrite history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Not unique: a disapproved appointment may be followed by a fresh
            // one for the same person, and the superseded row stays.
            $table->foreignUuid('application_id')->constrained('applicant_profiles');
            $table->foreignUuid('vacancy_id')->constrained('vacancies');
            // Denormalised from the application so the row stays readable on
            // its own, and so it is the person-grain key a future employee
            // record attaches to.
            $table->foreignUuid('candidate_id')->constrained('candidates');
            // Entered by HR, never lifted from the application: this one is
            // backed by a paper instrument and needs the legal name off it.
            $table->string('appointee_name');

            // ── Position snapshot, written once, never updated ───────────────
            $table->string('plantilla_item_no', 100)->nullable();
            $table->string('position_title');
            $table->smallInteger('salary_grade')->nullable();

            // A CODE from a controlled list, not free text: appointments have to
            // be countable by nature for CSC/COA reporting.
            $table->string('nature', 30);
            // A business date, not an instant. Paperwork is routinely recorded
            // after the fact and backdated appointments are normal.
            $table->date('effective_date');

            // ── CSC attestation ─────────────────────────────────────────────
            // PENDING → ATTESTED | DISAPPROVED. The one mutable part of the row.
            $table->string('attestation_status', 20)->default('PENDING');
            $table->string('attestation_reference')->nullable();
            $table->string('attestation_note', 500)->nullable();
            $table->dateTime('attestation_recorded_at')->nullable();
            $table->foreignId('attestation_recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();

            // Carries `vacancy_id` while the appointment is live and NULL once
            // it is DISAPPROVED. A generated column, not a UNIQUE index, because
            // neither MySQL nor SQLite has partial indexes: NULL-as-distinct is
            // what lets every disapproved row collapse without colliding.
            //
            // It carries no UNIQUE constraint, exactly as in the reference:
            // multi-slot vacancies allow more than one live appointment per
            // position, so "at most slots" is a count check under a row lock in
            // the appointment service, which a unique index cannot express.
            $table->string('live_vacancy_guard', 36)
                ->nullable()
                ->storedAs("CASE WHEN attestation_status <> 'DISAPPROVED' THEN vacancy_id END");

            // Serves the slot-capacity check and the per-vacancy listing.
            $table->index(['vacancy_id', 'attestation_status'], 'idx_appointments_vacancy_attestation');
            // Cross-vacancy reporting: "everyone appointed in Q1", by nature.
            $table->index(['nature', 'effective_date'], 'idx_appointments_nature_effective');
            $table->index('effective_date', 'idx_appointments_effective');
            $table->index('attestation_status', 'idx_appointments_attestation_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
