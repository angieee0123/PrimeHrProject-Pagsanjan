<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('system_ai_settings') || Schema::hasColumn('system_ai_settings', 'theme')) {
            return;
        }

        Schema::table('system_ai_settings', function (Blueprint $table) {
            $table->string('theme')->default('default')->after('model');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_ai_settings') || ! Schema::hasColumn('system_ai_settings', 'theme')) {
            return;
        }

        Schema::table('system_ai_settings', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
