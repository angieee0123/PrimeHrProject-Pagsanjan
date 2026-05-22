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
            // Change time columns to varchar to support 'TO', 'LEAVE', etc.
            $table->string('am_in', 10)->nullable()->change();
            $table->string('am_out', 10)->nullable()->change();
            $table->string('pm_in', 10)->nullable()->change();
            $table->string('pm_out', 10)->nullable()->change();
            $table->string('ot_in', 10)->nullable()->change();
            $table->string('ot_out', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Revert back to time columns (this may cause data loss)
            $table->time('am_in')->nullable()->change();
            $table->time('am_out')->nullable()->change();
            $table->time('pm_in')->nullable()->change();
            $table->time('pm_out')->nullable()->change();
            $table->time('ot_in')->nullable()->change();
            $table->time('ot_out')->nullable()->change();
        });
    }
};