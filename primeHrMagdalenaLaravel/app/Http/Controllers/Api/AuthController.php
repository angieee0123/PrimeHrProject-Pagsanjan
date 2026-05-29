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

        // Check if user is active (matching web login - uses 'Active' with capital A)
        if ($user->status !== 'Active') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account is not active. Please contact HR.'],
            ]);
        }

        // Load employee data with relationships (same as web login)
        $user->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');

        // Create token for mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'employee_id' => $user->employee_id,
                    'status' => $user->status,
                ],
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
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
                    'department_code' => $user->employee->employmentDetail?->departmentRelation?->code,
                    'designation' => $user->employee->employmentDetail?->designationRelation?->title,
                    'designation_code' => $user->employee->employmentDetail?->designationRelation?->code,
                    'appointment_date' => $user->employee->employmentDetail?->appointment_date,
                ] : null,
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
        $user->load('employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'employee_id' => $user->employee_id,
                ],
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'first_name' => $user->employee->first_name,
                    'middle_name' => $user->employee->middle_name,
                    'last_name' => $user->employee->last_name,
                    'suffix' => $user->employee->suffix,
                    'full_name' => trim($user->employee->first_name . ' ' . 
                                       ($user->employee->middle_name ? $user->employee->middle_name . ' ' : '') . 
                                       $user->employee->last_name . 
                                       ($user->employee->suffix ? ' ' . $user->employee->suffix : '')),
                    'employment_status' => $user->employee->employmentDetail?->employment_status,
                    'department' => $user->employee->employmentDetail?->departmentRelation?->department_name,
                    'designation' => $user->employee->employmentDetail?->designationRelation?->designation_name,
                ] : null,
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
