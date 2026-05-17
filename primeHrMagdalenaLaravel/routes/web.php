<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmployeeRegistrationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PermanentAttendanceController;

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

        // Eager load employee data with relationships
        $user = Auth::user()->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');

        if ($user->email === 'admin@gmail.com' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'hr') {
            return redirect()->route('admin.dashboard');
        }

        // Check if employee has permanent employment status
        if ($user->employee && $user->employee->employmentDetail) {
            $employmentStatus = $user->employee->employmentDetail->employment_status;

            if ($employmentStatus === 'Permanent') {
                return redirect()->route('permanent.dashboard');
            }
        }

        // Fallback for explicit permanent role or email
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

Route::get('/permanent/attendance', [PermanentAttendanceController::class, 'index'])->middleware('auth')->name('permanent.attendance');
Route::get('/permanent/attendance/detailed', [PermanentAttendanceController::class, 'detailedDTR'])->middleware('auth')->name('permanent.attendance.detailed');

Route::get('/permanent/payslip', function () {
    return view('permanent.payslip.permanentPayslip');
})->middleware('auth')->name('permanent.payslip');

Route::get('/permanent/leave', function () {
    $employee = auth()->user()->employee;

    if (!$employee) {
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)
            ->orderBy('leave_name')
            ->get();

        $leaveApplications = collect();
        $employeeTransactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'))
            ->with('warning', 'Employee record not found. Displaying leave types without balance information.');
    }

    $currentYear = now()->year;

    // Only show leave types that have been assigned to the employee (total_credits > 0)
    $leaveTypes = \App\Models\LeaveType::where('is_active', true)
        ->with(['leaveBalances' => function($query) use ($employee, $currentYear) {
            $query->where('employee_id', $employee->id)
                  ->where('year', $currentYear)
                  ->where('total_credits', '>', 0); // Only show assigned leaves
        }])
        ->orderBy('leave_name')
        ->get()
        ->filter(function($leaveType) {
            // Filter out leave types that don't have any balance records
            return $leaveType->leaveBalances->isNotEmpty();
        })
        ->values(); // Reset array keys

    $leaveApplications = \App\Models\LeaveApplication::where('employee_id', $employee->id)
        ->with('leaveType')
        ->orderBy('created_at', 'desc')
        ->get();

    // Fetch employee transactions with filtering and sorting
    $transactionQuery = \App\Models\LeaveTransaction::where('employee_id', $employee->id)
        ->with('processedBy.employee');

    if (request('filter_type')) {
        $transactionQuery->where('transaction_type', request('filter_type'));
    }
    if (request('filter_leave_code')) {
        $transactionQuery->where('leave_code', request('filter_leave_code'));
    }
    if (request('filter_date')) {
        $transactionQuery->whereDate('transaction_date', request('filter_date'));
    }

    $sortBy = request('sort_by', 'transaction_date');
    $sortOrder = request('sort_order', 'desc');
    $allowedSortColumns = ['transaction_date', 'leave_code', 'transaction_type', 'amount', 'balance_before', 'balance_after'];

    if (in_array($sortBy, $allowedSortColumns)) {
        $transactionQuery->orderBy($sortBy, $sortOrder);
    } else {
        $transactionQuery->orderBy('transaction_date', 'desc');
    }

    $transactionQuery->orderBy('created_at', 'desc');

    $employeeTransactions = $transactionQuery->paginate(15)->appends(request()->except('page'));

    return view('permanent.leaveandbenefits.permanentLeaveandbenefits', compact('leaveTypes', 'leaveApplications', 'employeeTransactions'));
})->middleware('auth')->name('permanent.leave');

// Leave Application Routes
Route::post('/leave/store', [LeaveController::class, 'store'])->middleware('auth')->name('leave.store');
Route::post('/leave/{id}/cancel', [LeaveController::class, 'cancel'])->middleware('auth')->name('leave.cancel');

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

