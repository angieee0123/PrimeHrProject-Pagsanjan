<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Add attendance_type column to track different types of attendance
            $table->enum('attendance_type', ['REGULAR', 'LEAVE', 'TRAVEL_ORDER', 'HOLIDAY', 'ABSENT'])
                  ->default('REGULAR')
                  ->after('total_hours');
            
            // Add remarks column for additional information
            $table->text('remarks')->nullable()->after('attendance_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['attendance_type', 'remarks']);
        });
    }
};