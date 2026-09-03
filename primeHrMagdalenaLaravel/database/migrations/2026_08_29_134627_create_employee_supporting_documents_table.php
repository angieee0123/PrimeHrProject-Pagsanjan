<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_supporting_documents')) {
            return;
        }

        Schema::create('employee_supporting_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete()->unique();
            // CS / Appointment / Position forms
            $table->string('pds_file_path')->nullable();                    // CS Form 212 — Personal Data Sheet
            $table->string('appointment_form_file_path')->nullable();       // CS Form 33 — Appointment Form
            $table->string('position_description_file_path')->nullable();   // Position Description Form
            // Clearances & examinations
            $table->string('medical_certificate_file_path')->nullable();
            $table->string('nbi_clearance_file_path')->nullable();          // Clearances (NBI Clearance)
            $table->string('financial_clearance_file_path')->nullable();    // Clearance from financial obligations / property accountability
            $table->string('neuro_exam_file_path')->nullable();             // Neuro-psychiatric Examination
            // Professional / performance / disciplinary
            $table->string('licenses_file_path')->nullable();               // Licenses, if necessary
            $table->string('performance_eval_file_path')->nullable();
            $table->string('commendation_file_path')->nullable();           // Commendation / Certificate / Award, etc
            $table->string('disciplinary_file_path')->nullable();           // Disciplinary/Action Documents
            $table->string('other_records_file_path')->nullable();          // Other employee records
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_supporting_documents');
    }
};
