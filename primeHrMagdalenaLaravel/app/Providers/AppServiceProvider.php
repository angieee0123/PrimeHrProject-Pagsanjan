<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
