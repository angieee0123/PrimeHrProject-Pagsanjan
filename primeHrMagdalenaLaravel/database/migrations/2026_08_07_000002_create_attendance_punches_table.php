<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every individual punch, as it was captured.
 *
 * `attendance` holds one row per employee per day with the *current* value of
 * each slot, so it cannot answer "when was this scanned, by which device, and
 * what did it overwrite". That is exactly what an auditor asks about a time
 * record, and exactly what a biometric device's own log would hold — keeping
 * it here means the record survives the swap from QR to biometric.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_punches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained('attendance')->nullOnDelete();
            $table->date('date');
            $table->enum('slot', ['am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out']);
            $table->dateTime('punched_at');

            // How the punch reached us. 'qr_scan' today; 'biometric' is the
            // same pipeline with a different capture device, which is the
            // whole point of recording this rather than assuming.
            $table->enum('source', ['qr_scan', 'biometric', 'manual'])->default('qr_scan');
            $table->string('device_label', 100)->nullable();

            // The kiosk operator, not the employee — a QR scan proves the card
            // was present, not the person, so the staff member who ran the
            // terminal is the accountable party.
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            // What the slot held before this punch, so a re-scan that replaces
            // an earlier time is reconstructable.
            $table->string('previous_value', 10)->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['date', 'punched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_punches');
    }
};
