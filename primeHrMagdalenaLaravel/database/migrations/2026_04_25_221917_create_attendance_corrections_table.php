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
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->time('old_am_in')->nullable();
            $table->time('old_am_out')->nullable();
            $table->time('old_pm_in')->nullable();
            $table->time('old_pm_out')->nullable();
            $table->time('old_ot_in')->nullable();
            $table->time('old_ot_out')->nullable();
            $table->time('new_am_in')->nullable();
            $table->time('new_am_out')->nullable();
            $table->time('new_pm_in')->nullable();
            $table->time('new_pm_out')->nullable();
            $table->time('new_ot_in')->nullable();
            $table->time('new_ot_out')->nullable();
            $table->text('reason');
            $table->json('attachments');
            $table->unsignedBigInteger('corrected_by');
            $table->timestamps();
            
            $table->foreign('attendance_id')->references('id')->on('attendance')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('corrected_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};
