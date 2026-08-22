<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('user.login');
    }

    public function login(Request $request)
    {
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

            // Every dashboard now sits behind `EnsureEmailIsVerifiedForArea`,
            // so send an unverified account straight to the notice rather than
            // let it bounce off the page it was aimed at — or, for an account
            // holding several roles, off the role picker and *then* the page.
            // The gate is the middleware; this is only the shorter route to it.
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

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
                session(['active_role' => $user->primaryRole()]);
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
    }

    public function showSelectRole()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return redirect()->route('login');
        }

        $dashboardRoutes = $user->dashboardRoutes();
        if (count($dashboardRoutes) <= 1) {
            return redirect()->route($dashboardRoutes[0] ?? 'employee.dashboard');
        }

        $options = collect($user->normalizedRoles())
            ->unique()
            ->map(fn ($role) => ['role' => $role, 'route' => User::dashboardRouteForRole($role)])
            ->filter(fn ($option) => $option['route'] !== null)
            ->unique('route')
            ->values();

        return view('user.select-role', ['options' => $options]);
    }

    public function selectRole(Request $request)
    {
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
    }

    public function showForgotPassword()
    {
        return view('user.forgot-password');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
