<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MobileProfileController extends Controller
{
    /**
     * Full employee profile for the authenticated mobile user.
     */
    public function show()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $employee->load([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation',
            'addresses',
            'contacts',
            'governmentIds',
        ]);

        $mobile = $employee->contacts->firstWhere('type', 'mobile');
        $emergency = $employee->contacts->firstWhere('type', 'emergency');
        $address = $employee->addresses->first();
        $gov = $employee->governmentIds->first();
        $employment = $employee->employmentDetail;

        $fullName = trim(
            $employee->first_name . ' ' .
            ($employee->middle_name ? $employee->middle_name . ' ' : '') .
            $employee->last_name .
            ($employee->suffix ? ' ' . $employee->suffix : '')
        );

        $appointmentDate = $employment?->appointment_date;
        $formattedAppointment = $appointmentDate
            ? Carbon::parse($appointmentDate)->format('Y-m-d')
            : null;

        $birthDate = $employee->birth_date
            ? Carbon::parse($employee->birth_date)->format('Y-m-d')
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'personal' => [
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'last_name' => $employee->last_name,
                    'suffix' => $employee->suffix,
                    'full_name' => $fullName,
                    'sex' => $employee->sex,
                    'birth_date' => $birthDate,
                    'civil_status' => $employee->civil_status,
                    'email' => $user->email ?? $employee->email,
                    'phone' => $mobile?->number ?? $employee->mobile_number,
                    'address' => $this->formatAddress($address),
                ],
                'employment' => [
                    'employee_id' => $employee->employee_id,
                    'employment_status' => $employment?->employment_status,
                    'designation' => $employment?->designationRelation?->title,
                    'department' => $employment?->departmentRelation?->name,
                    'appointment_date' => $formattedAppointment,
                    'salary_grade' => $employment?->salary_grade,
                    'step_increment' => $employment?->step_increment,
                    'monthly_rate' => $employment?->designationRelation?->monthly_rate !== null
                        ? (float) $employment->designationRelation->monthly_rate
                        : null,
                    'user_status' => $user->status,
                ],
                'government_ids' => [
                    'gsis_no' => $gov?->gsis_no,
                    'philhealth_no' => $gov?->philhealth_no,
                    'pagibig_no' => $gov?->pagibig_no,
                    'tin_no' => $gov?->tin_no,
                    'license_no' => $gov?->license_no,
                ],
                'emergency' => [
                    'contact_person' => $emergency?->contact_person,
                    'phone' => $emergency?->number,
                    'address' => $this->formatAddress($address),
                ],
            ],
        ]);
    }

    private function formatAddress($address): ?string
    {
        if (!$address) {
            return null;
        }

        $line1 = trim(($address->house_no ?? '') . ' ' . ($address->street ?? ''));
        $parts = array_filter([
            $line1 !== '' ? $line1 : null,
            $address->barangay,
            $address->city,
            $address->province,
            $address->zip_code,
        ]);

        $formatted = implode(', ', $parts);

        return $formatted !== '' ? $formatted : null;
    }
}
