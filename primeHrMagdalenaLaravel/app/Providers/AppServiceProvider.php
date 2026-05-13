<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\AccreditedHoursLog;
use App\Observers\AccreditedHoursLogObserver;

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
        
        // Share authenticated user data with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $employee = $user->employee;
                
                $userData = [
                    'authUser' => $user,
                    'authEmployee' => $employee,
                    'authFullName' => $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'User',
                    'authInitials' => $employee ? strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) : 'U',
                    'authEmployeeId' => $employee->employee_id ?? 'N/A',
                    'authRole' => ucfirst($user->role ?? 'Employee'),
                ];
                
                $view->with($userData);
            }
        });
    }
}
