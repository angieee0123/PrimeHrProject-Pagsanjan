<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editable content for the public welcome page.
 *
 * One row per section rather than one column per field: the sections hold
 * repeating lists (announcements, service items, contact lines) whose length
 * the admin controls, and a column-per-field table cannot grow a row without
 * a migration. The shape of each section's JSON is owned by
 * SiteContentService::defaults(), which is also what renders when a section
 * has never been saved — so the page looks identical before the first edit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_contents')) {
            return;
        }

        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            // The section name — 'hero', 'announcements', 'contact'…
            $table->string('key')->unique();
            $table->json('value');
            // Who last touched it. Public-facing copy is worth an audit trail,
            // and nullOnDelete keeps the row when the account is removed.
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
