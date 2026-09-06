<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmployeeRegistrationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\EmployeeLeaveBalanceController;
use App\Http\Controllers\PassSlipController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

// ── Auth ──
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::get('/select-role', [\App\Http\Controllers\AuthController::class, 'showSelectRole'])->middleware('auth')->name('select-role');
Route::post('/select-role', [\App\Http\Controllers\AuthController::class, 'selectRole'])->middleware('auth')->name('select-role.post');
// Forgot password: email -> six-digit code -> new password.
//
// Throttled per IP on top of the per-address cooldown in
// PasswordResetCodeService: these are the only unauthenticated POST endpoints
// besides login, and the middle one guards a six-digit secret. `send` is the
// tighter limit because each call spends an SMTP send against the shared
// mailbox; `verify` is looser so a mistyped code is not answered with a 429,
// and the per-code attempt counter is what actually stops guessing.
Route::get('/password/forgot', [\App\Http\Controllers\PasswordResetController::class, 'show'])->name('password.forgot');
Route::post('/password/forgot/send', [\App\Http\Controllers\PasswordResetController::class, 'send'])->middleware('throttle:6,1')->name('password.forgot.send');
Route::post('/password/forgot/verify', [\App\Http\Controllers\PasswordResetController::class, 'verify'])->middleware('throttle:12,1')->name('password.forgot.verify');
Route::post('/password/forgot/reset', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.forgot.reset');

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Email Verification
Route::get('/email/verify', function () {
    return view('user.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    // Land on the dashboard this user's roles actually reach. Hard-coding
    // the employee dashboard dropped an admin or mayor onto a page that is
    // not theirs. Mirrors AuthController::login()'s routing, including the
    // role picker for an account holding more than one.
    $user = $request->user();
    $dashboards = $user instanceof User ? $user->dashboardRoutes() : [];

    if (count($dashboards) > 1) {
        return redirect()->route('select-role');
    }

    return redirect()->route($dashboards[0] ?? 'employee.dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    // Flashed as `resent` because that is the key `user/verify-email.blade.php`
    // reads. It was flashed as `message`, which nothing on that page looks at,
    // so "Resend Verification Email" reloaded the page unchanged — the one
    // control on a screen whose whole job is waiting for an email gave no sign
    // it had done anything, and the natural response is to press it until the
    // 6-per-minute throttle answers with a 429.
    return back()->with('resent', true);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ── Admin Dashboard ──
Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->middleware('auth')->name('admin.dashboard');

Route::get('/mayor/dashboard', [\App\Http\Controllers\MayorDashboardController::class, 'index'])->middleware('auth')->name('mayor.dashboard');
Route::get('/mayor/personnel', [\App\Http\Controllers\MayorPersonnelController::class, 'index'])->middleware('auth')->name('mayor.personnel');
Route::get('/mayor/leave', [\App\Http\Controllers\MayorLeaveController::class, 'index'])->middleware('auth')->name('mayor.leave');

// The mayor's read-only CS Form No. 6 preview, opened from the Leave & Travel
// Calendar. Same LeaveController methods the admin's three call — a filed leave
// form is one document, and a second renderer for the mayor would be a second
// thing to keep true. They live under /mayor because EnsureRoleForArea closes
// the /admin prefix to the mayor entirely.
Route::get('/mayor/leave/{id}/view-form', [LeaveController::class, 'viewLeaveForm'])->middleware('auth')->name('mayor.leave.view-form');
Route::get('/mayor/leave/{id}/print-form', [LeaveController::class, 'generateLeaveForm'])->middleware('auth')->name('mayor.leave.print-form');
Route::get('/mayor/leave/{id}/download-form', [LeaveController::class, 'generateLeaveForm'])->middleware('auth')->name('mayor.leave.download-form');

// Leave & Travel Calendar — the mayor's copy of the admin availability monitor,
// opened from the floating button on every mayor page. MayorLeaveCalendarController
// inherits the whole computation; only the links and the view differ.
Route::get('/mayor/leave-calendar', [\App\Http\Controllers\MayorLeaveCalendarController::class, 'index'])->middleware('auth')->name('mayor.leaveCalendar');
Route::get('/mayor/travelorder', [\App\Http\Controllers\MayorTravelOrderController::class, 'index'])->middleware('auth')->name('mayor.travelorder');
Route::get('/mayor/travelorder/{id}', [\App\Http\Controllers\MayorTravelOrderController::class, 'show'])->middleware('auth')->name('mayor.travelorder.view');
Route::get('/mayor/passslip', [\App\Http\Controllers\MayorPassSlipController::class, 'index'])->middleware('auth')->name('mayor.passslip');

// ── Permanent Employee Dashboard ──
//
// These six used to be wrapped in `Route::middleware(['verified'])`, which made
// them the only verified-gated routes in the file — the other ~165, including
// every admin, HR and mayor page, were not. `EnsureEmailIsVerifiedForArea` now
// gates the admin/, mayor/ and employee/ prefixes wholesale from the web group,
// so the wrapper here is redundant. Removing it also removes the wrong
// impression it gave: that verification was something a route opted into, and
// so something a new route could forget.
Route::get('/employee/dashboard', [\App\Http\Controllers\EmployeeDashboardController::class, 'index'])->middleware('auth')->name('employee.dashboard');

Route::get('/employee/attendance', [EmployeeAttendanceController::class, 'index'])->middleware('auth')->name('employee.attendance');
Route::get('/employee/attendance/detailed', [EmployeeAttendanceController::class, 'detailedDTR'])->middleware('auth')->name('employee.attendance.detailed');
Route::get('/employee/attendance/export', [EmployeeAttendanceController::class, 'export'])->middleware('auth')->name('employee.attendance.export');

Route::get('/employee/payslip', [\App\Http\Controllers\EmployeePayslipController::class, 'index'])->middleware('auth')->name('employee.payslip');
Route::get('/employee/payslip/export', [\App\Http\Controllers\EmployeePayslipController::class, 'export'])->middleware('auth')->name('employee.payslip.export');
Route::get('/employee/payslip/{id}/details', [\App\Http\Controllers\EmployeePayslipController::class, 'getPayslipDetails'])->middleware('auth')->name('employee.payslip.details');

Route::get('/employee/leave', [EmployeeLeaveBalanceController::class, 'show'])->middleware('auth')->name('employee.leave');

// Leave Application Routes
Route::post('/leave/store', [LeaveController::class, 'store'])->middleware('auth')->name('leave.store');
Route::post('/leave/{id}/cancel', [LeaveController::class, 'cancel'])->middleware('auth')->name('leave.cancel');

// Monetization Request Routes — filed from the employee's My Monetization tab.
Route::post('/employee/monetization', [\App\Http\Controllers\MonetizationRequestController::class, 'store'])->middleware('auth')->name('monetization.store');
Route::get('/employee/monetization/{id}', [\App\Http\Controllers\MonetizationRequestController::class, 'show'])->middleware('auth')->name('monetization.show');
Route::post('/employee/monetization/{id}/cancel', [\App\Http\Controllers\MonetizationRequestController::class, 'cancel'])->middleware('auth')->name('monetization.cancel');
// Print Sheet: the office's Monetization form as a PDF. Two routes for one
// document because the controller reads routeIs() — print streams it into the
// browser's viewer, download sends it as a file. Same pair as the Travel
// Order, the Pass Slip and the printed DTR. Scoped to the caller's own record.
Route::get('/employee/monetization/{id}/print-form', [\App\Http\Controllers\MonetizationRequestController::class, 'generateOwnForm'])->middleware('auth')->name('monetization.print-form');
Route::get('/employee/monetization/{id}/download-form', [\App\Http\Controllers\MonetizationRequestController::class, 'generateOwnForm'])->middleware('auth')->name('monetization.download-form');

// Busy dates for the File Leave / File Travel Order calendars: the logged-in
// employee's own leave and travel date ranges, so the pickers can mark them.
Route::get('/employee/busy-dates', function () {
    $user = Auth::user();
    $employee = $user instanceof User ? $user->employee : null;

    return response()->json(\App\Services\BusyDatesService::forEmployee($employee));
})->middleware('auth')->name('employee.busy-dates');

// Same payload for ANY employee, for the admin modals' busy-date calendars.
// Admin/HR only — this exposes one employee's schedule to another user, which
// the self-scoped route above deliberately never does.
Route::get('/admin/employee-busy-dates', function (\Illuminate\Http\Request $request) {
    $user = Auth::user();
    if (!$user instanceof User || !$user->hasAnyRole(['admin', 'hr'])) {
        abort(403);
    }

    $employeeId = $request->query('employee_id');
    $employee = $employeeId ? \App\Models\Employee::find($employeeId) : null;

    return response()->json(\App\Services\BusyDatesService::forEmployee($employee));
})->middleware('auth')->name('admin.employee-busy-dates');

// Employee's own Leave & Travel calendar (self-scoped, read-only). Opened from
// the floating button on every employee page; ?embed=1 loads the bare modal view.
Route::get('/employee/leave-calendar', [\App\Http\Controllers\EmployeeLeaveCalendarController::class, 'index'])->middleware('auth')->name('employee.leaveCalendar');

Route::get('/employee/performance', function () {
    $user = Auth::user();
    $employee = $user instanceof User ? $user->employee : null;

    if (!$employee) {
        return view('employee.performance.employeePerformance');
    }

    // Load employee relationships for topbar
    $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

    return view('employee.performance.employeePerformance', compact('employee'));
})->middleware('auth')->name('employee.performance');

Route::get('/employee/training', [\App\Http\Controllers\EmployeeTrainingController::class, 'index'])->middleware('auth')->name('employee.training');
Route::post('/employee/training', [\App\Http\Controllers\EmployeeTrainingController::class, 'store'])->middleware('auth')->name('employee.training.store');
Route::delete('/employee/training/{id}', [\App\Http\Controllers\EmployeeTrainingController::class, 'destroy'])->middleware('auth')->name('employee.training.delete');
Route::get('/employee/training/export', [\App\Http\Controllers\EmployeeTrainingController::class, 'export'])->middleware('auth')->name('employee.training.export');
Route::get('/employee/training/{id}/certificate', [\App\Http\Controllers\EmployeeTrainingController::class, 'certificate'])->middleware('auth')->name('employee.training.certificate');

Route::get('/employee/profile', [\App\Http\Controllers\EmployeeProfileController::class, 'index'])->middleware('auth')->name('employee.profile');
Route::post('/employee/profile/update', [\App\Http\Controllers\EmployeeProfileController::class, 'update'])->middleware('auth')->name('employee.profile.update');

Route::get('/employee/settings', [\App\Http\Controllers\EmployeeSettingsController::class, 'index'])->middleware('auth')->name('employee.settings');
Route::post('/employee/settings/photo', [\App\Http\Controllers\EmployeeSettingsController::class, 'updatePhoto'])->middleware('auth')->name('employee.settings.photo');
Route::post('/employee/settings/profile', [\App\Http\Controllers\EmployeeSettingsController::class, 'updateProfile'])->middleware('auth')->name('employee.settings.profile');
Route::post('/employee/settings/password', [\App\Http\Controllers\EmployeeSettingsController::class, 'updatePassword'])->middleware('auth')->name('employee.settings.password');
Route::post('/employee/settings/notifications', [\App\Http\Controllers\EmployeeSettingsController::class, 'updateNotifications'])->middleware('auth')->name('employee.settings.notifications');

// Kept as a redirect rather than deleted: this URL rendered the bell partial on
// its own — a floating button and a dropdown over a blank white page, with no
// layout, sidebar or way back. Whatever reached it wanted the notification
// list, which is now a real page.
Route::get('/employee/notification', function () {
    return redirect()->route('employee.notifications');
})->middleware('auth')->name('employee.notification');

Route::get('/employee/chatbot', function () {
    return view('employee.chatbot.employeeChatbot');
})->middleware('auth')->name('employee.chatbot');

Route::get('/employee/travelorder', [\App\Http\Controllers\EmployeeTravelOrderController::class, 'index'])->middleware('auth')->name('employee.travelorder');
Route::post('/employee/travelorder', [\App\Http\Controllers\EmployeeTravelOrderController::class, 'store'])->middleware('auth')->name('travelorder.store');
Route::post('/employee/travelorder/{id}/companion-response', [\App\Http\Controllers\EmployeeTravelOrderController::class, 'companionResponse'])->middleware('auth')->name('travelorder.companion.respond');
Route::post('/employee/travelorder/{id}/forward', [\App\Http\Controllers\EmployeeTravelOrderController::class, 'forward'])->middleware('auth')->name('travelorder.forward');
Route::get('/employee/travelorder/{id}', [\App\Http\Controllers\EmployeeTravelOrderController::class, 'show'])->middleware('auth')->name('travelorder.show');
Route::delete('/employee/travelorder/{id}', [\App\Http\Controllers\EmployeeTravelOrderController::class, 'destroy'])->middleware('auth')->name('travelorder.delete');

Route::get('/employee/passslip', [PassSlipController::class, 'indexPermanent'])->middleware('auth')->name('employee.passslip');
Route::post('/employee/passslip', [PassSlipController::class, 'store'])->middleware('auth')->name('passslip.store');
Route::get('/employee/passslip/{id}', [PassSlipController::class, 'show'])->middleware('auth')->name('passslip.show');
Route::delete('/employee/passslip/{id}', [PassSlipController::class, 'destroy'])->middleware('auth')->name('passslip.delete');

Route::get('/admin/recruitment', function () {
    return view('admin.recruitment.adminRecruitment');
})->middleware('auth')->name('admin.recruitment');

Route::get('/admin/personnel', function () {
    $departments = \App\Models\Department::where('status', 'Active')->orderBy('name')->get();
    $employees = \App\Models\Employee::with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'user', 'schedule'])
        ->orderBy('created_at', 'desc')
        ->get();

    $stats = [
        'total' => $employees->count(),
        'active' => $employees->filter(fn($e) => $e->user && $e->user->status === 'Active')->count(),
        'inactive' => $employees->filter(fn($e) => !$e->user || $e->user->status === 'Inactive')->count(),
        'permanent' => $employees->filter(fn($e) => $e->employmentDetail && $e->employmentDetail->employment_status === 'Permanent')->count(),
    ];

    return view('admin.personnel.adminPersonnel', compact('departments', 'employees', 'stats'));
})->middleware('auth')->name('admin.personnel');

Route::post('/admin/personnel', [EmployeeRegistrationController::class, 'store'])->middleware('auth')->name('admin.personnel.store');
Route::post('/admin/personnel/bulk-import', [EmployeeRegistrationController::class, 'bulkImport'])->middleware('auth')->name('admin.personnel.bulk-import');
Route::post('/admin/personnel/government-ids/extract', [\App\Http\Controllers\GovernmentIdOcrController::class, 'extract'])->middleware('auth')->name('admin.personnel.government-ids.extract');

// Declared above the parameterised `/admin/personnel/{id}` route below: routes
// match in declaration order, so with `export` after it the URL would resolve
// to show('export') -> Employee::findOrFail('export') -> 404.
Route::get('/admin/personnel/export', [\App\Http\Controllers\PersonnelExportController::class, 'export'])->middleware('auth')->name('admin.personnel.export');

// Schedule Routes
Route::post('/admin/schedules/assign', [\App\Http\Controllers\ScheduleController::class, 'assign'])->middleware('auth')->name('admin.schedules.assign');
Route::post('/admin/schedules/bulk-assign', [\App\Http\Controllers\ScheduleController::class, 'bulkAssign'])->middleware('auth')->name('admin.schedules.bulk-assign');
Route::post('/admin/schedules/check-overlap', [\App\Http\Controllers\ScheduleController::class, 'checkOverlap'])->middleware('auth')->name('admin.schedules.check-overlap');
Route::get('/admin/schedules/employee/{employeeId}', [\App\Http\Controllers\ScheduleController::class, 'forEmployee'])->middleware('auth')->name('admin.schedules.employee');
// Literal segments must be declared before the `{id}` wildcard: Laravel
// matches in declaration order, so with `export` below it "/admin/schedules/
// export" resolved to show('export') → Schedule::findOrFail('export') → 404,
// which is what the Work Schedules Export button was hitting.
// The export itself lives on PersonnelExportController, beside the Employee
// Records one: both are tabs of the Personnel page and both must carry the
// same CsvReportWriter letterhead.
Route::get('/admin/schedules/export', [\App\Http\Controllers\PersonnelExportController::class, 'schedules'])->middleware('auth')->name('admin.schedules.export');
Route::get('/admin/schedules/{id}', [\App\Http\Controllers\ScheduleController::class, 'show'])->middleware('auth')->name('admin.schedules.show');
Route::delete('/admin/schedules/{id}/delete', [\App\Http\Controllers\ScheduleController::class, 'destroy'])->middleware('auth')->name('admin.schedules.delete');
Route::delete('/admin/schedules/{id}/remove', [\App\Http\Controllers\ScheduleController::class, 'remove'])->middleware('auth')->name('admin.schedules.remove');

Route::post('/admin/personnel/{id}/status', function (\Illuminate\Http\Request $request, $id) {
    $employee = \App\Models\Employee::findOrFail($id);

    if (!$employee->user) {
        return redirect()->route('admin.personnel')->with('error', 'Employee does not have a user account.');
    }

    $newStatus = $request->validate(['status' => 'required|in:Active,Inactive'])['status'];

    $employee->user->update(['status' => $newStatus]);

    // Drop any live mobile tokens so a deactivated account loses API access now
    // rather than whenever its token would have expired.
    if ($newStatus === 'Inactive') {
        $employee->user->tokens()->delete();
    }

    $message = $newStatus === 'Active'
        ? 'Employee account activated successfully.'
        : 'Employee account deactivated successfully.';

    return redirect()->route('admin.personnel')->with('success', $message);
})->middleware('auth')->name('admin.personnel.updateStatus');

Route::get('/admin/personnel/{id}/edit', function ($id) {
    $employee = \App\Models\Employee::with([
        'employmentDetail.departmentRelation',
        'employmentDetail.designationRelation',
        'addresses',
        'contacts',
        'governmentIds',
        'supportingDocuments',
        'user'
    ])->findOrFail($id);

    $data = $employee->toArray();
    $data['roles'] = $employee->user?->roles ?? [];
    // Expose supporting_documents as a single object for the wizard (hasOne), plus keep original key for compatibility
    $data['supporting_documents'] = $employee->supportingDocuments;
    $data['supportingDocuments'] = $employee->supportingDocuments;
    return response()->json($data);
})->middleware('auth')->name('admin.personnel.edit');

Route::post('/admin/personnel/{id}/update', function (\Illuminate\Http\Request $request, $id) {
    // Same rules and same wording as registration — an edit must not accept a
    // format the wizard refused, or refuse one it accepted. The ID scans and
    // the photo were previously stored with no validation at all on this path,
    // so anything the picker could be talked into offering was written to the
    // public disk.
    $request->validate(
        ['photo' => ['nullable', 'image', 'max:' . \App\Services\UploadLimits::perFileKb(5120)]]
            + \App\Models\GovernmentId::rules()
            + \App\Models\EmployeeSupportingDocument::rules(),
        [],
        \App\Models\GovernmentId::attributeNames()
            + \App\Models\EmployeeSupportingDocument::attributeNames()
    );

    $employee = \App\Models\Employee::with(['employmentDetail', 'addresses', 'contacts', 'governmentIds', 'supportingDocuments'])->findOrFail($id);

    $updateData = [
        'first_name'     => $request->first_name,
        'middle_name'    => $request->middle_name,
        'last_name'      => $request->last_name,
        'suffix'         => $request->suffix,
        'birth_date'     => $request->birth_date,
        'place_of_birth' => $request->place_of_birth,
        'sex'            => $request->sex,
        'civil_status'   => $request->civil_status,
        'height'         => $request->height,
        'weight'         => $request->weight,
        'blood_type'     => $request->blood_type,
        'citizenship'    => $request->citizenship,
    ];

    // Handle photo upload if provided. Stored through the registration
    // controller's helper so an edited employee's files are named the same way
    // a newly registered one's are.
    // A failed store returns null, which on an *edit* would blank the photo
    // already on record — the upload failing must not delete what it was
    // meant to replace.
    if ($request->hasFile('photo')) {
        $storedPhoto = \App\Http\Controllers\EmployeeRegistrationController::handleFileUpload(
            $request->file('photo'),
            'employees/photos'
        );
        if ($storedPhoto) {
            $updateData['photo'] = $storedPhoto;
        }
    }

    $employee->update($updateData);

    if ($employee->employmentDetail) {
        $employee->employmentDetail->update([
            'designation_id'    => $request->designation_id,
            'department_id'     => $request->department,
            'employment_status' => $request->employment_status,
            'appointment_date'  => $request->appointment_date,
            'salary_grade'      => $request->salary_grade,
            'step_increment'    => $request->step_increment,
        ]);
    }

    $mobile    = $employee->contacts->firstWhere('type', 'mobile');
    $landline  = $employee->contacts->firstWhere('type', 'landline');
    $emergency = $employee->contacts->firstWhere('type', 'emergency');

    if ($mobile)    $mobile->update(['number' => $request->mobile_number]);
    if ($landline)  $landline->update(['number' => $request->landline_number]);
    if ($emergency) $emergency->update(['contact_person' => $request->emergency_contact_person, 'number' => $request->emergency_contact_number]);

    $address = $employee->addresses->first();
    if ($address) {
        $address->update([
            'house_no'  => $request->house_no,
            'street'    => $request->street,
            'barangay'  => $request->barangay,
            'city'      => $request->city,
            'province'  => $request->province,
            'zip_code'  => $request->zip_code,
        ]);
    }

    $govIdUpdateData = [
        'gsis_no'       => $request->gsis_no,
        'philhealth_no' => $request->philhealth_no,
        'pagibig_no'    => $request->pagibig_no,
        'tin_no'        => $request->tin_no,
        'license_no'    => $request->license_no,
    ];

    foreach (\App\Models\GovernmentId::columnMap() as $inputName => $column) {
        if ($request->hasFile($inputName)) {
            $storedScan = \App\Http\Controllers\EmployeeRegistrationController::handleFileUpload(
                $request->file($inputName),
                'employees/government_ids'
            );
            if ($storedScan) {
                $govIdUpdateData[$column] = $storedScan;
            }
        }
    }

    $employee->governmentIds()->updateOrCreate([], $govIdUpdateData);

    // Supporting Documents (201 file) — formats validated above
    $supportingData = [];
    foreach (\App\Models\EmployeeSupportingDocument::columnMap() as $inputName => $column) {
        if ($request->hasFile($inputName)) {
            $storedDocument = \App\Http\Controllers\EmployeeRegistrationController::handleFileUpload(
                $request->file($inputName),
                'employees/supporting_documents'
            );
            if ($storedDocument) {
                $supportingData[$column] = $storedDocument;
            }
        }
    }
    if (!empty($supportingData)) {
        $employee->supportingDocuments()->updateOrCreate(
            ['employee_id' => $employee->id],
            $supportingData
        );
    } elseif (!$employee->supportingDocuments) {
        // Row has never existed (e.g. employee created before this feature) — create empty shell so future edits have a row to update.
        $employee->supportingDocuments()->create(['employee_id' => $employee->id]);
    }

    if ($request->has('roles') && $employee->user) {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:' . implode(',', User::ROLES)],
        ]);
        $employee->user->update(['roles' => array_values(array_unique($validated['roles']))]);
    }

    // Personnel data is what payroll, leave credits and the DTR are computed
    // from, so a change somebody else made to it is the employee's to check.
    // accountUpdated() is a no-op when the editor *is* the employee.
    \App\Services\NotificationService::accountUpdated($employee);

    return redirect()->route('admin.personnel')->with('success', "Employee {$employee->first_name} {$employee->last_name} updated successfully!");
})->middleware('auth')->name('admin.personnel.update');

Route::get('/admin/personnel/{id}', function ($id) {
    $employee = \App\Models\Employee::with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'addresses', 'contacts', 'governmentIds'])
        ->findOrFail($id);

    return response()->json($employee);
})->middleware('auth')->name('admin.personnel.show');