Route::get('/admin/payroll', function (\Illuminate\Http\Request $request) {
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

Route::post('/admin/payroll/generate', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'pay_date' => 'required|date',
        'payroll_type' => 'required|in:regular,13th_month,bonus,special',
        'department' => 'nullable|string',
        'employment_status' => 'nullable|string',
        'include_deductions' => 'nullable|boolean',
        'include_loans' => 'nullable|boolean',
        'include_overtime' => 'nullable|boolean',
    ]);

    try {
        // Get employees based on filters
        $employeesQuery = \App\Models\Employee::with([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation'
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
        $processedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            // Get all attendance records for the period
            $attendances = \App\Models\Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$data['start_date'], $data['end_date']])
                ->get();

            foreach ($attendances as $attendance) {
                // Check if accredited hours log exists
                $accreditedLog = \App\Models\AccreditedHoursLog::where('attendance_id', $attendance->id)->first();
                
                if (!$accreditedLog) {
                    $errors[] = "No accredited hours log for {$employee->first_name} {$employee->last_name} on {$attendance->date}";
                    continue;
                }

                // Check if salary computation already exists
                $existingComputation = \App\Models\DailySalaryComputation::where('accredited_hours_log_id', $accreditedLog->id)->first();
                
                if (!$existingComputation) {
                    // Generate salary computation
                    \App\Models\DailySalaryComputation::computeFromAccreditedLog($accreditedLog);
                    $processedCount++;
                }
            }
        }

        $message = "Payroll generated successfully! Processed {$processedCount} record(s) for period " . 
                   date('M d, Y', strtotime($data['start_date'])) . ' to ' . 
                   date('M d, Y', strtotime($data['end_date']));

        if (count($errors) > 0) {
            $message .= " (" . count($errors) . " records skipped due to missing data)";
        }

        return redirect()->route('admin.payroll', ['tab' => 'register', 'start_date' => $data['start_date'], 'end_date' => $data['end_date']])
            ->with('success', $message);

    } catch (\Exception $e) {
        return redirect()->route('admin.payroll', ['tab' => 'generate'])
            ->with('error', 'Failed to generate payroll: ' . $e->getMessage());
    }
})->middleware('auth')->name('admin.payroll.generate');

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

Route::get('/admin/deductions', function () {
    // Get all employee deductions with relationships
    $employeeDeductions = \App\Models\EmployeeDeduction::with([
        'employee.employmentDetail.departmentRelation',
        'deductionType'
    ])
    ->orderBy('created_at', 'desc')
    ->get();

    // Get only loans (category = LOAN)
    $loans = \App\Models\EmployeeDeduction::with([
        'employee.employmentDetail.departmentRelation',
        'deductionType'
    ])
    ->whereHas('deductionType', function($q) {
        $q->where('category', 'LOAN');
    })
    ->orderBy('created_at', 'desc')
    ->get();

    // Get employees with active deductions for schedules tab
    $employeesWithDeductions = \App\Models\Employee::with([
        'employmentDetail.departmentRelation',
        'deductions' => function($q) {
            $q->where('status', 'ACTIVE')->with('deductionType');
        }
    ])
    ->whereHas('deductions', function($q) {
        $q->where('status', 'ACTIVE');
    })
    ->orderBy('last_name')
    ->get()
    ->map(function($employee) {
        $deductions = $employee->deductions;
        $loansCount = $deductions->filter(function($d) {
            return $d->deductionType->category === 'LOAN';
        })->count();
        $deductionsCount = $deductions->count();

        return [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'department' => $employee->employmentDetail->departmentRelation->name ?? 'N/A',
            'deductions_count' => $deductionsCount,
            'loans_count' => $loansCount,
            'updated_at' => $deductions->max('updated_at'),
        ];
    });

    // Get statistics
    $stats = [
        'total_types' => \App\Models\DeductionType::where('is_active', true)->count(),
        'mandatory_count' => \App\Models\DeductionType::where('category', 'MANDATORY')->where('is_active', true)->count(),
        'loan_count' => \App\Models\DeductionType::where('category', 'LOAN')->where('is_active', true)->count(),
        'active_loans' => \App\Models\EmployeeDeduction::whereHas('deductionType', function($q) {
            $q->where('category', 'LOAN');
        })->where('status', 'ACTIVE')->count(),
        'total_outstanding' => \App\Models\EmployeeDeduction::whereHas('deductionType', function($q) {
            $q->where('category', 'LOAN');
        })->where('status', 'ACTIVE')->sum('remaining_balance'),
        'transactions_this_month' => 0, // PayrollDeduction table is empty
    ];

    return view('admin.deductions.adminDeductions', compact('employeeDeductions', 'loans', 'employeesWithDeductions', 'stats'));
})->middleware('auth')->name('admin.deductions');

