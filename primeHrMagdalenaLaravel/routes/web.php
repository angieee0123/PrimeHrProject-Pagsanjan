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
    return view('admin.adminDashboard');
})->middleware('auth')->name('admin.dashboard');

Route::get('/admin/recruitment', function () {
    return view('admin.adminRecruitment');
})->middleware('auth')->name('admin.recruitment');

Route::get('/admin/personnel', function () {
    return view('admin.adminPersonnel');
})->middleware('auth')->name('admin.personnel');

Route::get('/admin/training', function () {
    return view('admin.adminTraining');
})->middleware('auth')->name('admin.training');

Route::get('/admin/performance', function () {
    return view('admin.adminPerformance');
})->middleware('auth')->name('admin.performance');

Route::get('/admin/attendance', function () {
    return view('admin.adminAttendance');
})->middleware('auth')->name('admin.attendance');

Route::get('/admin/leave', function () {
    return view('admin.adminLeaveAndBenefits');
})->middleware('auth')->name('admin.leave');

Route::get('/admin/payroll', function () {
    return view('admin.adminPayroll');
})->middleware('auth')->name('admin.payroll');

Route::get('/admin/departments', function () {
    return view('admin.adminDepartments');
})->middleware('auth')->name('admin.departments');

Route::get('/admin/reports', function () {
    return view('admin.adminReports');
})->middleware('auth')->name('admin.reports');
