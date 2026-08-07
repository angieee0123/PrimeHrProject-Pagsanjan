<?php

namespace Tests\Unit;

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\AttendancePunch;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Services\AttendanceComputationService;
use App\Services\AttendancePunchService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * AttendancePunchService is the seam a biometric reader will plug into, so
 * these assertions are about the pipeline rather than the QR badge: a punch
 * lands in the right slot, a double-read does not rewrite an arrival time, an
 * approved leave day is not silently overwritten, and — the one that would
 * quietly cost employees real leave credits — a morning punch does not charge
 * the afternoon as undertime before the afternoon has happened.
 */
class AttendancePunchServiceTest extends TestCase
{
    private AttendancePunchService $punches;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->punches = new AttendancePunchService(new AttendanceComputationService());

        $this->employee = Employee::create([
            'employee_id' => 'EMP-001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        DB::table('designations')->insert(['id' => 1, 'title' => 'Clerk III', 'monthly_rate' => 22000]);
        DB::table('employment_details')->insert([
            'employee_id' => $this->employee->id,
            'department_id' => 1,
            'designation_id' => 1,
        ]);

        DB::table('schedules')->insert([
            'employee_id' => $this->employee->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'am_in' => '08:00:00',
            'am_out' => '12:00:00',
            'pm_in' => '13:00:00',
            'pm_out' => '17:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function a_punch_fills_the_chosen_slot_and_leaves_an_audit_row(): void
    {
        $at = Carbon::parse('2026-08-10 07:58:00');

        $result = $this->punches->punch($this->employee, 'am_in', $at, 'qr_scan', 99, 'Front desk');

        $this->assertSame('recorded', $result['status']);

        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertSame('07:58', $attendance->am_in);
        $this->assertNull($attendance->pm_in);

        $punch = AttendancePunch::first();
        $this->assertSame('am_in', $punch->slot);
        $this->assertSame('qr_scan', $punch->source);
        $this->assertSame(99, $punch->recorded_by);
        $this->assertNull($punch->previous_value);
    }

    #[Test]
    public function a_second_read_moments_later_does_not_rewrite_the_arrival_time(): void
    {
        $at = Carbon::parse('2026-08-10 07:58:00');
        $this->punches->punch($this->employee, 'am_in', $at);

        // The camera decodes the same badge continuously while it is held up.
        $result = $this->punches->punch($this->employee, 'am_in', $at->copy()->addSeconds(40));

        $this->assertSame('duplicate', $result['status']);
        $this->assertSame('07:58', Attendance::first()->am_in);
        $this->assertSame(1, AttendancePunch::count());
    }

    #[Test]
    public function a_genuine_rescan_updates_the_slot_and_records_what_it_replaced(): void
    {
        $this->punches->punch($this->employee, 'am_in', Carbon::parse('2026-08-10 07:58:00'));

        $result = $this->punches->punch($this->employee, 'am_in', Carbon::parse('2026-08-10 08:20:00'));

        $this->assertSame('updated', $result['status']);
        $this->assertSame('08:20', Attendance::first()->am_in);
        $this->assertSame('07:58', AttendancePunch::latest('id')->first()->previous_value);
    }

    #[Test]
    public function a_day_already_owned_by_an_approved_leave_is_not_overwritten(): void
    {
        // What the LeaveApplication observer writes when leave is approved.
        Attendance::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-08-10',
            'attendance_type' => 'LEAVE',
            'remarks' => 'Leave: Vacation Leave - APP-0001',
        ]);

        $result = $this->punches->punch($this->employee, 'am_in', Carbon::parse('2026-08-10 07:58:00'));

        $this->assertSame('blocked', $result['status']);
        $this->assertStringContainsString('approved leave', $result['message']);
        $this->assertNull(Attendance::first()->am_in);
        $this->assertSame(0, AttendancePunch::count());
    }

    #[Test]
    public function leave_credits_are_not_charged_until_the_day_is_finished(): void
    {
        $balance = LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_code' => 'VL',
            'year' => 2026,
            'total_credits' => 15,
            'used_credits' => 0,
            'pending_credits' => 0,
            'available_credits' => 15,
            'carried_over' => 0,
        ]);

        // Morning punch only. The accreditation engine reads an unfinished day
        // as an eight-hour absence, so running deductions here would charge a
        // full day of leave to someone still at their desk.
        $this->punches->punch($this->employee, 'am_in', Carbon::parse('2026-08-10 08:00:00'));

        $log = AccreditedHoursLog::first();
        $this->assertNotNull($log, 'the day should still be accredited so the DTR updates live');
        $this->assertFalse((bool) $log->undertime_deducted_from_leave);
        $this->assertEqualsWithDelta(15, $balance->fresh()->available_credits, 0.0001);
    }

