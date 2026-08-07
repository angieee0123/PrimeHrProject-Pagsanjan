<?php

namespace Tests\Unit;

use App\Models\DailySalaryComputation;
use App\Services\AttendanceComputationService;
use App\Services\CscTimeConversionService;
use App\Services\HrPolicyFactsService;
use App\Services\LateDeductionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The assistant may only state rules it read.
 *
 * Its knowledge used to be a string constant listing 7 leave types while
 * `leave_types_config` held 20 active ones, and quoting 480 / 5 / 22 as
 * literals beside the services that define them. Those are two copies of one
 * rule with nothing keeping them equal, and the copy had already gone stale.
 *
 * These tests pin the property that replaced it: every figure is derived, so
 * editing the configuration changes the answer.
 */
class HrPolicyFactsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        Schema::create('leave_accrual_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_type_id');
            $table->decimal('credits_earned_per_period', 8, 4);
            $table->string('accrual_frequency');
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
        });

        // Every row carries the same keys: a bulk insert with differing key
        // sets is rejected outright.
        $row = fn (array $values) => $values + [
            'is_accrued' => false, 'annual_limit' => 0, 'is_cumulative' => false,
            'requires_6_months' => false, 'is_monetizable' => false,
            'requires_attachment' => false, 'attachment_info' => null, 'is_active' => true,
        ];

        DB::table('leave_types_config')->insert([
            $row(['id' => 1, 'leave_code' => 'VL', 'leave_name' => 'Vacation Leave', 'is_accrued' => true,
                'annual_limit' => 15, 'is_cumulative' => true, 'is_monetizable' => true]),
            $row(['id' => 2, 'leave_code' => 'BL', 'leave_name' => 'Bereavement Leave',
                'annual_limit' => 3, 'requires_attachment' => true]),
            $row(['id' => 3, 'leave_code' => 'OLD', 'leave_name' => 'Retired Leave',
                'annual_limit' => 9, 'is_active' => false]),
        ]);

        DB::table('leave_accrual_rates')->insert([
            ['leave_type_id' => 1, 'credits_earned_per_period' => 1.25,
                'accrual_frequency' => 'monthly', 'effective_date' => '2026-01-01', 'is_active' => true],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('leave_accrual_rates');
        Schema::dropIfExists('leave_types_config');
        parent::tearDown();
    }

    private function facts(): HrPolicyFactsService
    {
        // A fresh instance each time: the service caches per request, and these
        // tests deliberately change the configuration underneath it.
        return new HrPolicyFactsService();
    }

    #[Test]
    public function leave_types_come_from_the_configuration_table(): void
    {
        $block = $this->facts()->knowledgeBlock();

        $this->assertStringContainsString('BL (Bereavement Leave)', $block);
        $this->assertStringContainsString('VL (Vacation Leave)', $block);
    }

    /**
     * The bug this class exists to prevent, stated directly: the answer has to
     * follow the configuration, not a remembered copy of it.
     */
    #[Test]
    public function editing_the_configuration_changes_the_answer(): void
    {
        $this->assertStringContainsString('3 day(s)/year', $this->facts()->knowledgeBlock());

        DB::table('leave_types_config')->where('leave_code', 'BL')->update(['annual_limit' => 7]);

        $this->assertStringContainsString('7 day(s)/year', $this->facts()->knowledgeBlock());
    }

    #[Test]
    public function a_newly_configured_leave_type_appears_without_a_code_change(): void
    {
        $this->assertStringNotContainsString('Wellness Leave', $this->facts()->knowledgeBlock());

        DB::table('leave_types_config')->insert([
            'id' => 4, 'leave_code' => 'WL', 'leave_name' => 'Wellness Leave',
            'annual_limit' => 5, 'is_active' => true,
        ]);

        $this->assertStringContainsString('WL (Wellness Leave)', $this->facts()->knowledgeBlock());
    }

    #[Test]
    public function deactivated_leave_types_are_not_offered(): void
    {
        $this->assertStringNotContainsString('Retired Leave', $this->facts()->knowledgeBlock());
    }

    #[Test]
    public function the_accrual_rate_is_read_not_assumed(): void
    {
        $this->assertStringContainsString('accrues 1.25/month', $this->facts()->knowledgeBlock());

        DB::table('leave_accrual_rates')->where('leave_type_id', 1)
            ->update(['credits_earned_per_period' => 2.5]);

        $this->assertStringContainsString('accrues 2.5/month', $this->facts()->knowledgeBlock());
    }

    /**
     * The numbers the assistant quotes must be the ones the payroll and
     * attendance code actually uses, read from those classes rather than
     * retyped beside them.
     */
    #[Test]
    public function constants_are_taken_from_the_services_that_define_them(): void
    {
        $facts = $this->facts()->facts();

        $this->assertSame(
            CscTimeConversionService::MINUTES_PER_WORK_DAY,
            $facts['conversion']['minutes_per_day']
        );
        $this->assertSame(
            AttendanceComputationService::GRACE_MINUTES,
            $facts['attendance']['grace_minutes']
        );
        $this->assertSame(
            DailySalaryComputation::WORKING_DAYS_PER_MONTH,
            $facts['payroll']['working_days_per_month']
        );
        $this->assertSame(
            LateDeductionService::DEDUCTION_ORDER,
            $facts['payroll']['deduction_order']
        );
    }

    #[Test]
    public function the_rendered_block_quotes_those_same_constants(): void
    {
        $block = $this->facts()->knowledgeBlock();

        $this->assertStringContainsString(
            CscTimeConversionService::MINUTES_PER_WORK_DAY . ' minutes = 1 work day',
            $block
        );
        $this->assertStringContainsString(
            '÷ ' . DailySalaryComputation::WORKING_DAYS_PER_MONTH . ') × LWOP days',
            $block
        );
        $this->assertStringContainsString('VL first, then SL', $block);
    }

    /**
     * A missing configuration table must produce silence, not a fallback list.
     * A stale hard-coded list is exactly the failure being designed out, so an
     * empty answer is the correct degraded behaviour.
     */
    #[Test]
    public function an_unreadable_configuration_lists_nothing_rather_than_guessing(): void
    {
        Schema::drop('leave_types_config');

        $block = $this->facts()->knowledgeBlock();

        $this->assertStringContainsString('Do not list leave types from memory', $block);
        $this->assertStringNotContainsString('Vacation Leave', $block);
    }
}