Route::get('/admin/training', [\App\Http\Controllers\TrainingController::class, 'index'])->middleware('auth')->name('admin.training');
Route::post('/admin/training/{id}/approve', [\App\Http\Controllers\TrainingController::class, 'approve'])->middleware('auth')->name('admin.training.approve');
Route::post('/admin/training/{id}/reject', [\App\Http\Controllers\TrainingController::class, 'reject'])->middleware('auth')->name('admin.training.reject');
Route::get('/admin/training/export', [\App\Http\Controllers\TrainingExportController::class, 'export'])->middleware('auth')->name('admin.training.export');
Route::get('/admin/training/{id}/certificate', [\App\Http\Controllers\TrainingController::class, 'certificate'])->middleware('auth')->name('admin.training.certificate');

Route::get('/admin/performance', function () {
    return view('admin.performance.adminPerformance');
})->middleware('auth')->name('admin.performance');

// QR Attendance Scanner — the staffed kiosk standing in for a biometric
// reader. Registered before the parameterised attendance routes so `scanner`
// is never read as an id. Throttled because a kiosk in a busy lobby is the
// one screen that can hammer the punch endpoint by accident.
Route::get('/admin/attendance/scanner', [\App\Http\Controllers\AttendanceScannerController::class, 'index'])->middleware('auth')->name('admin.attendance.scanner');
Route::post('/admin/attendance/scanner/punch', [\App\Http\Controllers\AttendanceScannerController::class, 'punch'])->middleware(['auth', 'throttle:60,1'])->name('admin.attendance.scanner.punch');
Route::post('/admin/attendance/scanner/suggest', [\App\Http\Controllers\AttendanceScannerController::class, 'suggest'])->middleware(['auth', 'throttle:60,1'])->name('admin.attendance.scanner.suggest');
Route::get('/admin/attendance/scanner/recent', [\App\Http\Controllers\AttendanceScannerController::class, 'recent'])->middleware('auth')->name('admin.attendance.scanner.recent');

