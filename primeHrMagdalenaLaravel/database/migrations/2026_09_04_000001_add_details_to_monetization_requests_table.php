<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original table only carried the workflow state (request_number,
     * employee, status, filed/approved by). A monetization decision needs the
     * numbers behind it — which VL/SL days were asked for, the balances and
     * salary they were read against, and the computed peso amount — plus the
     * reason and the approver's remarks the detail modals render.
     */
    public function up(): void
    {
        Schema::table('monetization_requests', function (Blueprint $table) {
            $table->decimal('vl_days', 10, 3)->default(0)->after('employee_id');
            $table->decimal('sl_days', 10, 3)->default(0)->after('vl_days');
            $table->decimal('monthly_salary', 12, 2)->nullable()->after('sl_days');
            $table->decimal('vl_balance', 10, 3)->nullable()->after('monthly_salary');
            $table->decimal('sl_balance', 10, 3)->nullable()->after('vl_balance');
            $table->decimal('computed_amount', 14, 2)->nullable()->after('sl_balance');
            $table->text('reason')->nullable()->after('computed_amount');
            $table->text('approver_remarks')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monetization_requests', function (Blueprint $table) {
            $table->dropColumn([
                'vl_days',
                'sl_days',
                'monthly_salary',
                'vl_balance',
                'sl_balance',
                'computed_amount',
                'reason',
                'approver_remarks',
            ]);
        });
    }
};
