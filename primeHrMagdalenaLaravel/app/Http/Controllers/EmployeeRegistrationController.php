<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\EmploymentDetail;
use App\Models\Address;
use App\Models\Contact;
use App\Models\GovernmentId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeRegistrationController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Create Employee
            $employee = Employee::create([
                'employee_id' => $request->employee_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'photo' => $this->handleFileUpload($request->file('photo')),
                'birth_date' => $request->birth_date,
                'place_of_birth' => $request->place_of_birth,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'height' => $request->height,
                'weight' => $request->weight,
                'blood_type' => $request->blood_type,
                'citizenship' => $request->citizenship,
                'email' => $request->user_email,
            ]);

            // Create User Account
            User::create([
                'employee_id' => $employee->id,
                'email' => $request->user_email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Create Employment Details
            EmploymentDetail::create([
                'employee_id'       => $employee->id,
                'designation_id'    => $request->designation_id,
                'department_id'     => $request->department,
                'employment_status' => $request->employment_status,
                'appointment_date'  => $request->appointment_date,
                'salary_grade'      => $request->salary_grade,
                'step_increment'    => $request->step_increment,
            ]);

            // Create Residential Address
            Address::create([
                'employee_id' => $employee->id,
                'type' => 'residential',
                'house_no' => $request->house_no,
                'street' => $request->street,
                'barangay' => $request->barangay,
                'city' => $request->city,
                'province' => $request->province,
                'zip_code' => $request->zip_code,
            ]);

            // Create Contacts
            if ($request->mobile_number) {
                Contact::create([
                    'employee_id' => $employee->id,
                    'type' => 'mobile',
                    'number' => $request->mobile_number,
                ]);
            }

            if ($request->landline_number) {
                Contact::create([
                    'employee_id' => $employee->id,
                    'type' => 'landline',
                    'number' => $request->landline_number,
                ]);
            }

            if ($request->emergency_contact_number) {
                Contact::create([
                    'employee_id' => $employee->id,
                    'type' => 'emergency',
                    'contact_person' => $request->emergency_contact_person,
                    'number' => $request->emergency_contact_number,
                ]);
            }

            // Create Government IDs
            GovernmentId::create([
                'employee_id' => $employee->id,
                'gsis_no' => $request->gsis_no,
                'philhealth_no' => $request->philhealth_no,
                'pagibig_no' => $request->pagibig_no,
                'tin_no' => $request->tin_no,
                'license_no' => $request->license_no,
            ]);

            DB::commit();

            return redirect()->route('admin.personnel')
                ->with('success', "Employee {$employee->first_name} {$employee->last_name} registered successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error registering employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function handleFileUpload($file)
    {
        if (!$file) {
            return null;
        }

        try {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('employees/photos', $filename, 'public');
            return '/storage/' . $path;
        } catch (\Exception $e) {
            return null;
        }
    }
}
