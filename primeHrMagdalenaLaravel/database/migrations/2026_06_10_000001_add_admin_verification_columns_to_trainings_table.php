<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->string('position_type')->nullable()->after('hours');
            $table->string('ref_doc_no')->nullable()->after('conducted_by');
            $table->string('certificate_path')->nullable()->after('ref_doc_no');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->after('certificate_path');
            $table->text('rejected_reason')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('rejected_reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn([
                'position_type',
                'ref_doc_no',
                'certificate_path',
                'status',
                'rejected_reason',
                'verified_at',
                'created_at',
                'updated_at'
            ]);
        });
    }
};
