<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One scheduled interview event for one application.
 *
 * Ported from `class Interview` (native port, staff-only scope).
 *
 * `status` is the gate: a scorecard is only legal once a recruiter has moved the
 * interview to COMPLETED. `services/interviews.py` owns that state machine — the
 * port puts it in one service rather than letting each route re-derive it.
 *
 * Soft-delete is audit-compliant removal: a hiring record is never hard-deleted,
 * and `deleted_at` frees the round number for reuse (gap-fill) while keeping the
 * row and its history for auditors. That behaviour needs the partial-uniqueness
 * expressed by the functional index at the bottom of `up()`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // An "application" IS an applicant_profiles row: this schema has no
            // separate applications table.
            $table->foreignUuid('application_id')->constrained('applicant_profiles');
            // Sequential per application: Round 1, Round 2, …
            $table->integer('round');
            $table->string('interview_type', 20);
            $table->dateTime('scheduled_at');
            $table->string('status', 20)->default('SCHEDULED');

            // Denormalized counter so "moved three times" is a column read; the
            // log below stays the source of truth for what the moves were.
            $table->integer('reschedule_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // Soft delete: distinct from CANCELLED, which is terminal and keeps
            // its round.
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['application_id', 'status'], 'idx_interviews_application_status');
            $table->index('status', 'idx_interviews_status');
            $table->index('deleted_at', 'idx_interviews_deleted_at');
        });

        // Soft-delete-aware uniqueness that no Laravel Blueprint method can
        // express: only active (deleted_at IS NULL) rows are constrained by
        // (application_id, round). A deleted row's round becomes NULL in the
        // index expression, and MySQL treats every NULL as DISTINCT — so any
        // number of deleted rows may share a round while two active rows with
        // the same round still collide. That is what lets "delete Round 2 → the
        // next interview retains Round 2" work without a second table.
        //
        // Both MySQL 8.0.13+ (functional key parts) and SQLite (expression
        // indexes) accept this exact statement.
        DB::statement(
            'CREATE UNIQUE INDEX uq_interviews_application_round_active '
            . 'ON interviews (application_id, (CASE WHEN deleted_at IS NULL THEN round ELSE NULL END))'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
