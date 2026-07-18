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
Route::get('/login', function () {
    return view('user.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $user = Auth::user();
        if (!$user instanceof User) {
            Auth::logout();
            return back()->withInput($request->only('email'))
                ->with('error', 'Invalid email or password. Please try again.');
        }

        // Auth::attempt only proves the password is right. An account the admin
        // has not activated yet — or has deactivated — must not get a session.
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withInput($request->only('email'))
                ->with('error', 'Your account is inactive. Please contact your administrator to activate it.');
        }

        $request->session()->regenerate();

        // Eager load employee data with relationships
        $user->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');

        if ($user->email === 'admin@gmail.com') {
            session(['active_role' => 'admin']);
            return redirect()->route('admin.dashboard');
        }

        $dashboardRoutes = $user->dashboardRoutes();

        if (count($dashboardRoutes) > 1) {
            return redirect()->route('select-role');
        }

        if (count($dashboardRoutes) === 1) {
            session(['active_role' => $user->roles[0] ?? null]);
            return redirect()->route($dashboardRoutes[0]);
        }

        // Check if employee has permanent employment status
        if ($user->employee && $user->employee->employmentDetail) {
            $employmentStatus = $user->employee->employmentDetail->employment_status;

            if ($employmentStatus === 'Permanent') {
                return redirect()->route('employee.dashboard');
            }
        }

        // Fallback for the legacy hardcoded permanent test account
        if ($user->email === 'permanent@gmail.com') {
            return redirect()->route('employee.dashboard');
        }

        return redirect()->route('employee.dashboard');
    }

    return back()->withInput($request->only('email'))
                 ->with('error', 'Invalid email or password. Please try again.');
})->name('login.post');

Route::get('/select-role', function () {
    $user = Auth::user();
    if (!$user instanceof User) {
        return redirect()->route('login');
    }

    $dashboardRoutes = $user->dashboardRoutes();
    if (count($dashboardRoutes) <= 1) {
        return redirect()->route($dashboardRoutes[0] ?? 'employee.dashboard');
    }

    $options = collect($user->roles ?? [])
        ->unique()
        ->map(fn ($role) => ['role' => $role, 'route' => User::dashboardRouteForRole($role)])
        ->filter(fn ($option) => $option['route'] !== null)
        ->unique('route')
        ->values();

    return view('user.select-role', ['options' => $options]);
})->middleware('auth')->name('select-role');

Route::post('/select-role', function (\Illuminate\Http\Request $request) {
    $user = Auth::user();
    if (!$user instanceof User) {
        return redirect()->route('login');
    }

    $role = $request->validate(['role' => ['required', 'in:' . implode(',', User::ROLES)]])['role'];

    if (!$user->hasRole($role)) {
        abort(403);
    }

    $routeName = User::dashboardRouteForRole($role);
    if (!$routeName) {
        abort(403);
    }

    session(['active_role' => $role]);

    return redirect()->route($routeName);
})->middleware('auth')->name('select-role.post');

Route::get('/password/forgot', function () {
    return view('user.forgot-password');
})->name('password.forgot');

// ── Signup ──
Route::get('/signup', function () {
    return view('user.signup');
})->name('signup');

