<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deduction_type_id')->constrained()->onDelete('cascade');
            $table->enum('cutoff_schedule', ['1ST_ONLY', '2ND_ONLY', 'BOTH_SPLIT', 'BOTH_FULL']);
            $table->integer('priority_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_schedules');
    }
};
