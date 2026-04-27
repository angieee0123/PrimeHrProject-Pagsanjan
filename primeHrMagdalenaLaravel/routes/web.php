<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmployeeRegistrationController;
use App\Http\Controllers\AttendanceController;

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
        $request->session()->regenerate();

        // Redirect admin to dashboard
        if (Auth::user()->email === 'admin@gmail.com') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended('/');
    }

    return back()->withInput($request->only('email'))
                 ->with('error', 'Invalid email or password. Please try again.');
})->name('login.post');

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
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard.adminDashboard');
})->middleware('auth')->name('admin.dashboard');

Route::get('/admin/recruitment', function () {
    return view('admin.recruitment.adminRecruitment');
})->middleware('auth')->name('admin.recruitment');

Route::get('/admin/personnel', function () {
    $departments = \App\Models\Department::where('status', 'Active')->orderBy('name')->get();
    $employees = \App\Models\Employee::with(['employmentDetail.departmentRelation', 'user'])
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

Route::post('/admin/personnel/{id}/status', function (\Illuminate\Http\Request $request, $id) {
    $employee = \App\Models\Employee::findOrFail($id);

    if (!$employee->user) {
        return redirect()->route('admin.personnel')->with('error', 'Employee does not have a user account.');
    }

    $newStatus = $request->validate(['status' => 'required|in:Active,Inactive'])['status'];

    $employee->user->update(['status' => $newStatus]);

    $message = $newStatus === 'Active'
        ? 'Employee account activated successfully.'
        : 'Employee account deactivated successfully.';

    return redirect()->route('admin.personnel')->with('success', $message);
})->middleware('auth')->name('admin.personnel.updateStatus');

Route::get('/admin/personnel/{id}', function ($id) {
    $employee = \App\Models\Employee::with(['employmentDetail', 'addresses', 'contacts', 'governmentIds'])
        ->findOrFail($id);

    return response()->json($employee);
})->middleware('auth')->name('admin.personnel.show');

Route::get('/admin/training', function () {
    return view('admin.training.adminTraining');
})->middleware('auth')->name('admin.training');

Route::get('/admin/performance', function () {
    return view('admin.performance.adminPerformance');
})->middleware('auth')->name('admin.performance');

Route::get('/admin/attendance', [AttendanceController::class, 'index'])->middleware('auth')->name('admin.attendance');
Route::get('/admin/attendance/detailed/{employeeId}', [AttendanceController::class, 'detailedDTR'])->middleware('auth')->name('admin.attendance.detailed');
Route::get('/admin/attendance/detailed/{employeeId}/export', [AttendanceController::class, 'exportDetailedDTR'])->middleware('auth')->name('admin.attendance.detailed.export');
Route::get('/admin/attendance/record/{attendanceId}', [AttendanceController::class, 'getAttendanceRecord'])->middleware('auth')->name('admin.attendance.record');
Route::post('/admin/attendance/correct', [AttendanceController::class, 'correctAttendance'])->middleware('auth')->name('admin.attendance.correct');

Route::get('/admin/leave', function () {
    return view('admin.leaveAndBenefits.adminLeaveAndBenefits');
})->middleware('auth')->name('admin.leave');

Route::get('/admin/payroll', function () {
    return view('admin.payroll.adminPayroll');
})->middleware('auth')->name('admin.payroll');

Route::get('/admin/departments', function () {
    $departments  = \App\Models\Department::orderBy('name')->get();
    $designations = \App\Models\Designation::with('department')->orderBy('title')->get();
    return view('admin.departments.adminDepartments', compact('departments', 'designations'));
})->middleware('auth')->name('admin.departments');

Route::get('/admin/designations/template', function () {
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => 'attachment; filename=designations_template.csv',
    ];

    $callback = function () {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['title', 'department_code', 'salary_grade', 'monthly_rate', 'employment_type', 'description']);
        fputcsv($file, ['Municipal Health Officer', 'MHO', 'SG-24', '35000', 'Permanent', 'Head of Municipal Health Office']);
        fputcsv($file, ['Administrative Assistant', 'OM', 'SG-8', '18000', 'Casual', '']);
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.designations.template');

Route::post('/admin/designations/import', function (\Illuminate\Http\Request $request) {
    $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

    $file     = fopen($request->file('csv_file')->getRealPath(), 'r');
    $header   = fgetcsv($file);
    $imported = 0;
    $skipped  = 0;

    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 2) { $skipped++; continue; }

        [$title, $department_code, $salary_grade, $monthly_rate, $employment_type, $description] = array_pad($row, 6, null);

        if (!$title || !$department_code) { $skipped++; continue; }

        $department = \App\Models\Department::where('code', strtoupper(trim($department_code)))->first();
        if (!$department) { $skipped++; continue; }

        $validTypes = ['Permanent', 'Casual', 'Contractual', 'Job Order'];
        if (!in_array($employment_type, $validTypes)) $employment_type = null;

        \App\Models\Designation::create([
            'title'           => trim($title),
            'department_id'   => $department->id,
            'salary_grade'    => $salary_grade ? trim($salary_grade) : null,
            'monthly_rate'    => $monthly_rate ? (float) $monthly_rate : null,
            'employment_type' => $employment_type,
            'description'     => $description ? trim($description) : null,
        ]);
        $imported++;
    }

    fclose($file);

    return redirect()->route('admin.departments')
        ->with('success', "Import complete: {$imported} designations imported, {$skipped} rows skipped.");
})->middleware('auth')->name('admin.designations.import');

Route::post('/admin/designations', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'title'           => ['required', 'string', 'max:255'],
        'department_id'   => ['required', 'exists:departments,id'],
        'salary_grade'    => ['nullable', 'string', 'max:50'],
        'monthly_rate'    => ['nullable', 'numeric', 'min:0'],
        'employment_type' => ['nullable', 'in:Permanent,Casual,Contractual,Job Order'],
        'description'     => ['nullable', 'string'],
    ]);

    \App\Models\Designation::create($data);

    return redirect()->route('admin.departments')->with('success', 'Designation added successfully.');
})->middleware('auth')->name('admin.designations.store');

