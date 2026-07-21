<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('appointment_status')->nullable();
            $table->boolean('is_government')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