Route::get('/admin/attendance', [AttendanceController::class, 'index'])->middleware('auth')->name('admin.attendance');
// "Export" on the Attendance page toolbar -> the Attendance Summary tab.
// Same rule as the two below: a literal segment before any `{employeeId}`.
Route::get('/admin/attendance/summary-export', [AttendanceController::class, 'exportSummary'])->middleware('auth')->name('admin.attendance.summary.export');
// "Export All" on the Detailed Time Record tab. Registered before the
// parameterised `detailed/{employeeId}` routes so `detailed-export` is never
// read as an employee id.
Route::get('/admin/attendance/detailed-export', [AttendanceController::class, 'exportDetailedRecords'])->middleware('auth')->name('admin.attendance.detailed.export-all');
Route::get('/admin/attendance/detailed/{employeeId}', [AttendanceController::class, 'detailedDTR'])->middleware('auth')->name('admin.attendance.detailed');
// The Detailed DTR modal's two form buttons. One method, one document: `print`
// streams it into the browser's PDF viewer, `export` sends it as a file. They
// are separate routes rather than a query flag because routeIs() is what the
// controller reads, the same way the Travel Order's print/download pair works.
Route::get('/admin/attendance/detailed/{employeeId}/print', [AttendanceController::class, 'exportDetailedDTR'])->middleware('auth')->name('admin.attendance.detailed.print');
Route::get('/admin/attendance/detailed/{employeeId}/export', [AttendanceController::class, 'exportDetailedDTR'])->middleware('auth')->name('admin.attendance.detailed.export');
Route::get('/admin/attendance/record/{attendanceId}', [AttendanceController::class, 'getAttendanceRecord'])->middleware('auth')->name('admin.attendance.record');
Route::get('/admin/attendance/employee-appointment/{employeeId}', [AttendanceController::class, 'employeeAppointment'])->middleware('auth')->name('admin.attendance.employee-appointment');
Route::get('/admin/attendance/dtr-summary/{employeeId}', [AttendanceController::class, 'dtrSummary'])->middleware('auth')->name('admin.attendance.dtr-summary');
Route::get('/admin/attendance/{attendanceId}/accredited-log', [AttendanceController::class, 'getAccreditedHoursLog'])->middleware('auth')->name('admin.attendance.accredited-log');
Route::post('/admin/attendance/correct', [AttendanceController::class, 'correctAttendance'])->middleware('auth')->name('admin.attendance.correct');
Route::post('/admin/attendance/bulk-import', [AttendanceController::class, 'bulkImport'])->middleware('auth')->name('admin.attendance.bulk-import');

