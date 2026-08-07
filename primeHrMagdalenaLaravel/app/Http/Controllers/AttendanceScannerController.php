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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * The attendance kiosk: a staffed terminal that reads employee QR badges.
 *
 * It stands in for the biometric reader the municipality has not bought yet.
 * Everything it does goes through AttendancePunchService, so when the reader
 * arrives the device changes and the attendance pipeline does not.
 *
 * Access is inherited from the `/admin` prefix, which EnsureRoleForArea already
 * restricts to `admin` and `hr` — the kiosk is operated by staff, not by the
 * employee being scanned.
 */
class AttendanceScannerController extends Controller
{
    public function __construct(
        private readonly AttendanceQrService $qr,
        private readonly AttendancePunchService $punches,
    ) {
    }

    /** The kiosk screen. */
    public function index()
    {
        return view('admin.attendance.scanner', [
            'recent' => $this->recentPunches(),
            'slots' => AttendancePunch::SLOTS,
        ]);
    }

    /**
     * Record a scan.
     *
     * The operator picks the slot; the badge only says who. Both are validated
     * server-side — a slot arriving from the browser is untrusted input even
     * though the kiosk's own buttons are the only intended source.
     */
    public function punch(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'slot' => 'required|string|in:' . implode(',', AttendancePunch::SLOTS),
            'device_label' => 'nullable|string|max:100',
        ]);

        try {
            $employee = $this->qr->resolveEmployee($validated['code']);
        } catch (InvalidAttendanceQrException $e) {
            // Logged so a run of rejected scans during a demo is diagnosable,
            // without recording the scanned string itself — a rejected code is
            // still someone's badge data.
            Log::warning('Attendance scan rejected', [
                'operator_id' => Auth::id(),
                'reason' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'invalid',
                'message' => $e->getMessage(),
            ], 422);
        }

        $result = $this->punches->punch(
            employee: $employee,
            slot: $validated['slot'],
            at: Carbon::now(),
            source: 'qr_scan',
            recordedBy: Auth::id(),
            deviceLabel: $validated['device_label'] ?? null,
        );

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'employee' => $this->employeeCard($employee),
            'slot' => $result['slot'],
            'slot_label' => AttendancePunch::slotLabel($result['slot']),
            'time' => $result['time'],
            'day' => $this->dayStrip($result['attendance']),
            'recent' => $this->recentPunches(),
        ], $result['status'] === 'blocked' ? 409 : 200);
    }

    /**
     * Which slot the kiosk should pre-select for a scanned badge, so the
     * operator confirms rather than chooses from scratch. Read-only: it
     * records nothing.
     */
    public function suggest(Request $request)
    {
        $validated = $request->validate(['code' => 'required|string|max:255']);

        try {
            $employee = $this->qr->resolveEmployee($validated['code']);
        } catch (InvalidAttendanceQrException $e) {
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

    /** The live feed beside the camera. */
    public function recent()
    {
        return response()->json(['recent' => $this->recentPunches()]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentPunches(int $limit = 12): array
    {
        return AttendancePunch::with('employee')
            ->whereDate('date', now()->toDateString())
            ->latest('punched_at')
            ->limit($limit)
            ->get()
            ->map(fn(AttendancePunch $punch) => [
                'id' => $punch->id,
                'name' => $this->fullName($punch->employee),
                'photo' => $punch->employee?->photo,
                'slot_label' => AttendancePunch::slotLabel($punch->slot),
                'time' => $punch->punched_at->format('g:i A'),
                'source' => $punch->source,
            ])
            ->all();
    }

    /**
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
     * Today's four slots as the DTR will show them — the operator's proof the
     * scan landed where they meant it to.
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
        if (!$employee) {
            return 'Unknown employee';
        }

        return trim(collect([$employee->first_name, $employee->middle_name, $employee->last_name, $employee->suffix])
            ->filter()
            ->implode(' '));
    }
}
