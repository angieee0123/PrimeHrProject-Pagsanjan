<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extracted text for uploaded files, so the AI Assistant can search what is
 * *inside* a document rather than only its filename and type.
 *
 * Rows are keyed polymorphically because uploads live in more than one table
 * here: `documents.file_path`, `trainings.certificate_path`,
 * `leave_applications.attachment_path`, `travel_orders.attachment`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_extractions', function (Blueprint $table) {
            $table->id();

            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->string('file_path');
            $table->string('file_type', 12)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_hash', 64)->nullable();

            $table->longText('content')->nullable();
            $table->json('metadata')->nullable();

            $table->enum('status', ['pending', 'extracted', 'ocr_required', 'unsupported', 'failed'])
                ->default('pending');
            $table->string('extractor', 40)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('extracted_at')->nullable();

            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
            $table->index('employee_id');
            $table->index('status');
        });

        // Content search is the whole point of this table, so give MySQL a
        // FULLTEXT index for it. Other drivers (the SQLite test database) fall
        // back to LIKE, which is fine at this data size.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE document_extractions ADD FULLTEXT ftx_content (content)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_extractions');
    }
};
