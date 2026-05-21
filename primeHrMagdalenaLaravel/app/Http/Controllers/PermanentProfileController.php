<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PermanentProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('permanent.dashboard')->with('error', 'Employee record not found.');
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

        return view('permanent.profile.permanentProfile', compact('employee', 'yearsOfService', 'leaveBalance', 'trainingsCompleted'));
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
            'email' => 'required|email|max:255',
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

        // Update email
        $user->update(['email' => $data['email']]);

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

        // Build full address for response
        $fullAddress = trim(($data['house_no'] ?? '') . ' ' . ($data['street'] ?? '') . ', ' . 
                            ($data['barangay'] ?? '') . ', ' . ($data['city'] ?? '') . ', ' . 
                            ($data['province'] ?? ''));

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
