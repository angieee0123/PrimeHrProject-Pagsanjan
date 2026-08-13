<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Endpoint-contract test for the public website chatbot (POST
 * /api/public/chatbot/chat), the welcome page's widget endpoint.
 *
 * Everything here runs unauthenticated — there is deliberately no user behind
 * this route. The assertions that matter are the ones no employee data comes
 * back and no provider is required for the deterministic paths (greeting,
 * policy answers). The provider resolution fallback runs on general questions,
 * so the test connection builds an empty `system_ai_settings` table and forces
 * no key, which degrades any free-form question to the deterministic fallback
 * instead of spending a real provider call.
 */
class PublicChatbotControllerTest extends TestCase
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

        // No provider may be configured for this test run: a general question
        // must degrade to the deterministic fallback, not reach a real API.
        config(['services.groq.api_key' => '']);
        $_ENV['GROQ_API_KEY'] = '';
        putenv('GROQ_API_KEY=');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('system_ai_settings');
        parent::tearDown();
    }

    public function test_route_is_public_and_answers_a_greeting(): void
    {
        // No actingAs() anywhere in this file: the endpoint must work for a
        // logged-out visitor, which the authenticated /chatbot/chat cannot.
        $response = $this->postJson('/api/public/chatbot/chat', ['message' => 'Hello']);

        $response->assertOk()
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['response', 'full_response', 'follow_up_questions']);

        $this->assertStringContainsString('PRIME HRIS Assistant', $response->json('response'));
    }

    public function test_various_greetings_are_recognized(): void
    {
        foreach (['Hi there', 'Good morning', 'Kumusta'] as $greeting) {
            $response = $this->postJson('/api/public/chatbot/chat', ['message' => $greeting]);
            $response->assertOk()->assertJson(['status' => 'success']);
            $this->assertNotEmpty($response->json('response'), "empty response for: {$greeting}");
        }
    }

    public function test_empty_message_is_rejected(): void
    {
        $this->postJson('/api/public/chatbot/chat', ['message' => ''])
            ->assertStatus(400)
            ->assertJson(['error' => 'No message provided']);
    }

    public function test_a_policy_question_is_answered_without_a_provider(): void
    {
        // The grace-period shortcut is deterministic — read from a service
        // constant, no model call, no database. A visitor must be able to get
        // the municipality's policy answers without anything configured.
        $response = $this->postJson('/api/public/chatbot/chat', ['message' => 'What is the grace period?']);

        $response->assertOk()->assertJson(['status' => 'success']);
        $this->assertStringContainsString('grace period', strtolower($response->json('response')));
    }

    public function test_a_data_question_never_returns_rows(): void
    {
        // This is the whole point of the guest scope: an anonymous visitor who
        // asks for org data gets prose at most — never a row, table, file, or
        // chart. No key is configured in this test, so the general fallback is
        // the deterministic message rather than a live answer.
        $response = $this->postJson('/api/public/chatbot/chat', ['message' => 'How many employees work here?']);

        $response->assertOk()
            ->assertJson(['status' => 'success'])
            ->assertJsonMissingPath('data')
            ->assertJsonMissingPath('table')
            ->assertJsonMissingPath('files')
            ->assertJsonMissingPath('charts');

        $this->assertIsString($response->json('response'));
    }
}
