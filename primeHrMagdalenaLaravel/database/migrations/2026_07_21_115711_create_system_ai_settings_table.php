<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('system_ai_settings')) {
            return;
        }

        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });

        // Migrate today's .env-based config into the database as the seed row,
        // so switching to DB-managed settings doesn't interrupt the chatbot —
        // going forward this row is what Settings → AI/Chatbot edits, and .env
        // is only a break-glass fallback if this row is ever empty.
        $envKey = env('GROQ_API_KEY');
        if ($envKey) {
            DB::table('system_ai_settings')->insert([
                'provider' => 'groq',
                'api_key' => Crypt::encryptString($envKey),
                'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_ai_settings');
    }
};
