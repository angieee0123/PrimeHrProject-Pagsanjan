<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\ChartDataService;
use App\Services\ConversationMemoryService;
use App\Services\DashboardAssistantService;
use App\Services\DocumentSearchService;
use App\Services\EmployeeSearchService;
use App\Services\EmployeeChatbotService;
use App\Services\HrChatbotAnswerer;
use App\Services\AiQueryService;
use App\Services\ReportGeneratorService;
use App\Services\SafeSqlService;
use App\Services\WorkflowAssistantService;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * How the orchestrator treats a refusal.
 *
 * SafeSqlService restricts generated SQL to org-wide roles, because a statement
 * with arbitrary joins cannot be scoped to one employee's own rows after the
 * fact. That restriction is only worth anything if being refused ends the
 * request. It previously did not: a blocked result fell through to
 * HrChatbotAnswerer, which generated and executed SQL with no allow-list and no
 * AiAccessPolicy scoping — so the denial was itself the trigger for an
 * unscoped answer.
 *
 * These tests pin both halves of the behaviour: a refusal is returned as a
 * refusal, and a query that merely failed still falls back, so ordinary
 * conversation is unaffected.
 */
class AiQueryRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The orchestrator audits every turn through the ai_audit channel;
        // nothing here asserts on the log, it just must not blow up.
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info', 'warning', 'error')->andReturnNull();
    }

    private function user(array $roles, ?int $employeeId = null): User
    {
        $user = new User();
        $user->roles = $roles;
        $user->status = 'Active';
        $user->employee_id = $employeeId;

        return $user;
    }

    /**
     * Every collaborator the orchestrator needs, with the two that matter for a
     * given test supplied by the caller.
     */
    private function assistant(
        SafeSqlService $sql,
        HrChatbotAnswerer $fallback,
        ?EmployeeChatbotService $selfService = null,
    ): AiQueryService {
        return new AiQueryService(
            new AiAccessPolicy(),
            new ConversationMemoryService(),
            $this->createMock(EmployeeSearchService::class),
            $this->createMock(DocumentSearchService::class),
            $this->createMock(DashboardAssistantService::class),
            $this->createMock(ReportGeneratorService::class),
            $this->createMock(ChartDataService::class),
            $this->createMock(WorkflowAssistantService::class),
            $sql,
            $fallback,
            $selfService ?? $this->createMock(EmployeeChatbotService::class),
        );
    }

    /**
     * Classify a question without exercising any capability behind it. Every
     * collaborator is a mock, so the returned intent is the routing decision
     * on its own.
     */
    private function intentOf(string $question, array $roles = ['employee'], ?int $employeeId = null): string
    {
        $assistant = $this->assistant(
            $this->createMock(SafeSqlService::class),
            $this->createMock(HrChatbotAnswerer::class),
        );

        return $assistant->ask($this->user($roles, $employeeId), $question)['intent'];
    }

    /**
     * The vulnerability, stated as a test: an employee asks a data question,
     * SafeSqlService refuses because they lack org-wide access, and the answer
     * must be that refusal — not a second attempt through an unscoped path.
     */
    #[Test]
    public function a_blocked_query_is_not_rerouted_to_the_general_answerer(): void
    {
        $fallback = $this->createMock(HrChatbotAnswerer::class);
        $fallback->expects($this->never())->method('answer');

        // The real service, so the refusal comes from the real policy check.
        $assistant = $this->assistant(new SafeSqlService(new AiAccessPolicy()), $fallback);

        $result = $assistant->ask(
            $this->user(['employee'], 5),
            'list all employees with their salary grade'
        );

        $this->assertSame('data_query', $result['intent']);
        $this->assertStringContainsString('limited to HR, admin, and mayor', $result['answer']);
        $this->assertSame([], $result['data']);
    }

    /**
     * The same refusal reaches a user with no linked employee record, who has
     * no "own" data to fall back to either.
     */
    #[Test]
    public function an_orphaned_account_is_refused_rather_than_rerouted(): void
    {
        $fallback = $this->createMock(HrChatbotAnswerer::class);
        $fallback->expects($this->never())->method('answer');

        $assistant = $this->assistant(new SafeSqlService(new AiAccessPolicy()), $fallback);

        $result = $assistant->ask($this->user(['employee'], null), 'list all pending leave applications');

        $this->assertStringContainsString('limited to HR, admin, and mayor', $result['answer']);
    }

    /**
     * The counterpart: a query that was allowed but did not work is a failure,
     * not a permission decision, so it still falls back. Without this the fix
     * above would have quietly removed ordinary error recovery.
     */
    #[Test]
    public function a_failed_query_still_falls_back(): void
    {
        $sql = $this->createMock(SafeSqlService::class);
        $sql->method('query')->willReturn([
            'answer' => 'I could not build a working query for that.',
            'data' => [],
            'error' => 'Unknown column x in field list',
        ]);

        $fallback = $this->createMock(HrChatbotAnswerer::class);
        $fallback->expects($this->once())
            ->method('answer')
            ->willReturn('Here is what I know about leave policy instead.');

        $assistant = $this->assistant($sql, $fallback);

        $result = $assistant->ask($this->user(['hr'], 1), 'list all employees with their salary grade');

        $this->assertSame('Here is what I know about leave policy instead.', $result['answer']);
    }

    /**
     * The reason step 1's refusal is acceptable: a question about the asker's
     * own records no longer goes near generated SQL, so an employee is
     * answered rather than denied.
     */
    #[Test]
    public function questions_about_ones_own_records_route_to_self_service(): void
    {
        foreach ([
            'what is my leave balance?',
            'show my latest payslip',
            'how many VL credits do I have left?',
            'do I have any pending leave',
            'my attendance this month',
            'aking payslip',
            'who am i',
        ] as $question) {
            $this->assertSame('self_service', $this->intentOf($question), "misrouted: {$question}");
        }
    }

    /**
     * The self-reference has to attach to a personal HR noun. An HR officer
     * asking about their department means the department, not themselves —
     * a blanket match on "my" would silently narrow the answer to one row.
     */
    #[Test]
    public function a_possessive_about_something_other_than_own_records_is_not_self_service(): void
    {
        $this->assertSame('dashboard', $this->intentOf('how many employees are in my department', ['hr'], 1));
        $this->assertSame('report', $this->intentOf('generate the payroll report for my department', ['hr'], 1));
    }

    /**
     * "How do I check my leave balance" is a how-to, not a lookup of that
     * balance — so the how-to rule has to be tested ahead of self-service.
     */
    #[Test]
    public function how_to_questions_route_to_the_knowledge_base(): void
    {
        foreach ([
            'how do i file a leave application?',
            'how to add a training record',
            'how do I check my leave balance',
            'paano mag-file ng leave',
            'pano mag time in',
            'what is the process for a travel order',
        ] as $question) {
            $this->assertSame('how_to', $this->intentOf($question), "misrouted: {$question}");
        }
    }

    /**
     * A how-to is answered from curated policy and the knowledge base, never
     * from generated SQL — `explain()` rather than `answer()`.
     */
    #[Test]
    public function a_how_to_never_reaches_the_sql_answerer(): void
    {
        $fallback = $this->createMock(HrChatbotAnswerer::class);
        $fallback->expects($this->never())->method('answer');
        $fallback->expects($this->once())
            ->method('explain')
            ->willReturn('Go to Leave Management > File Leave Application.');

        $assistant = $this->assistant($this->createMock(SafeSqlService::class), $fallback);

        $result = $assistant->ask($this->user(['employee'], 5), 'how do i file a leave application?');

        $this->assertSame('Go to Leave Management > File Leave Application.', $result['answer']);
    }

    /**
     * An account with no employee row has no "own" records. It must be told
     * so, not handed someone else's.
     */
    #[Test]
    public function self_service_for_an_unlinked_account_explains_rather_than_guesses(): void
    {
        $selfService = $this->createMock(EmployeeChatbotService::class);
        $selfService->expects($this->never())->method('assist');

        $assistant = $this->assistant(
            $this->createMock(SafeSqlService::class),
            $this->createMock(HrChatbotAnswerer::class),
            $selfService,
        );

        $result = $assistant->ask($this->user(['employee'], null), 'what is my leave balance?');

        $this->assertSame('self_service', $result['intent']);
        $this->assertStringContainsString('not linked to an employee record', $result['answer']);
    }

    /**
     * "how many" is a count for the dashboard, not an instruction — the how-to
     * rule must not swallow it.
     */
    #[Test]
    public function how_many_is_still_a_dashboard_question(): void
    {
        $this->assertSame('dashboard', $this->intentOf('how many employees are on leave today', ['hr'], 1));
    }

    #[Test]
    public function asking_what_the_assistant_can_do_is_answered_without_a_model_call(): void
    {
        foreach ([
            'what can you do?',
            'what can i ask you',
            'who are you',
            'anong kaya mo',
            'help',
        ] as $question) {
            $this->assertSame('capabilities', $this->intentOf($question), "misrouted: {$question}");
        }
    }

    /**
     * "help me find Juan" is a search. A bare "help" is the only form that
     * means the capability list.
     */
    #[Test]
    public function help_with_an_object_is_not_a_capability_question(): void
    {
        $this->assertNotSame('capabilities', $this->intentOf('help me find Juan dela Cruz', ['hr'], 1));
    }

    /**
     * The capability list is built from AiAccessPolicy, so it cannot offer an
     * employee something their scope would then refuse.
     */
    #[Test]
    public function the_capability_list_matches_the_callers_scope(): void
    {
        $assistant = $this->assistant(
            $this->createMock(SafeSqlService::class),
            $this->createMock(HrChatbotAnswerer::class),
        );

        $employee = $assistant->ask($this->user(['employee'], 5), 'what can you do?');
        $hr = $assistant->ask($this->user(['hr'], 1), 'what can you do?');

        $this->assertStringContainsString('your own records', $employee['answer']);
        $this->assertStringNotContainsString('Organisation-wide', $employee['answer']);
        $this->assertNotEmpty($employee['follow_ups']);

        $this->assertStringContainsString('Organisation-wide', $hr['answer']);
    }
}
