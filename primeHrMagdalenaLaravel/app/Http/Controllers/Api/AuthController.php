<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt authentication using Laravel's Auth::attempt (same as web login)
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password. Please try again.'],
            ]);
        }

        $user = Auth::user();

        // Check if user instance is valid
        if (!$user instanceof User) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password. Please try again.'],
            ]);
        }

        // Auth::attempt only proves the password is right. An account the admin
        // has not activated yet — or has deactivated — must not receive a token.
        if (!$user->isActive()) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact your administrator to activate it.',
            ], 403);
        }

        // Load employee data with relationships (same as web login)
        $user->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');

        // Determine user type (same logic as routes/web.php login.post)
        $userType = 'joborder';
        $isPermanent = false;

        if ($user->email === 'admin@gmail.com' || $user->hasRole('admin')) {
            $userType = 'admin';
        } elseif ($user->hasRole('hr')) {
            $userType = 'hr';
        } elseif ($user->employee && $user->employee->employmentDetail) {
            $employmentStatus = $user->employee->employmentDetail->employment_status;

            if ($employmentStatus === 'Permanent') {
                $userType = 'permanent';
                $isPermanent = true;
            }
        }

        // Fallback for the legacy hardcoded permanent test account
        if (!$isPermanent && $user->email === 'permanent@gmail.com') {
            $userType = 'permanent';
            $isPermanent = true;
        }

        // Get latest payroll data for permanent employees
        $payrollData = null;
        if ($isPermanent && $user->employee) {
            $latestPayslip = \App\Models\SalaryComputation::where('employee_id', $user->employee->id)
                ->where('status', 'approved')
                ->orderBy('period_end', 'desc')
                ->first();

            if ($latestPayslip) {
                $deductionBreakdown = $latestPayslip->deduction_breakdown;
                if (is_string($deductionBreakdown)) {
                    $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
                } elseif (!is_array($deductionBreakdown)) {
                    $deductionBreakdown = [];
                }

                $payrollData = [
                    'period_start' => $latestPayslip->period_start->format('Y-m-d'),
                    'period_end' => $latestPayslip->period_end->format('Y-m-d'),
                    'pay_date' => $latestPayslip->pay_date ? $latestPayslip->pay_date->format('Y-m-d') : null,
                    'monthly_rate' => (float) $latestPayslip->monthly_rate,
                    'daily_rate' => (float) $latestPayslip->daily_rate,
                    'total_days_present' => $latestPayslip->total_days_present,
                    'basic_pay' => (float) $latestPayslip->basic_pay,
                    'ot_pay' => (float) $latestPayslip->ot_pay,
                    'gross_pay' => (float) $latestPayslip->gross_pay,
                    'late_deduction' => (float) $latestPayslip->late_deduction,
                    'undertime_deduction' => (float) $latestPayslip->undertime_deduction,
                    'other_deductions' => (float) $latestPayslip->other_deductions,
                    'deduction_breakdown' => $deductionBreakdown,
                    'total_deductions' => (float) ($latestPayslip->late_deduction + $latestPayslip->undertime_deduction + $latestPayslip->other_deductions),
                    'net_pay' => (float) $latestPayslip->net_pay,
                    'status' => $latestPayslip->status,
                ];
            }
        }

        // Create token for mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

        $displayName = $user->name;
        if (!$displayName && $user->employee) {
            $displayName = trim($user->employee->first_name . ' ' . $user->employee->last_name);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user_type' => $userType,
                'is_permanent' => $isPermanent,
                'user' => [
                    'id' => $user->id,
                    'name' => $displayName,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->roles[0] ?? null,
                    'roles' => $user->roles,
                    'employee_id' => $user->employee_id,
                    'status' => $user->status,
                ],
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'employee_id' => $user->employee->employee_id,
                    'first_name' => $user->employee->first_name,
                    'middle_name' => $user->employee->middle_name,
                    'last_name' => $user->employee->last_name,
                    'suffix' => $user->employee->suffix,
                    'full_name' => trim($user->employee->first_name . ' ' . 
                                       ($user->employee->middle_name ? $user->employee->middle_name . ' ' : '') . 
                                       $user->employee->last_name . 
                                       ($user->employee->suffix ? ' ' . $user->employee->suffix : '')),
                    'birth_date' => $user->employee->birth_date,
                    'sex' => $user->employee->sex,
                    'civil_status' => $user->employee->civil_status,
                    'employment_status' => $user->employee->employmentDetail?->employment_status,
                    'department' => $user->employee->employmentDetail?->departmentRelation?->name,
                    'department_code' => $user->employee->employmentDetail?->departmentRelation?->code,
                    'designation' => $user->employee->employmentDetail?->designationRelation?->title,
                    'salary_grade' => $user->employee->employmentDetail?->salary_grade,
                    'step_increment' => $user->employee->employmentDetail?->step_increment,
                    'appointment_date' => $user->employee->employmentDetail?->appointment_date,
                    'monthly_rate' => $user->employee->employmentDetail?->designationRelation?->monthly_rate !== null
                        ? (float) $user->employee->employmentDetail->designationRelation->monthly_rate
                        : null,
                ] : null,
                'payroll' => $payrollData,
            ],
        ], 200);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');

        $userType = 'joborder';
        $isPermanent = false;

        if ($user->email === 'admin@gmail.com' || $user->hasRole('admin')) {
            $userType = 'admin';
        } elseif ($user->hasRole('hr')) {
            $userType = 'hr';
        } elseif ($user->employee && $user->employee->employmentDetail) {
            if ($user->employee->employmentDetail->employment_status === 'Permanent') {
                $userType = 'permanent';
                $isPermanent = true;
            }
        }

        if (!$isPermanent && $user->email === 'permanent@gmail.com') {
            $userType = 'permanent';
            $isPermanent = true;
        }

        $payrollData = null;
        if ($isPermanent && $user->employee) {
            $latestPayslip = \App\Models\SalaryComputation::where('employee_id', $user->employee->id)
                ->where('status', 'approved')
                ->orderBy('period_end', 'desc')
                ->first();

            if ($latestPayslip) {
                $deductionBreakdown = $latestPayslip->deduction_breakdown;
                if (is_string($deductionBreakdown)) {
                    $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
                } elseif (!is_array($deductionBreakdown)) {
                    $deductionBreakdown = [];
                }

                $payrollData = [
                    'period_start' => $latestPayslip->period_start->format('Y-m-d'),
                    'period_end' => $latestPayslip->period_end->format('Y-m-d'),
                    'pay_date' => $latestPayslip->pay_date ? $latestPayslip->pay_date->format('Y-m-d') : null,
                    'monthly_rate' => (float) $latestPayslip->monthly_rate,
                    'daily_rate' => (float) $latestPayslip->daily_rate,
                    'total_days_present' => $latestPayslip->total_days_present,
                    'basic_pay' => (float) $latestPayslip->basic_pay,
                    'ot_pay' => (float) $latestPayslip->ot_pay,
                    'gross_pay' => (float) $latestPayslip->gross_pay,
                    'late_deduction' => (float) $latestPayslip->late_deduction,
                    'undertime_deduction' => (float) $latestPayslip->undertime_deduction,
                    'other_deductions' => (float) $latestPayslip->other_deductions,
                    'deduction_breakdown' => $deductionBreakdown,
                    'total_deductions' => (float) ($latestPayslip->late_deduction + $latestPayslip->undertime_deduction + $latestPayslip->other_deductions),
                    'net_pay' => (float) $latestPayslip->net_pay,
                    'status' => $latestPayslip->status,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_type' => $userType,
                'is_permanent' => $isPermanent,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->roles[0] ?? null,
                    'roles' => $user->roles,
                    'employee_id' => $user->employee_id,
                    'status' => $user->status,
                ],
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'employee_id' => $user->employee->employee_id,
                    'first_name' => $user->employee->first_name,
                    'middle_name' => $user->employee->middle_name,
                    'last_name' => $user->employee->last_name,
                    'suffix' => $user->employee->suffix,
                    'full_name' => trim($user->employee->first_name . ' ' .
                                       ($user->employee->middle_name ? $user->employee->middle_name . ' ' : '') .
                                       $user->employee->last_name .
                                       ($user->employee->suffix ? ' ' . $user->employee->suffix : '')),
                    'employment_status' => $user->employee->employmentDetail?->employment_status,
                    'department' => $user->employee->employmentDetail?->departmentRelation?->name,
                    'designation' => $user->employee->employmentDetail?->designationRelation?->title,
                    'monthly_rate' => $user->employee->employmentDetail?->designationRelation?->monthly_rate !== null
                        ? (float) $user->employee->employmentDetail->designationRelation->monthly_rate
                        : null,
                ] : null,
                'payroll' => $payrollData,
            ],
        ], 200);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
            ],
        ], 200);
    }
}
