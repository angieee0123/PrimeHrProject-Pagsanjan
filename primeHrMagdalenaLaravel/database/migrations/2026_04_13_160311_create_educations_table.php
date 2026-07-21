<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('level')->nullable();
            $table->string('school_name')->nullable();
            $table->string('degree')->nullable();
            $table->string('year_graduated')->nullable();
            $table->string('honors')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
