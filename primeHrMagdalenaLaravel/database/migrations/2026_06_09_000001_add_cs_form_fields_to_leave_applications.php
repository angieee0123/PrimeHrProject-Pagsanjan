<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->boolean('commutation_requested')->default(false)->after('reason');
            $table->string('leave_location', 10)->nullable()->after('commutation_requested')
                ->comment('ph or abroad — for VL/SPL');
            $table->string('leave_location_specify', 255)->nullable()->after('leave_location');
            $table->string('sick_leave_type', 20)->nullable()->after('leave_location_specify')
                ->comment('in_hospital or out_patient');
            $table->string('illness_specify', 255)->nullable()->after('sick_leave_type');
            $table->string('study_leave_purpose', 20)->nullable()->after('illness_specify')
                ->comment('masters, bar_review, or other');
            $table->decimal('approved_days_with_pay', 5, 2)->nullable()->after('approver_remarks');
            $table->decimal('approved_days_without_pay', 5, 2)->nullable()->after('approved_days_with_pay');
            $table->string('approved_other_specify', 255)->nullable()->after('approved_days_without_pay');
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn([
                'commutation_requested',
                'leave_location',
                'leave_location_specify',
                'sick_leave_type',
                'illness_specify',
                'study_leave_purpose',
                'approved_days_with_pay',
                'approved_days_without_pay',
                'approved_other_specify',
            ]);
        });
    }
};
