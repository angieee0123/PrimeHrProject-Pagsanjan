<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Citizen's Charter knowledge base for the AI chatbot.
 *
 * A single active document: uploading a new charter deactivates the previous
 * row (history is kept, only one row is active). The extracted plain text is
 * stored on the row itself so every chatbot surface — the full-page AI
 * Assistant, both chatheads, the mobile API, and the public welcome-page
 * widget — can answer municipality-information questions from the same text
 * without a second lookup. The charter is public information, so no
 * employee scoping applies to these rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('citizen_charters')) {
            return;
        }

        Schema::create('citizen_charters', function (Blueprint $table) {
            $table->id();

            $table->string('original_name');
            $table->string('stored_path');
            $table->string('file_type', 12)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('content_hash', 64)->nullable();

            $table->longText('content')->nullable();
            $table->unsignedInteger('page_count')->nullable();

            // extracted | ocr_required | failed — string, not enum, so the
            // migration runs on SQLite (tests) as well as MySQL.
            $table->string('status', 20)->default('extracted');
            $table->string('extractor', 40)->nullable();
            $table->text('error')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('extracted_at')->nullable();

            $table->timestamps();

            $table->index('is_active');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_charters');
    }
};