// Attendance Exemption Routes
Route::get('/admin/attendance/exemptions/options', [AttendanceController::class, 'getExemptionOptions'])->middleware('auth')->name('admin.attendance.exemptions.options');
Route::get('/admin/attendance/exemptions/{id}', [AttendanceController::class, 'getExemption'])->middleware('auth')->name('admin.attendance.exemptions.show');
Route::post('/admin/attendance/exemptions', [AttendanceController::class, 'storeExemption'])->middleware('auth')->name('admin.attendance.exemptions.store');
Route::put('/admin/attendance/exemptions/{id}', [AttendanceController::class, 'updateExemption'])->middleware('auth')->name('admin.attendance.exemptions.update');
Route::delete('/admin/attendance/exemptions/{id}', [AttendanceController::class, 'destroyExemption'])->middleware('auth')->name('admin.attendance.exemptions.destroy');

Route::get('/admin/leave', [LeaveController::class, 'index'])->middleware('auth')->name('admin.leave');

// Leave & Benefits CSV exports — one endpoint per tab, because each tab
// reports a different thing and a shared endpoint would have to pick one of
// them. Each answers with a Content-Disposition attachment, so the toolbar
// button downloads the file without navigating the page away.
Route::middleware('auth')->group(function () {
    $export = \App\Http\Controllers\LeaveBenefitsExportController::class;

    Route::get('/admin/leave/export/requests',     [$export, 'leaveRequests'])->name('admin.leave.export.requests');
    Route::get('/admin/leave/export/transactions', [$export, 'transactions'])->name('admin.leave.export.transactions');
    Route::get('/admin/leave/export/credits',      [$export, 'leaveCredits'])->name('admin.leave.export.credits');
    Route::get('/admin/leave/export/benefits',     [$export, 'benefits'])->name('admin.leave.export.benefits');
    Route::get('/admin/leave/export/types',        [$export, 'leaveTypes'])->name('admin.leave.export.types');
    Route::get('/admin/leave/export/accrual',      [$export, 'accrualRates'])->name('admin.leave.export.accrual');
});

