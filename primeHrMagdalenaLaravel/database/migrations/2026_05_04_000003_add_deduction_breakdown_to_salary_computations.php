<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_computations', function (Blueprint $table) {
            // Add JSON column to store detailed deduction breakdown
            $table->json('deduction_breakdown')->nullable()->after('other_deductions');
            
            // Add pay date column
            $table->date('pay_date')->nullable()->after('period_end');
        });
    }

    public function down(): void
    {
        Schema::table('salary_computations', function (Blueprint $table) {
            $table->dropColumn(['deduction_breakdown', 'pay_date']);
        });
    }
};
