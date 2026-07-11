<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('request_type'); // 'payslip', 'deduction_inquiry', 'leave_balance', 'attendance_correction', 'certificate', 'other'
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'processing', 'completed', 'rejected'
            $table->text('admin_response')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // employee_id FK is added later in
            // add_deferred_foreign_keys_for_fresh_install, since `employees`
            // is created by a much later migration (2026_04_13) and doesn't
            // exist yet at this point in a from-scratch migrate.
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['employee_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_requests');
    }
};