// Leave & Travel Calendar — read-only availability monitor for the admin.
Route::get('/admin/leave-calendar', [\App\Http\Controllers\AdminLeaveCalendarController::class, 'index'])->middleware('auth')->name('admin.leaveCalendar');

Route::get('/admin/travelorder', [\App\Http\Controllers\TravelOrderController::class, 'index'])->middleware('auth')->name('admin.travelorder');
// Travel Order CSV exports — one endpoint per tab, for the same reason the six
// Leave & Benefits ones exist: a Pending file has no approver to name and a
// Disapproved one exists to carry the reason, so a single endpoint would print
// empty columns on one tab or drop the reason from the other.
//
// Registered *before* `travelorder/{id}`, which matches in declaration order —
// underneath it "/admin/travelorder/export-pending" would resolve to
// show('export-pending') → TravelOrder::findOrFail('export-pending') → 404,
// which is the trap `/admin/schedules/export` already fell into once.
Route::middleware('auth')->group(function () {
    $travelExport = \App\Http\Controllers\TravelOrderExportController::class;

    Route::get('/admin/travelorder/export/pending',     [$travelExport, 'pending'])->name('admin.travelorder.export.pending');
    Route::get('/admin/travelorder/export/approved',    [$travelExport, 'approved'])->name('admin.travelorder.export.approved');
    Route::get('/admin/travelorder/export/disapproved', [$travelExport, 'disapproved'])->name('admin.travelorder.export.disapproved');
    // The complete register — every status in one file, with a Status column.
    Route::get('/admin/travelorder/export/all',         [$travelExport, 'all'])->name('admin.travelorder.export.all');
});
Route::post('/admin/travelorder/{id}/approve', [\App\Http\Controllers\TravelOrderController::class, 'approve'])->middleware('auth')->name('admin.travelorder.approve');
Route::post('/admin/travelorder/{id}/disapprove', [\App\Http\Controllers\TravelOrderController::class, 'disapprove'])->middleware('auth')->name('admin.travelorder.disapprove');
Route::get('/admin/travelorder/{id}', [\App\Http\Controllers\TravelOrderController::class, 'show'])->middleware('auth')->name('admin.travelorder.view');
// The printable Authority to Travel, mirroring the Pass Slip's three: an HTML
// preview the Approved tab embeds, and the same view through dompdf to stream
// for printing or download. All three refuse an order that is not approved —
// see TravelOrderController::viewForm().
Route::get('/admin/travelorder/{id}/view-form', [\App\Http\Controllers\TravelOrderController::class, 'viewForm'])->middleware('auth')->name('admin.travelorder.view-form');
Route::get('/admin/travelorder/{id}/print-form', [\App\Http\Controllers\TravelOrderController::class, 'generateForm'])->middleware('auth')->name('admin.travelorder.print-form');
Route::get('/admin/travelorder/{id}/download-form', [\App\Http\Controllers\TravelOrderController::class, 'generateForm'])->middleware('auth')->name('admin.travelorder.download-form');

Route::get('/admin/passslip', [PassSlipController::class, 'indexAdmin'])->middleware('auth')->name('admin.passslip');
// Pass Slip CSV exports — one per tab, and likewise declared above
// `passslip/{id}` so the export paths are never read as a slip id.
Route::middleware('auth')->group(function () {
    $passSlipExport = \App\Http\Controllers\PassSlipExportController::class;

    Route::get('/admin/passslip/export/pending',     [$passSlipExport, 'pending'])->name('admin.passslip.export.pending');
    Route::get('/admin/passslip/export/approved',    [$passSlipExport, 'approved'])->name('admin.passslip.export.approved');
    Route::get('/admin/passslip/export/disapproved', [$passSlipExport, 'disapproved'])->name('admin.passslip.export.disapproved');
    // The complete register — every status in one file, with a Status column.
    Route::get('/admin/passslip/export/all',         [$passSlipExport, 'all'])->name('admin.passslip.export.all');
});
Route::post('/admin/passslip/{id}/approve', [PassSlipController::class, 'approve'])->middleware('auth')->name('admin.passslip.approve');
Route::post('/admin/passslip/{id}/disapprove', [PassSlipController::class, 'disapprove'])->middleware('auth')->name('admin.passslip.disapprove');
Route::get('/admin/passslip/{id}', [PassSlipController::class, 'viewAdmin'])->middleware('auth')->name('admin.passslip.view');
Route::get('/admin/passslip/{id}/view-form', [PassSlipController::class, 'viewForm'])->middleware('auth')->name('admin.passslip.view-form');
Route::get('/admin/passslip/{id}/print-form', [PassSlipController::class, 'generateForm'])->middleware('auth')->name('admin.passslip.print-form');
Route::get('/admin/passslip/{id}/download-form', [PassSlipController::class, 'generateForm'])->middleware('auth')->name('admin.passslip.download-form');

