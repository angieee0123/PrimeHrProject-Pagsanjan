<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiConversation;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        // A greeting no longer avoids the database: chathead turns are rows in
        // ai_conversations / ai_messages rather than session entries, so the
        // handler reaches for the caller's newest thread. Those two tables are
        // built by hand because RefreshDatabase does not work in this project
        // — see CLAUDE.md and AiConversationStoreTest, which builds the same
        // pair the same way.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->string('role');
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        // Provider resolution (user key → org default → .env) runs on every
        // answer, greeting included.
        Schema::create('user_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });

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

        // The route requires auth. `status` is not optional: EnsureUserIsActive
        // runs on the whole web group and User::isActive() tests
        // `status === 'Active'`, so a user built without it is an *inactive*
        // user and every request here came back 403. That middleware postdates
        // this test, which is why these assertions had been failing.
        $user = User::create([
            'email'  => 'chatbot-test@example.test',
            'roles'  => ['employee'],
            'status' => 'Active',
        ]);
        $this->actingAs($user);

        // Force no provider: the greeting/how-to paths are deterministic, and
        // without this the resolveConfig fallback reads the real GROQ_API_KEY
        // from the environment (config, $_ENV, $_SERVER, getenv) and every
        // greeting makes a live API call — which must not happen in a test,
        // and now surfaces as a rate-limit exception when the real key's daily
        // budget is spent.
        config(['services.groq.api_key' => '']);
        $_ENV['GROQ_API_KEY'] = '';
        $_SERVER['GROQ_API_KEY'] = '';
        putenv('GROQ_API_KEY=');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('system_ai_settings');
        Schema::dropIfExists('user_ai_settings');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('users');
        parent::tearDown();
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

    public function test_chathead_style_requests_continue_the_newest_thread(): void
    {
        // No conversation_id key: the widget picks up the user's newest thread.
        $first = $this->postJson('/chatbot/chat', ['message' => 'Hello'])->assertOk();
        $firstId = $first->json('conversation_id');
        $this->assertNotNull($firstId);

        $continued = $this->postJson('/chatbot/chat', ['message' => 'Hi there'])->assertOk();
        $this->assertSame($firstId, $continued->json('conversation_id'));
    }

    public function test_explicit_null_conversation_starts_a_fresh_thread(): void
    {
        // The full-page assistant sends conversation_id: null when the user is
        // starting a new chat; that must fork, never rejoin the newest thread.
        $first = $this->postJson('/chatbot/chat', ['message' => 'Hello'])->assertOk();
        $firstId = $first->json('conversation_id');

        $fresh = $this->postJson('/chatbot/chat', ['message' => 'Hello', 'conversation_id' => null])
            ->assertOk();

        $this->assertNotNull($fresh->json('conversation_id'));
        $this->assertNotSame($firstId, $fresh->json('conversation_id'));
        // The new thread is titled from its first question so the sidebar can
        // label it without a separate request.
        $this->assertNotNull($fresh->json('title'));
    }

    public function test_explicit_conversation_id_rejoins_that_thread(): void
    {
        $created = $this->postJson('/chatbot/chat', ['message' => 'Hello'])->assertOk();
        $id = $created->json('conversation_id');

        // Selecting a sidebar conversation posts its id and must continue it,
        // not the newest one.
        $rejoin = $this->postJson('/chatbot/chat', ['message' => 'Hi', 'conversation_id' => $id])
            ->assertOk();

        $this->assertSame($id, $rejoin->json('conversation_id'));
    }

    public function test_a_conversation_belonging_to_another_user_starts_fresh(): void
    {
        $other = User::create([
            'email'  => 'other-chatbot-test@example.test',
            'roles'  => ['employee'],
            'status' => 'Active',
        ]);
        $foreign = AiConversation::create(['user_id' => $other->id, 'title' => 'Someone else’s thread']);
        $foreignId = $foreign->id;

        // An explicit id the caller does not own must not be hijacked: it falls
        // back to a fresh thread of the caller's own.
        $response = $this->postJson('/chatbot/chat', ['message' => 'Hello', 'conversation_id' => $foreignId])
            ->assertOk();

        $this->assertNotSame($foreignId, $response->json('conversation_id'));
    }
}
