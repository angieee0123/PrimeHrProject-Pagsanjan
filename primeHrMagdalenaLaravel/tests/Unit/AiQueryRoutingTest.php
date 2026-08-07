<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\ChartDataService;
use App\Services\ConversationMemoryService;
use App\Services\DashboardAssistantService;
use App\Services\DocumentSearchService;
use App\Services\EmployeeSearchService;
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
    private function assistant(SafeSqlService $sql, HrChatbotAnswerer $fallback): AiQueryService
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
            $sql,
            $fallback,
        );
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
}
