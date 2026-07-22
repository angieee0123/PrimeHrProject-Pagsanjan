<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\AccreditedHoursLog;
use App\Observers\AccreditedHoursLogObserver;
use App\Models\LeaveApplication;
use App\Observers\LeaveApplicationObserver;
use App\Models\TravelOrder;
use App\Observers\TravelOrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AccreditedHoursLog::observe(AccreditedHoursLogObserver::class);
        LeaveApplication::observe(LeaveApplicationObserver::class);
        TravelOrder::observe(TravelOrderObserver::class);
        
        // Share authenticated user data with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $employee = $user->employee;
                $employmentStatus = $employee->employmentDetail->employment_status ?? null;

                $activeRole = session('active_role');
                if (!$activeRole || !$user->hasRole($activeRole)) {
                    $activeRole = $user->roles[0] ?? 'employee';
                }

                $userData = [
                    'authUser' => $user,
                    'authEmployee' => $employee,
                    'authFullName' => $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'User',
                    'authInitials' => $employee ? strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) : 'U',
                    'authEmployeeId' => $employee->employee_id ?? 'N/A',
                    'authRole' => ucfirst($activeRole),
                    'authRoles' => $user->roles ?? [],
                    // Permanent, Temporary, Coterminous, Casual, and Contractual all get the
                    // full leave-and-benefits experience; only Job Order does not.
                    'isPermanent' => $employmentStatus !== null && $employmentStatus !== 'Job Order',
                ];

                $view->with($userData);
            }
        });
    }
}
