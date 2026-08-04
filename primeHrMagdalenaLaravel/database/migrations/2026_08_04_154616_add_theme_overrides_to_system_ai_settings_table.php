<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_ai_settings', function (Blueprint $table) {
            $table->string('theme_secondary', 7)->nullable()->after('custom_theme_primary');
            $table->string('theme_accent',    7)->nullable()->after('theme_secondary');
            $table->string('theme_muted',     7)->nullable()->after('theme_accent');
        });
    }

    public function down(): void
    {
        Schema::table('system_ai_settings', function (Blueprint $table) {
            $table->dropColumn(['theme_secondary', 'theme_accent', 'theme_muted']);
        });
    }
};
