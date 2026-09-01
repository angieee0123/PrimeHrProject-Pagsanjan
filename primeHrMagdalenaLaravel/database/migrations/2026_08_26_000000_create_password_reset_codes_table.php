<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storage for the forgot-password verification codes.
 *
 * Deliberately *not* Laravel's `password_reset_tokens`. That table belongs to
 * the framework's reset broker — one signed link, no attempt counter — and this
 * flow is a six-digit code the user types, which needs a guessing budget and a
 * second secret proving the code was cleared. Sharing the table would also make
 * a future `Password::sendResetLink()` collide on its `email` primary key with a
 * code issued here.
 *
 * One row per address: requesting a new code replaces the pending one, so a
 * mailbox holding three codes still only has the newest one working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('password_reset_codes')) {
            return;
        }

        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->string('email')->primary();

            // Hashed, never the digits themselves. A reset code is a credential
            // for the whole account; a leaked table dump must not be a set of
            // live passwords-in-waiting.
            $table->string('code_hash');

            // Six digits is a million guesses at worst and far fewer in
            // practice, so the code has to be spendable rather than merely
            // expiring. See PasswordResetCodeService::MAX_ATTEMPTS.
            $table->unsignedTinyInteger('attempts')->default(0);

            // Issued once the code is accepted, and the only thing step 3 will
            // take. Without it, knowing an email address would be enough to
            // POST a new password straight to the reset endpoint.
            $table->string('ticket_hash')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};
