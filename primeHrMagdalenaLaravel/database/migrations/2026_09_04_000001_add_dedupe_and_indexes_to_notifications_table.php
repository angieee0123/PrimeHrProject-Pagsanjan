<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two things the notifications table could not do.
 *
 * `dedupe_key` makes a notification idempotent: an approval processed twice —
 * a double-clicked button, a retried request — carries the same key and the
 * second write is dropped rather than putting the same sentence in the bell
 * twice. It is unique *per user*, not globally, because one event legitimately
 * notifies several people with the same key.
 *
 * The indexes match what the panels actually query. Every bell runs
 * `user_id + audience + is_read` (the badge) and `user_id + audience` ordered
 * by `created_at` (the list); the shipped index stopped at `user_id, is_read`,
 * so the audience filter and the ordering were both resolved by scanning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'dedupe_key')) {
                $table->string('dedupe_key', 191)->nullable()->after('related_type');
                $table->unique(['user_id', 'dedupe_key'], 'notifications_user_dedupe_unique');
            }

            $table->index(['user_id', 'audience', 'is_read'], 'notifications_user_audience_read_idx');
            $table->index(['user_id', 'audience', 'created_at'], 'notifications_user_audience_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_audience_read_idx');
            $table->dropIndex('notifications_user_audience_created_idx');

            if (Schema::hasColumn('notifications', 'dedupe_key')) {
                $table->dropUnique('notifications_user_dedupe_unique');
                $table->dropColumn('dedupe_key');
            }
        });
    }
};
