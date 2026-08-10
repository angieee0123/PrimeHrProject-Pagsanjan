<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmployeeRegistrationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\EmployeeLeaveBalanceController;
use App\Http\Controllers\PassSlipController;
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
Route::get('/password/forgot', [\App\Http\Controllers\AuthController::class, 'showForgotPassword'])->name('password.forgot');

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// ── Admin Dashboard ──
Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->middleware('auth')->name('admin.dashboard');

Route::get('/mayor/dashboard', [\App\Http\Controllers\MayorDashboardController::class, 'index'])->middleware('auth')->name('mayor.dashboard');
Route::get('/mayor/personnel', [\App\Http\Controllers\MayorPersonnelController::class, 'index'])->middleware('auth')->name('mayor.personnel');
Route::get('/mayor/leave', [\App\Http\Controllers\MayorLeaveController::class, 'index'])->middleware('auth')->name('mayor.leave');
Route::get('/mayor/travelorder', [\App\Http\Controllers\MayorTravelOrderController::class, 'index'])->middleware('auth')->name('mayor.travelorder');
Route::get('/mayor/travelorder/{id}', [\App\Http\Controllers\MayorTravelOrderController::class, 'show'])->middleware('auth')->name('mayor.travelorder.view');
Route::get('/mayor/passslip', [\App\Http\Controllers\MayorPassSlipController::class, 'index'])->middleware('auth')->name('mayor.passslip');

// ── Permanent Employee Dashboard ──
Route::get('/employee/dashboard', [\App\Http\Controllers\EmployeeDashboardController::class, 'index'])->middleware('auth')->name('employee.dashboard');

Route::get('/employee/attendance', [EmployeeAttendanceController::class, 'index'])->middleware('auth')->name('employee.attendance');
Route::get('/employee/attendance/detailed', [EmployeeAttendanceController::class, 'detailedDTR'])->middleware('auth')->name('employee.attendance.detailed');

Route::get('/employee/payslip', [\App\Http\Controllers\EmployeePayslipController::class, 'index'])->middleware('auth')->name('employee.payslip');
Route::get('/employee/payslip/{id}/details', [\App\Http\Controllers\EmployeePayslipController::class, 'getPayslipDetails'])->middleware('auth')->name('employee.payslip.details');

Route::get('/employee/leave', [EmployeeLeaveBalanceController::class, 'show'])->middleware('auth')->name('employee.leave');

// Leave Application Routes
Route::post('/leave/store', [LeaveController::class, 'store'])->middleware('auth')->name('leave.store');
Route::post('/leave/{id}/cancel', [LeaveController::class, 'cancel'])->middleware('auth')->name('leave.cancel');

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

Route::get('/employee/notification', function () {
    return view('employee.notification.employeeNotification');
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

// Schedule Routes
Route::post('/admin/schedules/assign', [\App\Http\Controllers\ScheduleController::class, 'assign'])->middleware('auth')->name('admin.schedules.assign');
Route::post('/admin/schedules/bulk-assign', [\App\Http\Controllers\ScheduleController::class, 'bulkAssign'])->middleware('auth')->name('admin.schedules.bulk-assign');
Route::post('/admin/schedules/check-overlap', [\App\Http\Controllers\ScheduleController::class, 'checkOverlap'])->middleware('auth')->name('admin.schedules.check-overlap');
Route::get('/admin/schedules/employee/{employeeId}', [\App\Http\Controllers\ScheduleController::class, 'forEmployee'])->middleware('auth')->name('admin.schedules.employee');
// Literal segments must be declared before the `{id}` wildcard: Laravel
// matches in declaration order, so with `export` below it "/admin/schedules/
// export" resolved to show('export') → Schedule::findOrFail('export') → 404,
// which is what the Work Schedules Export button was hitting.
Route::get('/admin/schedules/export', [\App\Http\Controllers\ScheduleController::class, 'export'])->middleware('auth')->name('admin.schedules.export');
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
        'user'
    ])->findOrFail($id);

    $data = $employee->toArray();
    $data['roles'] = $employee->user?->roles ?? [];
    return response()->json($data);
})->middleware('auth')->name('admin.personnel.edit');

