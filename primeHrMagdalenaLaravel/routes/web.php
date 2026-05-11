<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmployeeRegistrationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;

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

        $user = Auth::user();

        if ($user->email === 'admin@gmail.com' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'hr') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'permanent' || $user->email === 'permanent@gmail.com') {
            return redirect()->route('permanent.dashboard');
        }

        return redirect()->route('joborder.dashboard');
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

// ── Permanent Employee Dashboard ──
Route::get('/permanent/dashboard', function () {
    return view('permanent.dashboard.permanentDashboard');
})->middleware('auth')->name('permanent.dashboard');

Route::get('/permanent/attendance', function () {
    return view('permanent.attendance.permanentAttendance');
})->middleware('auth')->name('permanent.attendance');

Route::get('/permanent/payslip', function () {
    return view('permanent.payslip.permanentPayslip');
})->middleware('auth')->name('permanent.payslip');

Route::get('/permanent/leave', function () {
    return view('permanent.leaveandbenefits.permanentLeaveandbenefits');
})->middleware('auth')->name('permanent.leave');

Route::get('/permanent/performance', function () {
    return view('permanent.performance.permanentPerformance');
})->middleware('auth')->name('permanent.performance');

Route::get('/permanent/training', function () {
    return view('permanent.training.permanentTraining');
})->middleware('auth')->name('permanent.training');

Route::get('/permanent/profile', function () {
    return view('permanent.profile.permanentProfile');
})->middleware('auth')->name('permanent.profile');

Route::get('/permanent/settings', function () {
    return view('permanent.settings.permanentSettings');
})->middleware('auth')->name('permanent.settings');

Route::get('/permanent/notification', function () {
    return view('permanent.notification.permanentNotification');
})->middleware('auth')->name('permanent.notification');

Route::get('/permanent/chatbot', function () {
    return view('permanent.chatbot.permanentChatbot');
})->middleware('auth')->name('permanent.chatbot');

// ── Job Order Employee Dashboard ──
Route::get('/joborder/dashboard', function () {
    return view('joborder.dashboard.joborderDashboard');
})->middleware('auth')->name('joborder.dashboard');

Route::get('/joborder/attendance', function () {
    return view('joborder.attendance.joborderAttendance');
})->middleware('auth')->name('joborder.attendance');

Route::get('/joborder/payslip', function () {
    return view('joborder.payslip.joborderPayslip');
})->middleware('auth')->name('joborder.payslip');

Route::get('/joborder/performance', function () {
    return view('joborder.performance.joborderPerformance');
})->middleware('auth')->name('joborder.performance');

Route::get('/joborder/training', function () {
    return view('joborder.training.joborderTraining');
})->middleware('auth')->name('joborder.training');

Route::get('/joborder/profile', function () {
    return view('joborder.profile.joborderProfile');
})->middleware('auth')->name('joborder.profile');

Route::get('/joborder/settings', function () {
    return view('joborder.settings.joborderSettings');
})->middleware('auth')->name('joborder.settings');

Route::get('/joborder/notification', function () {
    return view('joborder.notification.joborderNotification');
})->middleware('auth')->name('joborder.notification');

Route::get('/joborder/chatbot', function () {
    return view('joborder.chatbot.joborderChatbot');
})->middleware('auth')->name('joborder.chatbot');

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