Route::post('/admin/leave/types', [LeaveController::class, 'storeLeaveType'])->middleware('auth')->name('admin.leave.types.store');
Route::get('/admin/leave/types/{code}', [LeaveController::class, 'show'])->middleware('auth')->name('admin.leave.types.show');
Route::put('/admin/leave/types/{code}', [LeaveController::class, 'update'])->middleware('auth')->name('admin.leave.types.update');

// Leave Application Admin Actions
Route::post('/admin/leave/{id}/approve', [LeaveController::class, 'approve'])->middleware('auth')->name('admin.leave.approve');
Route::post('/admin/leave/{id}/reject', [LeaveController::class, 'reject'])->middleware('auth')->name('admin.leave.reject');

// Monetization Request Admin Actions — decided from the Monetization Requests tab.
Route::get('/admin/monetization/{id}', [\App\Http\Controllers\MonetizationRequestController::class, 'adminShow'])->middleware('auth')->name('admin.monetization.show');
Route::post('/admin/monetization/{id}/approve', [\App\Http\Controllers\MonetizationRequestController::class, 'approve'])->middleware('auth')->name('admin.monetization.approve');
Route::post('/admin/monetization/{id}/disapprove', [\App\Http\Controllers\MonetizationRequestController::class, 'disapprove'])->middleware('auth')->name('admin.monetization.disapprove');
// The same Monetization sheet the employee prints, for any employee's request.
Route::get('/admin/monetization/{id}/print-form', [\App\Http\Controllers\MonetizationRequestController::class, 'generateForm'])->middleware('auth')->name('admin.monetization.print-form');
Route::get('/admin/monetization/{id}/download-form', [\App\Http\Controllers\MonetizationRequestController::class, 'generateForm'])->middleware('auth')->name('admin.monetization.download-form');

