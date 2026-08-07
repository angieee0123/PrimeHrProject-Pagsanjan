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
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsAttendanceSchema;
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
    use BuildsAttendanceSchema;

    private AttendancePunchService $punches;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttendanceSchema();
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

}
