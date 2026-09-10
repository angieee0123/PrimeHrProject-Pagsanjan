<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidAttendanceQrException;
use App\Models\Attendance;
use App\Models\AttendancePunch;
use App\Models\Employee;
use App\Services\AttendancePunchService;
use App\Services\AttendanceQrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The attendance kiosk: a public terminal an employee operates themselves.
 *
 * It replaced a staffed scanner that lived at `/admin/attendance/scanner`,
 * where `EnsureRoleForArea` confined it to `admin` and `hr`. That was the right
 * shape while the terminal stood at the HR desk with an operator driving it,
 * and the wrong one for a tablet in the lobby: employees cannot sign in to
 * reach it, and should not be given an admin area to do so.
 *
 * Two things follow from removing the operator, and both are decisions rather
 * than details:
 *
 * 1. **The server picks the slot.** With an operator, a badge was not allowed
 *    to move the slot buttons between aiming and scanning — the human chose.
 *    With no operator there is nobody to choose, so
 *    {@see AttendancePunchService::suggestSlot()} becomes the authority, which
 *    is exactly the handover the service's own docblock anticipated. The
 *    employee confirms the slot the system inferred rather than selecting from
 *    six buttons, and an override is available for the cases inference gets
 *    wrong.
 * 2. **The roster feed is gone.** The staffed page showed a live list of
 *    everyone who had punched, names and photos included. That is fine on an
 *    HR desk and unacceptable on a screen anyone can walk up to and on a URL
 *    that needs no login, so the kiosk shows the scanned employee their own
 *    result and nothing about anybody else.
 *
 * A punch here records `recorded_by = null`. The column's own migration calls
 * it "the kiosk operator, not the employee", and there is no longer an operator
 * — so NULL is the honest value, and it is also the audit query that separates
 * unattended punches from staffed ones.
 */
class AttendanceKioskController extends Controller
{
    /**
     * What a punch captured with no human operator is labelled in the log. A
     * constant because the label is read back when auditing unattended punches.
     */
    private const DEVICE_LABEL = 'Self-service kiosk';

    public function __construct(
        private readonly AttendanceQrService $qr,
        private readonly AttendancePunchService $punches,
    ) {
    }

    /** The kiosk screen. Carries no employee data at all. */
    public function show(Request $request)
    {
        $token = (string) $request->route('token');

        return view('kiosk.attendance', [
            'peekUrl' => route('kiosk.attendance.peek', ['token' => $token]),
            'punchUrl' => route('kiosk.attendance.punch', ['token' => $token]),
            'slots' => AttendancePunch::SLOTS,
        ]);
    }

    /**
     * Identify a badge and say which punch it looks like, without recording
     * anything. This is the confirm step: the employee sees who the system
     * thinks they are and which slot it inferred before committing.
     */
    public function peek(Request $request)
    {
        $validated = $request->validate(['code' => 'required|string|max:255']);

        try {
            $employee = $this->qr->resolveEmployee($validated['code']);
        } catch (InvalidAttendanceQrException $e) {
            $this->logRejection($e);

            return response()->json(['status' => 'invalid', 'message' => $e->getMessage()], 422);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        return response()->json([
            'status' => 'ok',
            'employee' => $this->employeeCard($employee),
            'suggested_slot' => $this->punches->suggestSlot($employee),
            'day' => $this->dayStrip($attendance),
        ]);
    }

    /**
     * Record the punch the employee confirmed.
     *
     * The slot arrives from the browser and is therefore untrusted even though
     * the kiosk's own confirm button is the only intended source — the same
     * rule the staffed page followed. It is validated against the model's slot
     * list, never passed through.
     */
    public function punch(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'slot' => 'required|string|in:' . implode(',', AttendancePunch::SLOTS),
        ]);

        try {
            $employee = $this->qr->resolveEmployee($validated['code']);
        } catch (InvalidAttendanceQrException $e) {
            $this->logRejection($e);

            return response()->json(['status' => 'invalid', 'message' => $e->getMessage()], 422);
        }

        $result = $this->punches->punch(
            employee: $employee,
            slot: $validated['slot'],
            at: Carbon::now(),
            source: 'qr_scan',
            // No operator exists at a self-service kiosk. NULL is what marks
            // this punch as unattended.
            recordedBy: null,
            deviceLabel: self::DEVICE_LABEL,
        );

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'employee' => $this->employeeCard($employee),
            'slot' => $result['slot'],
            'slot_label' => AttendancePunch::slotLabel($result['slot']),
            'time' => $result['time'],
            'day' => $this->dayStrip($result['attendance']),
        ], $result['status'] === 'blocked' ? 409 : 200);
    }

    /**
     * A rejected scan is logged so a run of them at a lobby kiosk is
     * diagnosable — without recording the scanned string, which is still
     * somebody's badge data even when it fails to verify.
     */
    private function logRejection(InvalidAttendanceQrException $e): void
    {
        Log::warning('Kiosk scan rejected', [
            'kiosk' => 'self-service',
            'reason' => $e->getMessage(),
        ]);
    }

    /**
     * The scanned employee's own details — the badge they presented is the
     * authority for showing them these, and for showing them nothing else.
     *
     * @return array<string, mixed>
     */
    private function employeeCard(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'name' => $this->fullName($employee),
            'photo' => $employee->photo,
            'department' => $employee->employmentDetail?->departmentRelation?->name,
            'designation' => $employee->employmentDetail?->designationRelation?->title,
        ];
    }

    /**
     * Today's slots as the DTR will show them, so the employee can see the
     * punch landed where they meant it to before walking away.
     *
     * @return array<string, ?string>
     */
    private function dayStrip(?Attendance $attendance): array
    {
        $strip = [];

        foreach (AttendancePunch::SLOTS as $slot) {
            $strip[$slot] = $attendance?->{$slot} ? substr($attendance->{$slot}, 0, 5) : null;
        }

        return $strip;
    }

    private function fullName(?Employee $employee): string
    {
        if (! $employee) {
            return 'Unknown employee';
        }

        return trim(collect([$employee->first_name, $employee->middle_name, $employee->last_name, $employee->suffix])
            ->filter()
            ->implode(' '));
    }
}
