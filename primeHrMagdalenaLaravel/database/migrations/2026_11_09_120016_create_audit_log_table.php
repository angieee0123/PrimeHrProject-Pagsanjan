<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The tamper-evident security trail for recruitment actions.
 *
 * Ported from `class AuditLog` (native port, staff-only scope).
 *
 * `user_name` is a denormalized snapshot: the trail must say who acted in the
 * words the record needs, and must keep saying it after the account changes.
 * `action` is a code (e.g. VACANCY_CLOSED), not a sentence, so actions stay
 * countable.
 *
 * Note this is the *recruitment* log. The HRIS's own `audits` table remains the
 * Laravel-wide audit trail; the two answer different questions and neither
 * replaces the other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action', 50);
            $table->string('entity', 50)->nullable();
            $table->string('entity_id', 36)->nullable();
            $table->dateTime('at')->useCurrent();

            $table->index('action', 'idx_audit_action');
            $table->index('at', 'idx_audit_at');
            $table->index(['entity', 'entity_id'], 'idx_audit_entity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
