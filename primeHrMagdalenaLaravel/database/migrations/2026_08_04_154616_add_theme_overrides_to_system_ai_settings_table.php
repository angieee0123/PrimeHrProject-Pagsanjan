<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_ai_settings')) {
            return;
        }

        foreach (['theme_secondary', 'theme_accent', 'theme_muted'] as $column) {
            if (Schema::hasColumn('system_ai_settings', $column)) {
                continue;
            }

            Schema::table('system_ai_settings', function (Blueprint $table) use ($column) {
                $table->string($column, 7)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_ai_settings')) {
            return;
        }

        foreach (['theme_secondary', 'theme_accent', 'theme_muted'] as $column) {
            if (! Schema::hasColumn('system_ai_settings', $column)) {
                continue;
            }

            Schema::table('system_ai_settings', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }
};
