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
use App\Services\TemporaryPasswordService;
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
        'first_name'        => [1, 'Personal'],
        'last_name'         => [1, 'Personal'],
        'photo'             => [1, 'Personal'],
        'birth_date'        => [1, 'Personal'],
        'sex'               => [1, 'Personal'],
        'civil_status'      => [1, 'Personal'],
        'username'          => [2, 'Account'],
        'user_email'        => [2, 'Account'],
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
                // There is deliberately no `employee_id` rule: the wizard no
                // longer has the field, and the number is minted by the model
                // (Employee::booted() → generateEmployeeId()) as
                // EMP-<year>-<sequence>. Accepting one from the request would
                // let a submitted value — or a stale draft — override it.
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'photo' => ['nullable', 'image', 'max:5120'],
                'birth_date' => ['required', 'date'],
                'sex' => ['required', 'in:Male,Female'],
                'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                'user_email' => ['required', 'email', 'max:255', 'unique:users,email'],
                // There is deliberately no `password` rule: the wizard no
                // longer posts one, and nothing an admin types reaches the
                // account's credentials. The password is generated below and
                // exists in readable form only inside the credentials email.
                'roles' => ['required', 'array', 'min:1'],
                'roles.*' => ['in:' . implode(',', User::ROLES)],
                'department' => ['required', 'exists:departments,id'],
                'designation_id' => ['required', 'exists:designations,id'],
                'employment_status' => ['required', 'in:Permanent,Temporary,Coterminous,Casual,Contractual,Job Order'],
                'appointment_date' => ['required', 'date'],
                // The five ID scans and the twelve 201-file documents. Both
                // sets of rules live on their model beside the `accept`
                // attribute the wizard renders from, so the picker and the
                // validator cannot disagree about what may be uploaded.
            ] + GovernmentId::rules() + EmployeeSupportingDocument::rules(), [], [
                // Without these, Laravel humanises the column name and the
                // admin is told "the user email field is required" for a box
                // labelled "Email Address".
                'user_email'        => 'email address',
                'department'        => 'department',
                'designation_id'    => 'designation',
                'roles'             => 'role',
            ] + GovernmentId::attributeNames() + EmployeeSupportingDocument::attributeNames());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', $this->validationSummary($e->errors()))
                ->with('error_details', $this->describeValidationErrors($e->errors()));
        }

        try {
            // The one plaintext copy of the password in this request. It is
            // generated rather than accepted from the request, so no admin
            // knows the login they just created and no two accounts share a
            // hand-typed value; the credentials email below is the only place
            // it is ever readable. Everything else stores the hash.
            $temporaryPassword = TemporaryPasswordService::generate();

            DB::beginTransaction();

            // Create Employee. The employee number is not passed: the model
            // assigns the next `EMP-<year>-<sequence>` on create.
            $employee = Employee::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'photo' => self::handleFileUpload($request->file('photo')),
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
                'password' => Hash::make($temporaryPassword),
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
            $govIdPaths = [
                'employee_id'   => $employee->id,
                'gsis_no'       => $request->gsis_no,
                'philhealth_no' => $request->philhealth_no,
                'pagibig_no'    => $request->pagibig_no,
                'tin_no'        => $request->tin_no,
                'license_no'    => $request->license_no,
            ];
            foreach (GovernmentId::columnMap() as $input => $column) {
                $govIdPaths[$column] = self::handleFileUpload($request->file($input), 'employees/government_ids');
            }
            GovernmentId::create($govIdPaths);

            // Create Supporting Documents. The twelve inputs and the columns
            // they land in come from the model, so adding a document to the
            // 201 file is one edit rather than four in step.
            $supportingPaths = ['employee_id' => $employee->id];
            foreach (EmployeeSupportingDocument::columnMap() as $input => $column) {
                $supportingPaths[$column] = self::handleFileUpload(
                    $request->file($input),
                    'employees/supporting_documents'
                );
            }
            EmployeeSupportingDocument::create($supportingPaths);

            $employeeUserDetails = $this->credentialsFor(
                $employee,
                $employeeUser,
                $temporaryPassword,
                $request->roles
            );

            DB::commit();

            // An in-app welcome as well as the emails, so something is waiting
            // in the bell the first time they sign in — the emails carry the
            // credentials and are read once, then filed or lost.
            \App\Services\NotificationService::accountCreated($employeeUser, $employee);

            // Both emails go out only once the account is durably committed.
            // Sent from inside the transaction, a later failure would roll the
            // user row back while the credentials were already in somebody's
            // inbox — an email cannot be recalled.
            //
            // They are sent in their own try blocks, because they are not one
            // event: the credentials email is the employee's only copy of their
            // password, and it used to sit behind the verification link in a
            // single block, so a link that failed to send cost them the password
            // too — and neither the log nor the modal said which of the two had
            // gone.
            //
            // Past this point the employee exists, so a mail failure is a
            // warning, not a failed registration. Reporting it as one would send
            // the admin back to re-submit a form that can now only fail on a
            // duplicate number. `\Throwable`, not `\Exception`: a PHP fatal — the
            // SMTP socket read hitting max_execution_time is the one this app has
            // actually logged — is an \Error, and would sail past an \Exception
            // handler into the catch-all below, which would then report a
            // committed registration as rolled back.
            $verificationFailure = null;
            $credentialsFailure = null;

            try {
                event(new Registered($employeeUser));
            } catch (\Throwable $e) {
                $verificationFailure = $e;

                // The admin sees this on screen, but only until they navigate
                // away. Mail failures are exactly what someone comes asking
                // about days later ("nobody got their login"), so the reason
                // has to outlive the flash message.
                Log::error('Employee registered but the verification email failed', [
                    'user_id' => $employeeUser->id,
                    'employee_id' => $employee->id,
                    'email' => $employeeUser->email,
                    'exception' => $e,
                ]);
            }

            try {
                $employeeUser->notify(new EmployeeDetailsEmail($employeeUserDetails));
            } catch (\Throwable $e) {
                $credentialsFailure = $e;

                Log::error('Employee registered but account email failed', [
                    'user_id' => $employeeUser->id,
                    'employee_id' => $employee->id,
                    'email' => $employeeUser->email,
                    'exception' => $e,
                ]);
            }

            $emailNotice = $this->emailNoticeFor($employeeUser, $verificationFailure, $credentialsFailure);

            if ($emailNotice['status'] !== 'sent') {
                return redirect()->route('admin.personnel')
                    ->with('warning', "Employee {$employee->first_name} {$employee->last_name} was registered.")
                    ->with('email_notice', $emailNotice);
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
                ->with('email_notice', $emailNotice);

        } catch (\Exception $e) {
            DB::rollBack();

            // Without this the rollback is invisible: the transaction undoes
            // every row, the admin gets a flash message they can dismiss, and
            // nothing anywhere records why the registration failed.
            Log::error('Employee registration failed and was rolled back', [
                // `?? null`: the failure may have happened before the employee
                // row existed, in which case there is no generated number yet.
                'employee_id' => $employee->employee_id ?? null,
                'email' => $request->user_email,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error registering employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Is this username / email already in use? Answers the account-step fields
     * on blur so the admin learns the login is taken while they are still
     * looking at the box they typed it in.
     *
     * This changes how the conflict is *reported*, not whether it is enforced:
     * `unique:users,username` and `unique:users,email` in store() remain the
     * authority, and this endpoint cannot be talked into letting a duplicate
     * through because it writes nothing. Without it the admin only found out
     * after six steps of typing, from a modal that named the field but not the
     * value already holding it.
     *
     * The caller has to be signed in (see the route), so this is not a public
     * "does this address have an account" oracle — the account list is already
     * readable from the Personnel page by anyone who may call it.
     */
    public function usernameEmailAvailable(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            // A value with no field to sit in is a bug on the calling page, not
            // input the admin typed, so it is rejected rather than guessed at.
            'field' => ['required', 'in:username,user_email'],
            'value' => ['nullable', 'string', 'max:255'],
        ]);

        $value = trim((string) ($validated['value'] ?? ''));

        // Blank means "not answered yet" — the wizard has an empty box when it
        // opens an existing record, and an error beside an untouched field
        // would read as a defect. `required` is the local validator's job.
        if ($value === '') {
            return response()->json(['available' => true, 'message' => null]);
        }

        $column = $validated['field'] === 'username' ? 'username' : 'email';
        $taken = User::where($column, $value)->exists();

        return response()->json([
            'available' => !$taken,
            'message' => $taken ? self::takenMessage($column, $value) : null,
        ]);
    }

    /**
     * What the admin is told when the login they typed already exists.
     *
     * One definition, read by both the endpoint above and the test that pins
     * it, so the sentence the wizard shows cannot drift from the sentence that
     * was checked. The value is echoed back because "already taken" without it
     * makes the admin re-read their own typing to see which of the two fields
     * the complaint is about.
     */
    public static function takenMessage(string $column, string $value): string
    {
        return $column === 'username'
            ? "The username \"{$value}\" is already taken — usernames must be unique, so choose another."
            : "The email address \"{$value}\" already has an account in this system.";
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
     * The Password row is the employee's **only** copy of it: the wizard stopped
     * accepting a password from the admin, so this email is not a convenience
     * restating something already on the admin's screen — it is the delivery
     * channel. Both callers pass `TemporaryPasswordService::generate()`.
     *
     * @param  array<int, string>  $roles
     * @return array<string, string>
     */
    private function credentialsFor(Employee $employee, User $user, string $temporaryPassword, array $roles): array
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
            'Password'    => $temporaryPassword,
            'Role'        => implode(', ', array_map(
                fn ($role) => ucfirst((string) $role),
                array_values(array_unique($roles))
            )),
        ];
    }

    /**
     * What to tell the admin about the two emails that carry a new account.
     *
     * Three outcomes, not two, because the two messages are sent separately:
     * both went, only the password went, or the password did not. The middle
     * one matters most and had no way to be said — the employee can sign in the
     * moment they use "Forgot password", but nothing will ever verify their
     * address, and the link has to be re-sent from the sign-in screen.
     *
     * `failed` is reserved for the credentials email, because that is the one
     * with no second copy anywhere: it is the only place the generated password
     * is ever readable.
     *
     * @return array{status: string, email: string, reason?: string, verification_failed?: bool}
     */
    private function emailNoticeFor(User $user, ?\Throwable $verificationFailure, ?\Throwable $credentialsFailure): array
    {
        if ($credentialsFailure) {
            $notice = [
                'status' => 'failed',
                'email' => (string) $user->email,
                'reason' => $credentialsFailure->getMessage(),
            ];

            // Named only when it is also true: the panel then says the link is
            // missing too, and a `false` here would be a state nothing reads.
            if ($verificationFailure) {
                $notice['verification_failed'] = true;
            }

            return $notice;
        }

        if ($verificationFailure) {
            return [
                'status' => 'partial',
                'email' => (string) $user->email,
                'reason' => $verificationFailure->getMessage(),
            ];
        }

        return [
            'status' => 'sent',
            'email' => (string) $user->email,
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

    /**
     * Record why one imported account's email did not go out.
     *
     * The wizard has logged this since it shipped ("Employee registered but
     * account email failed"), on the grounds that a mail failure is exactly
     * what somebody comes asking about days later — so the reason has to
     * outlive the response. The import did not log it at all: it appended the
     * failure to the JSON message and nowhere else, which left "nobody got
     * their login" with no trace to investigate once the modal was closed.
     *
     * @param  array<string, string>  $details  the credentials rows, as mailed
     */
    private function logFailedAccountEmail(string $kind, array $details, \Throwable $e): void
    {
        Log::error('Bulk import: employee email failed', [
            'email' => $details['Email'] ?? null,
            'employee_id' => $details['Employee ID'] ?? null,
            'email_kind' => $kind,
            'exception' => $e->getMessage(),
        ]);
    }

    /**
     * Stores one upload on the public disk and returns the URL it is served at.
     *
     * Public and static because the personnel update route stores the same
     * three kinds of file — the photo, the ID scans and the 201-file documents
     * — and a second copy of the naming rule is a second answer to "what is
     * this file called on disk".
     *
     * The name is `<timestamp>_<random>_<sanitised original>`. The random
     * segment is not decoration: `time()` is identical across the twelve
     * documents submitted in one request, so an admin scanning each form to
     * the same default filename ("scan.pdf" out of an office MFP, which is the
     * normal case) had the second upload overwrite the first, leaving two
     * columns pointing at one file with nothing to show it had happened.
     *
     * Sanitising matters more now that these are forms rather than photos:
     * a filename like "CS Form 212 (Revised 2017) - Dela Cruz #2.xlsx" is
     * stored verbatim into a `/storage/...` URL, where the `#` truncates the
     * link and the document becomes unreachable from the wizard.
     */
    public static function handleFileUpload($file, string $folder = 'employees/photos')
    {
        if (!$file) {
            return null;
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $base = Str::limit(Str::slug($base) ?: 'file', 60, '');

            $filename = time() . '_' . Str::random(6) . '_' . $base . ($extension ? '.' . $extension : '');
            $path = $file->storeAs($folder, $filename, 'public');

            return '/storage/' . $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function bulkImport(Request $request)
    {
        // Bulk import touches N employees × ~8 tables + 2 mails per row.
        // Under php-fpm / nginx the public/.htaccess php_value max_execution_time 300
        // is not applied (mod_php only), so the default 30 s kills a 200-row file
        // mid-transaction. Raise for this request only — splitting into
        // docs/bulk_import_parts/ (50 rows) is still the safer path for slow SMTP.
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('max_input_time', '300');

        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $headers = array_shift($csvData);

            if (!$headers || count(array_filter($headers, fn ($h) => trim((string) $h) !== '')) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file is empty or missing a header row.',
                ], 422);
            }

            // Strip UTF-8 BOM from first header (Excel-saved CSVs) and trim.
            $headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
            $headers = array_map(fn ($h) => trim((string) $h), $headers);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Rows refused because the employee already exists, kept apart from
            // $errors and structured rather than worded. The alert renders one
            // compact line per record; a sentence per duplicate appended to the
            // summary paragraph is what used to crowd it off the screen.
            $duplicates = [];

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

                // Skip fully-blank rows (trailing newlines).
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                // Required-field guard: prevents creating departments / designations
                // with NULL names when a required column is empty.
                //
                // `employee_id` is deliberately not here. The file no longer has
                // to carry one — the model mints the next EMP-<year>-<sequence>
                // when the cell is blank (see Employee::booted()) — so requiring
                // it would refuse every row of a template that correctly leaves
                // it out.
                $missing = [];
                foreach (['first_name', 'last_name', 'department', 'designation'] as $required) {
                    if (empty($data[$required])) {
                        $missing[] = $required;
                    }
                }
                if ($missing) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": Missing required field(s): " . implode(', ', $missing);
                    continue;
                }

                // Refuse a row that is already on record.
                //
                // With the number optional, "already exists" is asked of
                // whichever identifier the row actually carries: the employee
                // number when the file supplies one (the migration files in
                // docs/ do), the email when it does not. Both columns are
                // UNIQUE, and a repeat uploaded by mistake shows up as one or
                // the other. A row with neither cannot be told apart from a
                // genuinely new hire of the same name, so it is imported rather
                // than refused — silently dropping a real employee is the worse
                // of the two mistakes.
                $csvEmployeeId = trim((string) ($data['employee_id'] ?? ''));
                $csvEmail      = trim((string) ($data['email'] ?? ''));

                $matchedId = ($csvEmployeeId !== '' && Employee::where('employee_id', $csvEmployeeId)->exists())
                    ? $csvEmployeeId
                    : null;
                $matchedEmail = ($matchedId === null && $csvEmail !== '' && Employee::where('email', $csvEmail)->exists())
                    ? $csvEmail
                    : null;

                if ($matchedId !== null || $matchedEmail !== null) {
                    $skipped++;
                    $duplicates[] = [
                        'row' => $index + 2,
                        // Which of the two identified the record, so the alert
                        // names it instead of printing a blank where the ID used
                        // to be on a CSV that has no ID column.
                        'employee_id' => $matchedId ?? '',
                        'email' => $matchedEmail ?? '',
                        'name' => trim(implode(' ', array_filter([
                            $data['first_name'] ?? null,
                            $data['last_name'] ?? null,
                        ]))),
                    ];
                    continue;
                }

                try {
                    // One row per savepoint, so a failure in the middle of a row
                    // rolls the whole row back instead of leaving a half-created
                    // employee behind and reporting it as skipped anyway.
                    DB::transaction(function () use ($data, &$pendingAccounts) {
                        // Create Employee. The number comes from the file when the
                        // file has one — the docs/ migration files carry real
                        // municipal numbers that must be preserved — and is
                        // otherwise assigned by the model as EMP-<year>-<sequence>.
                        $employee = Employee::create([
                            'employee_id' => $data['employee_id'] ?? null,
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
                        //
                        // A blank Password column gets a generated password,
                        // exactly like the wizard. The old fallback was one
                        // hard-coded literal shared by every imported account
                        // whose CSV cell was empty — and the credentials email
                        // then printed it, in full, for each of them.
                        $rawPassword = trim((string) ($data['password'] ?? '')) !== ''
                            ? (string) $data['password']
                            : TemporaryPasswordService::generate();
                        $employeeUser = User::create([
                            'employee_id' => $employee->id,
                            // The fallback address is built from the number the
                            // model just assigned, not from the CSV cell: the
                            // cell may be absent, and reading it as '' gave every
                            // emailless row the same "@lgu.gov.ph" address, so
                            // `users.email` being UNIQUE failed the second one
                            // and every row after it.
                            'email' => $data['email'] ?? $employee->employee_id . '@lgu.gov.ph',
                            'username' => $this->generateUsername(
                                $data['first_name'] ?? '',
                                $data['last_name'] ?? ''
                            ),
                            'password' => Hash::make($rawPassword),
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
                                $rawPassword,
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

            // Now that every imported row is durable, mail the accounts. A send
            // that fails must not cost the import: the employees exist, and an
            // employee who never got their password can still get in through
            // "Forgot password" — whereas throwing here would report a
            // committed import as failed.
            //
            // What actually went out is collected and returned, because
            // "Successfully imported 50 employee(s)" over a silent mail failure
            // is how an admin ends up telling fifty people to look for a
            // password that was never sent — the modal used to *assert* the
            // emails had gone, whatever happened here. The addresses are
            // reported rather than a bare count for a second reason: a file's
            // sample rows can carry addresses nobody reads (the shipped sample
            // uses `@maildrop.cc`, a throwaway host), and seeing one in the
            // result is the only chance to notice that before the staff do.
            $emailed = [];
            $mailFailures = [];
            $notAttempted = [];

            // A relay that is down fails every send, and each failure costs the
            // mail timeout in config/mail.php — so three accounts in a row is
            // enough to conclude it is unreachable and stop, instead of burning
            // the request's whole time budget failing the rest one at a time.
            // The accounts skipped this way are named in the response.
            $consecutiveFailures = 0;
            $failureLimit = 3;

            foreach ($pendingAccounts as $account) {
                $address = (string) $account['details']['Email'];

                \App\Services\NotificationService::accountCreated($account['user']);

                if ($consecutiveFailures >= $failureLimit) {
                    $notAttempted[] = $address;
                    continue;
                }

                $delivered = true;

                // The two messages are sent in their own try blocks. They used
                // to share one, so a verification link that failed took the
                // credentials email down with it — and that is the employee's
                // only copy of their password, where the verification link can
                // be re-sent from the sign-in screen.
                try {
                    event(new Registered($account['user']));
                } catch (\Throwable $e) {
                    $delivered = false;
                    $mailFailures[] = [
                        'email' => $address,
                        'kind' => 'verification link',
                        'reason' => $e->getMessage(),
                    ];
                    $this->logFailedAccountEmail('verification link', $account['details'], $e);
                }

                try {
                    $account['user']->notify(new EmployeeDetailsEmail($account['details']));
                    $emailed[] = $address;
                } catch (\Throwable $e) {
                    $delivered = false;
                    $mailFailures[] = [
                        'email' => $address,
                        'kind' => 'username and password',
                        'reason' => $e->getMessage(),
                    ];
                    $this->logFailedAccountEmail('username and password', $account['details'], $e);
                }

                $consecutiveFailures = $delivered ? 0 : $consecutiveFailures + 1;
            }

            // "Successfully imported 0 employee(s)." over a list of records that
            // already exist reads as a contradiction of the panel under it, so
            // the nothing-imported case says what happened instead.
            $message = $imported > 0
                ? "Successfully imported {$imported} employee(s)."
                : 'No new employees were imported.';

            // Duplicates carry their own count in their own panel, so counting
            // them again here would state the same number twice in two places
            // that can disagree. Only rows skipped for *other* reasons are
            // summarised in the sentence.
            $otherSkipped = $skipped - count($duplicates);
            if ($otherSkipped > 0) {
                $message .= " Skipped {$otherSkipped} row(s).";
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
                'errors' => $errors,
                'duplicates' => $duplicates,
                // Per account, not a count: which addresses were actually
                // emailed, which were refused and why, and which were never
                // attempted because the relay looked dead. The admin's next
                // move differs completely between those three.
                'emails' => [
                    'sent' => $emailed,
                    'failed' => $mailFailures,
                    'not_attempted' => $notAttempted,
                ],
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
