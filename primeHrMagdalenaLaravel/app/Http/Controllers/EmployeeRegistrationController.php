<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\EmploymentDetail;
use App\Models\Address;
use App\Models\Contact;
use App\Models\GovernmentId;
use App\Models\LegalRequirement;
use App\Models\Education;
use App\Models\Eligibility;
use App\Models\WorkExperience;
use App\Models\Training;
use App\Models\FamilyMember;
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
                'email' => $request->email,
            ]);

            // Create User Account
            $user = User::create([
                'employee_id' => $employee->id,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->user_email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Create Employment Details
            EmploymentDetail::create([
                'employee_id' => $employee->id,
                'position' => $request->position,
                'department_id' => $request->department,
                'employment_status' => $request->employment_status,
                'appointment_date' => $request->appointment_date,
                'salary_grade' => $request->salary_grade,
                'step_increment' => $request->step_increment,
                'account_status' => $request->account_status,
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

            // Create Legal Requirements
            LegalRequirement::create([
                'employee_id' => $employee->id,
                'saln_submitted' => $request->has('saln_submitted') ? 1 : 0,
                'oath_of_office' => $request->has('oath_of_office') ? 1 : 0,
                'assumption_date' => $request->assumption_date,
            ]);

            // Create Education Records
            if ($request->education_level) {
                foreach ($request->education_level as $key => $level) {
                    Education::create([
                        'employee_id' => $employee->id,
                        'level' => $level,
                        'school_name' => $request->education_school[$key] ?? null,
                        'degree' => $request->education_degree[$key] ?? null,
                        'year_graduated' => $request->education_year[$key] ?? null,
                        'honors' => $request->education_honors[$key] ?? null,
                    ]);
                }
            }

            // Create Eligibility Records
            if ($request->eligibility_type) {
                foreach ($request->eligibility_type as $key => $type) {
                    Eligibility::create([
                        'employee_id' => $employee->id,
                        'type' => $type,
                        'rating' => $request->eligibility_rating[$key] ?? null,
                        'exam_date' => $request->eligibility_exam_date[$key] ?? null,
                        'exam_place' => $request->eligibility_exam_place[$key] ?? null,
                        'license_no' => $request->eligibility_license_no[$key] ?? null,
                        'validity_date' => $request->eligibility_validity_date[$key] ?? null,
                    ]);
                }
            }

            // Create Work Experience Records
            if ($request->work_company) {
                foreach ($request->work_company as $key => $company) {
                    WorkExperience::create([
                        'employee_id' => $employee->id,
                        'company_name' => $company,
                        'position' => $request->work_position[$key] ?? null,
                        'from_date' => $request->work_from_date[$key] ?? null,
                        'to_date' => $request->work_to_date[$key] ?? null,
                        'salary' => $request->work_salary[$key] ?? null,
                    ]);
                }
            }

            // Create Training Records
            if ($request->training_title) {
                foreach ($request->training_title as $key => $title) {
                    Training::create([
                        'employee_id' => $employee->id,
                        'title' => $title,
                        'conducted_by' => $request->training_conducted[$key] ?? null,
                        'date_from' => $request->training_from[$key] ?? null,
                        'date_to' => $request->training_to[$key] ?? null,
                        'hours' => $request->training_hours[$key] ?? null,
                    ]);
                }
            }

            // Create Family Member Records
            if ($request->family_name) {
                foreach ($request->family_name as $key => $name) {
                    FamilyMember::create([
                        'employee_id' => $employee->id,
                        'name' => $name,
                        'relationship' => $request->family_relationship[$key] ?? null,
                        'birthdate' => $request->family_birthdate[$key] ?? null,
                        'occupation' => $request->family_occupation[$key] ?? null,
                    ]);
                }
            }

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
