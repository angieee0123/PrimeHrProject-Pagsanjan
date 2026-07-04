<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            // employee_id/approved_by FKs are added later in
            // add_deferred_foreign_keys_for_fresh_install, since employees
            // doesn't exist yet at this point in a fresh install.
            $table->unsignedBigInteger('employee_id');
            $table->string('destination');
            $table->text('purpose');
            $table->date('travel_date');
            $table->date('return_date');
            $table->integer('duration')->default(1);
            $table->string('transportation_mode')->nullable();
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
