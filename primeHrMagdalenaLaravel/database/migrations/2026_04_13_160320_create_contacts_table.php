<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mobile', 'landline', 'emergency']);
            $table->string('number');
            $table->string('contact_person')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
