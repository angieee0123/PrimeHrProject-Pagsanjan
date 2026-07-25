<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The preferences table was created for the admin categories only (leave
 * requests, training submissions, travel orders, employee requests). The
 * employee Settings page shows a different set, which had nowhere to persist —
 * its toggles were purely visual. These columns give them a home.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            // Same opt-out model as the existing columns: default on, so nobody
            // silently loses a notification they were already receiving.
            $table->boolean('payslip_available')->default(true)->after('employee_requests');
            $table->boolean('leave_status')->default(true)->after('payslip_available');
            $table->boolean('dtr_reminder')->default(true)->after('leave_status');
            $table->boolean('attendance_alert')->default(true)->after('dtr_reminder');
            $table->boolean('email_digest')->default(true)->after('attendance_alert');
        });
    }

    public function down(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'payslip_available',
                'leave_status',
                'dtr_reminder',
                'attendance_alert',
                'email_digest',
            ]);
        });
    }
};