// Deduction Type Routes
Route::post('/admin/deductions/types', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'code' => 'required|string|max:50|unique:deduction_types,code',
        'name' => 'required|string|max:100',
        'category' => 'required|in:MANDATORY,LOAN,OTHER',
        'computation_type' => 'required|in:PERCENTAGE,FIXED,CUSTOM',
        'rate' => 'nullable|numeric|min:0',
        'base_salary' => 'nullable|in:BASIC,GROSS,MONTHLY,CUSTOM',
        'max_amount' => 'nullable|numeric|min:0',
        'is_active' => 'required|boolean',
        'deducted_from_employee' => 'required|boolean',
        'description' => 'nullable|string',
    ]);

    // Map form fields to database fields
    $deductionData = [
        'code' => $data['code'],
        'name' => $data['name'],
        'category' => $data['category'],
        'computation_type' => $data['computation_type'],
        'percentage_rate' => $data['rate'] ?? null,
        'base_salary_type' => $data['base_salary'] ?? null,
        'max_amount' => $data['max_amount'] ?? null,
        'is_active' => $data['is_active'],
        'deducted_from_employee' => $data['deducted_from_employee'],
    ];

    \App\Models\DeductionType::create($deductionData);

    return redirect()->route('admin.deductions')
        ->with('success', 'Deduction type "' . $data['name'] . '" added successfully!');
})->middleware('auth')->name('admin.deductions.types.store');

Route::put('/admin/deductions/types/{code}', function (\Illuminate\Http\Request $request, $code) {
    $deductionType = \App\Models\DeductionType::where('code', $code)->firstOrFail();

    $data = $request->validate([
        'name' => 'required|string|max:100',
        'category' => 'required|in:MANDATORY,LOAN,OTHER',
        'computation_type' => 'required|in:PERCENTAGE,FIXED,CUSTOM',
        'rate' => 'nullable|numeric|min:0',
        'base_salary' => 'nullable|in:BASIC,GROSS,MONTHLY,CUSTOM',
        'max_amount' => 'nullable|numeric|min:0',
        'is_active' => 'required|boolean',
        'deducted_from_employee' => 'required|boolean',
        'description' => 'nullable|string',
    ]);

    $deductionType->update([
        'name' => $data['name'],
        'category' => $data['category'],
        'computation_type' => $data['computation_type'],
        'percentage_rate' => $data['rate'] ?? null,
        'base_salary_type' => $data['base_salary'] ?? null,
        'max_amount' => $data['max_amount'] ?? null,
        'is_active' => $data['is_active'],
        'deducted_from_employee' => $data['deducted_from_employee'],
    ]);

    return redirect()->route('admin.deductions')->with('success', 'Deduction type updated successfully.');
})->middleware('auth')->name('admin.deductions.types.update');

