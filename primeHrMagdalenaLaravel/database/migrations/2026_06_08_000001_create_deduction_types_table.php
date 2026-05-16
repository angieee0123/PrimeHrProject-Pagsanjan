<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->enum('category', ['MANDATORY', 'LOAN', 'OTHER']);
            $table->enum('computation_type', ['PERCENTAGE', 'FIXED', 'CUSTOM']);
            $table->decimal('percentage_rate', 5, 2)->nullable();
            $table->enum('base_salary_type', ['BASIC', 'GROSS', 'MONTHLY', 'CUSTOM'])->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_types');
    }
};
