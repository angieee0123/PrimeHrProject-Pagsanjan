<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Idempotent: the current `trainings` CREATE migration already ships some
            // of these columns (e.g. timestamps), so only add what's missing. This
            // keeps a fresh install from hitting "duplicate column" errors while
            // remaining a no-op on databases where this migration already ran.
            if (! Schema::hasColumn('trainings', 'position_type')) {
                $table->string('position_type')->nullable()->after('hours');
            }
            if (! Schema::hasColumn('trainings', 'ref_doc_no')) {
                $table->string('ref_doc_no')->nullable()->after('conducted_by');
            }
            if (! Schema::hasColumn('trainings', 'certificate_path')) {
                $table->string('certificate_path')->nullable()->after('ref_doc_no');
            }
            if (! Schema::hasColumn('trainings', 'status')) {
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->after('certificate_path');
            }
            if (! Schema::hasColumn('trainings', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('trainings', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('rejected_reason');
            }
            if (! Schema::hasColumn('trainings', 'created_at')) {
                $table->timestamps();
            }
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
