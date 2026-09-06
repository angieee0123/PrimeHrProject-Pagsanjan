<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\MonetizationRequest;
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
use App\Services\HrPolicyFactsService;
use App\Services\ReportGeneratorService;
use App\Services\SafeSqlService;
use App\Services\WorkflowAssistantService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Monetization in the AI Assistant.
 *
 * Converting VL/SL credits to cash is a first-class HR workflow with its own
 * table, statuses, and formula — but the assistant could neither route its
 * questions (the word matched no intent rule) nor read its rows (the table
 * was not in the generated-SQL allow-list). These pin every layer of the
 * fix:
 *
 *  1. routing — own rows to self_service, org-wide rows to data_query, rule
 *     questions to how_to, counts to dashboard, registers to report;
 *  2. policy — rule answers read the live config and the model's own
 *     constant, and record phrasings never receive the rulebook;
 *  3. self-service — the caller's own requests render from the stored
 *     computed_amount, never recomputed;
 *  4. reports and SQL — the register exists and the table is queryable.
 */
class MonetizationAssistantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info', 'warning', 'error')->andReturnNull();

        // No provider in tests: narration degrades to deterministic fallbacks,
        // which is what these assertions read.
        config(['services.groq.api_key' => '']);
        $_ENV['GROQ_API_KEY'] = '';
        $_SERVER['GROQ_API_KEY'] = '';
        putenv('GROQ_API_KEY=');

        $this->createSchema();
        $this->seedData();
    }

    private function createSchema(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });

        Schema::create('leave_accrual_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_type_id');
            $table->decimal('credits_earned_per_period', 8, 4);
            $table->string('accrual_frequency');
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('monetization_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->decimal('vl_days', 10, 3)->default(0);
            $table->decimal('sl_days', 10, 3)->default(0);
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->decimal('vl_balance', 10, 3)->nullable();
            $table->decimal('sl_balance', 10, 3)->nullable();
            $table->decimal('computed_amount', 14, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('approver_remarks')->nullable();
            $table->unsignedBigInteger('filed_by')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_types_config', function (Blueprint $table) {
            $table->id();
            $table->string('leave_code');
            $table->string('leave_name');
            $table->boolean('is_accrued')->default(false);
            $table->decimal('annual_limit', 8, 2)->default(0);
            $table->boolean('is_cumulative')->default(false);
            $table->boolean('requires_6_months')->default(false);
            $table->boolean('is_monetizable')->default(false);
            $table->boolean('requires_attachment')->default(false);
            $table->text('attachment_info')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });

        Schema::create('user_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });
    }

    private function seedData(): void
    {
        DB::table('employees')->insert([
            'id' => 1, 'employee_id' => 'EMP-001',
            'first_name' => 'Juan', 'last_name' => 'Santos',
        ]);

        DB::table('leave_types_config')->insert([
            ['id' => 1, 'leave_code' => 'VL', 'leave_name' => 'Vacation Leave',
                'is_monetizable' => true, 'is_active' => true],
            ['id' => 2, 'leave_code' => 'SL', 'leave_name' => 'Sick Leave',
                'is_monetizable' => true, 'is_active' => true],
        ]);

        MonetizationRequest::create([
            'employee_id' => 1, 'vl_days' => 10, 'sl_days' => 5,
            'monthly_salary' => 20000, 'vl_balance' => 20, 'sl_balance' => 15,
            'computed_amount' => 14457.81, 'reason' => 'Medical expenses',
            'status' => 'pending',
        ]);

        MonetizationRequest::create([
            'employee_id' => 1, 'vl_days' => 4, 'sl_days' => 0,
            'monthly_salary' => 20000, 'vl_balance' => 30, 'sl_balance' => 15,
            'computed_amount' => 3855.42, 'reason' => 'Tuition fee',
            'status' => 'disapproved', 'approver_remarks' => 'Insufficient service credits documentation',
        ]);
    }

    private function user(array $roles, ?int $employeeId = null): User
    {
        $user = new User();
        $user->roles = $roles;
        $user->status = 'Active';
        $user->employee_id = $employeeId;

        return $user;
    }

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

    // ── routing ─────────────────────────────────────────────────────────────

    #[Test]
    public function own_monetization_rows_route_to_self_service(): void
    {
        $assistant = $this->assistant(
            $this->createMock(SafeSqlService::class),
            $this->createMock(HrChatbotAnswerer::class),
        );

        $result = $assistant->ask(
            $this->user(['employee'], 1),
            'what is the status of my monetization request'
        );

        $this->assertSame('self_service', $result['intent']);
    }

    #[Test]
    public function org_wide_monetization_rows_route_to_data_query(): void
    {
        $sql = $this->createMock(SafeSqlService::class);
        $sql->method('query')->willReturn(['answer' => 'rows', 'data' => []]);

        $assistant = $this->assistant(
            $sql,
            $this->createMock(HrChatbotAnswerer::class),
        );

        $result = $assistant->ask(
            $this->user(['hr'], 1),
            'show me all monetization requests'
        );

        $this->assertSame('data_query', $result['intent']);
    }

    #[Test]
    public function monetization_rule_questions_route_to_how_to(): void
    {
        $fallback = $this->createMock(HrChatbotAnswerer::class);
        $fallback->method('explain')->willReturn('policy');

        $assistant = $this->assistant(
            $this->createMock(SafeSqlService::class),
            $fallback,
        );

        $result = $assistant->ask(
            $this->user(['employee'], 1),
            'how is the monetization amount computed'
        );

        $this->assertSame('how_to', $result['intent']);
        $this->assertSame('policy', $result['answer']);
    }

    // ── policy (rulebook, never records) ────────────────────────────────────

    private function answerer(): HrChatbotAnswerer
    {
        return new HrChatbotAnswerer(
            new AiAccessPolicy(),
            new SafeSqlService(new AiAccessPolicy()),
            new HrPolicyFactsService(),
        );
    }

    #[Test]
    public function a_rule_question_is_answered_from_the_live_config(): void
    {
        $answer = $this->answerer()->shortcutAnswer('how is the monetization amount computed');

        $this->assertNotNull($answer);
        $this->assertStringContainsString('VL and SL', $answer);
        $this->assertStringContainsString('0.0481927', $answer);
        $this->assertStringContainsString('My Monetization', $answer);
    }

    #[Test]
    public function a_record_question_never_gets_the_rulebook(): void
    {
        $this->assertNull($this->answerer()->shortcutAnswer('what is the status of my monetization request'));
        $this->assertNull($this->answerer()->shortcutAnswer('list all pending monetization requests'));
    }

    #[Test]
    public function monetization_facts_are_read_not_typed(): void
    {
        $facts = (new HrPolicyFactsService())->monetization();

        $this->assertSame(['VL', 'SL'], $facts['codes']);
        $this->assertSame(MonetizationRequest::CONSTANT_FACTOR, $facts['constant_factor']);
        $this->assertContains('pending', $facts['statuses']);
        $this->assertContains('disapproved', $facts['statuses']);

        // The rendered block states the same figures the policy answer does.
        $block = (new HrPolicyFactsService())->knowledgeBlock();
        $this->assertStringContainsString('MONETIZATION', $block);
        $this->assertStringContainsString('0.0481927', $block);
    }

    // ── self-service (own rows, stored pesos) ───────────────────────────────

    #[Test]
    public function an_employee_sees_their_own_requests_with_stored_amounts(): void
    {
        $service = new EmployeeChatbotService(new HrPolicyFactsService());
        $result = $service->handle(Employee::find(1), 'what is the status of my monetization request');

        $this->assertStringContainsString('MON-', $result['response']);
        $this->assertStringContainsString('14,457.81', $result['response']);
        $this->assertStringContainsString('pending', strtolower($result['response']));
        $this->assertStringContainsString('Insufficient service credits documentation', $result['response']);
    }

    #[Test]
    public function an_employee_asking_how_it_works_gets_the_rule(): void
    {
        $service = new EmployeeChatbotService(new HrPolicyFactsService());
        $result = $service->handle(Employee::find(1), 'can I monetize my leave credits');

        $this->assertStringContainsString('My Monetization', $result['response']);
        $this->assertStringContainsString('0.0481927', $result['response']);
    }

    // ── reports and generated SQL ───────────────────────────────────────────

    #[Test]
    public function a_monetization_report_lists_requests_with_totals(): void
    {
        $service = new ReportGeneratorService(new AiAccessPolicy());
        $result = $service->generate($this->user(['hr']), 'generate a monetization report');

        $this->assertCount(2, $result['data']);
        $this->assertSame('Monetization Report', substr($result['report']['title'], 0, 19));
        $this->assertSame(2, $result['report']['totals']['Requests']);
        $this->assertEqualsWithDelta(18313.23, $result['report']['totals']['Total amount (PHP)'], 0.01);
        $this->assertSame(1, $result['report']['totals']['Pending']);
        $this->assertSame(1, $result['report']['totals']['Disapproved']);
    }

    #[Test]
    public function generated_sql_may_read_the_monetization_table(): void
    {
        $this->assertContains('monetization_requests', SafeSqlService::allowedTables());

        $verdict = (new SafeSqlService(new AiAccessPolicy()))->validate(
            'SELECT request_number, computed_amount, status FROM monetization_requests WHERE status = \'pending\' LIMIT 5'
        );

        $this->assertTrue($verdict['safe'], $verdict['reason'] ?? 'rejected');
    }
}

