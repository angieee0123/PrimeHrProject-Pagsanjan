<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per *person*, across every application they have made.
 *
 * Ported from `class Candidate` (native port, staff-only scope).
 *
 * **Identity here is asserted, never inferred.** The only automatic match is
 * `user_id` equality. Names are never a matching criterion — a false merge
 * attaches one person's history to another's application in merit-based hiring.
 * `full_name` is display-only and comes from resume extraction, which is a
 * guess about document layout.
 *
 * `user_id` is unique but nullable, and that combination is load-bearing:
 * MySQL treats every NULL as distinct in a unique index, so the many
 * HR-uploaded candidates (no account) coexist while an account owns at most one
 * candidate row. Under the staff-only scope this column is NULL for every
 * HR-uploaded application; it is kept, and still constrained, so a later
 * applicant-account link needs no migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('full_name')->nullable();
            // Search key only — never a matching criterion on its own, because
            // surname collisions make name-only identity matching unsafe.
            $table->string('normalized_name')->nullable();

            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();

            // Soft merge: a merged candidate is a tombstone pointing at the
            // survivor, never a deleted row. Every merge is a human judgement,
            // so some will be wrong and all must be reversible.
            $table->foreignUuid('merged_into_id')->nullable()->constrained('candidates')->nullOnDelete();

            $table->dateTime('first_seen_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();

            $table->index('normalized_name', 'idx_candidates_normalized_name');
            $table->index('merged_into_id', 'idx_candidates_merged_into');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