Route::post('/signup', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'first_name'       => ['required', 'string', 'max:100'],
        'last_name'        => ['required', 'string', 'max:100'],
        'employee_id'      => ['required', 'string', 'max:50'],
        'employment_type'  => ['required', 'in:Permanent,Job Order'],
        'position'         => ['required', 'string', 'max:100'],
        'email'            => ['required', 'email', 'unique:users,email'],
        'password'         => ['required', 'min:4', 'confirmed'],
    ]);

    return back()
        ->with('signup_success', true)
        ->with('signup_name',  $data['first_name'] . ' ' . $data['last_name'])
        ->with('signup_email', $data['email'])
        ->with('signup_type',  $data['employment_type']);
})->name('signup.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ── Admin Dashboard ──
Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->middleware('auth')->name('admin.dashboard');

Route::get('/mayor/dashboard', [\App\Http\Controllers\MayorDashboardController::class, 'index'])->middleware('auth')->name('mayor.dashboard');
Route::get('/mayor/personnel', [\App\Http\Controllers\MayorPersonnelController::class, 'index'])->middleware('auth')->name('mayor.personnel');
Route::get('/mayor/leave', [\App\Http\Controllers\MayorLeaveController::class, 'index'])->middleware('auth')->name('mayor.leave');
Route::get('/mayor/travelorder', [\App\Http\Controllers\MayorTravelOrderController::class, 'index'])->middleware('auth')->name('mayor.travelorder');
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

Route::get('/employee/settings', function () {
    return view('employee.settings.employeeSettings');
})->middleware('auth')->name('employee.settings');

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

// Schedule Routes
Route::post('/admin/schedules/assign', [\App\Http\Controllers\ScheduleController::class, 'assign'])->middleware('auth')->name('admin.schedules.assign');
Route::post('/admin/schedules/bulk-assign', [\App\Http\Controllers\ScheduleController::class, 'bulkAssign'])->middleware('auth')->name('admin.schedules.bulk-assign');
Route::post('/admin/schedules/check-overlap', [\App\Http\Controllers\ScheduleController::class, 'checkOverlap'])->middleware('auth')->name('admin.schedules.check-overlap');
Route::get('/admin/schedules/employee/{employeeId}', [\App\Http\Controllers\ScheduleController::class, 'forEmployee'])->middleware('auth')->name('admin.schedules.employee');
Route::get('/admin/schedules/{id}', [\App\Http\Controllers\ScheduleController::class, 'show'])->middleware('auth')->name('admin.schedules.show');
Route::delete('/admin/schedules/{id}/delete', [\App\Http\Controllers\ScheduleController::class, 'destroy'])->middleware('auth')->name('admin.schedules.delete');
Route::delete('/admin/schedules/{id}/remove', [\App\Http\Controllers\ScheduleController::class, 'remove'])->middleware('auth')->name('admin.schedules.remove');
Route::get('/admin/schedules/export', [\App\Http\Controllers\ScheduleController::class, 'export'])->middleware('auth')->name('admin.schedules.export');

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

    $govId = $employee->governmentIds->first();
    if ($govId) {
        $govId->update([
            'gsis_no'       => $request->gsis_no,
            'philhealth_no' => $request->philhealth_no,
            'pagibig_no'    => $request->pagibig_no,
            'tin_no'        => $request->tin_no,
            'license_no'    => $request->license_no,
        ]);
    }

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
    $employee = \App\Models\Employee::with(['employmentDetail', 'addresses', 'contacts', 'governmentIds'])
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

Route::get('/admin/payroll', function (\Illuminate\Http\Request $request) {
    $activeTab = $request->input('tab', 'register');
    
    // Handle Payslip Management Tab
    if ($activeTab === 'payslips') {
        $salaryComputations = \App\Models\SalaryComputation::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation'
        ])
        ->orderBy('period_end', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(15);
        
        // Set empty payrollRecords for stats calculation
        $payrollRecords = collect();
        $viewMode = 'employee';
        $deductionTypes = collect();
        $departments = collect();
        $employees = collect();
        
        return view('admin.payroll.adminPayroll', compact('salaryComputations', 'payrollRecords', 'viewMode', 'deductionTypes', 'departments', 'employees'));
    }
    
    // Handle Payroll Register Tab (existing code)
    $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
    $department = $request->input('department');
    $employeeName = $request->input('employee_name');
    $status = $request->input('status');
    $viewMode = $request->input('view_mode', 'daily');

    // Determine cutoff period (1st or 2nd half of month)
    $startDay = (int) date('d', strtotime($startDate));
    $isCutoff1st = $startDay <= 15;
    
    // Get daily salary computations for the period
    $query = \App\Models\DailySalaryComputation::with([
        'employee.employmentDetail.departmentRelation',
        'employee.employmentDetail.designationRelation',
        'employee.deductions' => function($q) use ($startDate, $endDate) {
            $q->where('status', 'ACTIVE')
              ->where('start_date', '<=', $endDate)
              ->where(function($query) use ($endDate) {
                  $query->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
              })
              ->with('deductionType.schedules');
        },
        'accreditedHoursLog'
    ])
    ->whereBetween('work_date', [$startDate, $endDate])
    ->orderBy('work_date', 'asc')
    ->orderBy('employee_id');

    if ($department) {
        $query->whereHas('employee.employmentDetail.departmentRelation', function($q) use ($department) {
            $q->where('name', $department);
        });
    }

    if ($employeeName) {
        $query->whereHas('employee', function($q) use ($employeeName) {
            $q->whereRaw("CONCAT(first_name, ' ', COALESCE(CONCAT(SUBSTRING(middle_name, 1, 1), '. '), ''), last_name) = ?", [$employeeName]);
        });
    }

    $dailyComputations = $query->get();

    // Process based on view mode
    if ($viewMode === 'employee' || $viewMode === 'monthly') {
        // Group by employee
        $payrollRecords = $dailyComputations->groupBy('employee_id')->map(function($records) use ($viewMode, $startDate, $endDate, $isCutoff1st) {
            $employee = $records->first()->employee;
            $totalBasicPay = $records->sum('daily_basic_pay');
            $totalOtPay = $records->sum('ot_pay');
            $totalLateDeduction = $records->sum('late_deduction');
            $totalUndertimeDeduction = $records->sum('undertime_deduction');
            $recordStatus = $records->every(fn($r) => $r->daily_gross_pay > 0) ? 'Processed' : 'Pending';

            // Calculate deductions by type with cutoff schedule
            $deductions = [];
            foreach ($employee->deductions as $deduction) {
                $deductionType = $deduction->deductionType;
                $code = $deductionType->code;
                
                if (!$deductionType->deducted_from_employee) {
                    continue;
                }
                
                // Get schedule - prioritize employee's custom schedule over deduction type schedule
                $cutoffSchedule = 'BOTH_SPLIT'; // Default
                if ($deduction->custom_cutoff_schedule) {
                    // Use employee-specific custom schedule
                    $cutoffSchedule = $deduction->custom_cutoff_schedule;
                } else {
                    // Use deduction type's default schedule
                    $schedule = $deductionType->schedules->first();
                    $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                }
                
                // Calculate base deduction amount
                $deductionAmount = 0;
                
                if ($deductionType->category === 'MANDATORY') {
                    if ($deductionType->computation_type === 'PERCENTAGE') {
                        $baseAmount = 0;
                        if ($deductionType->base_salary_type === 'BASIC') {
                            $baseAmount = $totalBasicPay;
                        } elseif ($deductionType->base_salary_type === 'GROSS') {
                            $baseAmount = $totalBasicPay + $totalOtPay;
                        } elseif ($deductionType->base_salary_type === 'MONTHLY') {
                            $baseAmount = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                        } else {
                            $baseAmount = $totalBasicPay;
                        }
                        $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
                    } elseif ($deductionType->computation_type === 'FIXED') {
                        $deductionAmount = $deductionType->percentage_rate ?? $deduction->amount ?? 0;
                    } else {
                        $deductionAmount = $deduction->amount ?? 0;
                    }
                } elseif ($deductionType->category === 'LOAN') {
                    $deductionAmount = $deduction->installment_amount ?? 0;
                }
                
                // Apply cutoff schedule
                if ($cutoffSchedule === '1ST_ONLY') {
                    $deductions[$code] = $isCutoff1st ? $deductionAmount : 0;
                } elseif ($cutoffSchedule === '2ND_ONLY') {
                    $deductions[$code] = $isCutoff1st ? 0 : $deductionAmount;
                } elseif ($cutoffSchedule === 'BOTH_FULL') {
                    $deductions[$code] = $deductionAmount;
                } else { // BOTH_SPLIT
                    $deductions[$code] = $deductionAmount / 2;
                }
            }

            return [
                'id' => $employee->employee_id ?? 'N/A',
                'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'dept' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'photo' => $employee->photo,
                'work_date' => null,
                'daily_rate' => $records->first()->daily_rate ?? 0,
                'basic' => $totalBasicPay,
                'ot_pay' => $totalOtPay,
                'late_deduction' => $totalLateDeduction,
                'undertime_deduction' => $totalUndertimeDeduction,
                'deductions' => $deductions,
                'status' => $recordStatus,
                'days_count' => $records->count(),
            ];
        })->values();
    } else {
        // Daily view - one row per day per employee
        $payrollRecords = $dailyComputations->map(function($record) use ($startDate, $endDate, $isCutoff1st) {
            $employee = $record->employee;
            $recordStatus = $record->daily_gross_pay > 0 ? 'Processed' : 'Pending';

            // Calculate deductions by type (prorated for daily) with cutoff schedule
            $deductions = [];
            foreach ($employee->deductions as $deduction) {
                $deductionType = $deduction->deductionType;
                $code = $deductionType->code;
                
                if (!$deductionType->deducted_from_employee) {
                    continue;
                }
                
                // Get schedule - prioritize employee's custom schedule over deduction type schedule
                $cutoffSchedule = 'BOTH_SPLIT'; // Default
                if ($deduction->custom_cutoff_schedule) {
                    // Use employee-specific custom schedule
                    $cutoffSchedule = $deduction->custom_cutoff_schedule;
                } else {
                    // Use deduction type's default schedule
                    $schedule = $deductionType->schedules->first();
                    $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                }
                
                // Calculate base deduction amount
                $deductionAmount = 0;
                
                if ($deductionType->category === 'MANDATORY') {
                    if ($deductionType->computation_type === 'PERCENTAGE') {
                        $baseAmount = 0;
                        if ($deductionType->base_salary_type === 'BASIC') {
                            $baseAmount = $record->daily_basic_pay;
                        } elseif ($deductionType->base_salary_type === 'GROSS') {
                            $baseAmount = $record->daily_basic_pay + $record->ot_pay;
                        } elseif ($deductionType->base_salary_type === 'MONTHLY') {
                            $monthlySalary = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                            $baseAmount = $monthlySalary / 22;
                        } else {
                            $baseAmount = $record->daily_basic_pay;
                        }
                        $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
                    } elseif ($deductionType->computation_type === 'FIXED') {
                        $deductionAmount = ($deductionType->percentage_rate ?? $deduction->amount ?? 0) / 22;
                    } else {
                        $deductionAmount = ($deduction->amount ?? 0) / 22;
                    }
                } elseif ($deductionType->category === 'LOAN') {
                    $deductionAmount = ($deduction->installment_amount ?? 0) / 22;
                }
                
                // Apply cutoff schedule (for daily view, prorate based on cutoff)
                if ($cutoffSchedule === '1ST_ONLY') {
                    $deductions[$code] = $isCutoff1st ? $deductionAmount : 0;
                } elseif ($cutoffSchedule === '2ND_ONLY') {
                    $deductions[$code] = $isCutoff1st ? 0 : $deductionAmount;
                } elseif ($cutoffSchedule === 'BOTH_FULL') {
                    $deductions[$code] = $deductionAmount;
                } else { // BOTH_SPLIT
                    $deductions[$code] = $deductionAmount / 2;
                }
            }

            return [
                'id' => $employee->employee_id ?? 'N/A',
                'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'dept' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'photo' => $employee->photo,
                'work_date' => $record->work_date,
                'daily_rate' => $record->daily_rate,
                'basic' => $record->daily_basic_pay,
                'ot_pay' => $record->ot_pay,
                'late_deduction' => $record->late_deduction,
                'undertime_deduction' => $record->undertime_deduction,
                'deductions' => $deductions,
                'status' => $recordStatus,
                'days_count' => null,
            ];
        });
    }

    // Filter by status if provided
    if ($status) {
        $payrollRecords = $payrollRecords->filter(fn($r) => $r['status'] === $status)->values();
    }

    // Get all unique deduction types from the records
    $deductionTypes = collect();
    foreach ($payrollRecords as $record) {
        if (isset($record['deductions'])) {
            foreach (array_keys($record['deductions']) as $code) {
                if (!$deductionTypes->contains($code)) {
                    $deductionTypes->push($code);
                }
            }
        }
    }

    // Get unique departments for filter
    $departments = \App\Models\Department::where('status', 'Active')->pluck('name');

    // Get unique employee names for filter
    $employees = \App\Models\Employee::orderBy('first_name')
        ->get()
        ->map(function($emp) {
            return trim($emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '. ' : '') . $emp->last_name);
        })
        ->unique()
        ->values();

    return view('admin.payroll.adminPayroll', compact('payrollRecords', 'departments', 'employees', 'viewMode', 'deductionTypes'));
})->middleware('auth')->name('admin.payroll');

