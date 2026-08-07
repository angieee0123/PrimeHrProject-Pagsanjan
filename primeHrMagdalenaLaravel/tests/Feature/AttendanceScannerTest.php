<?php

namespace Tests\Feature;

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\AttendancePunch;
use App\Models\DailySalaryComputation;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceQrService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsAttendanceSchema;
use Tests\TestCase;

/**
 * The scanner endpoint, end to end.
 *
 * The point of the kiosk is that a scan becomes a real attendance record for a
 * real employee — the same row the DTR, accredited hours, and payroll read, not
 * a parallel log the rest of the system cannot see. These assertions follow one
 * scan all the way from the HTTP request to the daily salary figure, and check
 * that the badge alone is not enough to get in.
 */
class AttendanceScannerTest extends TestCase
{
    use BuildsAttendanceSchema;

    private Employee $employee;
    private string $badge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttendanceSchema();
        $this->createUsersSchema();

        $this->employee = Employee::create([
            'employee_id' => 'EMP-001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        DB::table('departments')->insert(['id' => 1, 'name' => 'Treasury', 'status' => 'Active']);
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

        $this->badge = app(AttendanceQrService::class)->payloadFor($this->employee);
    }

    protected function tearDown(): void
    {
        foreach ([
            'leave_transactions', 'leave_balances', 'daily_salary_computations',
            'accredited_hours_log', 'pass_slips', 'attendance_exemptions',
            'attendance_punches', 'attendance', 'schedules', 'employment_details',
            'departments', 'designations', 'employees', 'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    #[Test]
    public function a_scan_becomes_an_attendance_record_for_that_employee(): void
    {
        $response = $this->actingAs($this->staff())->postJson(route('admin.attendance.scanner.punch'), [
            'code' => $this->badge,
            'slot' => 'am_in',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'recorded',
            'employee' => [
                'employee_id' => 'EMP-001',
                'name' => 'Juan Dela Cruz',
                'department' => 'Treasury',
                'designation' => 'Clerk III',
            ],
        ]);

        // The row the DTR and payroll read — keyed to this employee and today.
        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance, 'the scan must land in `attendance`, not only in the punch log');
        $this->assertSame(now()->format('H:i'), $attendance->am_in);
        $this->assertSame('REGULAR', $attendance->attendance_type);

        $this->assertSame(1, AttendancePunch::where('attendance_id', $attendance->id)->count());
    }

    #[Test]
    public function a_full_day_of_scans_accredits_the_day_and_reaches_the_daily_salary(): void
    {
        $staff = $this->staff();

        // 08:03 and 12:58 are inside the five-minute grace, so a scanned day
        // must accredit the full eight hours exactly as a corrected one does.
        $this->travelTo(now()->setTime(8, 3));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'am_in'])->assertOk();

        $this->travelTo(now()->setTime(12, 1));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'am_out'])->assertOk();

        $this->travelTo(now()->setTime(12, 58));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'pm_in'])->assertOk();

        $this->travelTo(now()->setTime(17, 4));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'pm_out'])->assertOk();

        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertSame(['08:03', '12:01', '12:58', '17:04'], [
            $attendance->am_in, $attendance->am_out, $attendance->pm_in, $attendance->pm_out,
        ]);
        $this->assertSame(480, $attendance->accredited_hours);
        $this->assertSame(484, $attendance->total_hours);

        $log = AccreditedHoursLog::where('attendance_id', $attendance->id)->first();
        $this->assertSame(480, $log->total_accredited_minutes);
        $this->assertSame(0, $log->late_minutes);
        $this->assertSame(0, $log->undertime_minutes);
        $this->assertTrue((bool) $log->am_grace_applied);

        $this->assertSame(
            1,
            DailySalaryComputation::where('accredited_hours_log_id', $log->id)->count(),
            'a scanned day must reach payroll like any other day',
        );

        $this->travelBack();
    }

    #[Test]
    public function a_lateness_a_scan_captures_is_charged_like_any_other(): void
    {
        $staff = $this->staff();

        $this->travelTo(now()->setTime(9, 15)); // 75 minutes past 08:00, well beyond grace
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'am_in'])->assertOk();

        $this->travelTo(now()->setTime(12, 0));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'am_out'])->assertOk();

        $this->travelTo(now()->setTime(13, 0));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'pm_in'])->assertOk();

        $this->travelTo(now()->setTime(17, 0));
        $this->actingAs($staff)->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'pm_out'])->assertOk();

        $log = AccreditedHoursLog::first();
        $this->assertSame(75, $log->late_minutes);
        $this->assertSame(405, $log->total_accredited_minutes); // 8h less the 75 late minutes

        $this->travelBack();
    }

    #[Test]
    public function a_forged_badge_writes_nothing(): void
    {
        $forged = 'PHRM1.' . $this->employee->id . '.notarealsignature';

        $this->actingAs($this->staff())
            ->postJson(route('admin.attendance.scanner.punch'), ['code' => $forged, 'slot' => 'am_in'])
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid']);

        $this->assertSame(0, Attendance::count());
        $this->assertSame(0, AttendancePunch::count());
    }

    #[Test]
    public function an_unknown_slot_is_refused_rather_than_written_somewhere(): void
    {
        // The kiosk's own buttons are the only intended source, but the slot
        // still arrives from the browser and is therefore untrusted.
        $this->actingAs($this->staff())
            ->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'accredited_hours'])
            ->assertStatus(422);

        $this->assertSame(0, Attendance::count());
    }

    #[Test]
    public function a_plain_employee_cannot_operate_the_kiosk(): void
    {
        // A scan is recorded against the operator, so the kiosk is staffed. An
        // employee holding a valid badge must not be able to punch themselves in.
        $employeeUser = User::create([
            'email' => 'juan@example.test',
            'password' => bcrypt('secret'),
            'roles' => ['employee'],
            'status' => 'Active',
            'employee_id' => $this->employee->id,
        ]);

        $this->actingAs($employeeUser)
            ->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'am_in'])
            ->assertForbidden();

        $this->assertSame(0, Attendance::count());
    }

    #[Test]
    public function a_guest_cannot_punch(): void
    {
        $this->postJson(route('admin.attendance.scanner.punch'), ['code' => $this->badge, 'slot' => 'am_in'])
            ->assertUnauthorized();

        $this->assertSame(0, Attendance::count());
    }

    private function staff(): User
    {
        return User::create([
            'email' => 'hr@example.test',
            'password' => bcrypt('secret'),
            'roles' => ['hr'],
            'status' => 'Active',
        ]);
    }
}
