<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration documents the new reference types used for attendance correction
     * leave balance recalculations. No schema changes are needed as the reference_type
     * column already exists and accepts string values.
     * 
     * New reference types:
     * - 'attendance_correction_reversal': Used when reversing previous leave deductions
     *   due to attendance time corrections by admin
     */
    public function up(): void
    {
        // No schema changes needed - this migration is for documentation purposes
        // The leave_transactions table already has a reference_type column that can
        // store these new values:
        // 
        // 1. 'attendance_correction_reversal' - Credits back leave that was previously
        //    deducted when admin corrects attendance times (am_in, am_out, pm_in, pm_out)
        //
        // Example flow:
        // - Original: Employee late 60 mins → 0.125 days deducted from VL
        // - Admin corrects time → Creates reversal transaction crediting back 0.125 days
        // - New calculation: Employee late 30 mins → 0.0625 days deducted from VL
        // - Net result: Employee gains back 0.0625 days VL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No schema changes to reverse
    }
};
