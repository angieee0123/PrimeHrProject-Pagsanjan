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
        Schema::table('leave_types_config', function (Blueprint $table) {
            $table->dropPrimary('leave_code');
            $table->id()->first();
            $table->unique('leave_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types_config', function (Blueprint $table) {
            $table->dropUnique(['leave_code']);
            $table->dropColumn('id');
            $table->primary('leave_code');
        });
    }
};
