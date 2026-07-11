<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Endpoint-contract test for the web chatbot route (POST /chatbot/chat).
 *
 * Only the deterministic, dependency-free paths are asserted here — the
 * greeting handler and the empty-message validation — since the SQL/LLM
 * paths require an external Groq API key and live data, which don't belong
 * in an automated unit run. (The previous version of this test posted to a
 * non-existent /api/chatbot route and failed every assertion with HTTP 404.)
 */
class ChatbotControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // The route is under the `web` group; skip CSRF for the JSON POST.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        // The route requires auth. An in-memory user is enough — the greeting
        // and validation paths never touch the database.
        $user = new User();
        $user->roles = ['employee'];
        $this->actingAs($user);
    }

    public function test_route_exists(): void
    {
        // Anything other than 404/405 proves the route resolves.
        $status = $this->postJson('/chatbot/chat', ['message' => 'Hello'])->status();
        $this->assertNotSame(404, $status, 'chatbot/chat route should exist');
        $this->assertNotSame(405, $status, 'chatbot/chat should accept POST');
    }

    public function test_requires_authentication(): void
    {
        // Fresh instance without the acting-as user from setUp.
        $this->app['auth']->forgetGuards();
        $this->refreshApplication();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->postJson('/chatbot/chat', ['message' => 'Hello'])->assertStatus(401);
    }

    public function test_greeting_returns_success_without_external_services(): void
    {
        $response = $this->postJson('/chatbot/chat', ['message' => 'Hello']);

        $response->assertOk()
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['response', 'status']);

        $this->assertStringContainsString('PRIME HRIS', $response->json('response'));
    }

    public function test_various_greetings_are_recognized(): void
    {
        foreach (['Hi there', 'Good morning', 'Kumusta'] as $greeting) {
            $response = $this->postJson('/chatbot/chat', ['message' => $greeting]);
            $response->assertOk()->assertJson(['status' => 'success']);
            $this->assertNotEmpty($response->json('response'), "empty response for: {$greeting}");
        }
    }

    public function test_empty_message_is_rejected(): void
    {
        $this->postJson('/chatbot/chat', ['message' => ''])
            ->assertStatus(400)
            ->assertJson(['error' => 'No message provided']);
    }

    public function test_reset_with_empty_message_is_ok(): void
    {
        $this->postJson('/chatbot/chat', ['message' => '', 'reset' => true])
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }
}
