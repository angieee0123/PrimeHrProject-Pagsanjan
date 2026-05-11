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
        Schema::table('leave_accrual_rates', function (Blueprint $table) {
            // Drop the old foreign key and column
            $table->dropForeign(['leave_code']);
            $table->dropColumn('leave_code');
            
            // Add new leave_type_id column with foreign key
            $table->unsignedBigInteger('leave_type_id')->after('id');
            $table->foreign('leave_type_id')->references('id')->on('leave_types_config')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_accrual_rates', function (Blueprint $table) {
            // Drop the new foreign key and column
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
            
            // Restore the old leave_code column
            $table->string('leave_code', 10)->after('id');
            $table->foreign('leave_code')->references('leave_code')->on('leave_types_config')->cascadeOnDelete();
        });
    }
};
