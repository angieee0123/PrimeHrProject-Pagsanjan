<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One panelist's evaluation of one interview.
 *
 * Ported from `class InterviewScorecard` (native port, staff-only scope).
 *
 * Per-interview and per-interviewer, not per-application: a panel of three
 * produces three rows for the same application, which is exactly why this could
 * never have been columns on `applicant_profiles`.
 *
 * **There is deliberately no `overall_average_score` column.** One existed in
 * the reference schema and was dropped once proven inert. The mean is computed
 * from `competency_scores` on every read, so it can never outlive a corrected
 * rating. Do not reintroduce it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_scorecards', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('interview_id')->constrained('interviews');
            $table->foreignId('interviewer_id')->constrained('users')->restrictOnDelete();

            // [{key, rating: 1..5, notes}] — the fixed competency list. JSON
            // rather than a child table: fixed-length, always read whole, and
            // nothing queries one competency across scorecards. Promote it if
            // that stops being true.
            $table->json('competency_scores')->nullable();

            $table->text('strengths_notes')->nullable();
            $table->text('concerns_notes')->nullable();
            // SUPPORTS | CONTRADICTS | NEUTRAL — the behavioral cross-check
            // verdict, advisory only. It decides nothing.
            $table->string('behavioral_test_alignment', 20)->nullable();
            $table->text('behavioral_test_alignment_notes')->nullable();
            // STRONG_YES | YES | MAYBE | NO
            $table->string('recommendation', 20)->nullable();
            $table->text('justification_text')->nullable();

            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();

            // One evaluation per panelist per interview.
            $table->unique(['interview_id', 'interviewer_id'], 'uq_scorecards_interview_interviewer');
            $table->index('interview_id', 'idx_scorecards_interview');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_scorecards');
    }
};
