<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->decimal('monthly_rate', 12, 2)->nullable()->after('salary_grade');
        });
    }

    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->dropColumn('monthly_rate');
        });
    }
};
