<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('hours')->nullable();
            $table->string('conducted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