// Schedule Routes
Route::post('/admin/schedules/assign', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'schedule_id' => 'nullable|exists:schedules,id',
        'employee_id' => 'required|exists:employees,id',
        'start_date'  => 'required|date',
        'end_date'    => 'required|date|after_or_equal:start_date',
        'am_in'       => 'required',
        'am_out'      => 'required',
        'pm_in'       => 'required',
        'pm_out'      => 'required',
    ]);

    // Check for overlapping schedules
    $overlapQuery = \App\Models\Schedule::where('employee_id', $data['employee_id'])
        ->where(function($query) use ($data) {
            $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                  ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                  ->orWhere(function($q) use ($data) {
                      $q->where('start_date', '<=', $data['start_date'])
                        ->where('end_date', '>=', $data['end_date']);
                  });
        });

    // Exclude current schedule if editing
    if ($data['schedule_id']) {
        $overlapQuery->where('id', '!=', $data['schedule_id']);
    }

    $overlappingSchedules = $overlapQuery->get();

    if ($overlappingSchedules->count() > 0) {
        $overlapDetails = $overlappingSchedules->map(function($s) {
            return \Carbon\Carbon::parse($s->start_date)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($s->end_date)->format('M d, Y');
        })->join(', ');

        return redirect()->route('admin.personnel')
            ->with('error', "Schedule overlaps with existing schedule(s): {$overlapDetails}. Please adjust the dates.")
            ->with('active_tab', 'schedules');
    }

    if ($data['schedule_id']) {
        // Update existing schedule
        $schedule = \App\Models\Schedule::findOrFail($data['schedule_id']);
        $schedule->update([
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'am_in'       => $data['am_in'],
            'am_out'      => $data['am_out'],
            'pm_in'       => $data['pm_in'],
            'pm_out'      => $data['pm_out'],
        ]);
        $message = 'Schedule updated successfully.';

        // Recalculate attendance for this schedule period
        $attendanceController = app(\App\Http\Controllers\AttendanceController::class);
        $recalculatedCount = $attendanceController->recalculateAttendanceForSchedule(
            $data['employee_id'],
            $data['start_date'],
            $data['end_date']
        );

        if ($recalculatedCount > 0) {
            $message .= " Recalculated {$recalculatedCount} attendance record(s).";
        }
    } else {
        // Create new schedule
        \App\Models\Schedule::create([
            'employee_id' => $data['employee_id'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'am_in'       => $data['am_in'],
            'am_out'      => $data['am_out'],
            'pm_in'       => $data['pm_in'],
            'pm_out'      => $data['pm_out'],
        ]);
        $message = 'Schedule assigned successfully.';

        // Recalculate attendance for this schedule period
        $attendanceController = app(\App\Http\Controllers\AttendanceController::class);
        $recalculatedCount = $attendanceController->recalculateAttendanceForSchedule(
            $data['employee_id'],
            $data['start_date'],
            $data['end_date']
        );

        if ($recalculatedCount > 0) {
            $message .= " Recalculated {$recalculatedCount} attendance record(s).";
        }
    }

    return redirect()->route('admin.personnel')->with('success', $message)->with('active_tab', 'schedules');
})->middleware('auth')->name('admin.schedules.assign');

Route::post('/admin/schedules/bulk-assign', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'employee_ids'   => 'required|array',
        'employee_ids.*' => 'exists:employees,id',
        'start_date'     => 'required|date',
        'end_date'       => 'required|date|after_or_equal:start_date',
        'am_in'          => 'required',
        'am_out'         => 'required',
        'pm_in'          => 'required',
        'pm_out'         => 'required',
    ]);

    $successCount = 0;
    $skippedEmployees = [];

    foreach ($data['employee_ids'] as $employeeId) {
        // Check for overlapping schedules
        $hasOverlap = \App\Models\Schedule::where('employee_id', $employeeId)
            ->where(function($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                      ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                      ->orWhere(function($q) use ($data) {
                          $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                      });
            })
            ->exists();

        if ($hasOverlap) {
            $employee = \App\Models\Employee::find($employeeId);
            $fullName = trim($employee->first_name . ' ' . $employee->last_name);
            $skippedEmployees[] = $fullName;
            continue;
        }

        \App\Models\Schedule::create([
            'employee_id' => $employeeId,
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'am_in'       => $data['am_in'],
            'am_out'      => $data['am_out'],
            'pm_in'       => $data['pm_in'],
            'pm_out'      => $data['pm_out'],
        ]);
        $successCount++;
    }

    if ($successCount > 0 && count($skippedEmployees) > 0) {
        $skippedList = implode(', ', $skippedEmployees);
        return redirect()->route('admin.personnel')
            ->with('success', "Schedule assigned to {$successCount} employee(s). Skipped {count($skippedEmployees)} due to overlaps: {$skippedList}")
            ->with('active_tab', 'schedules');
    } elseif ($successCount > 0) {
        return redirect()->route('admin.personnel')
            ->with('success', "Schedule assigned to {$successCount} employee(s) successfully.")
            ->with('active_tab', 'schedules');
    } else {
        $skippedList = implode(', ', $skippedEmployees);
        return redirect()->route('admin.personnel')
            ->with('error', "No schedules were assigned. All selected employees have overlapping schedules: {$skippedList}")
            ->with('active_tab', 'schedules');
    }
})->middleware('auth')->name('admin.schedules.bulk-assign');

