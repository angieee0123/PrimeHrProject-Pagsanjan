<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('government_ids', function (Blueprint $table) {
            $table->string('gsis_file_path')->nullable()->after('gsis_no');
            $table->string('philhealth_file_path')->nullable()->after('philhealth_no');
            $table->string('pagibig_file_path')->nullable()->after('pagibig_no');
            $table->string('tin_file_path')->nullable()->after('tin_no');
            $table->string('license_file_path')->nullable()->after('license_no');
        });
    }

    public function down(): void
    {
        Schema::table('government_ids', function (Blueprint $table) {
            $table->dropColumn([
                'gsis_file_path',
                'philhealth_file_path',
                'pagibig_file_path',
                'tin_file_path',
                'license_file_path',
            ]);
        });
    }
};
