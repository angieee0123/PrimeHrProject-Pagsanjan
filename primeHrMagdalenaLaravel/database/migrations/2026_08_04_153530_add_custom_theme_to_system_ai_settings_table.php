<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_ai_settings') || Schema::hasColumn('system_ai_settings', 'custom_theme_primary')) {
            return;
        }

        Schema::table('system_ai_settings', function (Blueprint $table) {
            $table->string('custom_theme_primary', 7)->nullable()->after('theme');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_ai_settings') || ! Schema::hasColumn('system_ai_settings', 'custom_theme_primary')) {
            return;
        }

        Schema::table('system_ai_settings', function (Blueprint $table) {
            $table->dropColumn('custom_theme_primary');
        });
    }
};
