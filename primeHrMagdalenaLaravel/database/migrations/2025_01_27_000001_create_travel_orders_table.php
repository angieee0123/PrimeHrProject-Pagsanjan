<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('destination');
            $table->text('purpose');
            $table->date('travel_date');
            $table->date('return_date');
            $table->integer('duration'); // in days
            $table->string('transportation_mode')->nullable();
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'disapproved', 'cancelled'])->default('pending');
            
            // Approval fields
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Disapproval fields
            $table->foreignId('disapproved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('disapproved_at')->nullable();
            $table->text('disapproval_reason')->nullable();
            
            // Filing information
            $table->foreignId('filed_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'status']);
            $table->index(['travel_date', 'return_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};