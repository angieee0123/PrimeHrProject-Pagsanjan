<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only history of every time an interview's slot moved.
 *
 * Ported from `class InterviewRescheduleLog` (native port, staff-only scope).
 *
 * Rescheduling updates the existing `interviews` row in place — same id, same
 * round, same panel, new time — because cancel-and-recreate would break the link
 * between the original booking and the new one and force the whole panel to be
 * reassigned. This table is what keeps that in-place edit honest.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_reschedule_log', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('interview_id')->constrained('interviews');
            $table->dateTime('previous_scheduled_at');
            $table->dateTime('new_scheduled_at');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('changed_at')->useCurrent();

            $table->index('interview_id', 'idx_reschedule_interview');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_reschedule_log');
    }
};
