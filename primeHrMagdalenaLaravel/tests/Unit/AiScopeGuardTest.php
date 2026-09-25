<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\SystemAiSetting;
use App\Services\AiAccessPolicy;
use App\Services\AiChatService;
use App\Services\AiScopeGuard;
use App\Services\EmployeeChatbotService;
use App\Services\HrChatbotAnswerer;
use App\Services\HrPolicyFactsService;
use App\Services\SafeSqlService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The assistant's scope boundary.
 *
 * The bug: "how to write a for loop in python" was answered with a working
 * Python loop. Two doors led there — the knowledge-base answerer, whose prompt
 * said "answer this question using the system knowledge below" and nothing
 * about staying in the domain, and the `general` catch-all, whose failed
 * text-to-SQL fell through to that same answerer.
 *
 * These tests pin the boundary in both directions. Refusing off-topic questions
 * is only half of it: a scope guard that also refuses real HR questions is worse
 * than none, so `in_scope_questions_are_never_refused` runs the whole intent
 * golden set through the guard, and `an_hr_question_that_mentions_a_code_word_is_not_refused`
 * (in AiQueryRoutingTest) covers the deliberate asymmetry — "which of our
 * employees know python" is a real question and must still be answered.
 */
class AiScopeGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // AiChatService::resolveConfig() reads this table; the prompt-clause
        // test needs a configured provider to have a request to inspect.
        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
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

    private function guard(): AiScopeGuard
    {
        return new AiScopeGuard();
    }

    /**
     * The reported question, stated as a test.
     */
    #[Test]
    public function the_reported_question_is_refused(): void
    {
        $guard = $this->guard();

        $this->assertTrue($guard->isOutOfScope('how to write a for loop in python'));
        $this->assertSame('code', $guard->offTopicReason('how to write a for loop in python'));
    }

    /**
     * Subjects that belong to some other assistant: languages and frameworks,
     * code tasks that name no language at all, general knowledge, homework, and
     * small talk about the world outside the municipality.
     */
    #[Test]
    public function off_topic_subjects_are_refused(): void
    {
        $code = [
            'how to write a for loop in python',
            'Can you write me some React code?',
            'explain closures in javascript',
            'how do I center a div in css',
            'how to make an app in flutter',
            'write a script that renames files',
            'what is the best linux command for that',
            'give me the mysql query for that',
            'what does this PHP error mean',
        ];

        $other = [
            'what is the capital of France',
            'solve for x: 2x + 4 = 10',
            'write me an essay about discipline',
            'tell me a joke',
            'translate this into Spanish',
            'who won the basketball game',
            'the weather is nice today',
        ];

        foreach ($code as $question) {
            $this->assertSame('code', $this->guard()->offTopicReason($question), $question);
        }

        foreach ($other as $question) {
            $this->assertSame('other', $this->guard()->offTopicReason($question), $question);
        }
    }

    /**
     * The counterpart, and the more important half.
     *
     * Every question in the intent golden set is a question this assistant is
     * supposed to answer. If the guard refuses one of them, the boundary has
     * been drawn over real work — so the fixture is the guard's own regression
     * suite, and it grows every time somebody pins a new routing decision.
     */
    #[Test]
    public function in_scope_questions_are_never_refused(): void
    {
        $golden = require __DIR__ . '/../Fixtures/intent_golden_set.php';
        $guard = $this->guard();

        foreach ($golden as $question => $intent) {
            if ($intent === 'out_of_scope') {
                $this->assertTrue($guard->isOutOfScope((string) $question), "not refused: {$question}");

                continue;
            }

            $this->assertFalse(
                $guard->isOutOfScope((string) $question),
                "the guard would refuse a real question ({$intent}): {$question}"
            );
        }
    }

    /**
     * An HR noun anywhere in the question is enough to keep it in scope, because
     * the cost of the two mistakes is not symmetrical: a refused leave question
     * is a broken system, an off-topic question that reaches the prompt clause
     * is a sentence the model was told to decline.
     */
    #[Test]
    public function an_hr_subject_outweighs_an_off_topic_word(): void
    {
        $guard = $this->guard();

        foreach ([
            'which of our employees know python',
            'what is the code of conduct',
            'is there a training on javascript frameworks',
            'show me the documents of Juan',
            'how do I print my payslip',
        ] as $question) {
            $this->assertFalse($guard->isOutOfScope($question), $question);
        }
    }

    /**
     * The refusal has to leave the user somewhere to go. "I can't help with
     * that" tells an employee nothing about what this assistant is *for*, and
     * the suggested questions must themselves be in scope — a chip that leads
     * straight back to a refusal is worse than no chip.
     */
    #[Test]
    public function the_refusal_names_the_boundary_and_offers_a_way_back(): void
    {
        $guard = $this->guard();
        $refusal = $guard->refusal();

        $this->assertStringContainsString('Municipality of Pagsanjan', $refusal);
        $this->assertStringContainsString('outside that scope', $refusal);

        $followUps = $guard->followUps();
        $this->assertNotEmpty($followUps);

        foreach ($followUps as $followUp) {
            $this->assertFalse($guard->isOutOfScope($followUp), "a refusal chip is out of scope: {$followUp}");
        }
    }

    /**
     * The pattern gate can only refuse what somebody thought to name. The prompt
     * clause is what covers the rest, and it is attached in `AiChatService` —
     * the single door to a provider — so a prompt written later cannot be
     * written without it. This asserts the clause reaches the wire both for a
     * prompt that has its own system message and for a single-prompt
     * completion, which previously had none.
     */
    #[Test]
    public function every_prompt_carries_the_scope_boundary(): void
    {
        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'ok']]],
            ]),
        ]);

        AiChatService::complete(null, 'Who is the mayor?');
        AiChatService::chat(null, 'Custom system instructions.', [['role' => 'user', 'content' => 'hi']]);

        $systems = [];

        Http::assertSent(function ($request) use (&$systems) {
            $systems[] = $request['messages'][0]['content'] ?? '';

            return true;
        });

        $this->assertCount(2, $systems);

        foreach ($systems as $system) {
            $this->assertStringContainsString('OUT OF SCOPE', $system);
            $this->assertStringContainsString('Municipality of Pagsanjan', $system);
        }

        // Prepended, not substituted: a caller's own instructions still survive.
        $this->assertStringContainsString('Custom system instructions.', $systems[1]);
    }

    /**
     * The public welcome-page widget is the one assistant endpoint a logged-out
     * visitor can reach, and it calls HrChatbotAnswerer directly — never
     * AiQueryService. Its null-user path can run no SQL, so the free-text
     * answerer is all it has, which is exactly where the off-topic answers came
     * from.
     */
    #[Test]
    public function the_public_widget_refuses_out_of_scope_questions(): void
    {
        $answerer = new HrChatbotAnswerer(
            new AiAccessPolicy(),
            new SafeSqlService(new AiAccessPolicy()),
            new HrPolicyFactsService(),
        );

        $answer = $answerer->answer(null, 'how to write a for loop in python');

        $this->assertStringContainsString('Municipality of Pagsanjan', $answer);
        $this->assertStringNotContainsString('for item in', $answer);
    }

    /**
     * The mobile chatbot has its own controller and reaches
     * `EmployeeChatbotService` without passing through AiQueryService, so the
     * boundary has to hold there too — the web and mobile surfaces must not
     * answer different sets of questions.
     */
    #[Test]
    public function the_mobile_chatbot_refuses_out_of_scope_questions(): void
    {
        $service = new EmployeeChatbotService(new HrPolicyFactsService());

        $employee = new Employee();
        $employee->first_name = 'Ana';
        $employee->last_name = 'Ramos';

        $result = $service->handle($employee, 'how to write a for loop in python');

        $this->assertStringContainsString('Municipality of Pagsanjan', $result['response']);
        $this->assertStringNotContainsString('for item in', $result['response']);
        $this->assertNotEmpty($result['follow_up_questions']);
    }

    /**
     * A greeting is not small talk about the world — it is how every thread
     * starts. The boundary must not turn "hello" into a refusal.
     */
    #[Test]
    public function a_greeting_is_not_out_of_scope(): void
    {
        foreach (['hi', 'hello', 'good morning', 'kumusta', 'kamusta po'] as $greeting) {
            $this->assertFalse($this->guard()->isOutOfScope($greeting), $greeting);
        }
    }
}
