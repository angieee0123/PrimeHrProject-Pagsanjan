<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pass_slips', function (Blueprint $table) {
            $table->enum('type', ['official_activity', 'personal_reason'])
                ->default('official_activity')
                ->after('employee_id');

            $table->enum('purpose_category', [
                'coordinate_with',
                'meeting_conference',
                'secure_documents',
                'follow_up',
                'personal_matter',
            ])->default('follow_up')->after('type');

            $table->string('recommended_by_name')->nullable()->after('destination');

            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('pass_slips', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date']);
            $table->dropColumn(['type', 'purpose_category', 'recommended_by_name']);
        });
    }
};