Route::post('/admin/schedules/check-overlap', function (\Illuminate\Http\Request $request) {
    $employeeId = $request->employee_id;
    $scheduleId = $request->schedule_id;
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    $overlapQuery = \App\Models\Schedule::where('employee_id', $employeeId)
        ->where(function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
        });

    if ($scheduleId) {
        $overlapQuery->where('id', '!=', $scheduleId);
    }

    $overlappingSchedules = $overlapQuery->get();

    if ($overlappingSchedules->count() > 0) {
        $overlapDetails = $overlappingSchedules->map(function($s) {
            return \Carbon\Carbon::parse($s->start_date)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($s->end_date)->format('M d, Y');
        })->join(', ');

        return response()->json([
            'has_overlap' => true,
            'overlap_details' => "This schedule overlaps with: {$overlapDetails}"
        ]);
    }

    return response()->json(['has_overlap' => false]);
})->middleware('auth')->name('admin.schedules.check-overlap');

Route::get('/admin/schedules/employee/{employeeId}', function ($employeeId) {
    $schedules = \App\Models\Schedule::where('employee_id', $employeeId)
        ->orderBy('start_date', 'desc')
        ->get();

    return response()->json(['schedules' => $schedules]);
})->middleware('auth')->name('admin.schedules.employee');

Route::get('/admin/schedules/{id}', function ($id) {
    $schedule = \App\Models\Schedule::findOrFail($id);
    return response()->json($schedule);
})->middleware('auth')->name('admin.schedules.show');

Route::delete('/admin/schedules/{id}/delete', function ($id) {
    $schedule = \App\Models\Schedule::findOrFail($id);
    $schedule->delete();

    return redirect()->route('admin.personnel')->with('success', 'Schedule deleted successfully.')->with('active_tab', 'schedules');
})->middleware('auth')->name('admin.schedules.delete');

Route::delete('/admin/schedules/{id}/remove', function ($id) {
    $schedule = \App\Models\Schedule::where('employee_id', $id)->first();

    if ($schedule) {
        $schedule->delete();
        return redirect()->route('admin.personnel')->with('success', 'Schedule removed successfully.');
    }

    return redirect()->route('admin.personnel')->with('error', 'Schedule not found.');
})->middleware('auth')->name('admin.schedules.remove');

Route::get('/admin/schedules/export', function () {
    try {
        $employees = \App\Models\Employee::with(['schedule', 'employmentDetail.departmentRelation'])
            ->orderBy('last_name')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=schedules_' . now()->format('Y-m-d') . '.csv',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Employee ID', 'Employee Name', 'Department', 'AM In', 'AM Out', 'PM In', 'PM Out', 'Status']);

            foreach ($employees as $emp) {
                $fullName = trim($emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '. ' : '') . $emp->last_name . ($emp->suffix ? ' ' . $emp->suffix : ''));
                $department = $emp->employmentDetail && $emp->employmentDetail->departmentRelation
                    ? $emp->employmentDetail->departmentRelation->name
                    : 'N/A';
                $schedule = $emp->schedule;

                fputcsv($file, [
                    $emp->employee_id,
                    $fullName,
                    $department,
                    $schedule->am_in ?? '--:--',
                    $schedule->am_out ?? '--:--',
                    $schedule->pm_in ?? '--:--',
                    $schedule->pm_out ?? '--:--',
                    $schedule ? 'Assigned' : 'Not Set',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    } catch (\Exception $e) {
        return redirect()->route('admin.personnel')->with('error', 'Export failed: ' . $e->getMessage());
    }
})->middleware('auth')->name('admin.schedules.export');

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

Route::get('/admin/personnel/{id}/edit', function ($id) {
    $employee = \App\Models\Employee::with(['employmentDetail', 'addresses', 'contacts', 'governmentIds'])
        ->findOrFail($id);
    return response()->json($employee);
})->middleware('auth')->name('admin.personnel.edit');

Route::post('/admin/personnel/{id}/update', function (\Illuminate\Http\Request $request, $id) {
    $employee = \App\Models\Employee::with(['employmentDetail', 'addresses', 'contacts', 'governmentIds'])->findOrFail($id);

    $employee->update([
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
    ]);

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

    return redirect()->route('admin.personnel')->with('success', "Employee {$employee->first_name} {$employee->last_name} updated successfully!");
})->middleware('auth')->name('admin.personnel.update');

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
Route::get('/admin/attendance/{attendanceId}/accredited-log', [AttendanceController::class, 'getAccreditedHoursLog'])->middleware('auth')->name('admin.attendance.accredited-log');
Route::post('/admin/attendance/correct', [AttendanceController::class, 'correctAttendance'])->middleware('auth')->name('admin.attendance.correct');

