<?php

namespace Tests\Unit;

use App\Models\LeaveBalance;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * What "my current leave credits" means.
 *
 * Balance rows are not rewritten every January: a row is written when credits
 * are next computed, so an employee's live figures routinely sit under an older
 * `year`. The assistant used to ask for `where('year', now()->year)` and told an
 * employee "walang leave balances" while their own Leave & Benefits page showed
 * 136.25 days of SL — the page had always ordered by year instead.
 *
 * LeaveBalance::currentFor() is now the single implementation of that rule,
 * shared by the leave pages and the assistant, so the two cannot disagree.
 */
class LeaveBalanceCurrentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code');
            $table->integer('year');
            $table->decimal('total_credits', 12, 6)->default(0);
            $table->decimal('used_credits', 12, 6)->default(0);
            $table->decimal('pending_credits', 12, 6)->default(0);
            $table->decimal('available_credits', 12, 6)->default(0);
            $table->decimal('carried_over', 12, 6)->default(0);
            $table->timestamps();
        });

        // Employee 8's real shape: several years of history, the newest row
        // under 2023 even though "today" is 2026.
        $rows = [
            ['employee_id' => 8, 'leave_code' => 'VL', 'year' => 2022, 'available_credits' => 67.032],
            ['employee_id' => 8, 'leave_code' => 'SL', 'year' => 2022, 'available_credits' => 128.25],
            ['employee_id' => 8, 'leave_code' => 'VL', 'year' => 2023, 'available_credits' => 72.032],
            ['employee_id' => 8, 'leave_code' => 'SL', 'year' => 2023, 'available_credits' => 136.25],
            ['employee_id' => 9, 'leave_code' => 'VL', 'year' => 2026, 'available_credits' => 5.0],
        ];

        DB::table('leave_balances')->insert(array_map(
            fn (array $r) => $r + ['total_credits' => 15, 'used_credits' => 0, 'pending_credits' => 0,
                'carried_over' => 0, 'created_at' => now(), 'updated_at' => now()],
            $rows
        ));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('leave_balances');
        parent::tearDown();
    }

    /**
     * The bug, stated directly: the current balance is the newest row, not the
     * row whose year happens to equal today's.
     */
    #[Test]
    public function current_balances_come_from_the_latest_year_not_the_present_one(): void
    {
        $balances = LeaveBalance::currentFor(8);

        $this->assertCount(2, $balances);
        $this->assertEqualsWithDelta(72.032, (float) $balances['VL']->available_credits, 0.001);
        $this->assertEqualsWithDelta(136.25, (float) $balances['SL']->available_credits, 0.001);
    }

    #[Test]
    public function older_years_are_not_returned_alongside_the_current_one(): void
    {
        $years = LeaveBalance::currentFor(8)->map(fn ($b) => $b->year)->unique()->values()->all();

        $this->assertSame([2023], $years);
    }

    #[Test]
    public function a_filter_on_the_present_year_would_have_found_nothing(): void
    {
        // Why the old implementation reported "no leave balances on file".
        $this->assertSame(
            0,
            LeaveBalance::where('employee_id', 8)->where('year', (int) date('Y'))->count()
        );
        $this->assertNotEmpty(LeaveBalance::currentFor(8));
    }

    #[Test]
    public function balances_are_scoped_to_the_employee_asked_for(): void
    {
        $this->assertSame([8, 8], LeaveBalance::currentFor(8)->pluck('employee_id')->all());
        $this->assertCount(1, LeaveBalance::currentFor(9));
    }

    #[Test]
    public function a_single_code_resolves_to_its_latest_row(): void
    {
        $this->assertSame(2023, LeaveBalance::currentForCode(8, 'SL')?->year);
        $this->assertNull(LeaveBalance::currentForCode(8, 'SPL'));
    }

    #[Test]
    public function an_employee_with_no_rows_gets_an_empty_collection(): void
    {
        $this->assertTrue(LeaveBalance::currentFor(999)->isEmpty());
    }
}
