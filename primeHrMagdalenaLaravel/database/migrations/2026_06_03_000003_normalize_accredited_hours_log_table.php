<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            // Drop redundant columns that duplicate data from other tables
            $table->dropColumn([
                'attendance_date',
                'scheduled_am_in',
                'scheduled_am_out',
                'scheduled_pm_in',
                'scheduled_pm_out',
                'actual_am_in',
                'actual_am_out',
                'actual_pm_in',
                'actual_pm_out',
                'actual_ot_in',
                'actual_ot_out',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('accredited_hours_log', function (Blueprint $table) {
            // Restore columns
            $table->date('attendance_date')->after('schedule_id');
            $table->time('scheduled_am_in')->nullable()->after('attendance_date');
            $table->time('scheduled_am_out')->nullable()->after('scheduled_am_in');
            $table->time('scheduled_pm_in')->nullable()->after('scheduled_am_out');
            $table->time('scheduled_pm_out')->nullable()->after('scheduled_pm_in');
            $table->time('actual_am_in')->nullable()->after('scheduled_pm_out');
            $table->time('actual_am_out')->nullable()->after('actual_am_in');
            $table->time('actual_pm_in')->nullable()->after('actual_am_out');
            $table->time('actual_pm_out')->nullable()->after('actual_pm_in');
            $table->time('actual_ot_in')->nullable()->after('actual_pm_out');
            $table->time('actual_ot_out')->nullable()->after('actual_ot_in');
        });
    }
};
