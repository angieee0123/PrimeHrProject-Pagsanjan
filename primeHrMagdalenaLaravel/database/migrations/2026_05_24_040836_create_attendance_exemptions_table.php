<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores attendance exemption configurations for employees, departments,
     * or designations that should not be flagged as abandoned/incomplete due to the
     * nature of their work (e.g., field workers, flexible schedules).
     */
    public function up(): void
    {
        Schema::create('attendance_exemptions', function (Blueprint $table) {
            $table->id();
            $table->enum('exemption_type', ['employee', 'department', 'designation'])->comment('Type of exemption');
            $table->unsignedBigInteger('reference_id')->comment('ID of employee, department, or designation');
            $table->string('reference_name')->comment('Name for display purposes');
            $table->boolean('exempt_from_abandoned')->default(true)->comment('Exempt from abandoned flag');
            $table->boolean('exempt_from_incomplete')->default(true)->comment('Exempt from incomplete flag');
            $table->text('reason')->nullable()->comment('Reason for exemption');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['exemption_type', 'reference_id']);
            $table->index('created_by');
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exemptions');
    }
};
