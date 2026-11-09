<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The candidate-journey timeline: one row per milestone in an application's
 * post-screening lifecycle.
 *
 * Ported from `class ApplicantEvent` (native port, staff-only scope).
 *
 * Distinct from `audit_log` on purpose: `audit_log` is the tamper-evident
 * security trail (admin/auditor-gated, every entity); this is a candidate-facing
 * narrative that HR staff can also read, scoped to one profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_events', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('applicant_id')->constrained('applicant_profiles');
            $table->string('event_type', 40);
            $table->json('detail')->nullable();

            // Actor id plus a denormalized name, so the timeline stays readable
            // even if the account is later removed.
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();

            $table->dateTime('at')->useCurrent();

            $table->index(['applicant_id', 'at'], 'idx_events_applicant_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_events');
    }
};
