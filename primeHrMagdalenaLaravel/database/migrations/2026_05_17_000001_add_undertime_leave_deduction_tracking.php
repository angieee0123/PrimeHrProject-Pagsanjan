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
            // Add undertime leave deduction tracking fields (same as late deduction)
            $table->boolean('undertime_deducted_from_leave')
                ->default(false)
                ->after('undertime_minutes')
                ->comment('Flag indicating if undertime was covered by leave credits');
            
            $table->string('undertime_deduction_leave_type', 50)
                ->nullable()
                ->after('undertime_deducted_from_leave')
                ->comment('Which leave type was used to cover undertime (e.g., VL, SL)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->dropColumn([
                'undertime_deducted_from_leave',
                'undertime_deduction_leave_type'
            ]);
        });
    }
};