Route::post('/admin/personnel/{id}/update', function (\Illuminate\Http\Request $request, $id) {
    $employee = \App\Models\Employee::with(['employmentDetail', 'addresses', 'contacts', 'governmentIds'])->findOrFail($id);

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

    // Handle photo upload if provided
    if ($request->hasFile('photo')) {
        $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
        $path = $request->file('photo')->storeAs('employees/photos', $filename, 'public');
        $updateData['photo'] = '/storage/' . $path;
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

    foreach (['gsis', 'philhealth', 'pagibig', 'tin', 'license'] as $govIdKey) {
        if ($request->hasFile($govIdKey . '_file')) {
            $govFile = $request->file($govIdKey . '_file');
            $filename = time() . '_' . $govFile->getClientOriginalName();
            $path = $govFile->storeAs('employees/government_ids', $filename, 'public');
            $govIdUpdateData[$govIdKey . '_file_path'] = '/storage/' . $path;
        }
    }

    $employee->governmentIds()->updateOrCreate([], $govIdUpdateData);

    if ($request->has('roles') && $employee->user) {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:' . implode(',', User::ROLES)],
        ]);
        $employee->user->update(['roles' => array_values(array_unique($validated['roles']))]);
    }

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
Route::get('/admin/training/export', [\App\Http\Controllers\TrainingController::class, 'export'])->middleware('auth')->name('admin.training.export');
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
Route::get('/admin/attendance/detailed/{employeeId}', [AttendanceController::class, 'detailedDTR'])->middleware('auth')->name('admin.attendance.detailed');
Route::get('/admin/attendance/detailed/{employeeId}/export', [AttendanceController::class, 'exportDetailedDTR'])->middleware('auth')->name('admin.attendance.detailed.export');
Route::get('/admin/attendance/record/{attendanceId}', [AttendanceController::class, 'getAttendanceRecord'])->middleware('auth')->name('admin.attendance.record');
Route::get('/admin/attendance/employee-appointment/{employeeId}', [AttendanceController::class, 'employeeAppointment'])->middleware('auth')->name('admin.attendance.employee-appointment');
Route::get('/admin/attendance/dtr-summary/{employeeId}', [AttendanceController::class, 'dtrSummary'])->middleware('auth')->name('admin.attendance.dtr-summary');
Route::get('/admin/attendance/{attendanceId}/accredited-log', [AttendanceController::class, 'getAccreditedHoursLog'])->middleware('auth')->name('admin.attendance.accredited-log');
Route::post('/admin/attendance/correct', [AttendanceController::class, 'correctAttendance'])->middleware('auth')->name('admin.attendance.correct');

// Attendance Exemption Routes
Route::get('/admin/attendance/exemptions/options', [AttendanceController::class, 'getExemptionOptions'])->middleware('auth')->name('admin.attendance.exemptions.options');
Route::get('/admin/attendance/exemptions/{id}', [AttendanceController::class, 'getExemption'])->middleware('auth')->name('admin.attendance.exemptions.show');
Route::post('/admin/attendance/exemptions', [AttendanceController::class, 'storeExemption'])->middleware('auth')->name('admin.attendance.exemptions.store');
Route::put('/admin/attendance/exemptions/{id}', [AttendanceController::class, 'updateExemption'])->middleware('auth')->name('admin.attendance.exemptions.update');
Route::delete('/admin/attendance/exemptions/{id}', [AttendanceController::class, 'destroyExemption'])->middleware('auth')->name('admin.attendance.exemptions.destroy');

Route::get('/admin/leave', [LeaveController::class, 'index'])->middleware('auth')->name('admin.leave');

// Leave & Travel Calendar — read-only availability monitor for the admin.
Route::get('/admin/leave-calendar', [\App\Http\Controllers\AdminLeaveCalendarController::class, 'index'])->middleware('auth')->name('admin.leaveCalendar');

Route::get('/admin/travelorder', [\App\Http\Controllers\TravelOrderController::class, 'index'])->middleware('auth')->name('admin.travelorder');
Route::post('/admin/travelorder/{id}/approve', [\App\Http\Controllers\TravelOrderController::class, 'approve'])->middleware('auth')->name('admin.travelorder.approve');
Route::post('/admin/travelorder/{id}/disapprove', [\App\Http\Controllers\TravelOrderController::class, 'disapprove'])->middleware('auth')->name('admin.travelorder.disapprove');
Route::get('/admin/travelorder/{id}', [\App\Http\Controllers\TravelOrderController::class, 'show'])->middleware('auth')->name('admin.travelorder.view');

Route::get('/admin/passslip', [PassSlipController::class, 'indexAdmin'])->middleware('auth')->name('admin.passslip');
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
Route::get('/admin/payroll/payslips/export', [\App\Http\Controllers\PayrollController::class, 'exportPayslips'])->middleware('auth')->name('admin.payroll.payslips.export');
Route::get('/admin/payroll/preview', [\App\Http\Controllers\PayrollController::class, 'preview'])->middleware('auth')->name('admin.payroll.preview');
Route::post('/admin/payroll/calculate', [\App\Http\Controllers\PayrollController::class, 'calculate'])->middleware('auth')->name('admin.payroll.calculate');
Route::get('/admin/payroll/export', [\App\Http\Controllers\PayrollController::class, 'export'])->middleware('auth')->name('admin.payroll.export');

Route::get('/admin/deductions', [\App\Http\Controllers\DeductionController::class, 'index'])->middleware('auth')->name('admin.deductions');

// Deduction Type Routes
Route::post('/admin/deductions/types', [\App\Http\Controllers\DeductionController::class, 'storeType'])->middleware('auth')->name('admin.deductions.types.store');
Route::put('/admin/deductions/types/{code}', [\App\Http\Controllers\DeductionController::class, 'updateType'])->middleware('auth')->name('admin.deductions.types.update');

// Employee Deduction Routes
Route::post('/admin/deductions/employee', [\App\Http\Controllers\DeductionController::class, 'storeEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.store');
Route::put('/admin/deductions/employee/{id}', [\App\Http\Controllers\DeductionController::class, 'updateEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.update');

// Bulk Assign Deductions Route
Route::post('/admin/deductions/employee/bulk-assign', [\App\Http\Controllers\DeductionController::class, 'bulkAssignEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.bulk-assign');

// Export employee deductions
Route::get('/admin/deductions/employee/export', [\App\Http\Controllers\DeductionController::class, 'exportEmployeeDeductions'])->middleware('auth')->name('admin.deductions.employee.export');

Route::get('/admin/deductions/employee/{id}', [\App\Http\Controllers\DeductionController::class, 'showEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.show');

// Get active deductions for an employee
Route::get('/admin/deductions/employee/{employeeId}/active', [\App\Http\Controllers\DeductionController::class, 'activeForEmployee'])->middleware('auth')->name('admin.deductions.employee.active');

// Delete employee deduction
Route::delete('/admin/deductions/employee/{id}/delete', [\App\Http\Controllers\DeductionController::class, 'deleteEmployeeDeduction'])->middleware('auth')->name('admin.deductions.employee.delete');

// Export loans
Route::get('/admin/deductions/loans/export', [\App\Http\Controllers\DeductionController::class, 'exportLoans'])->middleware('auth')->name('admin.deductions.loans.export');

// Get employee deductions for schedule modal
Route::get('/admin/deductions/employee/{employeeId}/deductions', [\App\Http\Controllers\DeductionController::class, 'deductionsForEmployee'])->middleware('auth')->name('admin.deductions.employee.deductions');

// Export schedules
Route::get('/admin/deductions/schedules/export', [\App\Http\Controllers\DeductionController::class, 'exportSchedules'])->middleware('auth')->name('admin.deductions.schedules.export');

Route::get('/admin/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->middleware('auth')->name('admin.departments');
Route::get('/admin/designations/template', [\App\Http\Controllers\DesignationController::class, 'template'])->middleware('auth')->name('admin.designations.template');
Route::post('/admin/designations/import', [\App\Http\Controllers\DesignationController::class, 'import'])->middleware('auth')->name('admin.designations.import');
Route::post('/admin/designations', [\App\Http\Controllers\DesignationController::class, 'store'])->middleware('auth')->name('admin.designations.store');
Route::get('/admin/departments/{id}/designations', [\App\Http\Controllers\DepartmentController::class, 'designationsForDepartment'])->middleware('auth')->name('admin.departments.designations');
Route::get('/admin/departments/export', [\App\Http\Controllers\DepartmentController::class, 'export'])->middleware('auth')->name('admin.departments.export');
Route::get('/admin/designations/export', [\App\Http\Controllers\DesignationController::class, 'export'])->middleware('auth')->name('admin.designations.export');
Route::get('/admin/departments/template', [\App\Http\Controllers\DepartmentController::class, 'template'])->middleware('auth')->name('admin.departments.template');
Route::post('/admin/departments/import', [\App\Http\Controllers\DepartmentController::class, 'import'])->middleware('auth')->name('admin.departments.import');
Route::post('/admin/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->middleware('auth')->name('admin.departments.store');

Route::get('/admin/reports', [\App\Http\Controllers\AdminReportsController::class, 'index'])->middleware('auth')->name('admin.reports');

Route::get('/admin/settings', [\App\Http\Controllers\AdminSettingsController::class, 'index'])->middleware('auth')->name('admin.settings');
Route::post('/admin/settings/profile', [\App\Http\Controllers\AdminSettingsController::class, 'updateProfile'])->middleware('auth')->name('admin.settings.profile');
Route::post('/admin/settings/password', [\App\Http\Controllers\AdminSettingsController::class, 'updatePassword'])->middleware('auth')->name('admin.settings.password');
Route::post('/admin/settings/notifications', [\App\Http\Controllers\AdminSettingsController::class, 'updateNotifications'])->middleware('auth')->name('admin.settings.notifications');
Route::post('/admin/settings/ai', [\App\Http\Controllers\AdminSettingsController::class, 'updateAiSettings'])->middleware('auth')->name('admin.settings.ai');
Route::post('/admin/settings/photo', [\App\Http\Controllers\AdminSettingsController::class, 'updatePhoto'])->middleware('auth')->name('admin.settings.photo');
Route::post('/admin/settings/system-ai', [\App\Http\Controllers\AdminSettingsController::class, 'updateSystemAiSettings'])->middleware('auth')->name('admin.settings.systemAi');
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
    // Throttled: one question can spend several provider calls (memory rewrite,
    // intent classification, SQL generation retries, narration) against a
    // shared org API key, so an unbounded endpoint is a cost and quota risk.
    Route::post("/{$aiArea}/ai-assistant/message", [\App\Http\Controllers\AiAssistantController::class, 'send'])->middleware(['auth', 'throttle:20,1'])->name("{$aiArea}.ai-assistant.send");
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

// Notification API Routes
Route::post('/api/notifications/mark-all-read', function (\Illuminate\Http\Request $request) {
    \App\Services\NotificationService::markAllAsRead(Auth::id(), $request->input('audience'));
    return response()->json(['success' => true]);
})->middleware('auth');

Route::post('/api/notifications/{id}/read', function ($id) {
    $notification = \App\Models\Notification::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();
    
    $notification->markAsRead();
    
    return response()->json(['success' => true]);
})->middleware('auth');

// Employee Request Routes
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    
    // Employee requests (for permanent employees)
    Route::post('/requests/submit', [\App\Http\Controllers\NotificationController::class, 'submitRequest']);
    Route::get('/requests/my-requests', [\App\Http\Controllers\NotificationController::class, 'myRequests']);
    
    // Admin request management
    Route::get('/requests/all', [\App\Http\Controllers\NotificationController::class, 'allRequests']);
    Route::post('/requests/{id}/update-status', [\App\Http\Controllers\NotificationController::class, 'updateRequestStatus']);
});

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
