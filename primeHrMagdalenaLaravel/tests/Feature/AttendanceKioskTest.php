<?php

namespace Tests\Feature;

use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\AttendancePunch;
use App\Models\DailySalaryComputation;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceKioskService;
use App\Services\AttendanceQrService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsAttendanceSchema;
use Tests\TestCase;

/**
 * The public attendance kiosk, end to end.
 *
 * This terminal is the only unauthenticated write path in the application: it
 * exists because the employee using it has no account in front of them, and it
 * replaced a staffed scanner that lived behind the admin area's role gate. That
 * makes two properties worth pinning down harder than the average feature —
 * that an anonymous scan still becomes a *real* attendance record, and that
 * being anonymous does not expose anybody else's data.
 *
 * The first is why these assertions follow one scan from the HTTP request all
 * the way to the daily salary figure; the second is why the roster feed the
 * staffed page carried is asserted to be absent rather than merely unused.
 */
class AttendanceKioskTest extends TestCase
{
    use BuildsAttendanceSchema;

    private Employee $employee;
    private string $badge;
    private string $token;

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
        $this->token = app(AttendanceKioskService::class)->token();
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

    // ---------- the point of the move: no login ----------

    #[Test]
    public function a_guest_with_the_kiosk_address_can_punch_without_signing_in(): void
    {
        // No actingAs anywhere in this test — that is the feature. The employee
        // at the kiosk has no account in front of them, and must not be handed
        // the admin area to reach a terminal.
        $response = $this->postJson($this->punchUrl(), [
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
    public function the_kiosk_screen_loads_for_a_guest(): void
    {
        $this->get(route('kiosk.attendance', ['token' => $this->token]))
            ->assertOk()
            ->assertSee('Attendance Kiosk', false);
    }

    #[Test]
    public function an_employee_who_happens_to_be_signed_in_can_still_use_it(): void
    {
        // `kiosk` is not one of EnsureRoleForArea::AREA_ROLES, so a signed-in
        // employee is neither helped nor blocked by holding a session. Someone
        // who scans on the way past must not be turned away for being logged in.
        $user = User::create([
            'email' => 'juan@example.test',
            'password' => bcrypt('secret'),
            'roles' => ['employee'],
            'status' => 'Active',
            'employee_id' => $this->employee->id,
        ]);

        $this->actingAs($user)
            ->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in'])
            ->assertOk();
    }

    // ---------- the token is the door ----------

    #[Test]
    public function every_kiosk_route_is_unreachable_without_the_token(): void
    {
        $wrong = ['token' => 'not-the-real-token'];

        // 404 rather than 403: a wrong token must not confirm that a kiosk
        // exists at this address.
        $this->get(route('kiosk.attendance', $wrong))->assertNotFound();
        $this->postJson(route('kiosk.attendance.peek', $wrong), ['code' => $this->badge])->assertNotFound();
        $this->postJson(route('kiosk.attendance.punch', $wrong), ['code' => $this->badge, 'slot' => 'am_in'])->assertNotFound();

        $this->assertSame(0, Attendance::count(), 'an unauthorised request must not reach the punch service');
    }

    #[Test]
    public function the_address_changes_when_the_application_key_is_rotated(): void
    {
        // Rotation is the revocation path for a leaked kiosk URL — the same one
        // that invalidates every printed badge, which is why it is shared.
        $before = app(AttendanceKioskService::class)->token();

        Config::set('app.key', 'base64:' . base64_encode(str_repeat('z', 32)));

        $after = app(AttendanceKioskService::class)->token();

        $this->assertNotSame($before, $after);
        $this->assertFalse(app(AttendanceKioskService::class)->matches($before));
    }

    #[Test]
    public function the_old_admin_scanner_route_is_gone(): void
    {
        // The move was a replacement, not an addition. A second, staffed way to
        // write punches would quietly outlive the decision to have one terminal.
        $this->assertFalse(Route::has('admin.attendance.scanner'));
        $this->assertFalse(Route::has('admin.attendance.scanner.punch'));

        $this->get('/admin/attendance/scanner')->assertNotFound();
    }

    // ---------- nothing is written until the employee confirms ----------

    #[Test]
    public function the_peek_step_writes_nothing(): void
    {
        // Frozen at 08:00 deliberately. `suggestSlot()` reads the clock and the
        // day's record, so an unfrozen assertion here would pass or fail on what
        // time the suite happened to run — it asserted `am_in` at 08:00 and got
        // `pm_out` when the suite ran in the evening, which is the function
        // being right and the test being wrong.
        $this->travelTo(now()->setTime(8, 0));

        $this->postJson(route('kiosk.attendance.peek', ['token' => $this->token]), ['code' => $this->badge])
            ->assertOk()
            ->assertJson(['status' => 'ok', 'suggested_slot' => 'am_in']);

        $this->assertSame(0, Attendance::count());
        $this->assertSame(0, AttendancePunch::count());

        $this->travelBack();
    }

    #[Test]
    public function the_suggested_slot_follows_the_days_record(): void
    {
        // The server picks the slot now that there is no operator, so its
        // inference is the default the employee confirms. Morning in, then the
        // same morning reads as a morning out.
        $this->travelTo(now()->setTime(8, 0));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in'])->assertOk();

        $this->travelTo(now()->setTime(11, 30));
        $this->postJson(route('kiosk.attendance.peek', ['token' => $this->token]), ['code' => $this->badge])
            ->assertOk()
            ->assertJson(['suggested_slot' => 'am_out']);

        $this->travelBack();
    }

    // ---------- privacy ----------

    #[Test]
    public function the_kiosk_never_hands_back_another_employees_records(): void
    {
        $other = Employee::create(['employee_id' => 'EMP-002', 'first_name' => 'Pedro', 'last_name' => 'Reyes']);

        // Someone else has already punched today on this terminal.
        $this->travelTo(now()->setTime(7, 45));
        $this->postJson($this->punchUrl(), ['code' => app(AttendanceQrService::class)->payloadFor($other), 'slot' => 'am_in'])
            ->assertOk();
        $this->travelBack();

        $peek = $this->postJson(route('kiosk.attendance.peek', ['token' => $this->token]), ['code' => $this->badge]);
        $punch = $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in']);

        // The staffed page carried a live "today's punches" feed naming everyone
        // who had scanned. On a screen anybody can walk up to, and a URL that
        // needs no login, that is the whole roster's arrival times — so it is
        // asserted absent rather than merely left unrendered by the view.
        foreach ([$peek, $punch] as $response) {
            $response->assertOk();
            $this->assertArrayNotHasKey('recent', $response->json());
            $this->assertStringNotContainsString('Pedro', $response->getContent());
            $this->assertStringNotContainsString('EMP-002', $response->getContent());
        }
    }

    // ---------- the punch reaches payroll, as it always did ----------

    #[Test]
    public function a_full_day_of_scans_accredits_the_day_and_reaches_the_daily_salary(): void
    {
        // 08:03 and 12:58 are inside the five-minute grace, so a scanned day
        // must accredit the full eight hours exactly as a corrected one does.
        $this->travelTo(now()->setTime(8, 3));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in'])->assertOk();

        $this->travelTo(now()->setTime(12, 1));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_out'])->assertOk();

        $this->travelTo(now()->setTime(12, 58));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'pm_in'])->assertOk();

        $this->travelTo(now()->setTime(17, 4));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'pm_out'])->assertOk();

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
            'a kiosk scan must reach payroll like any other day',
        );

        $this->travelBack();
    }

    #[Test]
    public function a_lateness_a_scan_captures_is_charged_like_any_other(): void
    {
        $this->travelTo(now()->setTime(9, 15)); // 75 minutes past 08:00, well beyond grace
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in'])->assertOk();

        $this->travelTo(now()->setTime(12, 0));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_out'])->assertOk();

        $this->travelTo(now()->setTime(13, 0));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'pm_in'])->assertOk();

        $this->travelTo(now()->setTime(17, 0));
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'pm_out'])->assertOk();

        $log = AccreditedHoursLog::first();
        $this->assertSame(75, $log->late_minutes);
        $this->assertSame(405, $log->total_accredited_minutes); // 8h less the 75 late minutes

        $this->travelBack();
    }

    // ---------- the audit trail says "unattended" ----------

    #[Test]
    public function an_unattended_punch_records_no_operator(): void
    {
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in'])->assertOk();

        $punch = AttendancePunch::first();

        // `recorded_by` is the accountable operator. A self-service kiosk has
        // none, and NULL is both the honest value and the query an auditor runs
        // to separate unattended punches from staffed ones.
        $this->assertNull($punch->recorded_by);
        $this->assertSame('Self-service kiosk', $punch->device_label);
        $this->assertSame('qr_scan', $punch->source);
    }

    // ---------- the badge is still the credential ----------

    #[Test]
    public function a_forged_badge_writes_nothing(): void
    {
        $forged = 'PHRM1.' . $this->employee->id . '.notarealsignature';

        $this->postJson($this->punchUrl(), ['code' => $forged, 'slot' => 'am_in'])
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid']);

        $this->assertSame(0, Attendance::count());
        $this->assertSame(0, AttendancePunch::count());
    }

    #[Test]
    public function a_legacy_unsigned_card_is_refused_with_the_reissue_message(): void
    {
        // Losing the operator does not loosen anything about the badge: a bare
        // id is still exactly the forgeable thing signing was introduced for.
        $this->postJson($this->punchUrl(), ['code' => (string) $this->employee->id, 'slot' => 'am_in'])
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid']);

        $this->assertSame(0, Attendance::count());
    }

    #[Test]
    public function an_unknown_slot_is_refused_rather_than_written_somewhere(): void
    {
        // The slot arrives from the browser — now from a confirm button the
        // employee presses rather than an operator's — and is untrusted either way.
        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'accredited_hours'])
            ->assertStatus(422);

        $this->assertSame(0, Attendance::count());
    }

    #[Test]
    public function a_protected_day_is_refused_rather_than_overwritten(): void
    {
        // The punch rules did not change with the venue: an approved leave owns
        // the day, and a kiosk scan must not erase the approval's trace.
        Attendance::create([
            'employee_id' => $this->employee->id,
            'date' => now()->toDateString(),
            'attendance_type' => 'LEAVE',
        ]);

        $this->postJson($this->punchUrl(), ['code' => $this->badge, 'slot' => 'am_in'])
            ->assertStatus(409)
            ->assertJson(['status' => 'blocked']);

        $this->assertNull(Attendance::first()->am_in);
    }

    private function punchUrl(): string
    {
        return route('kiosk.attendance.punch', ['token' => $this->token]);
    }
}