Route::get('/admin/leave', [LeaveController::class, 'index'])->middleware('auth')->name('admin.leave');
Route::post('/admin/leave/types/store', [LeaveController::class, 'storeLeaveType'])->middleware('auth')->name('admin.leave.types.store');
Route::get('/admin/leave/types/{code}', [LeaveController::class, 'show'])->middleware('auth')->name('admin.leave.types.show');
Route::put('/admin/leave/types/{code}', [LeaveController::class, 'update'])->middleware('auth')->name('admin.leave.types.update');

// Accrual Rate Routes
Route::post('/admin/leave/accrual-rates', [LeaveController::class, 'storeAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.store');
Route::get('/admin/leave/accrual-rates/{id}', [LeaveController::class, 'showAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.show');
Route::put('/admin/leave/accrual-rates/{id}', [LeaveController::class, 'updateAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.update');
Route::delete('/admin/leave/accrual-rates/{id}', [LeaveController::class, 'destroyAccrualRate'])->middleware('auth')->name('admin.leave.accrual-rates.destroy');

Route::get('/admin/payroll', function (\Illuminate\Http\Request $request) {
    $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
    $department = $request->input('department');
    $status = $request->input('status');
    $viewMode = $request->input('view_mode', 'daily');

    // Get daily salary computations for the period
    $query = \App\Models\DailySalaryComputation::with([
        'employee.employmentDetail.departmentRelation',
        'employee.employmentDetail.designationRelation',
        'accreditedHoursLog'
    ])
    ->whereBetween('work_date', [$startDate, $endDate])
    ->orderBy('work_date', 'desc')
    ->orderBy('employee_id');

    if ($department) {
        $query->whereHas('employee.employmentDetail.departmentRelation', function($q) use ($department) {
            $q->where('name', $department);
        });
    }

    $dailyComputations = $query->get();

    // Process based on view mode
    if ($viewMode === 'employee' || $viewMode === 'monthly') {
        // Group by employee
        $payrollRecords = $dailyComputations->groupBy('employee_id')->map(function($records) use ($viewMode) {
            $employee = $records->first()->employee;
            $totalBasicPay = $records->sum('daily_basic_pay');
            $totalOtPay = $records->sum('ot_pay');
            $totalLateDeduction = $records->sum('late_deduction');
            $totalUndertimeDeduction = $records->sum('undertime_deduction');
            $recordStatus = $records->every(fn($r) => $r->daily_gross_pay > 0) ? 'Processed' : 'Pending';
            
            return [
                'id' => $employee->employee_id ?? 'N/A',
                'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'dept' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'work_date' => null,
                'daily_rate' => $records->first()->daily_rate ?? 0,
                'basic' => $totalBasicPay,
                'ot_pay' => $totalOtPay,
                'late_deduction' => $totalLateDeduction,
                'undertime_deduction' => $totalUndertimeDeduction,
                'status' => $recordStatus,
                'days_count' => $records->count(),
            ];
        })->values();
    } else {
        // Daily view - one row per day per employee
        $payrollRecords = $dailyComputations->map(function($record) {
            $employee = $record->employee;
            $recordStatus = $record->daily_gross_pay > 0 ? 'Processed' : 'Pending';
            
            return [
                'id' => $employee->employee_id ?? 'N/A',
                'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'dept' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'work_date' => $record->work_date,
                'daily_rate' => $record->daily_rate,
                'basic' => $record->daily_basic_pay,
                'ot_pay' => $record->ot_pay,
                'late_deduction' => $record->late_deduction,
                'undertime_deduction' => $record->undertime_deduction,
                'status' => $recordStatus,
                'days_count' => null,
            ];
        });
    }

    // Filter by status if provided
    if ($status) {
        $payrollRecords = $payrollRecords->filter(fn($r) => $r['status'] === $status)->values();
    }

    // Get unique departments for filter
    $departments = \App\Models\Department::where('status', 'Active')->pluck('name');

    return view('admin.payroll.adminPayroll', compact('payrollRecords', 'departments', 'viewMode'));
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
    $skipped  = [];

    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 2) { $skipped[] = '(invalid row — missing columns)'; continue; }

        [$title, $department_code, $salary_grade, $monthly_rate, $employment_type, $description] = array_pad($row, 6, null);
        $title           = trim($title ?? '');
        $department_code = strtoupper(trim($department_code ?? ''));

        if (!$title || !$department_code) { $skipped[] = $title ?: '(empty title)'; continue; }

        $department = \App\Models\Department::where('code', $department_code)->first();
        if (!$department) { $skipped[] = "{$title} — department code '{$department_code}' not found"; continue; }

        $monthly_rate_clean = $monthly_rate ? (float) preg_replace('/[^0-9.]/', '', $monthly_rate) : null;

        $exists = \App\Models\Designation::where('title', $title)
            ->where('department_id', $department->id)
            ->where('monthly_rate', $monthly_rate_clean)
            ->exists();

        if ($exists) { $skipped[] = "{$title} ({$department_code}) ₱" . number_format($monthly_rate_clean, 2) . ' — already exists'; continue; }

        \App\Models\Designation::create([
            'title'           => $title,
            'department_id'   => $department->id,
            'salary_grade'    => $salary_grade ? trim($salary_grade) : null,
            'monthly_rate'    => $monthly_rate_clean ?: null,
            'employment_type' => $employment_type,
            'description'     => $description ? trim($description) : null,
        ]);
        $imported++;
    }

    fclose($file);

    return redirect()->route('admin.departments')
        ->with('import_imported', $imported)
        ->with('import_skipped', $skipped)
        ->with('import_type', 'designation');
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

Route::get('/admin/departments/{id}/designations', function ($id) {
    $designations = \App\Models\Designation::where('department_id', $id)
        ->orderBy('title')
        ->get(['id', 'title', 'employment_type', 'salary_grade']);
    return response()->json($designations);
})->middleware('auth')->name('admin.departments.designations');

Route::get('/admin/departments/export', function () {
    try {
        $departments = \App\Models\Department::orderBy('name')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=departments_' . now()->format('Y-m-d') . '.csv',
        ];

        $callback = function () use ($departments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, ['Code', 'Department / Office', 'Department Head', 'Personnel Count', 'Status', 'Description']);
            foreach ($departments as $dept) {
                fputcsv($file, [
                    $dept->code,
                    $dept->name,
                    $dept->head,
                    $dept->personnel_count,
                    $dept->status,
                    $dept->description ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    } catch (\Exception $e) {
        return redirect()->route('admin.departments')->with('export_error', $e->getMessage());
    }
})->middleware('auth')->name('admin.departments.export');

Route::get('/admin/designations/export', function () {
    try {
        $designations = \App\Models\Designation::with('department')->orderBy('title')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=designations_' . now()->format('Y-m-d') . '.csv',
        ];

        $callback = function () use ($designations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, ['Title', 'Department', 'Department Code', 'Salary Grade', 'Monthly Rate', 'Employment Type', 'Description']);
            foreach ($designations as $d) {
                fputcsv($file, [
                    $d->title,
                    $d->department->name ?? 'N/A',
                    $d->department->code ?? 'N/A',
                    $d->salary_grade ?? '',
                    $d->monthly_rate ?? '',
                    $d->employment_type ?? '',
                    $d->description ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    } catch (\Exception $e) {
        return redirect()->route('admin.departments')->with('export_error', $e->getMessage());
    }
})->middleware('auth')->name('admin.designations.export');

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
    $header = fgetcsv($file);

    $imported = 0;
    $skipped  = [];

    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 5) { $skipped[] = '(invalid row — missing columns)'; continue; }

        [$code, $name, $head, $personnel_count, $status, $description] = array_pad($row, 6, null);
        $code = strtoupper(trim($code ?? ''));

        if (!$code || !$name || !$head) { $skipped[] = $code ?: '(empty code)'; continue; }
        if (!in_array($status, ['Active', 'Inactive'])) $status = 'Active';

        if (\App\Models\Department::where('code', $code)->exists()) {
            $skipped[] = "{$code} — " . trim($name) . ' (already exists)';
            continue;
        }

        \App\Models\Department::create([
            'code'            => $code,
            'name'            => trim($name),
            'head'            => trim($head),
            'personnel_count' => (int) ($personnel_count ?? 0),
            'status'          => $status,
            'description'     => $description ? trim($description) : null,
        ]);
        $imported++;
    }

    fclose($file);

    return redirect()->route('admin.departments')
        ->with('import_imported', $imported)
        ->with('import_skipped', $skipped)
        ->with('import_type', 'department');
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

// ✅ NEW: Chatbot with Laravel Session Integration
Route::get('/admin/chatbot', function () {
    return view('admin.chatbot');
})->middleware('auth')->name('admin.chatbot');

// Chatbot API
Route::post('/api/chatbot', [\App\Http\Controllers\ChatbotController::class, 'chat'])->middleware('auth');

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
