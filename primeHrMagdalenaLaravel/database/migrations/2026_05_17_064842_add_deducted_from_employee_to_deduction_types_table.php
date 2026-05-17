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
        Schema::table('deduction_types', function (Blueprint $table) {
            $table->boolean('deducted_from_employee')->default(true)->after('is_active')
                ->comment('True if deducted from employee salary, False if employer/government share only (for record-keeping)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction_types', function (Blueprint $table) {
            $table->dropColumn('deducted_from_employee');
        });
    }
};
