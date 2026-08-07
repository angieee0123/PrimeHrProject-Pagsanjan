<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\AiQueryService;
use App\Services\ChartDataService;
use App\Services\ConversationMemoryService;
use App\Services\DashboardAssistantService;
use App\Services\DocumentSearchService;
use App\Services\EmployeeChatbotService;
use App\Services\EmployeeSearchService;
use App\Services\HrChatbotAnswerer;
use App\Services\ReportGeneratorService;
use App\Services\SafeSqlService;
use App\Services\WorkflowAssistantService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Accuracy scoreboard for intent routing.
 *
 * `AiQueryRoutingTest` pins the handful of decisions that carry a security or
 * capability consequence. This one is the breadth complement: every entry in
 * tests/Fixtures/intent_golden_set.php, reported together so a rule change
 * shows its full blast radius in one run rather than one failure at a time.
 *
 * Every collaborator is a mock and no question reaches a database or a model,
 * so the whole set runs in milliseconds and can be extended freely.
 */
class IntentRoutingGoldenSetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info', 'warning', 'error')->andReturnNull();

        // A question no pattern claims falls through to the model classifier,
        // which reads this table to decide whether a provider is configured.
        // Building it empty means that path resolves to "not configured" and
        // returns 'general' — so a gap in the rules is reported as a misroute
        // in the scoreboard below rather than blowing the whole run up with a
        // missing-table error.
        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });
    }

    /** Every collaborator mocked, so nothing behind the router actually runs. */
    private function assistant(): AiQueryService
    {
        return new AiQueryService(
            new AiAccessPolicy(),
            new ConversationMemoryService(),
            $this->createMock(EmployeeSearchService::class),
            $this->createMock(DocumentSearchService::class),
            $this->createMock(DashboardAssistantService::class),
            $this->createMock(ReportGeneratorService::class),
            $this->createMock(ChartDataService::class),
            $this->createMock(WorkflowAssistantService::class),
            $this->createMock(SafeSqlService::class),
            $this->createMock(HrChatbotAnswerer::class),
            $this->createMock(EmployeeChatbotService::class),
        );
    }

    /**
     * The routing decision alone.
     *
     * The caller holds an org-wide role with no linked employee record: role
     * does not affect pattern routing at all, and a null employee id keeps the
     * self-service branch from reaching for a table the test connection has
     * not built.
     */
    private function classify(string $question): string
    {
        $user = new User();
        $user->roles = ['hr'];
        $user->status = 'Active';
        $user->employee_id = null;

        return $this->assistant()->ask($user, $question)['intent'];
    }

    #[Test]
    public function every_golden_set_question_routes_to_its_expected_capability(): void
    {
        $golden = require __DIR__ . '/../Fixtures/intent_golden_set.php';

        $misrouted = [];

        foreach ($golden as $question => $expected) {
            $actual = $this->classify((string) $question);

            if ($actual !== $expected) {
                $misrouted[] = sprintf('  "%s"%s    expected %s, got %s', $question, PHP_EOL, $expected, $actual);
            }
        }

        $total = count($golden);
        $correct = $total - count($misrouted);

        $this->assertSame(
            [],
            $misrouted,
            sprintf(
                "Intent routing accuracy: %d/%d (%.1f%%). Misrouted:%s%s",
                $correct,
                $total,
                ($correct / $total) * 100,
                PHP_EOL,
                implode(PHP_EOL, $misrouted)
            )
        );
    }

    /**
     * A question no rule claims must land on 'general', not throw. The
     * scoreboard above depends on this: a gap in the rules has to show up as a
     * misroute you can read, not as a stack trace that hides the other 70
     * results.
     */
    #[Test]
    public function an_unrecognised_question_falls_through_to_general(): void
    {
        $this->assertSame('general', $this->classify('the weather is nice today'));
    }

    /**
     * Routing must not depend on who is asking — the capability that answers
     * enforces permissions, the classifier only decides which one that is.
     * If these diverged, an employee and an admin typing the same question
     * would take different paths and only one of them would be audited as the
     * intent it really was.
     */
    #[Test]
    public function classification_is_independent_of_the_callers_role(): void
    {
        $golden = require __DIR__ . '/../Fixtures/intent_golden_set.php';

        $user = function (array $roles) {
            $u = new User();
            $u->roles = $roles;
            $u->status = 'Active';
            $u->employee_id = null;

            return $u;
        };

        foreach (array_keys($golden) as $question) {
            $asEmployee = $this->assistant()->ask($user(['employee']), (string) $question)['intent'];
            $asAdmin = $this->assistant()->ask($user(['admin']), (string) $question)['intent'];

            $this->assertSame($asAdmin, $asEmployee, "role changed the route for: {$question}");
        }
    }
}
