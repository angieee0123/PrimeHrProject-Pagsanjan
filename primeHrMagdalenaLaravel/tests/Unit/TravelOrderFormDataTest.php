<?php

namespace Tests\Unit;

use App\Models\TravelOrder;
use App\Services\TravelOrderFormDataService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The printed Authority to Travel is a document the municipality hands out, so
 * what it states has to come from the record it was printed from. These pin the
 * decisions the service makes on the way there — which are exactly the ones a
 * form built by hand would get wrong:
 *
 *  1. every field on the sheet is read from the travel order, never re-typed;
 *  2. only the people actually travelling are named;
 *  3. the letter is dated when the authority was granted, not when it is
 *     reprinted;
 *  4. a signature line is left blank rather than filled with a placeholder.
 */
class TravelOrderFormDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    /**
     * Only the tables the service reads, built by hand on the in-memory SQLite
     * connection. The project's own migrations cannot run here —
     * 2026_04_15_182306_add_timestamps_to_tables emits MySQL-only
     * `ON UPDATE CURRENT_TIMESTAMP` — so RefreshDatabase is not an option.
     */
    private function createSchema(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->text('roles')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
        });

        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('destination')->nullable();
            $table->text('purpose')->nullable();
            $table->date('travel_date')->nullable();
            $table->date('return_date')->nullable();
            $table->integer('duration')->default(1);
            $table->string('transportation_mode')->nullable();
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_order_companions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_order_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        // TravelOrder is Auditable, so every write here goes looking for it.
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event')->nullable();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
        });
    }

    private function employee(string $first, string $middle, string $last, string $designation): int
    {
        $designationId = DB::table('designations')->insertGetId(['title' => $designation]);

        $id = DB::table('employees')->insertGetId([
            'employee_id' => 'PGS-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
        ]);

        DB::table('employment_details')->insert([
            'employee_id' => $id,
            'designation_id' => $designationId,
        ]);

        return $id;
    }

    private function order(array $overrides = []): TravelOrder
    {
        $employeeId = $overrides['employee_id']
            ?? $this->employee('Anna Linda', 'Javier', 'Cabrega', 'Administrative Aide IV');

        return TravelOrder::create(array_merge([
            'order_number' => 'TO-202602-0001',
            'employee_id' => $employeeId,
            'destination' => 'PSA San Pablo',
            'purpose' => 'To bring pertinent documents',
            'travel_date' => '2026-02-06',
            'return_date' => '2026-02-06',
            'duration' => 1,
            'status' => 'approved',
            'approved_at' => '2026-07-24 09:15:00',
        ], $overrides));
    }

    private function build(TravelOrder $order): array
    {
        return (new TravelOrderFormDataService())->build($order->id);
    }

    #[Test]
    public function it_prints_the_travellers_name_and_designation_from_the_record(): void
    {
        $data = $this->build($this->order());

        $this->assertSame(
            [['name' => 'ANNA LINDA J. CABREGA', 'designation' => 'ADMINISTRATIVE AIDE IV']],
            $data['personnel']
        );
    }

    #[Test]
    public function the_letter_is_dated_when_the_authority_was_granted(): void
    {
        // Not the travel date, and not "today": a reprint next year must carry
        // the same date as the copy already in the file.
        $data = $this->build($this->order());

        $this->assertSame('July 24, 2026', $data['issuedDate']);
    }

    #[Test]
    public function an_order_never_approved_falls_back_to_the_date_it_was_filed(): void
    {
        $order = $this->order(['status' => 'pending', 'approved_at' => null]);
        $order->forceFill(['created_at' => '2026-01-09 08:00:00'])->save();

        $this->assertSame('January 09, 2026', $this->build($order->fresh())['issuedDate']);
    }

    #[Test]
    public function a_one_day_trip_prints_the_bare_date_and_a_range_names_both_ends(): void
    {
        $this->assertSame('February 06, 2026', $this->build($this->order())['durationText']);

        $range = $this->order([
            'order_number' => 'TO-202602-0002',
            'return_date' => '2026-02-10',
            'duration' => 5,
        ]);

        $this->assertSame(
            'February 06, 2026 to February 10, 2026 (5 days)',
            $this->build($range)['durationText']
        );
    }

    #[Test]
    public function only_companions_who_accepted_are_granted_authority_to_travel(): void
    {
        $order = $this->order();

        $accepted = $this->employee('Jose', 'Rizal', 'Mercado', 'Administrative Officer II');
        $declined = $this->employee('Andres', 'Katipunan', 'Bonifacio', 'Driver I');
        $pending  = $this->employee('Melchora', 'Tandang', 'Aquino', 'Utility Worker I');

        DB::table('travel_order_companions')->insert([
            ['travel_order_id' => $order->id, 'employee_id' => $accepted, 'status' => 'accepted'],
            ['travel_order_id' => $order->id, 'employee_id' => $declined, 'status' => 'rejected'],
            ['travel_order_id' => $order->id, 'employee_id' => $pending,  'status' => 'pending'],
        ]);

        $names = array_column($this->build($order->fresh())['personnel'], 'name');

        $this->assertSame(['ANNA LINDA J. CABREGA', 'JOSE R. MERCADO'], $names);
    }

    #[Test]
    public function the_optional_rows_appear_only_when_the_record_carries_them(): void
    {
        // The office's own template shows three rows; anything more has to be
        // something the filer actually entered.
        $labels = array_column($this->build($this->order())['detailRows'], 0);

        $this->assertSame(['DESTINATION', 'DURATION OF TRAVEL', 'PURPOSE OF TRAVEL'], $labels);

        $full = $this->order([
            'order_number' => 'TO-202602-0003',
            'transportation_mode' => 'Government Vehicle',
            'estimated_budget' => 2500,
        ]);

        $rows = $this->build($full)['detailRows'];

        $this->assertSame(
            ['DESTINATION', 'DURATION OF TRAVEL', 'PURPOSE OF TRAVEL', 'MEANS OF TRANSPORTATION', 'ESTIMATED BUDGET'],
            array_column($rows, 0)
        );
        $this->assertSame('Government Vehicle', $rows[3][1]);
        $this->assertSame('PHP 2,500.00', $rows[4][1]);
    }

    #[Test]
    public function the_recommending_line_names_whoever_approved_the_order(): void
    {
        $approverEmployee = $this->employee('Maria', 'Santos', 'Delos Reyes', 'Administrative Officer V');

        $approver = DB::table('users')->insertGetId([
            'username' => 'mdelosreyes',
            'email' => 'm@example.test',
            'password' => 'x',
            'employee_id' => $approverEmployee,
        ]);

        $order = $this->order(['approved_by' => $approver]);

        $this->assertSame('MARIA S. DELOS REYES', $this->build($order->fresh())['recommendingName']);
    }

    #[Test]
    public function a_placeholder_never_reaches_a_signature_line(): void
    {
        // "N/A" printed over "Municipal Administrator" is worse than a blank
        // rule, which is what the paper form expects anyway.
        config(['cs_form.travel_order.approving.name' => 'N/A']);

        $this->assertSame('', $this->build($this->order())['approvingName']);
    }

    #[Test]
    public function the_form_reports_whether_the_order_was_actually_approved(): void
    {
        // The controller refuses to print an unapproved order off this flag: an
        // authority to travel must not assert a decision nobody has made.
        $this->assertTrue($this->build($this->order())['isApproved']);

        $pending = $this->order(['order_number' => 'TO-202602-0004', 'status' => 'pending']);

        $this->assertFalse($this->build($pending)['isApproved']);
    }
}
