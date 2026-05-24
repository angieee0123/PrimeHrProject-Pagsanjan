<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds:
     * 1. Effectivity dates (start_date, end_date) for time-bound exemptions
     * 2. Granular time entry exemptions (am_in_not_required, am_out_not_required, etc.)
     * 3. Auto-fill flags to automatically populate exempted time entries with schedule defaults
     */
    public function up(): void
    {
        Schema::table('attendance_exemptions', function (Blueprint $table) {
            // Effectivity dates
            $table->date('start_date')->nullable()->after('reason')->comment('Exemption start date (null = no start limit)');
            $table->date('end_date')->nullable()->after('start_date')->comment('Exemption end date (null = no end limit)');
            
            // Granular time entry exemptions
            $table->boolean('am_in_not_required')->default(false)->after('end_date')->comment('AM IN is not required');
            $table->boolean('am_out_not_required')->default(false)->after('am_in_not_required')->comment('AM OUT is not required');
            $table->boolean('pm_in_not_required')->default(false)->after('am_out_not_required')->comment('PM IN is not required');
            $table->boolean('pm_out_not_required')->default(false)->after('pm_in_not_required')->comment('PM OUT is not required');
            
            // Auto-fill configuration
            $table->boolean('auto_fill_am_out')->default(true)->after('pm_out_not_required')->comment('Auto-fill AM OUT with schedule default when not required');
            $table->boolean('auto_fill_pm_in')->default(true)->after('auto_fill_am_out')->comment('Auto-fill PM IN with schedule default when not required');
            
            // Add index for date range queries
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_exemptions', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropColumn([
                'start_date',
                'end_date',
                'am_in_not_required',
                'am_out_not_required',
                'pm_in_not_required',
                'pm_out_not_required',
                'auto_fill_am_out',
                'auto_fill_pm_in',
            ]);
        });
    }
};
