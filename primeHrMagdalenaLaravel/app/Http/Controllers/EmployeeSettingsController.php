<?php

namespace App\Http\Controllers;

use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee record not found.');
        }

        $employee->load(['employmentDetail.designationRelation', 'employmentDetail.departmentRelation', 'contacts']);

        $fullName = trim($employee->first_name . ' ' . $employee->last_name);
        $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
        $contactNumber = optional($employee->contacts->firstWhere('type', 'mobile'))->number;
        // All employment types except Job Order get the leave-and-benefits experience
        $employmentStatus = $employee->employmentDetail?->employment_status;
        $isPermanent = $employmentStatus !== null && $employmentStatus !== 'Job Order';

        // Opt-out model: a user with no row yet has every category switched on.
        $preference = $user->notificationPreference;
        $prefs = [];
        foreach (UserNotificationPreference::EMPLOYEE_KEYS as $key) {
            $prefs[$key] = $preference?->{$key} ?? true;
        }

        return view('employee.settings.employeeSettings', [
            'employee' => $employee,
            'fullName' => $fullName,
            'initials' => $initials,
            'contactNumber' => $contactNumber,
            'isPermanent' => $isPermanent,
            'prefs' => $prefs,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No employee record is linked to this account.'], 404);
        }

        $data = $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update(['email' => $data['email']]);
        $employee->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
        ]);

        // The mobile number lives in the contacts table, not on employees.
        if ($request->filled('contact_number')) {
            $mobile = $employee->contacts->firstWhere('type', 'mobile');
            if ($mobile) {
                $mobile->update(['number' => $data['contact_number']]);
            } else {
                $employee->contacts()->create(['type' => 'mobile', 'number' => $data['contact_number']]);
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Personal information updated.',
            'fullName' => trim($data['first_name'] . ' ' . $data['last_name']),
            'initials' => strtoupper(substr($data['first_name'], 0, 1) . substr($data['last_name'], 0, 1)),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->input('new_password'))]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate(
            array_fill_keys(UserNotificationPreference::EMPLOYEE_KEYS, ['required', 'boolean'])
        );

        UserNotificationPreference::updateOrCreate(['user_id' => $user->id], $data);

        return response()->json(['success' => true, 'message' => 'Notification preferences saved.']);
    }

    public function updatePhoto(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No employee record is linked to this account.'], 404);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
        $path = $request->file('photo')->storeAs('employees/photos', $filename, 'public');
        $employee->update(['photo' => '/storage/' . $path]);

        return response()->json(['success' => true, 'message' => 'Profile photo updated.', 'photo' => $employee->photo]);
    }
}
