<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A possible duplicate a human has to decide about.
 *
 * Ported from `class DuplicateFlag` (native port, staff-only scope).
 *
 * Nothing here is ever auto-resolved. The only signal raised is
 * SAME_RESUME_DIFFERENT_CANDIDATE — a byte-identical `content_hash` held by two
 * different candidate records — and even that is a suggestion, because two
 * people submitting the same downloadable form and one person split across two
 * records produce identical bytes.
 *
 * `counterpart_candidate_id` is what LINK_CANDIDATES merges into; it is
 * separate from `counterpart_profile_id` because the resume-level kinds point at
 * a competing *file* while the identity-level kind points at a competing
 * *person*.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('kind', 40);
            // OPEN | RESOLVED | DISMISSED
            $table->string('status', 20)->default('OPEN');

            $table->foreignUuid('candidate_id')->constrained('candidates');
            $table->foreignUuid('counterpart_candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            $table->foreignUuid('vacancy_id')->nullable()->constrained('vacancies')->nullOnDelete();
            $table->foreignUuid('profile_id')->nullable()->constrained('applicant_profiles')->nullOnDelete();
            $table->foreignUuid('counterpart_profile_id')->nullable()->constrained('applicant_profiles')->nullOnDelete();

            $table->json('detail')->nullable();
            $table->string('resolution', 40)->nullable();
            $table->foreignId('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
            // Denormalized name snapshot so the row stays readable on its own.
            $table->string('resolved_by')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('kind', 'idx_flags_kind');
            $table->index('status', 'idx_flags_status');
            // The review queue's own read: open flags, newest first.
            $table->index(['status', 'created_at'], 'idx_flags_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_flags');
    }
};
