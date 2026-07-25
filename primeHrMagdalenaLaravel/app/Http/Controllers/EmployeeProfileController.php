<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee record not found.');
        }

        $employee->load(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'addresses', 'contacts', 'governmentIds']);

        $yearsOfService = $employee->employmentDetail && $employee->employmentDetail->appointment_date
            ? Carbon::parse($employee->employmentDetail->appointment_date)->diffInYears(Carbon::now(), true)
            : 0;

        $leaveBalance = \App\Models\LeaveBalance::where('employee_id', $employee->id)
            ->where('year', Carbon::now()->year)
            ->sum('available_credits');

        $trainingsCompleted = \App\Models\Training::where('employee_id', $employee->id)
            ->where('status', 'verified')
            ->count();

        // Attendance rate for the current year. Replaces a hardcoded "4.9"
        // performance rating — this schema has no performance/evaluation table,
        // so there was nothing behind that figure.
        $year = Carbon::now()->year;
        $byType = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->selectRaw('attendance_type, COUNT(*) AS total')
            ->groupBy('attendance_type')
            ->pluck('total', 'attendance_type');

        $daysPresent = (int) ($byType['REGULAR'] ?? 0);
        $daysAbsent  = (int) ($byType['ABSENT'] ?? 0);
        $daysLogged  = $daysPresent + $daysAbsent;
        $attendanceRate = $daysLogged > 0 ? round($daysPresent / $daysLogged * 100, 1) : null;

        return view('employee.profile.employeeProfile', compact(
            'employee', 'yearsOfService', 'leaveBalance', 'trainingsCompleted',
            'attendanceRate', 'daysPresent', 'daysAbsent', 'year'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
        }

        $data = $request->validate([
            'contact_number' => 'required|string|max:20',
            // Without the unique rule an employee could save an address already
            // held by another account and hit the users.email constraint as a 500.
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'house_no' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:10',
            'emergency_contact_person' => 'required|string|max:255',
            'emergency_phone' => 'required|string|max:20',
        ]);

        // Update mobile contact
        $mobile = $employee->contacts->firstWhere('type', 'mobile');
        if ($mobile) {
            $mobile->update(['number' => $data['contact_number']]);
        } else {
            $employee->contacts()->create([
                'type' => 'mobile',
                'number' => $data['contact_number']
            ]);
        }

        // Update email on both records — employees.email is what HR reports read,
        // users.email is what the account signs in with. Settings keeps these in
        // step too; leaving one behind makes them silently diverge.
        $user->update(['email' => $data['email']]);
        $employee->update(['email' => $data['email']]);

        // Update address
        $address = $employee->addresses->first();
        if ($address) {
            $address->update([
                'house_no' => $data['house_no'],
                'street' => $data['street'],
                'barangay' => $data['barangay'],
                'city' => $data['city'],
                'province' => $data['province'],
                'zip_code' => $data['zip_code']
            ]);
        } else {
            $employee->addresses()->create([
                'type' => 'residential',
                'house_no' => $data['house_no'],
                'street' => $data['street'],
                'barangay' => $data['barangay'],
                'city' => $data['city'],
                'province' => $data['province'],
                'zip_code' => $data['zip_code']
            ]);
        }

        // Update emergency contact
        $emergency = $employee->contacts->firstWhere('type', 'emergency');
        if ($emergency) {
            $emergency->update([
                'contact_person' => $data['emergency_contact_person'],
                'number' => $data['emergency_phone']
            ]);
        } else {
            $employee->contacts()->create([
                'type' => 'emergency',
                'contact_person' => $data['emergency_contact_person'],
                'number' => $data['emergency_phone']
            ]);
        }

        // Build the display address. Joining only the parts that were filled in
        // avoids the ", , ," that a blank barangay/city used to leave behind.
        $line = trim(($data['house_no'] ?? '') . ' ' . ($data['street'] ?? ''));
        $fullAddress = collect([$line, $data['barangay'] ?? null, $data['city'] ?? null, $data['province'] ?? null])
            ->filter(fn ($part) => filled(trim((string) $part)))
            ->implode(', ');

        if (filled($data['zip_code'] ?? null)) {
            $fullAddress = trim($fullAddress . ' ' . $data['zip_code']);
        }
        $fullAddress = $fullAddress !== '' ? $fullAddress : 'N/A';

        return response()->json([
            'success' => true, 
            'message' => 'Profile updated successfully.',
            'data' => [
                'contact_number' => $data['contact_number'],
                'email' => $data['email'],
                'address' => $fullAddress,
                'emergency_contact_person' => $data['emergency_contact_person'],
                'emergency_phone' => $data['emergency_phone']
            ]
        ]);
    }
}
