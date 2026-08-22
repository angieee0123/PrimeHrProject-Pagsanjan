<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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
        // `Registered` is deliberately NOT wired to
        // `SendEmailVerificationNotification` here. The framework already
        // registers that pairing itself, in
        // `Foundation\Support\Providers\EventServiceProvider::configureEmailVerification()`
        // — and its "is it already registered?" guard only inspects the
        // `$listen` *array*, so a manual `Event::listen()` call is invisible
        // to it and the framework adds a second copy anyway. The result was
        // two verification emails for every employee registered.

        // The framework's default verification mail is written for someone who
        // just signed themselves up: "Please click the button below to verify
        // your email address", no sender, no context. Here the account was
        // created *for* the employee by an HR administrator, and a second mail
        // carrying their password is landing in the same inbox at the same
        // moment. Unexplained, that pair reads like a phishing attempt — which
        // is exactly the message an employee is trained not to click.
        //
        // So the copy names the municipality, says who created the account and
        // why two messages arrived, and states the order: verify here, sign in
        // with the other one. Overriding the notification's mail rather than
        // subclassing it keeps `sendEmailVerificationNotification()` — used by
        // both registration and the "resend" route — on one body of copy.
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $appName = config('app.name');

            return (new MailMessage)
                ->subject("Verify your email address · {$appName}")
                ->greeting('Verify your email address')
                ->line("An account has been created for you on the {$appName} by the "
                    . 'Human Resources office of the Municipality of Pagsanjan.')
                ->line('Confirm this is your email address to activate it.')
                ->action('Verify email address', $url)
                ->line('A separate email contains the username and password you will '
                    . 'use to sign in. Verify here first, then sign in with those.')
                ->line('If you were not expecting this, no account action is needed — '
                    . 'ignore this email and tell the HR office.');
        });

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
                    $activeRole = $user->primaryRole() ?? 'employee';
                }

                $userData = [
                    'authUser' => $user,
                    'authEmployee' => $employee,
                    'authFullName' => $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'User',
                    'authInitials' => $employee ? strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) : 'U',
                    'authEmployeeId' => $employee->employee_id ?? 'N/A',
                    'authRole' => ucfirst($activeRole),
                    'authRoles' => $user->normalizedRoles(),
                    // Permanent, Temporary, Coterminous, Casual, and Contractual all get the
                    // full leave-and-benefits experience; only Job Order does not.
                    'isPermanent' => $employmentStatus !== null && $employmentStatus !== 'Job Order',
                ];

                $view->with($userData);
            }
        });
    }
}
