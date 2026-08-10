<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A user's personal palette — the top of the theme precedence chain
 * (personal → global → application default).
 *
 * The table already exists on the working database, created outside the
 * migration set: there was no migration file for it, no model, and no code
 * reading it. This migration formalises it so a fresh install gets the same
 * schema, and reshapes what is already there.
 *
 * The existing table was keyed unique on (user_id, role), i.e. one palette
 * per role a user holds — account 1 had amber as an employee and violet as
 * an admin. A theme is a personal preference about how the app looks, not a
 * property of the hat you are wearing, and per-role rows make "Current
 * theme" and "Reset" ambiguous for anyone holding several roles. It is now
 * one row per user, which is also the precedence the rest of the feature
 * documents.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_theme_settings')) {
            Schema::create('user_theme_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('theme')->default('default');
                $table->string('custom_theme_primary', 7)->nullable();
                $table->string('theme_secondary', 7)->nullable();
                $table->string('theme_accent', 7)->nullable();
                $table->string('theme_muted', 7)->nullable();
                $table->timestamps();
            });

            return;
        }

        if (! Schema::hasColumn('user_theme_settings', 'role')) {
            return; // Already reshaped.
        }

        // Collapse to one row per user, keeping each user's most recent pick.
        $keep = DB::table('user_theme_settings')
            ->select('user_id', DB::raw('MAX(id) as keep_id'))
            ->groupBy('user_id')
            ->pluck('keep_id');

        DB::table('user_theme_settings')->whereNotIn('id', $keep)->delete();

        // The foreign key on user_id is backed by the (user_id, role) index,
        // so MySQL refuses to drop that index while the constraint stands.
        // Release it, reshape, then put it back on the new unique index.
        Schema::table('user_theme_settings', function (Blueprint $table) {
            $table->dropForeign('user_theme_settings_user_id_foreign');
        });

        Schema::table('user_theme_settings', function (Blueprint $table) {
            $table->dropUnique('user_theme_settings_user_id_role_unique');
            $table->dropColumn('role');
            $table->unique('user_id');
        });

        Schema::table('user_theme_settings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Deliberately not restoring the per-role shape: the rows that would
        // be needed to rebuild it were merged in up(), so recreating the
        // column would only produce a column of defaults.
        Schema::dropIfExists('user_theme_settings');
    }
};
