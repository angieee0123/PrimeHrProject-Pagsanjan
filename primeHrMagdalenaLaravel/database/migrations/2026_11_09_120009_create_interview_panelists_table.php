<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One interviewer assigned to one interview.
 *
 * Ported from `class InterviewPanelist` (native port, staff-only scope).
 *
 * Its own table rather than a list column on `interviews`, so panelists can be
 * added without rewriting a blob and so "is this user on this panel?" — the check
 * a scorecard submission must make — is an indexed lookup rather than a JSON
 * scan.
 *
 * Deliberately no role column: nothing distinguishes a lead interviewer from a
 * regular panelist, and a column with no reader is not added speculatively.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_panelists', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // cascadeOnDelete mirrors the ORM's delete-orphan: a panelist row
            // has no meaning once its interview is truly gone.
            $table->foreignUuid('interview_id')->constrained('interviews')->cascadeOnDelete();
            // Restrict: a panelist assignment is part of the hiring record, so
            // removing the account must not silently empty a panel.
            $table->foreignId('interviewer_id')->constrained('users')->restrictOnDelete();

            $table->unique(['interview_id', 'interviewer_id'], 'uq_panelists_interview_interviewer');
            $table->index('interview_id', 'idx_panelists_interview');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_panelists');
    }
};
