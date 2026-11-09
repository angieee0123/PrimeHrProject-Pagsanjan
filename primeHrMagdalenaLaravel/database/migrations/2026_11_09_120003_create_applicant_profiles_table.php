<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per person **per vacancy** — that is, one application.
 *
 * Ported from `class ApplicantProfile` (native port, staff-only scope).
 *
 * Everything the pipeline derives lives here: the extracted `parsed` profile,
 * the `redaction_report` proving PII was stripped before storage, the fit score
 * (via `screening_results`), and the two independent HR decisions —
 * `review_status` (accept/disapprove an application) and `selection_status`
 * (shortlist for interview). HIRED is the one selection value no user sets: it
 * is written by the appointment service in the same transaction as the
 * `appointments` row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('vacancy_id')->constrained('vacancies');
            // Never null in practice, so a query joining candidates cannot
            // silently drop applications.
            $table->foreignUuid('candidate_id')->constrained('candidates');

            // Removed from the live ranking without being deleted. Everything —
            // score, explanation, history — is retained; only rank eligibility
            // changes. The reason is stored because TRANSFERRED and SUPERSEDED
            // read very differently in a candidate's history.
            $table->dateTime('archived_at')->nullable();
            $table->string('archived_reason', 20)->nullable();
            // ORIGINAL | REHIRE | CROSS_POSITION_COPY | CROSS_POSITION_MOVE |
            // RESUME_SUPERSEDED
            $table->string('origin_kind', 20)->default('ORIGINAL');
            // The application a rehire was built from — keeps the provenance
            // chain readable.
            $table->foreignUuid('origin_profile_id')->nullable()->constrained('applicant_profiles')->nullOnDelete();

            // The blinding code shown to screeners instead of a name.
            $table->string('anon_code', 20)->unique();
            $table->string('name')->nullable();
            $table->string('source_file_path', 500);
            $table->string('content_hash', 64)->nullable();

            // Reserved for a later applicant-account link; NULL for every
            // HR-uploaded application under the staff-only scope.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->json('parsed')->nullable();
            $table->json('redaction_report')->nullable();
            $table->string('parse_status', 20)->default('PENDING');

            $table->string('review_status', 20)->default('PENDING');
            // PENDING | QUALIFIED_INTERVIEW | NOT_QUALIFIED | HIRED
            $table->string('selection_status', 20)->default('PENDING');
            $table->string('selection_reason')->nullable();

            // Behavioral Tendencies Questionnaire — reachable only once
            // QUALIFIED_INTERVIEW. Presence of questionnaire_submitted_at is the
            // submitted flag, so no separate boolean is needed.
            $table->json('questionnaire_answers')->nullable();
            $table->dateTime('questionnaire_submitted_at')->nullable();
            // Server-computed, banded, HR-only advisory support. Never exposed
            // on an applicant-facing contract.
            $table->json('behavioral_summary')->nullable();

            $table->dateTime('created_at')->useCurrent();

            $table->index(['vacancy_id', 'parse_status'], 'idx_profiles_vacancy_status');
            $table->index(['vacancy_id', 'content_hash'], 'idx_profiles_vacancy_hash');
            // Duplicate detection looks a hash up ACROSS vacancies, which the
            // composite above cannot serve (wrong leftmost column).
            $table->index('content_hash', 'idx_profiles_content_hash');
            $table->index('candidate_id', 'idx_profiles_candidate');
            $table->index('user_id', 'idx_profiles_user');
            $table->index('review_status', 'idx_profiles_review_status');
            $table->index('selection_status', 'idx_profiles_selection_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_profiles');
    }
};