Route::post('/admin/payroll/generate', [\App\Http\Controllers\PayrollController::class, 'generate'])->middleware('auth')->name('admin.payroll.generate');

// Payslip Management Routes
Route::post('/admin/payroll/payslip/{id}/approve', function ($id) {
    try {
        $computation = \App\Models\SalaryComputation::findOrFail($id);
        $computation->update(['status' => 'approved']);
        
        return response()->json(['success' => true, 'message' => 'Payslip approved successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
})->middleware('auth')->name('admin.payroll.payslip.approve');

Route::get('/admin/payroll/payslip/{id}/details', function ($id) {
    try {
        $computation = \App\Models\SalaryComputation::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation'
        ])->findOrFail($id);
        
        // Parse deduction_breakdown if it's a JSON string
        $deductionBreakdown = $computation->deduction_breakdown;
        if (is_string($deductionBreakdown)) {
            $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
        } elseif (!is_array($deductionBreakdown)) {
            $deductionBreakdown = [];
        }
        
        $payslip = [
            'id' => $computation->id,
            'employee_name' => ($computation->employee->first_name ?? '') . ' ' . ($computation->employee->last_name ?? ''),
            'employee_id' => $computation->employee->employee_id ?? 'N/A',
            'department' => $computation->employee->employmentDetail->departmentRelation->name ?? 'N/A',
            'position' => $computation->employee->employmentDetail->designationRelation->title ?? 'N/A',
            'period' => $computation->period_start->format('M d, Y') . ' - ' . $computation->period_end->format('M d, Y'),
            'pay_date' => $computation->pay_date ? $computation->pay_date->format('M d, Y') : null,
            'monthly_rate' => $computation->monthly_rate,
            'daily_rate' => $computation->daily_rate,
            'total_days_present' => $computation->total_days_present,
            'basic_pay' => $computation->basic_pay,
            'ot_pay' => $computation->ot_pay,
            'gross_pay' => $computation->gross_pay,
            'late_deduction' => $computation->late_deduction,
            'undertime_deduction' => $computation->undertime_deduction,
            'other_deductions' => $computation->other_deductions,
            'deduction_breakdown' => $deductionBreakdown,
            'total_deductions' => $computation->late_deduction + $computation->undertime_deduction + $computation->other_deductions,
            'net_pay' => $computation->net_pay,
            'status' => $computation->status,
            'notes' => $computation->notes,
        ];
        
        return response()->json(['success' => true, 'payslip' => $payslip]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
})->middleware('auth')->name('admin.payroll.payslip.details');

Route::post('/admin/payroll/payslip/{id}/reject', function (\Illuminate\Http\Request $request, $id) {
    try {
        $request->validate(['reason' => 'required|string']);
        
        $computation = \App\Models\SalaryComputation::findOrFail($id);
        $computation->update([
            'status' => 'rejected',
            'notes' => $request->reason
        ]);
        
        return response()->json(['success' => true, 'message' => 'Payslip rejected successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
})->middleware('auth')->name('admin.payroll.payslip.reject');

Route::get('/admin/payroll/payslips/export', function (\Illuminate\Http\Request $request) {
    $status = $request->input('status');
    
    $query = \App\Models\SalaryComputation::with([
        'employee.employmentDetail.departmentRelation',
        'employee.employmentDetail.designationRelation'
    ])->orderBy('period_end', 'desc');
    
    if ($status) {
        $query->where('status', $status);
    }
    
    $computations = $query->get();
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=payslips_' . now()->format('Y-m-d') . '.csv',
    ];
    
    $callback = function () use ($computations) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($file, [
            'Employee ID',
            'Employee Name',
            'Department',
            'Position',
            'Period Start',
            'Period End',
            'Basic Pay',
            'OT Pay',
            'Late Deduction',
            'Undertime Deduction',
            'Other Deductions',
            'Gross Pay',
            'Net Pay',
            'Status'
        ]);
        
        foreach ($computations as $comp) {
            fputcsv($file, [
                $comp->employee->employee_id ?? 'N/A',
                ($comp->employee->first_name ?? '') . ' ' . ($comp->employee->last_name ?? ''),
                $comp->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                $comp->employee->employmentDetail->designationRelation->title ?? 'N/A',
                $comp->period_start->format('Y-m-d'),
                $comp->period_end->format('Y-m-d'),
                number_format($comp->basic_pay, 2),
                number_format($comp->ot_pay, 2),
                number_format($comp->late_deduction, 2),
                number_format($comp->undertime_deduction, 2),
                number_format($comp->other_deductions, 2),
                number_format($comp->gross_pay, 2),
                number_format($comp->net_pay, 2),
                ucfirst($comp->status)
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.payroll.payslips.export');

Route::get('/admin/payroll/preview', function (\Illuminate\Http\Request $request) {
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $department = $request->input('department');
    $employmentStatus = $request->input('employment_status');

    // Get employees based on filters
    $employeesQuery = \App\Models\Employee::with([
        'employmentDetail.departmentRelation',
        'employmentDetail.designationRelation'
    ]);

    if ($department) {
        $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($department) {
            $q->where('name', $department);
        });
    }

    if ($employmentStatus) {
        $employeesQuery->whereHas('employmentDetail', function($q) use ($employmentStatus) {
            $q->where('employment_status', $employmentStatus);
        });
    }

    $employees = $employeesQuery->get();
    $employeeIds = $employees->pluck('id');

    // Get existing salary computations for the period
    $computations = \App\Models\DailySalaryComputation::whereIn('employee_id', $employeeIds)
        ->whereBetween('work_date', [$startDate, $endDate])
        ->get();

    $estimatedGross = $computations->sum('daily_basic_pay') + $computations->sum('ot_pay');
    $estimatedDeductions = $computations->sum('late_deduction') + $computations->sum('undertime_deduction');
    $estimatedNet = $estimatedGross - $estimatedDeductions;

    return response()->json([
        'employee_count' => $employees->count(),
        'estimated_gross' => number_format($estimatedGross, 2, '.', ''),
        'estimated_deductions' => number_format($estimatedDeductions, 2, '.', ''),
        'estimated_net' => number_format($estimatedNet, 2, '.', ''),
    ]);
})->middleware('auth')->name('admin.payroll.preview');

Route::post('/admin/payroll/calculate', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'pay_date' => 'required|date',
        'payroll_type' => 'required|in:regular,13th_month,bonus,special',
        'department' => 'nullable|string',
        'employment_status' => 'nullable|string',
    ]);

    try {
        // Determine cutoff period
        $startDay = (int) date('d', strtotime($data['start_date']));
        $isCutoff1st = $startDay <= 15;
        
        // Get employees based on filters
        $employeesQuery = \App\Models\Employee::with([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation',
            'deductions' => function($q) use ($data) {
                $q->where('status', 'ACTIVE')
                  ->where('start_date', '<=', $data['end_date'])
                  ->where(function($query) use ($data) {
                      $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $data['end_date']);
                  })
                  ->with('deductionType.schedules');
            }
        ]);

        if ($data['department']) {
            $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($data) {
                $q->where('name', $data['department']);
            });
        }

        if ($data['employment_status']) {
            $employeesQuery->whereHas('employmentDetail', function($q) use ($data) {
                $q->where('employment_status', $data['employment_status']);
            });
        }

        $employees = $employeesQuery->get();
        $payrollData = [];
        $allDeductionTypes = collect();

        foreach ($employees as $employee) {
            // Get salary computations for the period
            $computations = \App\Models\DailySalaryComputation::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$data['start_date'], $data['end_date']])
                ->get();

            if ($computations->isEmpty()) {
                continue;
            }

            $basicPay = $computations->sum('daily_basic_pay');
            $otPay = $computations->sum('ot_pay');
            $lateDeduction = $computations->sum('late_deduction');
            $undertimeDeduction = $computations->sum('undertime_deduction');
            $daysWorked = $computations->count();
            $dailyRate = $computations->first()->daily_rate ?? 0;

            // Calculate deductions by type with cutoff schedule
            $deductions = [];
            foreach ($employee->deductions as $deduction) {
                $deductionType = $deduction->deductionType;
                $code = $deductionType->code;
                
                if (!$deductionType->deducted_from_employee) {
                    continue;
                }
                
                // Get schedule - prioritize custom over default
                $cutoffSchedule = $deduction->custom_cutoff_schedule 
                    ?? ($deductionType->schedules->first()->cutoff_schedule ?? 'BOTH_SPLIT');
                
                // Calculate base amount
                $deductionAmount = 0;
                if ($deductionType->category === 'MANDATORY') {
                    if ($deductionType->computation_type === 'PERCENTAGE') {
                        $baseAmount = $deductionType->base_salary_type === 'BASIC' ? $basicPay 
                            : ($deductionType->base_salary_type === 'GROSS' ? $basicPay + $otPay 
                            : ($deductionType->base_salary_type === 'MONTHLY' ? ($employee->employmentDetail?->designationRelation?->monthly_rate ?? 0) 
                            : $basicPay));
                        $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
                    } elseif ($deductionType->computation_type === 'FIXED') {
                        $deductionAmount = $deductionType->percentage_rate ?? $deduction->amount ?? 0;
                    } else {
                        $deductionAmount = $deduction->amount ?? 0;
                    }
                } elseif ($deductionType->category === 'LOAN') {
                    $deductionAmount = $deduction->installment_amount ?? 0;
                }
                
                // Apply cutoff schedule
                if ($cutoffSchedule === '1ST_ONLY') {
                    $deductions[$code] = $isCutoff1st ? $deductionAmount : 0;
                } elseif ($cutoffSchedule === '2ND_ONLY') {
                    $deductions[$code] = $isCutoff1st ? 0 : $deductionAmount;
                } elseif ($cutoffSchedule === 'BOTH_FULL') {
                    $deductions[$code] = $deductionAmount;
                } else {
                    $deductions[$code] = $deductionAmount / 2;
                }
                
                // Collect unique deduction types
                if (!$allDeductionTypes->contains($code)) {
                    $allDeductionTypes->push($code);
                }
            }

            $payrollData[] = [
                'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'department' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'days_worked' => $daysWorked,
                'daily_rate' => $dailyRate,
                'basic_pay' => $basicPay,
                'ot_pay' => $otPay,
                'late' => $lateDeduction,
                'undertime' => $undertimeDeduction,
                'deductions' => $deductions,
            ];
        }
        
        // Get deduction type names
        $deductionTypeNames = [];
        if ($allDeductionTypes->isNotEmpty()) {
            $deductionTypeModels = \App\Models\DeductionType::whereIn('code', $allDeductionTypes)->get();
            foreach ($deductionTypeModels as $dt) {
                $deductionTypeNames[$dt->code] = $dt->name;
            }
        }

        $payrollTypeLabels = [
            'regular' => 'Regular Payroll',
            '13th_month' => '13th Month Pay',
            'bonus' => 'Bonus',
            'special' => 'Special Payroll'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'period' => date('M d, Y', strtotime($data['start_date'])) . ' - ' . date('M d, Y', strtotime($data['end_date'])),
                'pay_date' => date('M d, Y', strtotime($data['pay_date'])),
                'payroll_type' => $payrollTypeLabels[$data['payroll_type']],
                'employees' => $payrollData,
                'deduction_types' => $allDeductionTypes->toArray(),
                'deduction_names' => $deductionTypeNames,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
})->middleware('auth')->name('admin.payroll.calculate');

Route::get('/admin/payroll/export', function (\Illuminate\Http\Request $request) {
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $payDate = $request->input('pay_date');
    $department = $request->input('department');
    $employmentStatus = $request->input('employment_status');

    // Get employees based on filters
    $employeesQuery = \App\Models\Employee::with([
        'employmentDetail.departmentRelation',
        'employmentDetail.designationRelation',
        'deductions' => function($q) use ($startDate, $endDate) {
            $q->where('status', 'ACTIVE')
              ->where('start_date', '<=', $endDate)
              ->where(function($query) use ($endDate) {
                  $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $endDate);
              })
              ->with('deductionType');
        }
    ]);

    if ($department) {
        $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($department) {
            $q->where('name', $department);
        });
    }

    if ($employmentStatus) {
        $employeesQuery->whereHas('employmentDetail', function($q) use ($employmentStatus) {
            $q->where('employment_status', $employmentStatus);
        });
    }

    $employees = $employeesQuery->get();

    // Get all unique deduction types (only employee shares)
    $deductionTypeCodes = [];
    $deductionTypeNames = [];
    foreach ($employees as $employee) {
        foreach ($employee->deductions as $deduction) {
            // Skip employer/government shares (only show employee shares in export)
            if (!$deduction->deductionType->deducted_from_employee) {
                continue;
            }
            
            $code = $deduction->deductionType->code;
            if (!in_array($code, $deductionTypeCodes)) {
                $deductionTypeCodes[] = $code;
                $deductionTypeNames[$code] = $deduction->deductionType->name;
            }
        }
    }

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=payroll_' . date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate)) . '.csv',
    ];

    $callback = function () use ($employees, $startDate, $endDate, $payDate, $deductionTypeCodes, $deductionTypeNames) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        // Header rows
        fputcsv($file, ['MUNICIPAL GOVERNMENT OF PAGSANJAN']);
        fputcsv($file, ['PAYROLL REGISTER']);
        fputcsv($file, ['Period: ' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate))]);
        fputcsv($file, ['Pay Date: ' . date('M d, Y', strtotime($payDate))]);
        fputcsv($file, []); // Empty row

        // Column headers
        $columnHeaders = [
            'No.',
            'Employee Name',
            'Position',
            'Department',
            'Days Worked',
            'Daily Rate',
            'Basic Pay',
            'OT Pay',
            'Late Deduction',
            'Undertime Deduction',
        ];
        
        // Add deduction type columns
        foreach ($deductionTypeCodes as $code) {
            $columnHeaders[] = $deductionTypeNames[$code];
        }
        
        $columnHeaders[] = 'Total Deductions';
        $columnHeaders[] = 'Net Pay';
        
        fputcsv($file, $columnHeaders);

        $totals = [
            'basic_pay' => 0,
            'ot_pay' => 0,
            'late' => 0,
            'undertime' => 0,
            'deductions' => array_fill_keys($deductionTypeCodes, 0),
            'total_deductions' => 0,
            'net_pay' => 0
        ];

        $rowNum = 1;
        foreach ($employees as $employee) {
            $computations = \App\Models\DailySalaryComputation::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$startDate, $endDate])
                ->get();

            if ($computations->isEmpty()) {
                continue;
            }

            $basicPay = $computations->sum('daily_basic_pay');
            $otPay = $computations->sum('ot_pay');
            $lateDeduction = $computations->sum('late_deduction');
            $undertimeDeduction = $computations->sum('undertime_deduction');
            $daysWorked = $computations->count();
            $dailyRate = $computations->first()->daily_rate ?? 0;

            // Calculate deductions by type
            $deductions = array_fill_keys($deductionTypeCodes, 0);
            foreach ($employee->deductions as $deduction) {
                // Skip employer/government shares (only deduct employee shares)
                if (!$deduction->deductionType->deducted_from_employee) {
                    continue;
                }
                
                $code = $deduction->deductionType->code;
                if ($deduction->deductionType->category === 'MANDATORY') {
                    if ($deduction->deductionType->computation_type === 'PERCENTAGE') {
                        $baseAmount = 0;
                        
                        // Determine base amount based on base_salary_type
                        if ($deduction->deductionType->base_salary_type === 'BASIC') {
                            $baseAmount = $basicPay;
                        } elseif ($deduction->deductionType->base_salary_type === 'GROSS') {
                            $baseAmount = $basicPay + $otPay;
                        } elseif ($deduction->deductionType->base_salary_type === 'MONTHLY') {
                            // Get monthly salary from designation
                            $baseAmount = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                        } else {
                            $baseAmount = $basicPay; // Default to basic
                        }
                        
                        $deductions[$code] = $baseAmount * ($deduction->deductionType->percentage_rate / 100);
                    } elseif ($deduction->deductionType->computation_type === 'FIXED') {
                        // For FIXED, use percentage_rate column (which stores the fixed amount)
                        $deductions[$code] = $deduction->deductionType->percentage_rate ?? $deduction->amount ?? 0;
                    } else {
                        $deductions[$code] = $deduction->amount ?? 0;
                    }
                } elseif ($deduction->deductionType->category === 'LOAN') {
                    $deductions[$code] = $deduction->installment_amount ?? 0;
                }
            }

            $totalDeductions = $lateDeduction + $undertimeDeduction + array_sum($deductions);
            $netPay = $basicPay + $otPay - $totalDeductions;

            $totals['basic_pay'] += $basicPay;
            $totals['ot_pay'] += $otPay;
            $totals['late'] += $lateDeduction;
            $totals['undertime'] += $undertimeDeduction;
            foreach ($deductionTypeCodes as $code) {
                $totals['deductions'][$code] += $deductions[$code];
            }
            $totals['total_deductions'] += $totalDeductions;
            $totals['net_pay'] += $netPay;

            $rowData = [
                $rowNum++,
                trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                $daysWorked,
                number_format($dailyRate, 2),
                number_format($basicPay, 2),
                number_format($otPay, 2),
                number_format($lateDeduction, 2),
                number_format($undertimeDeduction, 2),
            ];
            
            // Add deduction amounts
            foreach ($deductionTypeCodes as $code) {
                $rowData[] = number_format($deductions[$code], 2);
            }
            
            $rowData[] = number_format($totalDeductions, 2);
            $rowData[] = number_format($netPay, 2);
            
            fputcsv($file, $rowData);
        }

        // Total row
        $totalRow = [
            '',
            '',
            '',
            '',
            '',
            'TOTAL:',
            number_format($totals['basic_pay'], 2),
            number_format($totals['ot_pay'], 2),
            number_format($totals['late'], 2),
            number_format($totals['undertime'], 2),
        ];
        
        foreach ($deductionTypeCodes as $code) {
            $totalRow[] = number_format($totals['deductions'][$code], 2);
        }
        
        $totalRow[] = number_format($totals['total_deductions'], 2);
        $totalRow[] = number_format($totals['net_pay'], 2);
        
        fputcsv($file, $totalRow);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.payroll.export');

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

