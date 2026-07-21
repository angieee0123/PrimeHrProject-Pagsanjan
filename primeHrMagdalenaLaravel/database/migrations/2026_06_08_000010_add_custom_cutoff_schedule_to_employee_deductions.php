<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_deductions', function (Blueprint $table) {
            $table->enum('custom_cutoff_schedule', ['1ST_ONLY', '2ND_ONLY', 'BOTH_FULL', 'BOTH_SPLIT'])->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('employee_deductions', function (Blueprint $table) {
            $table->dropColumn('custom_cutoff_schedule');
        });
    }
};
