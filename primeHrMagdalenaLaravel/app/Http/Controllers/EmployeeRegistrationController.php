<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Models\EmploymentDetail;
use App\Models\Address;
use App\Models\Contact;
use App\Models\GovernmentId;
use App\Models\EmployeeSupportingDocument;
use App\Notifications\EmployeeDetailsEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmployeeRegistrationController extends Controller
{
    /**
     * Which wizard panel each validated field sits on. A rejected field is
     * reported as "Step 2 · Account" rather than a bare sentence the admin
     * then has to hunt for across six collapsed panels — the wizard reopens
     * from its draft after a failed submit, so the step number is the only
     * thing that makes the message actionable.
     *
     * Keys must stay in step with the rules in store(); a field missing here
     * still reports, just without a step label.
     */
    private const FIELD_STEPS = [
        'employee_id'       => [1, 'Personal'],
        'first_name'        => [1, 'Personal'],
        'last_name'         => [1, 'Personal'],
        'photo'             => [1, 'Personal'],
        'birth_date'        => [1, 'Personal'],
        'sex'               => [1, 'Personal'],
        'civil_status'      => [1, 'Personal'],
        'username'          => [2, 'Account'],
        'user_email'        => [2, 'Account'],
        'password'          => [2, 'Account'],
        'password_confirm'  => [2, 'Account'],
        'roles'             => [2, 'Account'],
        'department'        => [3, 'Employment'],
        'designation_id'    => [3, 'Employment'],
        'employment_status' => [3, 'Employment'],
        'appointment_date'  => [3, 'Employment'],
        'gsis_file'                   => [5, 'Gov IDs'],
        'philhealth_file'             => [5, 'Gov IDs'],
        'pagibig_file'                => [5, 'Gov IDs'],
        'tin_file'                    => [5, 'Gov IDs'],
        'license_file'                => [5, 'Gov IDs'],
        'pds_file'                    => [6, 'Supporting Docs'],
        'appointment_form_file'       => [6, 'Supporting Docs'],
        'position_description_file'   => [6, 'Supporting Docs'],
        'medical_certificate_file'    => [6, 'Supporting Docs'],
        'nbi_clearance_file'          => [6, 'Supporting Docs'],
        'financial_clearance_file'    => [6, 'Supporting Docs'],
        'neuro_exam_file'             => [6, 'Supporting Docs'],
        'supporting_licenses_file'    => [6, 'Supporting Docs'],
        'performance_eval_file'       => [6, 'Supporting Docs'],
        'commendation_file'           => [6, 'Supporting Docs'],
        'disciplinary_file'           => [6, 'Supporting Docs'],
        'other_records_file'          => [6, 'Supporting Docs'],
    ];

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
                'pds_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'appointment_form_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'position_description_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'medical_certificate_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'nbi_clearance_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'financial_clearance_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'neuro_exam_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'supporting_licenses_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'performance_eval_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'commendation_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'disciplinary_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
                'other_records_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            ], [], [
                // Without these, Laravel humanises the column name and the
                // admin is told "the user email field is required" for a box
                // labelled "Email Address".
                'employee_id'       => 'employee ID',
                'user_email'        => 'email address',
                'password_confirm'  => 'password confirmation',
                'department'        => 'department',
                'designation_id'    => 'designation',
                'roles'             => 'role',
                'gsis_file'                   => 'GSIS file',
                'philhealth_file'             => 'PhilHealth file',
                'pagibig_file'                => 'Pag-IBIG file',
                'tin_file'                    => 'TIN file',
                'license_file'                => 'license file',
                'pds_file'                    => 'CS Form 212 (PDS) file',
                'appointment_form_file'       => 'CS Form 33 (Appointment Form) file',
                'position_description_file'   => 'Position Description Form file',
                'medical_certificate_file'    => 'Medical Certificate file',
                'nbi_clearance_file'          => 'NBI Clearance file',
                'financial_clearance_file'    => 'financial clearance file',
                'neuro_exam_file'             => 'Neuro-psychiatric Examination file',
                'supporting_licenses_file'    => 'Licenses file',
                'performance_eval_file'       => 'Performance Evaluation file',
                'commendation_file'           => 'Commendation / Award file',
                'disciplinary_file'           => 'Disciplinary / Action file',
                'other_records_file'          => 'other records file',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', $this->validationSummary($e->errors()))
                ->with('error_details', $this->describeValidationErrors($e->errors()));
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

            // Create User Account.
            //
            // `status` is set explicitly because `users.status` defaults to
            // 'Inactive' and `AuthController::login()` refuses an inactive
            // account. Left at the default, the employee cannot sign in — and
            // the verification link is behind `auth`, so they cannot verify
            // either. Both emails would arrive describing an account that
            // nothing in the system can open, and there is no activation
            // screen to unstick it. Bulk import already creates accounts
            // Active; the wizard now matches it, which puts the gate where
            // this feature wants it: email verification, not a manual flip.
            $employeeUser = User::create([
                'employee_id' => $employee->id,
                'email' => $request->user_email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'roles' => array_values(array_unique($request->roles)),
                'status' => 'Active',
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

            // Create Supporting Documents (12 image-only files, PNG/JPG, 5 MB)
            EmployeeSupportingDocument::create([
                'employee_id'                       => $employee->id,
                'pds_file_path'                     => $this->handleFileUpload($request->file('pds_file'), 'employees/supporting_documents'),
                'appointment_form_file_path'        => $this->handleFileUpload($request->file('appointment_form_file'), 'employees/supporting_documents'),
                'position_description_file_path'    => $this->handleFileUpload($request->file('position_description_file'), 'employees/supporting_documents'),
                'medical_certificate_file_path'     => $this->handleFileUpload($request->file('medical_certificate_file'), 'employees/supporting_documents'),
                'nbi_clearance_file_path'           => $this->handleFileUpload($request->file('nbi_clearance_file'), 'employees/supporting_documents'),
                'financial_clearance_file_path'     => $this->handleFileUpload($request->file('financial_clearance_file'), 'employees/supporting_documents'),
                'neuro_exam_file_path'              => $this->handleFileUpload($request->file('neuro_exam_file'), 'employees/supporting_documents'),
                'licenses_file_path'                => $this->handleFileUpload($request->file('supporting_licenses_file'), 'employees/supporting_documents'),
                'performance_eval_file_path'        => $this->handleFileUpload($request->file('performance_eval_file'), 'employees/supporting_documents'),
                'commendation_file_path'            => $this->handleFileUpload($request->file('commendation_file'), 'employees/supporting_documents'),
                'disciplinary_file_path'            => $this->handleFileUpload($request->file('disciplinary_file'), 'employees/supporting_documents'),
                'other_records_file_path'           => $this->handleFileUpload($request->file('other_records_file'), 'employees/supporting_documents'),
            ]);

            $employeeUserDetails = $this->credentialsFor(
                $employee,
                $employeeUser,
                $request->password,
                $request->roles
            );

            DB::commit();

            // Both emails go out only once the account is durably committed.
            // Sent from inside the transaction, a later failure would roll the
            // user row back while the credentials were already in somebody's
            // inbox — an email cannot be recalled.
            //
            // Caught separately from the block below: past this point the
            // employee exists, so a mail failure is a warning, not a failed
            // registration. Reporting it as one would send the admin back to
            // re-submit a form that can now only fail on a duplicate ID.
            try {
                event(new Registered($employeeUser));
                $employeeUser->notify(new EmployeeDetailsEmail($employeeUserDetails));
            } catch (\Exception $e) {
                // The admin sees this on screen, but only until they navigate
                // away. Mail failures are exactly what someone comes asking
                // about days later ("nobody got their login"), so the reason
                // has to outlive the flash message.
                Log::error('Employee registered but account email failed', [
                    'user_id' => $employeeUser->id,
                    'employee_id' => $employee->id,
                    'email' => $employeeUser->email,
                    'exception' => $e,
                ]);

                return redirect()->route('admin.personnel')
                    ->with('warning', "Employee {$employee->first_name} {$employee->last_name} was registered.")
                    ->with('email_notice', [
                        'status' => 'failed',
                        'email'  => $employeeUser->email,
                        'reason' => $e->getMessage(),
                    ]);
            }

            // What went out is reported separately from *that* it worked. The
            // admin's next action depends on it: the employee cannot sign in
            // until they open the verification link, so an admin who does not
            // know a link was sent has no reason to tell them to look for it —
            // and the calls that follow ("it says my account isn't verified")
            // land on somebody with no idea what the employee is describing.
            //
            // Structured rather than a sentence because the modal renders the
            // address on its own line: it was typed by the admin one screen
            // ago, and reading it back is the only chance to catch a typo
            // before the employee is waiting on mail to nowhere.
            return redirect()->route('admin.personnel')
                ->with('success', "Employee {$employee->first_name} {$employee->last_name} registered successfully!")
                ->with('email_notice', [
                    'status' => 'sent',
                    'email'  => $employeeUser->email,
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Without this the rollback is invisible: the transaction undoes
            // every row, the admin gets a flash message they can dismiss, and
            // nothing anywhere records why the registration failed.
            Log::error('Employee registration failed and was rolled back', [
                'employee_id' => $request->employee_id,
                'email' => $request->user_email,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error registering employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Flatten a validator's errors into one ordered list, each tagged with the
     * wizard step that owns the field.
     *
     * Every message is kept. The old handler flashed
     * `collect($e->errors())->flatten()->first()` — one sentence — so an admin
     * with a taken username *and* a taken email fixed the username, resubmitted,
     * and only then learned about the email. Six steps of re-entry per round trip.
     */
    /**
     * The rows of the credentials email, keyed by the label the employee reads.
     *
     * Built here rather than inline at each call site so the wizard and the
     * bulk import cannot describe the same account differently. Three things
     * this fixes:
     *
     * - it sent `employees.id` under the label "Employee id". That is the
     *   internal primary key; the number on the employee's badge — the one
     *   they are asked for everywhere else in this system — is
     *   `employees.employee_id`.
     * - it sent `$employeeUser->status`, which is **null**: `User::create()`
     *   never set the attribute, the value came from the column default, and
     *   the in-memory model was not refreshed. Every credentials email carried
     *   a blank row. Status is not the employee's business anyway, so it is
     *   gone rather than corrected.
     * - keys were run through `ucfirst()` in the view, so the labels read
     *   "Employee_id" and "Roles". They are written out here instead.
     *
     * @param  array<int, string>  $roles
     * @return array<string, string>
     */
    private function credentialsFor(Employee $employee, User $user, string $password, array $roles): array
    {
        $name = trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
            $employee->suffix,
        ])));

        return [
            'Employee ID' => (string) $employee->employee_id,
            'Name'        => $name,
            'Username'    => (string) $user->username,
            'Email'       => (string) $user->email,
            'Password'    => $password,
            'Role'        => implode(', ', array_map(
                fn ($role) => ucfirst((string) $role),
                array_values(array_unique($roles))
            )),
        ];
    }

    private function describeValidationErrors(array $errors): array
    {
        $details = [];

        foreach ($errors as $field => $messages) {
            // `roles.*` arrives keyed as `roles.0`; the step map holds the base.
            $base = explode('.', $field)[0];
            [$step, $stepName] = self::FIELD_STEPS[$base] ?? [null, null];

            foreach ((array) $messages as $message) {
                $details[] = [
                    'field' => $base,
                    'step' => $step,
                    'step_name' => $stepName,
                    'message' => $message,
                ];
            }
        }

        // Present them in the order the admin filled the form, so the list
        // reads as a walk back through the wizard. Unmapped fields sort last
        // rather than silently leading.
        usort($details, fn ($a, $b) => ($a['step'] ?? PHP_INT_MAX) <=> ($b['step'] ?? PHP_INT_MAX));

        return $details;
    }

    /**
     * The headline above the list. Kept separate from the detail list because
     * `error` is also set by non-validation failures, which have no fields.
     */
    private function validationSummary(array $errors): string
    {
        $count = collect($errors)->flatten()->count();

        return $count === 1
            ? 'One field needs attention before this employee can be registered.'
            : "{$count} fields need attention before this employee can be registered.";
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

    /**
     * Derive a unique departments.code from a department name, because the
     * columns table does not accept a NULL code when a new department is
     * created during bulk import.
     */
    private function generateDepartmentCode(string $name): string
    {
        $slug = Str::upper(Str::slug($name));
        $slug = Str::limit($slug, 20, '');

        if ($slug === '') {
            $slug = 'DEPT';
        }

        $code = $slug;
        $n = 1;
        while (\App\Models\Department::where('code', $code)->exists()) {
            $n++;
            $code = $slug . $n;
        }

        return $code;
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

            // Accounts awaiting their verification + credentials emails. The
            // per-row DB::transaction() below is only a savepoint inside the
            // outer transaction, so a row "succeeding" is not yet durable —
            // nothing is mailed until the outer commit lands.
            $pendingAccounts = [];

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                if (count($row) !== count($headers)) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": Column count mismatch";
                    continue;
                }

                // Blank cells parse as '' — treat them as null so `?? default`
                // fallbacks below actually fire and DATE/NOT NULL columns are
                // not handed an empty string.
                $data = array_map(
                    fn ($value) => $value === '' ? null : $value,
                    array_combine($headers, $row)
                );

                // Check if employee ID already exists
                if (Employee::where('employee_id', $data['employee_id'])->exists()) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": Employee ID {$data['employee_id']} already exists";
                    continue;
                }

                try {
                    // One row per savepoint, so a failure in the middle of a row
                    // rolls the whole row back instead of leaving a half-created
                    // employee behind and reporting it as skipped anyway.
                    DB::transaction(function () use ($data, &$pendingAccounts) {
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
                        $employeeUser = User::create([
                            'employee_id' => $employee->id,
                            'email' => $data['email'] ?? $data['employee_id'] . '@lgu.gov.ph',
                            'username' => $this->generateUsername(
                                $data['first_name'] ?? '',
                                $data['last_name'] ?? ''
                            ),
                            'password' => $data['password'] ?? Hash::make('password123'),
                            'roles' => ['employee'],
                            'status' => 'Active',
                        ]);

                        // Find or create department. Creating one needs its
                        // NOT NULL columns (code, head), not just name + status.
                        $department = \App\Models\Department::firstOrCreate(
                            ['name' => $data['department']],
                            [
                                'code' => $this->generateDepartmentCode($data['department'] ?? ''),
                                'head' => $data['department'] ?? '',
                                'status' => 'Active',
                            ]
                        );

                        // Find or create designation. Creating one needs the
                        // NOT NULL department_id; `status` is not a column here.
                        $designation = \App\Models\Designation::firstOrCreate(
                            ['title' => $data['designation']],
                            ['department_id' => $department->id]
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

                        // Create Contacts
                        if (!empty($data['mobile_number'])) {
                            Contact::create([
                                'employee_id' => $employee->id,
                                'type' => 'mobile',
                                'number' => $data['mobile_number'],
                            ]);
                        }

                        if (!empty($data['landline_number'])) {
                            Contact::create([
                                'employee_id' => $employee->id,
                                'type' => 'landline',
                                'number' => $data['landline_number'],
                            ]);
                        }

                        // Create Government IDs
                        GovernmentId::create([
                            'employee_id' => $employee->id,
                            'gsis_no' => $data['gsis_no'] ?? null,
                            'philhealth_no' => $data['philhealth_no'] ?? null,
                            'pagibig_no' => $data['pagibig_no'] ?? null,
                            'tin_no' => $data['tin_no'] ?? null,
                            'license_no' => $data['license_no'] ?? null,
                        ]);

                        $pendingAccounts[] = [
                            'user' => $employeeUser,
                            'details' => $this->credentialsFor(
                                $employee,
                                $employeeUser,
                                $data['password'] ?? 'password123',
                                ['employee']
                            ),
                        ];
                    });

                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            // Now that every imported row is durable, mail the accounts. A
            // send that fails must not cost the import: the employees exist
            // and an admin can resend, whereas throwing here would report a
            // committed import as failed.
            foreach ($pendingAccounts as $account) {
                try {
                    event(new Registered($account['user']));
                    $account['user']->notify(new EmployeeDetailsEmail($account['details']));
                } catch (\Exception $e) {
                    $errors[] = "Account {$account['details']['email']}: created, but the "
                        . "credentials email could not be sent ({$e->getMessage()})";
                }
            }

            $message = "Successfully imported {$imported} employee(s).";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} row(s).";
            }

            // Reported outside the `$skipped` branch: a row can import
            // cleanly and still fail to mail, and that warning would
            // otherwise never reach the admin who ran the import.
            foreach ($errors as $error) {
                $message .= " {$error}";
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

            Log::error('Bulk employee import failed and was rolled back', [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error importing employees: ' . $e->getMessage()
            ], 500);
        }
    }
}