    #[Test]
    public function the_suggested_slot_follows_the_schedule_and_the_day_so_far(): void
    {
        $morning = Carbon::parse('2026-08-10 07:55:00');
        $this->assertSame('am_in', $this->punches->suggestSlot($this->employee, $morning));

        $this->punches->punch($this->employee, 'am_in', $morning);
        $this->assertSame('am_out', $this->punches->suggestSlot($this->employee, Carbon::parse('2026-08-10 12:01:00')));

        $this->punches->punch($this->employee, 'am_out', Carbon::parse('2026-08-10 12:01:00'));
        $this->assertSame('pm_in', $this->punches->suggestSlot($this->employee, Carbon::parse('2026-08-10 13:02:00')));

        $this->punches->punch($this->employee, 'pm_in', Carbon::parse('2026-08-10 13:02:00'));
        $this->assertSame('pm_out', $this->punches->suggestSlot($this->employee, Carbon::parse('2026-08-10 17:01:00')));

        $this->punches->punch($this->employee, 'pm_out', Carbon::parse('2026-08-10 17:01:00'));
        $this->assertSame('ot_in', $this->punches->suggestSlot($this->employee, Carbon::parse('2026-08-10 17:30:00')));
    }

    /**
     * Only the tables these assertions touch, built by hand on the in-memory
     * SQLite connection. The project's own migrations cannot run here — see
     * CLAUDE.md — so RefreshDatabase is not an option.
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
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('monthly_rate', 12, 2)->default(0);
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('am_in')->nullable();
            $table->string('am_out')->nullable();
            $table->string('pm_in')->nullable();
            $table->string('pm_out')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('am_in', 10)->nullable();
            $table->string('am_out', 10)->nullable();
            $table->string('pm_in', 10)->nullable();
            $table->string('pm_out', 10)->nullable();
            $table->string('ot_in', 10)->nullable();
            $table->string('ot_out', 10)->nullable();
            $table->integer('accredited_hours')->nullable();
            $table->integer('total_hours')->nullable();
            $table->string('attendance_type')->default('REGULAR');
            $table->text('remarks')->nullable();
        });

        Schema::create('attendance_punches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->date('date');
            $table->string('slot');
            $table->dateTime('punched_at');
            $table->string('source')->default('qr_scan');
            $table->string('device_label')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->string('previous_value', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_exemptions', function (Blueprint $table) {
            $table->id();
            $table->string('exemption_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_name')->nullable();
            $table->boolean('exempt_from_abandoned')->default(true);
            $table->boolean('exempt_from_incomplete')->default(true);
            $table->text('reason')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('am_in_not_required')->default(false);
            $table->boolean('am_out_not_required')->default(false);
            $table->boolean('pm_in_not_required')->default(false);
            $table->boolean('pm_out_not_required')->default(false);
            $table->boolean('auto_fill_am_out')->default(true);
            $table->boolean('auto_fill_pm_in')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('pass_slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('time_out')->nullable();
            $table->string('time_in')->nullable();
            $table->string('status')->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('accredited_hours_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->integer('am_accredited_minutes')->default(0);
            $table->integer('pm_accredited_minutes')->default(0);
            $table->integer('ot_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->boolean('late_deducted_from_leave')->default(false);
            $table->string('late_deduction_leave_type', 10)->nullable();
            $table->integer('undertime_minutes')->default(0);
            $table->boolean('undertime_deducted_from_leave')->default(false);
            $table->string('undertime_deduction_leave_type', 10)->nullable();
            $table->integer('lwop_minutes')->default(0);
            $table->boolean('requires_salary_deduction')->default(false);
            $table->integer('total_accredited_minutes')->default(0);
            $table->integer('total_actual_minutes')->default(0);
            $table->boolean('am_grace_applied')->default(false);
            $table->boolean('pm_grace_applied')->default(false);
            $table->text('computation_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_salary_computations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('accredited_hours_log_id');
            $table->date('work_date');
            $table->decimal('monthly_rate', 12, 2)->default(0);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('daily_basic_pay', 12, 2)->default(0);
            $table->decimal('ot_pay', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('undertime_deduction', 12, 2)->default(0);
            $table->decimal('daily_gross_pay', 12, 2)->default(0);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_rest_day')->default(false);
            $table->string('holiday_type')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code', 10);
            $table->integer('year');
            $table->decimal('total_credits', 10, 6)->default(0);
            $table->decimal('used_credits', 10, 6)->default(0);
            $table->decimal('pending_credits', 10, 6)->default(0);
            $table->decimal('available_credits', 10, 6)->default(0);
            $table->decimal('carried_over', 10, 6)->default(0);
            $table->timestamps();
        });

        Schema::create('leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('leave_code', 10)->nullable();
            $table->string('transaction_type')->nullable();
            $table->decimal('credits', 10, 6)->default(0);
            $table->decimal('balance_before', 10, 6)->default(0);
            $table->decimal('balance_after', 10, 6)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->date('transaction_date')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });
    }
}