// Employee Deduction Routes
Route::post('/admin/deductions/employee', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'deduction_type_id' => 'required',
        'other_provider_name' => 'nullable|string',
        'other_loan_type' => 'nullable|string',
        'amount' => 'nullable|numeric|min:0',
        'total_amount' => 'nullable|numeric|min:0',
        'installment_amount' => 'nullable|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required|in:ACTIVE,SUSPENDED,COMPLETED',
        'remarks' => 'nullable|string',
    ]);

    // Handle "Other" provider - create a custom deduction type
    if ($data['deduction_type_id'] === 'OTHER') {
        $providerName = $data['other_provider_name'] ?? 'External Provider';
        $loanDescription = $data['other_loan_type'] ?? 'Custom Loan';

        // Create unique code from provider name
        $code = 'LOAN_' . strtoupper(str_replace([' ', '-', '.'], '_', $providerName));

        // Create or find the custom deduction type
        $customDeduction = \App\Models\DeductionType::firstOrCreate(
            ['code' => $code],
            [
                'name' => $providerName . ' - ' . $loanDescription,
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'is_active' => true,
            ]
        );

        $data['deduction_type_id'] = $customDeduction->id;
        $data['remarks'] = trim(($data['remarks'] ?? '') . " [Provider: {$providerName}, Type: {$loanDescription}]");
    }

    // Set remaining balance equal to total amount for loans
    if ($data['total_amount'] ?? null) {
        $data['remaining_balance'] = $data['total_amount'];
    }

    // Remove non-database fields
    unset($data['other_provider_name'], $data['other_loan_type']);

    \App\Models\EmployeeDeduction::create($data);

    return redirect()->route('admin.deductions')->with('success', 'Loan assigned successfully.');
})->middleware('auth')->name('admin.deductions.employee.store');

Route::put('/admin/deductions/employee/{id}', function (\Illuminate\Http\Request $request, $id) {
    $employeeDeduction = \App\Models\EmployeeDeduction::findOrFail($id);

    $data = $request->validate([
        'amount' => 'nullable|numeric|min:0',
        'remaining_balance' => 'nullable|numeric|min:0',
        'installment_amount' => 'nullable|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required|in:ACTIVE,SUSPENDED,COMPLETED',
        'remarks' => 'nullable|string',
    ]);

    $employeeDeduction->update($data);

    return redirect()->route('admin.deductions')->with('success', 'Employee deduction updated successfully.');
})->middleware('auth')->name('admin.deductions.employee.update');

// Bulk Assign Deductions Route
Route::post('/admin/deductions/employee/bulk-assign', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'deduction_types' => 'required|array|min:1',
        'deduction_types.*' => 'exists:deduction_types,id',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required|in:ACTIVE,SUSPENDED,COMPLETED',
        'remarks' => 'nullable|string',
    ]);

    $assignedCount = 0;
    $skippedTypes = [];
    $employee = \App\Models\Employee::findOrFail($data['employee_id']);
    $employeeName = $employee->first_name . ' ' . $employee->last_name;

    foreach ($data['deduction_types'] as $deductionTypeId) {
        // Check if employee already has this deduction type active
        $exists = \App\Models\EmployeeDeduction::where('employee_id', $data['employee_id'])
            ->where('deduction_type_id', $deductionTypeId)
            ->where('status', 'ACTIVE')
            ->exists();

        if ($exists) {
            $deductionType = \App\Models\DeductionType::find($deductionTypeId);
            $skippedTypes[] = $deductionType->name;
            continue;
        }

        // Create employee deduction
        \App\Models\EmployeeDeduction::create([
            'employee_id' => $data['employee_id'],
            'deduction_type_id' => $deductionTypeId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'remarks' => $data['remarks'],
        ]);
        $assignedCount++;
    }

    // Build success message
    if ($assignedCount > 0 && count($skippedTypes) > 0) {
        $skippedList = implode(', ', $skippedTypes);
        return redirect()->route('admin.deductions')
            ->with('success', "{$assignedCount} deduction(s) assigned to {$employeeName}. Skipped (already active): {$skippedList}");
    } elseif ($assignedCount > 0) {
        return redirect()->route('admin.deductions')
            ->with('success', "{$assignedCount} deduction(s) assigned to {$employeeName} successfully.");
    } else {
        $skippedList = implode(', ', $skippedTypes);
        return redirect()->route('admin.deductions')
            ->with('warning', "No deductions were assigned. All selected deductions are already active for {$employeeName}: {$skippedList}");
    }
})->middleware('auth')->name('admin.deductions.employee.bulk-assign');

Route::get('/admin/deductions/employee/{id}', function ($id) {
    $deduction = \App\Models\EmployeeDeduction::with(['employee', 'deductionType'])->findOrFail($id);
    return response()->json($deduction);
})->middleware('auth')->name('admin.deductions.employee.show');

