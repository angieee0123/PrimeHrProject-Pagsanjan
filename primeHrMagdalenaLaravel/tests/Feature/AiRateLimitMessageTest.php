<?php

namespace Tests\Feature;

use App\Exceptions\AiRateLimitException;
use App\Models\SystemAiSetting;
use App\Services\AiChatService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * When the AI provider is rate-limited (HTTP 429, e.g. Groq's daily token
 * cap), the chatbot must say so helpfully instead of the generic "provider
 * appears unavailable" fallback — the provider is up, the budget is spent.
 *
 * The rate-limit exception is thrown at the provider call and caught at the
 * two user-facing boundaries: AiQueryService::ask() and HrChatbotAnswerer.
 */
class AiRateLimitMessageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->string('theme')->default('default');
            $table->string('custom_theme_primary', 7)->nullable();
            $table->string('theme_secondary', 7)->nullable();
            $table->string('theme_accent', 7)->nullable();
            $table->string('theme_muted', 7)->nullable();
            $table->string('sidebar_style', 20)->default('brand');
            $table->string('topbar_style', 20)->default('brand');
            $table->timestamps();
        });

        SystemAiSetting::create([
            'provider' => 'groq',
            'api_key' => 'test-key-for-fake-http',
            'model' => 'llama-3.3-70b-versatile',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('system_ai_settings');
        Http::stub(null);
        parent::tearDown();
    }

    public function test_exception_message_mentions_the_token_limit(): void
    {
        $e = new AiRateLimitException('groq', 'Rate limit reached ... Please try again in 34m13.728s.');

        $this->assertStringContainsString('token limit', $e->friendlyMessage());
        $this->assertStringContainsString('34 minute', $e->friendlyMessage());
    }

    public function test_openai_compatible_429_throws_rate_limit_exception(): void
    {
        Http::fake([
            'https://api.groq.com/*' => Http::response(
                '{"error":{"message":"Rate limit reached for model on tokens per day","type":"tokens","code":"rate_limit_exceeded"}}',
                429
            ),
        ]);

        $this->expectException(AiRateLimitException::class);
        AiChatService::complete(null, 'Who is the mayor?');
    }

    public function test_openai_compatible_5xx_returns_null_not_exception(): void
    {
        Http::fake([
            'https://api.groq.com/*' => Http::response('{"error":"upstream down"}', 503),
        ]);

        $this->assertNull(AiChatService::complete(null, 'Who is the mayor?'));
    }
}
