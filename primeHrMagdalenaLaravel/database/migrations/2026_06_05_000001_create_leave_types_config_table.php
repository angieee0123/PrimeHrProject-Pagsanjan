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
        Schema::create('leave_types_config', function (Blueprint $table) {
            $table->string('leave_code', 10)->primary()->comment('Short code (e.g., VL, SL, SPL, WL)');
            $table->string('leave_name', 100)->comment('Full name (e.g., "Special Leave Privilege")');
            $table->boolean('is_accrued')->default(false)->comment('True for VL/SL (earned 1.25/mo); False for fixed grants');
            $table->decimal('annual_limit', 5, 2)->comment('Max days allowed per year (e.g., 3.00 for SPL, 5.00 for Wellness)');
            $table->boolean('is_cumulative')->default(false)->comment('True if unused days carry over to next year (VL/SL); False if they expire');
            $table->boolean('requires_6_months')->default(false)->comment('If checked, new hires cannot use this until their 6th month (CSC requirement for VL)');
            $table->boolean('is_monetizable')->default(false)->comment('Whether these credits can be converted to cash (Strictly for VL and SL)');
            $table->boolean('requires_attachment')->default(false)->comment('If True, the system will force the user to upload a PDF/Image before submitting');
            $table->text('attachment_info')->nullable()->comment('Instructions for the user (e.g., "Upload Medical Cert if > 5 days")');
            $table->string('document_path')->nullable()->comment('Path to policy document or reference file');
            $table->boolean('is_active')->default(true)->comment('Allows Admin to "soft-delete" or deactivate a leave type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types_config');
    }
};
