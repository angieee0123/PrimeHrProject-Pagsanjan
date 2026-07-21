<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('department');
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        // Migrate existing string data to the new FK column
        DB::statement('UPDATE employment_details SET department_id = CAST(department AS UNSIGNED) WHERE department REGEXP \'^[0-9]+$\'');

        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->string('department')->nullable()->after('department_id');
        });

        DB::statement('UPDATE employment_details SET department = CAST(department_id AS CHAR) WHERE department_id IS NOT NULL');

        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
