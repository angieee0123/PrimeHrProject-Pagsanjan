<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An applicant's answer to "may we re-rank you for another position?".
 *
 * Ported from `class RerankConsentRequest` (native port, staff-only scope).
 *
 * **The row exists BEFORE anything is written to the ranking.** Re-ranking a
 * candidate from Position A to Position B is a new processing purpose under
 * RA 10173, so the plan HR approved is parked here and applied only if the
 * candidate says yes. The row carries the *entire* plan — mode, source
 * application, whether the resume is replaced, the acknowledged warnings, the
 * recorded lawful basis — because re-deriving it later is impossible.
 *
 * `token` is the bearer credential in the emailed Confirm/Decline links, so it
 * must never appear on an HR-facing response: an HR user who could read it could
 * consent on the candidate's behalf, which is the exact act this table exists to
 * make impossible.
 *
 * Four terminal readings: CONFIRMED (proceed), DECLINED (no — excluded from
 * *this* vacancy's re-rank only), EXPIRED (window lapsed unanswered — same
 * consequence, tracked apart because a non-response is not a decision), PENDING.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rerank_consent_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('candidate_id')->constrained('candidates');
            $table->foreignUuid('target_vacancy_id')->constrained('vacancies');
            $table->foreignUuid('source_profile_id')->constrained('applicant_profiles');

            // Denormalized at send time so a candidate who later loses their
            // account still has an auditable record of what was sent to whom —
            // the hiring record is a retention obligation.
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email')->nullable();

            // ── The approved plan, frozen ──
            $table->string('mode', 10)->default('COPY');
            $table->boolean('replace_resume')->default(false);
            $table->string('consent_basis', 500)->nullable();
            $table->json('acknowledged')->nullable();
            $table->string('note', 1000)->nullable();

            // PENDING | CONFIRMED | DECLINED | EXPIRED
            $table->string('status', 20)->default('PENDING');
            $table->string('token', 64)->unique();

            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_by_name')->nullable();

            $table->dateTime('sent_at')->useCurrent();
            $table->dateTime('expires_at');
            // Exactly one reminder is ever sent, 48h before expiry. Stamped
            // rather than counted so the sweep is idempotent.
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            // PORTAL | EMAIL_LINK — which surface the answer came from.
            $table->string('response_channel', 20)->nullable();

            $table->dateTime('executed_at')->nullable();
            $table->foreignUuid('created_profile_id')->nullable()->constrained('applicant_profiles')->nullOnDelete();
            // Set when a CONFIRMED request could not be applied (the target
            // closed, or the candidate applied themselves with a different
            // resume). The consent still stands; the execution needs a person,
            // so the recruiter is alerted rather than silently expired.
            $table->string('conflict_code', 40)->nullable();
            $table->string('blocked_reason', 500)->nullable();

            $table->dateTime('created_at')->useCurrent();

            $table->index('candidate_id', 'idx_rerank_consent_candidate');
            $table->index('target_vacancy_id', 'idx_rerank_consent_vacancy');
            $table->index('status', 'idx_rerank_consent_status');
            $table->index('expires_at', 'idx_rerank_consent_expires');
            // The recruiter's read: everything outstanding on one position.
            $table->index(['status', 'target_vacancy_id'], 'idx_rerank_consent_status_vacancy');
            // The sweep's read: what is due to expire or be reminded.
            $table->index(['status', 'expires_at'], 'idx_rerank_consent_status_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rerank_consent_requests');
    }
};
