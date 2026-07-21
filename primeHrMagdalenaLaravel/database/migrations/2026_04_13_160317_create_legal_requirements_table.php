<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->boolean('saln_submitted')->default(false);
            $table->boolean('oath_of_office')->default(false);
            $table->date('assumption_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_requirements');
    }
};
