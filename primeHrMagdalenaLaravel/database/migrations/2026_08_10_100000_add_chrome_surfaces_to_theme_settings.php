<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How the sidebar and the topbar banner are filled — "brand", "dark" or
 * "light" — stored beside the palette on both scopes.
 *
 * A name, not a colour: the surface's label, idle nav tone, hover fill and
 * divider are all measured from it by SystemTheme::surfaceVars(), so a light
 * sidebar gets dark nav text without anybody choosing one.
 *
 * The default is "brand" on both, which is exactly what the app rendered
 * before these columns existed.
 */
return new class extends Migration
{
    private const TABLES = ['system_ai_settings', 'user_theme_settings'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'sidebar_style')) {
                    $blueprint->string('sidebar_style', 20)->default('brand');
                }
                if (! Schema::hasColumn($table, 'topbar_style')) {
                    $blueprint->string('topbar_style', 20)->default('brand');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['sidebar_style', 'topbar_style']);
            });
        }
    }
};
