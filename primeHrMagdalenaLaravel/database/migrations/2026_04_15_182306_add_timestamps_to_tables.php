<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'employees',
            'addresses',
            'contacts',
            'government_ids',
            'educations',
            'eligibilities',
            'work_experiences',
            'trainings',
            'family_members',
            'documents',
            'legal_requirements',
            'employment_details'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'created_at')) {
                        $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                    }
                    if (!Schema::hasColumn($table->getTable(), 'updated_at')) {
                        $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'employees',
            'addresses',
            'contacts',
            'government_ids',
            'educations',
            'eligibilities',
            'work_experiences',
            'trainings',
            'family_members',
            'documents',
            'legal_requirements',
            'employment_details'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'created_at')) {
                        $table->dropColumn('created_at');
                    }
                    if (Schema::hasColumn($table->getTable(), 'updated_at')) {
                        $table->dropColumn('updated_at');
                    }
                });
            }
        }
    }
};