// Get active deductions for an employee
Route::get('/admin/deductions/employee/{employeeId}/active', function ($employeeId) {
    $deductions = \App\Models\EmployeeDeduction::where('employee_id', $employeeId)
        ->where('status', 'ACTIVE')
        ->with('deductionType')
        ->get()
        ->map(function($ed) {
            return [
                'id' => $ed->deduction_type_id,
                'name' => $ed->deductionType->name,
                'code' => $ed->deductionType->code,
            ];
        });

    return response()->json(['deductions' => $deductions]);
})->middleware('auth')->name('admin.deductions.employee.active');

// Delete employee deduction
Route::delete('/admin/deductions/employee/{id}/delete', function ($id) {
    $deduction = \App\Models\EmployeeDeduction::with(['employee', 'deductionType'])->findOrFail($id);
    $employeeName = $deduction->employee->first_name . ' ' . $deduction->employee->last_name;
    $deductionName = $deduction->deductionType->name;

    $deduction->delete();

    return redirect()->route('admin.deductions')
        ->with('success', "Deduction '{$deductionName}' removed from {$employeeName} successfully.");
})->middleware('auth')->name('admin.deductions.employee.delete');

// Export employee deductions
Route::get('/admin/deductions/employee/export', function () {
    $deductions = \App\Models\EmployeeDeduction::with([
        'employee.employmentDetail.departmentRelation',
        'deductionType'
    ])->orderBy('created_at', 'desc')->get();

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=employee_deductions_' . now()->format('Y-m-d') . '.csv',
    ];

    $callback = function () use ($deductions) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($file, [
            'Employee ID',
            'Employee Name',
            'Department',
            'Deduction Type',
            'Category',
            'Amount/Balance',
            'Total Amount',
            'Start Date',
            'End Date',
            'Status',
            'Remarks'
        ]);

        foreach ($deductions as $d) {
            $employeeName = $d->employee->first_name . ' ' . $d->employee->last_name;
            $department = $d->employee->employmentDetail->departmentRelation->name ?? 'N/A';

            $amount = '';
            if ($d->deductionType->category === 'LOAN') {
                $amount = number_format($d->remaining_balance ?? 0, 2);
            } elseif ($d->deductionType->computation_type === 'PERCENTAGE') {
                $amount = $d->deductionType->percentage_rate . '%';
            } elseif ($d->amount) {
                $amount = number_format($d->amount, 2);
            }

            fputcsv($file, [
                $d->employee->employee_id,
                $employeeName,
                $department,
                $d->deductionType->name,
                $d->deductionType->category,
                $amount,
                $d->total_amount ? number_format($d->total_amount, 2) : '',
                \Carbon\Carbon::parse($d->start_date)->format('Y-m-d'),
                $d->end_date ? \Carbon\Carbon::parse($d->end_date)->format('Y-m-d') : '',
                $d->status,
                $d->remarks ?? ''
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.deductions.employee.export');

// Export loans
Route::get('/admin/deductions/loans/export', function () {
    $loans = \App\Models\EmployeeDeduction::with([
        'employee.employmentDetail.departmentRelation',
        'deductionType.schedules'
    ])
    ->whereHas('deductionType', function($q) {
        $q->where('category', 'LOAN');
    })
    ->orderBy('created_at', 'desc')
    ->get();

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=employee_loans_' . now()->format('Y-m-d') . '.csv',
    ];

    $callback = function () use ($loans) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($file, [
            'Employee ID',
            'Employee Name',
            'Department',
            'Loan Type',
            'Provider',
            'Total Amount',
            'Amount Paid',
            'Remaining Balance',
            'Progress %',
            'Monthly Installment',
            'Schedule',
            '1st Cutoff Amount',
            '2nd Cutoff Amount',
            'Months Remaining',
            'Start Date',
            'End Date',
            'Status',
            'Remarks'
        ]);

        foreach ($loans as $loan) {
            $employeeName = $loan->employee->first_name . ' ' . $loan->employee->last_name;
            $department = $loan->employee->employmentDetail->departmentRelation->name ?? 'N/A';

            // Determine provider
            $provider = 'Other';
            if (str_contains($loan->deductionType->code, 'GSIS')) {
                $provider = 'GSIS';
            } elseif (str_contains($loan->deductionType->code, 'PAGIBIG')) {
                $provider = 'Pag-IBIG';
            }

            $totalAmount = $loan->total_amount ?? 0;
            $remainingBalance = $loan->remaining_balance ?? 0;
            $amountPaid = $totalAmount - $remainingBalance;
            $progress = $totalAmount > 0 ? (($amountPaid / $totalAmount) * 100) : 0;
            $installment = $loan->installment_amount ?? 0;
            $monthsRemaining = $installment > 0 ? ceil($remainingBalance / $installment) : 0;

            // Get schedule and calculate per-cutoff
            $schedule = $loan->deductionType->schedules->first();
            $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';

            if ($cutoffSchedule === '1ST_ONLY') {
                $perCutoff1st = $installment;
                $perCutoff2nd = 0;
            } elseif ($cutoffSchedule === '2ND_ONLY') {
                $perCutoff1st = 0;
                $perCutoff2nd = $installment;
            } elseif ($cutoffSchedule === 'BOTH_FULL') {
                $perCutoff1st = $installment;
                $perCutoff2nd = $installment;
            } else { // BOTH_SPLIT
                $perCutoff1st = $installment / 2;
                $perCutoff2nd = $installment / 2;
            }

            fputcsv($file, [
                $loan->employee->employee_id,
                $employeeName,
                $department,
                $loan->deductionType->name,
                $provider,
                number_format($totalAmount, 2),
                number_format($amountPaid, 2),
                number_format($remainingBalance, 2),
                number_format($progress, 2),
                number_format($installment, 2),
                $cutoffSchedule,
                number_format($perCutoff1st, 2),
                number_format($perCutoff2nd, 2),
                $monthsRemaining,
                \Carbon\Carbon::parse($loan->start_date)->format('Y-m-d'),
                $loan->end_date ? \Carbon\Carbon::parse($loan->end_date)->format('Y-m-d') : '',
                $loan->status,
                $loan->remarks ?? ''
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.deductions.loans.export');

// Get employee deductions for schedule modal
Route::get('/admin/deductions/employee/{employeeId}/deductions', function ($employeeId) {
    $deductions = \App\Models\EmployeeDeduction::where('employee_id', $employeeId)
        ->where('status', 'ACTIVE')
        ->with('deductionType.schedules')
        ->get()
        ->map(function($ed) {
            $schedule = $ed->deductionType->schedules->first();
            $defaultSchedule = $schedule ? $schedule->cutoff_schedule : '1ST_ONLY';
            
            // Use custom schedule if set, otherwise use default
            $currentSchedule = $ed->custom_cutoff_schedule ?? $defaultSchedule;

            return [
                'id' => $ed->id,
                'deduction_type_id' => $ed->deduction_type_id,
                'name' => $ed->deductionType->name,
                'code' => $ed->deductionType->code,
                'category' => $ed->deductionType->category,
                'computation_type' => $ed->deductionType->computation_type,
                'amount' => $ed->installment_amount ?? $ed->amount ?? ($ed->deductionType->percentage_rate ? $ed->deductionType->percentage_rate . '%' : 'Auto'),
                'current_schedule' => $currentSchedule,
                'has_custom_schedule' => $ed->custom_cutoff_schedule !== null,
                'default_schedule' => $defaultSchedule,
            ];
        });

    return response()->json(['deductions' => $deductions]);
})->middleware('auth')->name('admin.deductions.employee.deductions');

// Export schedules
Route::get('/admin/deductions/schedules/export', function () {
    $employees = \App\Models\Employee::with([
        'employmentDetail.departmentRelation',
        'deductions' => function($q) {
            $q->where('status', 'ACTIVE')->with('deductionType.schedules');
        }
    ])
    ->whereHas('deductions', function($q) {
        $q->where('status', 'ACTIVE');
    })
    ->orderBy('last_name')
    ->get();

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=deduction_schedules_' . now()->format('Y-m-d') . '.csv',
    ];

    $callback = function () use ($employees) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($file, [
            'Employee ID',
            'Employee Name',
            'Department',
            'Deduction Type',
            'Category',
            'Amount',
            'Cutoff Schedule',
            'Schedule Type',
            'Status'
        ]);

        foreach ($employees as $employee) {
            $employeeName = $employee->first_name . ' ' . $employee->last_name;
            $department = $employee->employmentDetail->departmentRelation->name ?? 'N/A';

            foreach ($employee->deductions as $deduction) {
                if (!$deduction->deductionType->deducted_from_employee) {
                    continue;
                }
                
                $schedule = $deduction->deductionType->schedules->first();
                $defaultSchedule = $schedule ? $schedule->cutoff_schedule : 'N/A';
                
                // Use custom schedule if set, otherwise use default
                $cutoffSchedule = $deduction->custom_cutoff_schedule ?? $defaultSchedule;
                $scheduleType = $deduction->custom_cutoff_schedule ? 'Custom' : 'Default';

                $amount = '';
                if ($deduction->deductionType->category === 'LOAN') {
                    $amount = '₱' . number_format($deduction->installment_amount ?? 0, 2) . '/month';
                } elseif ($deduction->deductionType->computation_type === 'PERCENTAGE') {
                    $amount = $deduction->deductionType->percentage_rate . '%';
                } elseif ($deduction->amount) {
                    $amount = '₱' . number_format($deduction->amount, 2);
                }

                fputcsv($file, [
                    $employee->employee_id,
                    $employeeName,
                    $department,
                    $deduction->deductionType->name,
                    $deduction->deductionType->category,
                    $amount,
                    $cutoffSchedule,
                    $scheduleType,
                    $deduction->status
                ]);
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->middleware('auth')->name('admin.deductions.schedules.export');

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

// Deduction Schedule Management Routes
Route::post('/admin/deductions/schedules/update', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'start_month' => 'required|date_format:Y-m',
        'end_month' => 'required|date_format:Y-m',
        'schedules' => 'required|array|min:1',
        'schedules.*.deduction_id' => 'required|exists:employee_deductions,id',
        'schedules.*.cutoff' => 'required|in:1ST,2ND,BOTH,DEFAULT',
    ]);

    $updatedCount = 0;
    
    foreach ($data['schedules'] as $schedule) {
        $employeeDeduction = \App\Models\EmployeeDeduction::findOrFail($schedule['deduction_id']);
        
        // Map cutoff values to schedule enum
        if ($schedule['cutoff'] === 'DEFAULT') {
            // Remove custom schedule, use deduction type's default
            $employeeDeduction->update(['custom_cutoff_schedule' => null]);
        } else {
            $cutoffSchedule = match($schedule['cutoff']) {
                '1ST' => '1ST_ONLY',
                '2ND' => '2ND_ONLY',
                'BOTH' => 'BOTH_SPLIT',
            };
            
            // Set custom schedule for this specific employee deduction
            $employeeDeduction->update(['custom_cutoff_schedule' => $cutoffSchedule]);
        }
        
        $updatedCount++;
    }
    
    $employee = \App\Models\Employee::findOrFail($data['employee_id']);
    $employeeName = $employee->first_name . ' ' . $employee->last_name;
    
    return redirect()->route('admin.deductions')
        ->with('success', "Custom deduction schedules updated for {$employeeName}. {$updatedCount} deduction(s) configured successfully.");
})->middleware('auth')->name('admin.deductions.schedules.update');

// Loan Type Management Routes
Route::post('/admin/deductions/loan-types/store', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'provider' => 'required|string',
        'code' => 'required|string|max:50|unique:deduction_types,code',
        'name' => 'required|string|max:100',
        'max_loanable_amount' => 'nullable|numeric|min:0',
        'interest_rate' => 'nullable|numeric|min:0|max:100',
        'max_terms_months' => 'nullable|integer|min:1',
        'is_active' => 'required|boolean',
        'description' => 'nullable|string',
    ]);

    // Create the deduction type for this loan
    $deductionType = \App\Models\DeductionType::create([
        'code' => 'LOAN_' . $data['code'],
        'name' => $data['name'],
        'category' => 'LOAN',
        'computation_type' => 'FIXED',
        'percentage_rate' => $data['interest_rate'] ?? null,
        'max_amount' => $data['max_loanable_amount'] ?? null,
        'is_active' => $data['is_active'],
    ]);

    // Create the loan type record (optional, for additional metadata)
    \App\Models\LoanType::create([
        'code' => $data['code'],
        'name' => $data['name'],
        'deduction_type_id' => $deductionType->id,
        'max_loanable_amount' => $data['max_loanable_amount'] ?? null,
        'interest_rate' => $data['interest_rate'] ?? null,
        'max_terms_months' => $data['max_terms_months'] ?? null,
        'is_active' => $data['is_active'],
    ]);

    return redirect()->route('admin.deductions')
        ->with('success', "Loan type \"{$data['name']}\" registered successfully! It's now available for assignment to employees.");
})->middleware('auth')->name('admin.deductions.loan-types.store');

Route::get('/admin/deductions/types/{code}', function ($code) {
    $deductionType = \App\Models\DeductionType::where('code', $code)->firstOrFail();
    
    // Get employee count
    $employeesCount = \App\Models\EmployeeDeduction::where('deduction_type_id', $deductionType->id)
        ->where('status', 'ACTIVE')
        ->distinct('employee_id')
        ->count();
    
    return response()->json([
        'id' => $deductionType->id,
        'code' => $deductionType->code,
        'name' => $deductionType->name,
        'category' => $deductionType->category,
        'computation_type' => $deductionType->computation_type,
        'percentage_rate' => $deductionType->percentage_rate,
        'max_amount' => $deductionType->max_amount,
        'is_active' => $deductionType->is_active,
        'employees_count' => $employeesCount,
    ]);
})->middleware('auth')->name('admin.deductions.types.show');

Route::put('/admin/deductions/loan-types/{id}', function (\Illuminate\Http\Request $request, $id) {
    $deductionType = \App\Models\DeductionType::findOrFail($id);
    
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'max_loanable_amount' => 'nullable|numeric|min:0',
        'interest_rate' => 'nullable|numeric|min:0|max:100',
        'max_terms_months' => 'nullable|integer|min:1',
        'is_active' => 'required|boolean',
        'description' => 'nullable|string',
    ]);
    
    // Update deduction type
    $deductionType->update([
        'name' => $data['name'],
        'percentage_rate' => $data['interest_rate'] ?? null,
        'max_amount' => $data['max_loanable_amount'] ?? null,
        'is_active' => $data['is_active'],
    ]);
    
    // Update loan type record if exists
    $loanType = \App\Models\LoanType::where('deduction_type_id', $id)->first();
    if ($loanType) {
        $loanType->update([
            'name' => $data['name'],
            'max_loanable_amount' => $data['max_loanable_amount'] ?? null,
            'interest_rate' => $data['interest_rate'] ?? null,
            'max_terms_months' => $data['max_terms_months'] ?? null,
            'is_active' => $data['is_active'],
        ]);
    }
    
    return redirect()->route('admin.deductions')
        ->with('success', "Loan type \"{$data['name']}\" updated successfully!");
})->middleware('auth')->name('admin.deductions.loan-types.update');

Route::delete('/admin/deductions/loan-types/{id}', function ($id) {
    $deductionType = \App\Models\DeductionType::findOrFail($id);
    
    // Check if any employees are using this loan type
    $employeesCount = \App\Models\EmployeeDeduction::where('deduction_type_id', $id)
        ->where('status', 'ACTIVE')
        ->count();
    
    if ($employeesCount > 0) {
        return redirect()->route('admin.deductions')
            ->with('error', "Cannot delete loan type \"{$deductionType->name}\" because it's currently assigned to {$employeesCount} employee(s).");
    }
    
    // Delete the loan type record first
    \App\Models\LoanType::where('deduction_type_id', $id)->delete();
    
    // Then delete the deduction type
    $deductionType->delete();
    
    return redirect()->route('admin.deductions')
        ->with('success', "Loan type \"{$deductionType->name}\" deleted successfully.");
})->middleware('auth')->name('admin.deductions.loan-types.delete');
