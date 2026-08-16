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
use Illuminate\Support\Str;

class EmployeeRegistrationController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => ['required', 'string', 'max:255', 'unique:employees,employee_id'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'photo' => ['nullable', 'image', 'max:5120'],
                'birth_date' => ['required', 'date'],
                'sex' => ['required', 'in:Male,Female'],
                'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                'user_email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'password_confirm' => ['required', 'same:password'],
                'roles' => ['required', 'array', 'min:1'],
                'roles.*' => ['in:' . implode(',', User::ROLES)],
                'department' => ['required', 'exists:departments,id'],
                'designation_id' => ['required', 'exists:designations,id'],
                'employment_status' => ['required', 'in:Permanent,Temporary,Coterminous,Casual,Contractual,Job Order'],
                'appointment_date' => ['required', 'date'],
                'gsis_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'philhealth_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'pagibig_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'tin_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'license_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first())->withInput();
        }

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
                'roles' => array_values(array_unique($request->roles)),
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
                'gsis_file_path' => $this->handleFileUpload($request->file('gsis_file'), 'employees/government_ids'),
                'philhealth_no' => $request->philhealth_no,
                'philhealth_file_path' => $this->handleFileUpload($request->file('philhealth_file'), 'employees/government_ids'),
                'pagibig_no' => $request->pagibig_no,
                'pagibig_file_path' => $this->handleFileUpload($request->file('pagibig_file'), 'employees/government_ids'),
                'tin_no' => $request->tin_no,
                'tin_file_path' => $this->handleFileUpload($request->file('tin_file'), 'employees/government_ids'),
                'license_no' => $request->license_no,
                'license_file_path' => $this->handleFileUpload($request->file('license_file'), 'employees/government_ids'),
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

    /**
     * Derive a username from first + last name, matching the wizard's JS
     * usernameSlug() logic: lowercase, strip diacritics, remove non-alphanumerics.
     * Appends an incrementing suffix if the base is already taken.
     */
    private function generateUsername(string $firstName, string $lastName): string
    {
        $slug = function (string $value): string {
            return Str::lower(
                preg_replace('/[^a-z0-9]/i', '', Str::ascii($value))
            );
        };

        $base = $slug($lastName) . $slug($firstName);

        if (!$base) {
            $base = 'user';
        }

        $username = $base;
        $n = 1;
        while (User::where('username', $username)->exists()) {
            $n++;
            $username = $base . $n;
        }

        return $username;
    }

    private function handleFileUpload($file, string $folder = 'employees/photos')
    {
        if (!$file) {
            return null;
        }

        try {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs($folder, $filename, 'public');
            return '/storage/' . $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function bulkImport(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $headers = array_shift($csvData);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                try {
                    if (count($row) !== count($headers)) {
                        $skipped++;
                        $errors[] = "Row " . ($index + 2) . ": Column count mismatch";
                        continue;
                    }

                    $data = array_combine($headers, $row);

                    // Check if employee ID already exists
                    if (Employee::where('employee_id', $data['employee_id'])->exists()) {
                        $skipped++;
                        $errors[] = "Row " . ($index + 2) . ": Employee ID {$data['employee_id']} already exists";
                        continue;
                    }

                    // Create Employee
                    $employee = Employee::create([
                        'employee_id' => $data['employee_id'],
                        'first_name' => $data['first_name'],
                        'middle_name' => $data['middle_name'] ?? null,
                        'last_name' => $data['last_name'],
                        'suffix' => $data['suffix'] ?? null,
                        'birth_date' => $data['birth_date'] ?? null,
                        'place_of_birth' => $data['place_of_birth'] ?? null,
                        'sex' => $data['sex'] ?? null,
                        'civil_status' => $data['civil_status'] ?? null,
                        'blood_type' => $data['blood_type'] ?? null,
                        'citizenship' => $data['citizenship'] ?? 'Filipino',
                        'email' => $data['email'] ?? null,
                    ]);

                    // Create User Account — derive username from name,
                    // matching the wizard's auto-fill: last name + first name.
                    $baseUsername = $this->generateUsername(
                        $data['first_name'] ?? '',
                        $data['last_name'] ?? ''
                    );

                    User::create([
                        'employee_id' => $employee->id,
                        'email' => $data['email'] ?? $data['employee_id'] . '@lgu.gov.ph',
                        'username' => $baseUsername,
                        'password' => Hash::make('password123'),
                        'roles' => ['employee'],
                        'status' => 'Active',
                    ]);

                    // Find or create department
                    $department = \App\Models\Department::firstOrCreate(
                        ['name' => $data['department']],
                        ['status' => 'Active']
                    );

                    // Find or create designation
                    $designation = \App\Models\Designation::firstOrCreate(
                        ['title' => $data['designation']],
                        ['status' => 'Active']
                    );

                    // Create Employment Details
                    EmploymentDetail::create([
                        'employee_id' => $employee->id,
                        'designation_id' => $designation->id,
                        'department_id' => $department->id,
                        'employment_status' => $data['employment_status'] ?? 'Permanent',
                        'appointment_date' => $data['appointment_date'] ?? now(),
                        'salary_grade' => $data['salary_grade'] ?? null,
                        'step_increment' => $data['step_increment'] ?? null,
                    ]);

                    // Create Address
                    Address::create([
                        'employee_id' => $employee->id,
                        'type' => 'residential',
                        'house_no' => $data['house_no'] ?? null,
                        'street' => $data['street'] ?? null,
                        'barangay' => $data['barangay'] ?? null,
                        'city' => $data['city'] ?? null,
                        'province' => $data['province'] ?? null,
                        'zip_code' => $data['zip_code'] ?? null,
                    ]);

                    // Create Contact
                    Contact::create([
                        'employee_id' => $employee->id,
                        'mobile_number' => $data['mobile_number'] ?? null,
                        'landline_number' => $data['landline_number'] ?? null,
                    ]);

                    // Create Government IDs
                    GovernmentId::create([
                        'employee_id' => $employee->id,
                        'gsis_no' => $data['gsis_no'] ?? null,
                        'philhealth_no' => $data['philhealth_no'] ?? null,
                        'pagibig_no' => $data['pagibig_no'] ?? null,
                        'tin_no' => $data['tin_no'] ?? null,
                        'license_no' => $data['license_no'] ?? null,
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully imported {$imported} employee(s).";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} row(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error importing employees: ' . $e->getMessage()
            ], 500);
        }
    }
}
