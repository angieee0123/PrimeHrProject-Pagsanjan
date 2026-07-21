<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->unsignedBigInteger('designation_id')->nullable()->after('employee_id');
            $table->foreign('designation_id')->references('id')->on('designations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropForeign(['designation_id']);
            $table->dropColumn('designation_id');
            $table->string('position')->nullable()->after('employee_id');
        });
    }
};
