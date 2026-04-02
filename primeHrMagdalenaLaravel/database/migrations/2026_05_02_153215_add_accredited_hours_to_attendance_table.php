<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Stored in minutes for precision (e.g. 480 = 8 hrs, 240 = 4 hrs)
            $table->unsignedSmallInteger('accredited_hours')->nullable()->after('ot_out');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn('accredited_hours');
        });
    }
};
