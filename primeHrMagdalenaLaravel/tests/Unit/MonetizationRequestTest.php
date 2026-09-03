<?php

namespace Tests\Unit;

use App\Models\MonetizationRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The approved modal renders the office's Monetization sheet straight from
 * the request row (S × D × CF), so the model's arithmetic is a payroll
 * figure, not display trivia. These pin the formula against the worked
 * example in docs/excels/Monetization-2022 2.docx and the request-number
 * sequence the tables sort by.
 */
class MonetizationRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    /**
     * Only the table these assertions touch, built by hand on the in-memory
     * SQLite connection. The project's own migrations cannot run here —
     * 2026_04_15_182306_add_timestamps_to_tables emits MySQL-only
     * `ON UPDATE CURRENT_TIMESTAMP` — so RefreshDatabase is not an option.
     */
    private function createSchema(): void
    {
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
    }

    #[Test]
    public function total_days_sums_vl_and_sl(): void
    {
        $request = new MonetizationRequest(['vl_days' => 10, 'sl_days' => 5]);

        $this->assertSame(15.0, $request->totalDays());
    }

    #[Test]
    public function computes_total_leave_benefits_like_the_office_sheet(): void
    {
        // First worked example in Monetization-2022 2.docx:
        // 12,087 × 75 days × 0.0481927. The sheet prints 43,687.88 — a
        // centavo under exact rounding (43,687.887…); the system rounds.
        $request = new MonetizationRequest([
            'monthly_salary' => 12087.00,
            'vl_days' => 75,
            'sl_days' => 0,
        ]);

        $this->assertSame(75.0, $request->totalDays());
        $this->assertSame(43687.89, $request->computeAmount());
    }

    #[Test]
    public function creating_assigns_sequential_request_numbers(): void
    {
        $first = MonetizationRequest::create([
            'employee_id' => 1, 'vl_days' => 5, 'sl_days' => 0,
            'monthly_salary' => 20000, 'reason' => 'Test',
        ]);
        $second = MonetizationRequest::create([
            'employee_id' => 1, 'vl_days' => 3, 'sl_days' => 2,
            'monthly_salary' => 20000, 'reason' => 'Test',
        ]);

        $year = date('Y');
        $this->assertSame("MON-{$year}-0001", $first->request_number);
        $this->assertSame("MON-{$year}-0002", $second->request_number);
    }
}