Route::get('/admin/reports', function () {
    return view('admin.reports.adminReports');
})->middleware('auth')->name('admin.reports');

// Chatbot Test Page
Route::get('/admin/test-chatbot', function () {
    return view('admin.test-chatbot');
})->middleware('auth')->name('admin.test-chatbot');

// ✅ NEW: Chatbot with Laravel Session Integration
Route::get('/admin/chatbot', function () {
    return view('admin.chatbot');
})->middleware('auth')->name('admin.chatbot');

// Chatbot API
Route::post('/chatbot/chat', [\App\Http\Controllers\ChatbotController::class, 'chat'])->middleware('auth')->name('chatbot.chat');
Route::get('/chatbot/history', [\App\Http\Controllers\ChatbotController::class, 'history'])->middleware('auth')->name('chatbot.history');

// Notification API Routes
Route::post('/api/notifications/mark-all-read', function () {
    \App\Services\NotificationService::markAllAsRead(Auth::id());
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
Route::get('/admin/deductions/types/{code}', [\App\Http\Controllers\DeductionController::class, 'showType'])->middleware('auth')->name('admin.deductions.types.show');
Route::put('/admin/deductions/loan-types/{id}', [\App\Http\Controllers\LoanTypeController::class, 'update'])->middleware('auth')->name('admin.deductions.loan-types.update');
Route::delete('/admin/deductions/loan-types/{id}', [\App\Http\Controllers\LoanTypeController::class, 'destroy'])->middleware('auth')->name('admin.deductions.loan-types.delete');

// Leave Records Import Route
Route::post('/admin/leave/import', [LeaveController::class, 'importLeaveRecords'])->middleware('auth')->name('admin.leave.import');

// Leave Template Download Route
Route::get('/admin/leave/download-template', [LeaveController::class, 'downloadTemplate'])->middleware('auth')->name('admin.leave.download-template');
