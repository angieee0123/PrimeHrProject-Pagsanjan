<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds explicit LWOP (Leave Without Pay) tracking fields to support
     * CSC cascade deduction rules: VL → SL → LWOP/Salary Deduction
     */
    public function up(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->integer('lwop_minutes')->default(0)->after('late_deduction_leave_type')
                ->comment('Minutes to be deducted from salary (Leave Without Pay)');
            
            $table->boolean('requires_salary_deduction')->default(false)->after('lwop_minutes')
                ->comment('Flag indicating salary deduction is required for payroll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            $table->dropColumn(['lwop_minutes', 'requires_salary_deduction']);
        });
    }
};
