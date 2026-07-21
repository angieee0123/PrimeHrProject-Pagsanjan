<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_status')->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('step_increment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_details');
    }
};
