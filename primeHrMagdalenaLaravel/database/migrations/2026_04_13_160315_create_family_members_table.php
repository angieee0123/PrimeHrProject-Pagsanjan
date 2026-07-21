<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->enum('relationship', ['spouse', 'father', 'mother', 'child']);
            $table->date('birthdate')->nullable();
            $table->string('occupation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