// Accrual Rate Routes
Route::post('/admin/leave/accrual-rates', [LeaveController::class, 'storeAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.store');
Route::get('/admin/leave/accrual-rates/{id}', [LeaveController::class, 'showAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.show');
Route::put('/admin/leave/accrual-rates/{id}', [LeaveController::class, 'updateAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.update');
Route::delete('/admin/leave/accrual-rates/{id}', [LeaveController::class, 'destroyAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.destroy');

// Manual Credit Adjustment Routes
Route::get('/admin/leave/employee/{employeeId}/balances', [LeaveController::class, 'getEmployeeBalances'])->middleware('auth')->name('admin.leave.employee.balances');
Route::post('/admin/leave/manual-credit/store', [LeaveController::class, 'storeManualCredit'])->middleware('auth')->name('admin.leave.manual-credit.store');
Route::put('/admin/leave/transactions/{id}', [LeaveController::class, 'updateTransaction'])->middleware('auth')->name('admin.leave.transactions.update');

// Leave Form Routes (CS Form No. 6)
Route::get('/admin/leave/{id}/view-form', [LeaveController::class, 'viewLeaveForm'])->middleware('auth')->name('admin.leave.view-form');
Route::get('/admin/leave/{id}/print-form', [LeaveController::class, 'generateLeaveForm'])->middleware('auth')->name('admin.leave.print-form');
Route::get('/admin/leave/{id}/download-form', [LeaveController::class, 'generateLeaveForm'])->middleware('auth')->name('admin.leave.download-form');

Route::get('/admin/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->middleware('auth')->name('admin.payroll');

Route::post('/admin/payroll/generate', [\App\Http\Controllers\PayrollController::class, 'generate'])->middleware('auth')->name('admin.payroll.generate');

// Payslip Management Routes
Route::post('/admin/payroll/payslip/{id}/approve', [\App\Http\Controllers\PayrollController::class, 'approvePayslip'])->middleware('auth')->name('admin.payroll.payslip.approve');
Route::get('/admin/payroll/payslip/{id}/details', [\App\Http\Controllers\PayrollController::class, 'payslipDetails'])->middleware('auth')->name('admin.payroll.payslip.details');
Route::post('/admin/payroll/payslip/{id}/reject', [\App\Http\Controllers\PayrollController::class, 'rejectPayslip'])->middleware('auth')->name('admin.payroll.payslip.reject');
Route::get('/admin/payroll/preview', [\App\Http\Controllers\PayrollController::class, 'preview'])->middleware('auth')->name('admin.payroll.preview');
Route::post('/admin/payroll/calculate', [\App\Http\Controllers\PayrollController::class, 'calculate'])->middleware('auth')->name('admin.payroll.calculate');
// Payroll CSV exports — one endpoint per tab, because each tab reports a
// different thing: the register reports a period's computation, Payslip
// Management the approval queue built from it, and Generate Payroll the run
// that was just produced. The register's button had no handler at all until
// now. Route names are unchanged so nothing that already links here breaks.
Route::middleware('auth')->group(function () {
    $payrollExport = \App\Http\Controllers\PayrollExportController::class;

    Route::get('/admin/payroll/register/export', [$payrollExport, 'register'])->name('admin.payroll.register.export');
    Route::get('/admin/payroll/payslips/export', [$payrollExport, 'payslips'])->name('admin.payroll.payslips.export');
    Route::get('/admin/payroll/export',          [$payrollExport, 'generated'])->name('admin.payroll.export');
});

Route::get('/admin/deductions', [\App\Http\Controllers\DeductionController::class, 'index'])->middleware('auth')->name('admin.deductions');

// Deductions & Loans CSV exports — one endpoint per tab, the same rule the
// Leave & Benefits and Travel Order exports follow.
//
// Declared *above* `/admin/deductions/employee/{id}`, which matches in
// declaration order: underneath it, "/admin/deductions/employee/export" would
// resolve to showEmployeeDeduction('export') -> findOrFail('export') -> 404.
// That is the trap `/admin/schedules/export` already fell into once.
//
// There is deliberately no Transactions endpoint: nothing in this system
// writes to `deduction_transactions`, so the file would be a letterhead over
// an empty table — which reads as "no deductions were taken" rather than
// "this is not built yet".
Route::middleware('auth')->group(function () {
    $deductionExport = \App\Http\Controllers\DeductionExportController::class;

    Route::get('/admin/deductions/types/export',     [$deductionExport, 'deductionTypes'])->name('admin.deductions.types.export');
    Route::get('/admin/deductions/employee/export',  [$deductionExport, 'employeeDeductions'])->name('admin.deductions.employee.export');
    Route::get('/admin/deductions/loans/export',     [$deductionExport, 'loans'])->name('admin.deductions.loans.export');
    Route::get('/admin/deductions/schedules/export', [$deductionExport, 'schedules'])->name('admin.deductions.schedules.export');
    Route::get('/admin/deductions/loan-types/export', [$deductionExport, 'loanTypes'])->name('admin.deductions.loanTypes.export');
});

// Deduction Type Routes
Route::post('/admin/deductions/types', [\App\Http\Controllers\DeductionController::class, 'storeType'])->middleware('auth')->name('admin.deductions.types.store');
Route::put('/admin/deductions/types/{code}', [\App\Http\Controllers\DeductionController::class, 'updateType'])->middleware('auth')->name('admin.deductions.types.update');

// Employee Deduction Routes
Route::post('/admin/deductions/employee', [\App\Http\Controllers\DeductionController::class, 'storeEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.store');
Route::put('/admin/deductions/employee/{id}', [\App\Http\Controllers\DeductionController::class, 'updateEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.update');

// Bulk Assign Deductions Route
Route::post('/admin/deductions/employee/bulk-assign', [\App\Http\Controllers\DeductionController::class, 'bulkAssignEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.bulk-assign');

Route::get('/admin/deductions/employee/{id}', [\App\Http\Controllers\DeductionController::class, 'showEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.show');

// Get active deductions for an employee
Route::get('/admin/deductions/employee/{employeeId}/active', [\App\Http\Controllers\DeductionController::class, 'activeForEmployee'])->middleware('auth')->name('admin.deductions.employee.active');

// Delete employee deduction
Route::delete('/admin/deductions/employee/{id}/delete', [\App\Http\Controllers\DeductionController::class, 'deleteEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.delete');

// Get employee deductions for schedule modal
Route::get('/admin/deductions/employee/{employeeId}/deductions', [\App\Http\Controllers\DeductionController::class, 'deductionsForEmployee'])->middleware('auth')->name('admin.deductions.employee.deductions');

Route::get('/admin/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->middleware('auth')->name('admin.departments');
Route::get('/admin/designations/template', [\App\Http\Controllers\DesignationController::class, 'template'])->middleware('auth')->name('admin.designations.template');
Route::post('/admin/designations/import', [\App\Http\Controllers\DesignationController::class, 'import'])->middleware('auth')->name('admin.designations.import');
Route::post('/admin/designations', [\App\Http\Controllers\DesignationController::class, 'store'])->middleware('auth')->name('admin.designations.store');
Route::get('/admin/departments/{id}/designations', [\App\Http\Controllers\DepartmentController::class, 'designationsForDepartment'])->middleware('auth')->name('admin.departments.designations');
// Both Departments-page tabs export from DepartmentExportController, for the
// same reason the Personnel ones share theirs — one letterhead, one office.
Route::get('/admin/departments/export', [\App\Http\Controllers\DepartmentExportController::class, 'departments'])->middleware('auth')->name('admin.departments.export');
Route::get('/admin/designations/export', [\App\Http\Controllers\DepartmentExportController::class, 'designations'])->middleware('auth')->name('admin.designations.export');
Route::get('/admin/departments/template', [\App\Http\Controllers\DepartmentController::class, 'template'])->middleware('auth')->name('admin.departments.template');
Route::post('/admin/departments/import', [\App\Http\Controllers\DepartmentController::class, 'import'])->middleware('auth')->name('admin.departments.import');
Route::post('/admin/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->middleware('auth')->name('admin.departments.store');

Route::get('/admin/reports', [\App\Http\Controllers\AdminReportsController::class, 'index'])->middleware('auth')->name('admin.reports');

// Admin Reports CSV exports — one endpoint per tab, the same rule the Leave &
// Benefits, Travel Order, Pass Slip and Payroll exports follow: each tab
// reports a different thing, so a shared endpoint would have to print a Gross
// Pay column on the Headcount file. The page's only button was
// `window.print()` until now.
//
// Recruitment and Performance are registered too, and answer with a file that
// states in its own words that no data is being recorded for them — a dead
// button on those two tabs is what would need explaining.
Route::middleware('auth')->group(function () {
    $reportExport = \App\Http\Controllers\AdminReportsExportController::class;

    Route::get('/admin/reports/export/payroll',     [$reportExport, 'payroll'])->name('admin.reports.export.payroll');
    Route::get('/admin/reports/export/department',  [$reportExport, 'department'])->name('admin.reports.export.department');
    Route::get('/admin/reports/export/deductions',  [$reportExport, 'deductions'])->name('admin.reports.export.deductions');
    Route::get('/admin/reports/export/headcount',   [$reportExport, 'headcount'])->name('admin.reports.export.headcount');
    Route::get('/admin/reports/export/recruitment', [$reportExport, 'recruitment'])->name('admin.reports.export.recruitment');
    Route::get('/admin/reports/export/training',    [$reportExport, 'training'])->name('admin.reports.export.training');
    Route::get('/admin/reports/export/performance', [$reportExport, 'performance'])->name('admin.reports.export.performance');
});

// Website Content — the editor for the public welcome page. Administrators
// only; WebsiteContentController re-checks the role on every endpoint rather
// than trusting the hidden nav entry, because this writes the one page an
// unauthenticated visitor can read.
Route::middleware('auth')->group(function () {
    Route::get('/admin/audits', [\App\Http\Controllers\AuditController::class, 'index'])->name('admin.audit');
    Route::get('/admin/website', [\App\Http\Controllers\WebsiteContentController::class, 'index'])->name('admin.website');
    // The logo routes are declared before the {section} wildcard: Laravel
    // matches in declaration order, so `/admin/website/logo` would otherwise
    // be swallowed as a section named "logo" and 404.
    Route::post('/admin/website/logo', [\App\Http\Controllers\WebsiteContentController::class, 'updateLogo'])->name('admin.website.logo');
    Route::delete('/admin/website/logo', [\App\Http\Controllers\WebsiteContentController::class, 'resetLogo'])->name('admin.website.logo.reset');
    Route::post('/admin/website/{section}', [\App\Http\Controllers\WebsiteContentController::class, 'update'])->name('admin.website.update');
    Route::delete('/admin/website/{section}', [\App\Http\Controllers\WebsiteContentController::class, 'reset'])->name('admin.website.reset');
});

Route::get('/admin/settings', [\App\Http\Controllers\AdminSettingsController::class, 'index'])->middleware('auth')->name('admin.settings');
Route::post('/admin/settings/profile', [\App\Http\Controllers\AdminSettingsController::class, 'updateProfile'])->middleware('auth')->name('admin.settings.profile');
Route::post('/admin/settings/password', [\App\Http\Controllers\AdminSettingsController::class, 'updatePassword'])->middleware('auth')->name('admin.settings.password');
Route::post('/admin/settings/notifications', [\App\Http\Controllers\AdminSettingsController::class, 'updateNotifications'])->middleware('auth')->name('admin.settings.notifications');
Route::post('/admin/settings/ai', [\App\Http\Controllers\AdminSettingsController::class, 'updateAiSettings'])->middleware('auth')->name('admin.settings.ai');
Route::post('/admin/settings/photo', [\App\Http\Controllers\AdminSettingsController::class, 'updatePhoto'])->middleware('auth')->name('admin.settings.photo');
Route::post('/admin/settings/system-ai', [\App\Http\Controllers\AdminSettingsController::class, 'updateSystemAiSettings'])->middleware('auth')->name('admin.settings.systemAi');
Route::post('/admin/settings/charter', [\App\Http\Controllers\AdminSettingsController::class, 'uploadCharter'])->middleware('auth')->name('admin.settings.charter');
Route::delete('/admin/settings/charter', [\App\Http\Controllers\AdminSettingsController::class, 'removeCharter'])->middleware('auth')->name('admin.settings.charter.remove');
// Appearance — personal palette for every signed-in user, organisation
// palette for administrators. The global routes are separate endpoints so
// the authorisation difference is visible here, not buried in a branch.
Route::middleware('auth')->group(function () {
    Route::post('/settings/appearance/preview', [\App\Http\Controllers\AppearanceController::class, 'preview'])->name('appearance.preview');
    Route::post('/settings/appearance', [\App\Http\Controllers\AppearanceController::class, 'updatePersonal'])->name('appearance.update');
    Route::delete('/settings/appearance', [\App\Http\Controllers\AppearanceController::class, 'resetPersonal'])->name('appearance.reset');
    Route::post('/admin/settings/appearance/global', [\App\Http\Controllers\AppearanceController::class, 'updateGlobal'])->name('appearance.global.update');
    Route::delete('/admin/settings/appearance/global', [\App\Http\Controllers\AppearanceController::class, 'resetGlobal'])->name('appearance.global.reset');
});

// Chatbot API
Route::post('/chatbot/chat', [\App\Http\Controllers\ChatbotController::class, 'chat'])->middleware(['auth', 'throttle:20,1'])->name('chatbot.chat');
Route::get('/chatbot/history', [\App\Http\Controllers\ChatbotController::class, 'history'])->middleware('auth')->name('chatbot.history');

// AI Assistant — full-page, persisted & searchable chat history. Same controller
// for all three areas; EnsureRoleForArea already guards each URL prefix.
foreach (['admin', 'employee', 'mayor'] as $aiArea) {
    Route::get("/{$aiArea}/ai-assistant", [\App\Http\Controllers\AiAssistantController::class, 'index'])->middleware('auth')->name("{$aiArea}.ai-assistant");
    Route::get("/{$aiArea}/ai-assistant/conversations/{conversation}", [\App\Http\Controllers\AiAssistantController::class, 'messages'])->middleware('auth')->name("{$aiArea}.ai-assistant.messages");
    Route::get("/{$aiArea}/ai-assistant/search", [\App\Http\Controllers\AiAssistantController::class, 'search'])->middleware('auth')->name("{$aiArea}.ai-assistant.search");
    Route::get("/{$aiArea}/ai-assistant/export/{token}", [\App\Http\Controllers\AiAssistantController::class, 'export'])->middleware('auth')->name("{$aiArea}.ai-assistant.export");
    Route::delete("/{$aiArea}/ai-assistant/conversations/{conversation}", [\App\Http\Controllers\AiAssistantController::class, 'destroy'])->middleware('auth')->name("{$aiArea}.ai-assistant.destroy");
}

// Files the assistant surfaces in chat. Area-agnostic on purpose: the answer to
// "may I see this file?" is AiAccessPolicy's, not the URL prefix's, and it is
// re-checked on every fetch inside the controller.
Route::get('/ai-assistant/file/{source}/{ref}', [\App\Http\Controllers\AiFileController::class, 'show'])
    ->middleware('auth')
    ->where('source', '[a-z_]+')
    ->where('ref', '[A-Za-z0-9_\-]+')
    ->name('ai-assistant.file');

// Notification and employee-request API. Every notification endpoint lives on
// NotificationController: two of these used to be closures registered above
// this group under the same method+URI, which Laravel's route collection keys
// by — so the group silently overwrote them and the closure that honoured the
// panel's audience parameter never ran.
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
    // Polled by the notification panels so a new notification appears without a
    // page reload. Static segment, so it must stay above /notifications/{id}/*.
    Route::get('/notifications/feed', [\App\Http\Controllers\NotificationController::class, 'feed']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->whereNumber('id');
    Route::post('/notifications/{id}/mark-unread', [\App\Http\Controllers\NotificationController::class, 'markAsUnread'])->whereNumber('id');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->whereNumber('id');

    // Employee requests (for permanent employees)
    Route::post('/requests/submit', [\App\Http\Controllers\NotificationController::class, 'submitRequest']);
    Route::get('/requests/my-requests', [\App\Http\Controllers\NotificationController::class, 'myRequests']);
    
    // Admin request management
    Route::get('/requests/all', [\App\Http\Controllers\NotificationController::class, 'allRequests']);
    Route::post('/requests/{id}/update-status', [\App\Http\Controllers\NotificationController::class, 'updateRequestStatus']);
});

// Notification history — "View all notifications" from any bell.
//
// One page per area rather than one shared URL, for the same reason the AI
// assistant is registered this way: EnsureRoleForArea already guards each URL
// prefix, so the area a page renders is settled by the router and the account's
// roles, never by a parameter the browser sends. `defaults()` is what hands the
// area to the controller.
foreach (['admin', 'employee', 'mayor'] as $notifArea) {
    Route::get("/{$notifArea}/notifications", [\App\Http\Controllers\NotificationController::class, 'history'])
        ->middleware('auth')
        ->defaults('area', $notifArea)
        ->name("{$notifArea}.notifications");
}

// Opening a notification. Area-agnostic on purpose: which record a click may
// reach is the stored row's question and the account's roles', not the URL
// prefix's, and NotificationController::open() re-checks both — it marks the
// row read and then refuses any destination this account could not have
// navigated to itself.
Route::get('/notifications/{id}/open', [\App\Http\Controllers\NotificationController::class, 'open'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('notifications.open');

// Request pages
Route::get('/employee/requests', function () {
    return view('employee.requests.permanentRequests');
})->middleware('auth')->name('employee.requests');

Route::get('/admin/requests', function () {
    return view('admin.requests.adminRequests');
})->middleware('auth')->name('admin.requests');

// ✅ NEW: Get current authenticated user's ID for chatbot
Route::get('/api/auth/user-id', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        return response()->json([
            'status' => 'success',
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'name' => Auth::user()->employee ?
                Auth::user()->employee->first_name . ' ' . Auth::user()->employee->last_name :
                'User ' . Auth::id()
        ]);
    }

    return response()->json([
        'status' => 'unauthenticated',
        'user_id' => null,
        'message' => 'User not authenticated'
    ], 401);
})->name('api.auth.user-id');

// Deduction Schedule Management Routes
Route::post('/admin/deductions/schedules/update', [\App\Http\Controllers\DeductionController::class, 'updateSchedules'])->middleware('auth')->name('admin.deductions.schedules.update');

// Loan Type Management Routes
Route::post('/admin/deductions/loan-types/store', [\App\Http\Controllers\LoanTypeController::class, 'store'])->middleware('auth')->name('admin.deductions.loan-types.store');
// {identifier}: the Deduction Types tab passes a code, the Loan Types tab an
// id. See DeductionController::showType().
Route::get('/admin/deductions/types/{identifier}', [\App\Http\Controllers\DeductionController::class, 'showType'])->middleware('auth')->name('admin.deductions.types.show');
Route::put('/admin/deductions/loan-types/{id}', [\App\Http\Controllers\LoanTypeController::class, 'update'])->middleware('auth')->name('admin.deductions.loan-types.update');
Route::delete('/admin/deductions/loan-types/{id}', [\App\Http\Controllers\LoanTypeController::class, 'destroy'])->middleware('auth')->name('admin.deductions.loan-types.delete');

// Leave Records Import Route
Route::post('/admin/leave/import', [LeaveController::class, 'importLeaveRecords'])->middleware('auth')->name('admin.leave.import');

// Leave Template Download Route
Route::get('/admin/leave/download-template', [LeaveController::class, 'downloadTemplate'])->middleware('auth')->name('admin.leave.download-template');
