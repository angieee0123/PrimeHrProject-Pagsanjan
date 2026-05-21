<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->string('late_deduction_leave_type', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->string('late_deduction_leave_type', 10)->nullable()->change();
        });
    }
};