Route::get('/admin/departments/template', function () {
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => 'attachment; filename=departments_template.csv',
    ];

    $callback = function () {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['code', 'name', 'head', 'personnel_count', 'status', 'description']);
        fputcsv($file, ['MHO', 'Municipal Health Office', 'Municipal Health Officer', '38', 'Active', 'Handles public health services']);
        fputcsv($file, ['MEO', 'Office of the Mun. Engineer', 'Municipal Engineer', '22', 'Active', '']);
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.departments.template');

Route::post('/admin/departments/import', function (\Illuminate\Http\Request $request) {
    $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

    $file = fopen($request->file('csv_file')->getRealPath(), 'r');
    $header = fgetcsv($file); // skip header row

    $imported = 0;
    $skipped  = 0;

    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 5) { $skipped++; continue; }

        [$code, $name, $head, $personnel_count, $status, $description] = array_pad($row, 6, null);

        if (!$code || !$name || !$head) { $skipped++; continue; }
        if (!in_array($status, ['Active', 'Inactive'])) $status = 'Active';

        \App\Models\Department::updateOrCreate(
            ['code' => strtoupper(trim($code))],
            [
                'name'            => trim($name),
                'head'            => trim($head),
                'personnel_count' => (int) ($personnel_count ?? 0),
                'status'          => $status,
                'description'     => $description ? trim($description) : null,
            ]
        );
        $imported++;
    }

    fclose($file);

    return redirect()->route('admin.departments')
        ->with('success', "Import complete: {$imported} departments imported, {$skipped} rows skipped.");
})->middleware('auth')->name('admin.departments.import');

Route::post('/admin/departments', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'code'            => ['required', 'string', 'max:20', 'unique:departments,code'],
        'name'            => ['required', 'string', 'max:255'],
        'head'            => ['required', 'string', 'max:255'],
        'personnel_count' => ['nullable', 'integer', 'min:0'],
        'status'          => ['required', 'in:Active,Inactive'],
        'description'     => ['nullable', 'string'],
    ]);

    \App\Models\Department::create($data);

    return redirect()->route('admin.departments')->with('success', 'Department registered successfully.');
})->middleware('auth')->name('admin.departments.store');

Route::get('/admin/reports', function () {
    return view('admin.reports.adminReports');
})->middleware('auth')->name('admin.reports');

// Chatbot Test Page
Route::get('/admin/test-chatbot', function () {
    return view('admin.test-chatbot');
})->middleware('auth')->name('admin.test-chatbot');

// Chatbot API
Route::post('/api/chatbot', [\App\Http\Controllers\ChatbotController::class, 'chat'])->middleware('auth');
