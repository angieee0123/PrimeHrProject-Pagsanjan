<?php

namespace App\Observers;

use App\Models\AttendanceExemption;
use Illuminate\Support\Facades\Cache;

class AttendanceExemptionObserver
{
    /**
     * Handle the AttendanceExemption "created" event.
     */
    public function created(AttendanceExemption $attendanceExemption): void
    {
        $this->refreshAttendanceExemptionCache();
    }

    /**
     * Handle the AttendanceExemption "updated" event.
     */
    public function updated(AttendanceExemption $attendanceExemption): void
    {
        //
    }

    /**
     * Handle the AttendanceExemption "deleted" event.
     */
    public function deleted(AttendanceExemption $attendanceExemption): void
    {
        //
    }

    /**
     * Handle the AttendanceExemption "restored" event.
     */
    public function restored(AttendanceExemption $attendanceExemption): void
    {
        //
    }

    /**
     * Handle the AttendanceExemption "force deleted" event.
     */
    public function forceDeleted(AttendanceExemption $attendanceExemption): void
    {
        //
    }

    
    private function refreshAttendanceExemptionCache(): void 
    {
        Cache::lock('attendance_exemption:all:lock', 10)->get(function () {
            Cache::put(
                'attendance_exemption:all',
                AttendanceExemption::with('employee', 'creator')->get(),
                now()->addHour()
            );
        });
    }
}
