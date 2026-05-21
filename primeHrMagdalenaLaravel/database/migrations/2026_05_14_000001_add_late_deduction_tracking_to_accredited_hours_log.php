<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->boolean('late_deducted_from_leave')->default(false)->after('late_minutes');
            $table->string('late_deduction_leave_type', 10)->nullable()->after('late_deducted_from_leave');
        });
    }

    public function down(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->dropColumn(['late_deducted_from_leave', 'late_deduction_leave_type']);
        });
    }
};
