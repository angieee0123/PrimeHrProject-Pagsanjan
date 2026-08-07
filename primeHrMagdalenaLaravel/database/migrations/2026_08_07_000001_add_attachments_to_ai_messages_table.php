<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Structured payload that accompanied an assistant turn — the table spec
     * and its rows, file cards, and chart specs — so reopening a conversation
     * replays what the user actually saw instead of the prose alone.
     *
     * Deliberately not stored here: the rendered chart SVG (derived from the
     * spec, re-rendered on read) and the export token (expires after an hour,
     * re-issued on read).
     *
     * Guarded because an unmerged branch added the same column under a
     * different migration name; databases that followed that branch already
     * have it, and this file must not fail on them.
     */
    public function up(): void
    {
        if (Schema::hasColumn('ai_messages', 'attachments')) {
            return;
        }

        Schema::table('ai_messages', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('ai_messages', 'attachments')) {
            return;
        }

        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
