<?php

namespace App\Observers;

use App\Models\AccreditedHoursLog;
use App\Models\DailySalaryComputation;

class AccreditedHoursLogObserver
{
    /**
     * Handle the AccreditedHoursLog "created" event.
     */
    public function created(AccreditedHoursLog $accreditedHoursLog): void
    {
        DailySalaryComputation::computeFromAccreditedLog($accreditedHoursLog);
    }

    /**
     * Handle the AccreditedHoursLog "updated" event.
     */
    public function updated(AccreditedHoursLog $accreditedHoursLog): void
    {
        DailySalaryComputation::computeFromAccreditedLog($accreditedHoursLog);
    }

    /**
     * Handle the AccreditedHoursLog "deleted" event.
     */
    public function deleted(AccreditedHoursLog $accreditedHoursLog): void
    {
        DailySalaryComputation::where('accredited_hours_log_id', $accreditedHoursLog->id)->delete();
    }
}
