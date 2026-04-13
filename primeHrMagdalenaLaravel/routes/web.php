<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    return view('admin.personnel.adminPersonnel');
})->middleware('auth')->name('admin.personnel');

Route::get('/admin/training', function () {
    return view('admin.training.adminTraining');
})->middleware('auth')->name('admin.training');

Route::get('/admin/performance', function () {
    return view('admin.performance.adminPerformance');
})->middleware('auth')->name('admin.performance');

Route::get('/admin/attendance', function () {
    return view('admin.attendance.adminAttendance');
})->middleware('auth')->name('admin.attendance');

Route::get('/admin/leave', function () {
    return view('admin.leaveAndBenefits.adminLeaveAndBenefits');
})->middleware('auth')->name('admin.leave');

Route::get('/admin/payroll', function () {
    return view('admin.payroll.adminPayroll');
})->middleware('auth')->name('admin.payroll');

Route::get('/admin/departments', function () {
    $departments = \App\Models\Department::orderBy('name')->get();
    return view('admin.departments.adminDepartments', compact('departments'));
})->middleware('auth')->name('admin.departments');

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
