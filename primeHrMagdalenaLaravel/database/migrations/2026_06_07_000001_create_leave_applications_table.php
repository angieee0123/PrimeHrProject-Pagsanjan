<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique()->comment('Auto-generated unique reference number');
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code', 10);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('number_of_days', 5, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->string('attachment_path')->nullable()->comment('Path to uploaded document');
            $table->unsignedBigInteger('filed_by')->comment('User ID who filed the application');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('User ID who approved/rejected');
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_code')->references('leave_code')->on('leave_types_config')->onDelete('restrict');
            $table->foreign('filed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['employee_id', 'status']);
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
