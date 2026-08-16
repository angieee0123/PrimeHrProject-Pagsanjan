<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'first_name', 'middle_name', 'last_name', 'suffix',
        'photo', 'birth_date', 'place_of_birth', 'sex', 'civil_status',
        'height', 'weight', 'blood_type', 'citizenship', 'email'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function employmentDetail()
    {
        return $this->hasOne(EmploymentDetail::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function governmentIds()
    {
        return $this->hasMany(GovernmentId::class);
    }

    public function legalRequirements()
    {
        return $this->hasMany(LegalRequirement::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function eligibilities()
    {
        return $this->hasMany(Eligibility::class);
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function schedule()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * The schedule in force today, or null.
     *
     * `schedule()` is a hasMany — an employee accumulates dated schedule
     * rows — so "their schedule" is always a question about a date. The
     * Work Schedules table and its CSV export were each answering it their
     * own way, and the export's answer (`$employee->schedule`, a Collection)
     * was not an answer at all: reading `->am_in` off it threw, and the
     * Collection being permanently truthy meant every row reported a
     * schedule as "Assigned" whether one existed or not.
     */
    public function currentSchedule(): ?Schedule
    {
        $today = now()->format('Y-m-d');

        return $this->schedule
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leaveTransactions()
    {
        return $this->hasMany(LeaveTransaction::class);
    }

    public function deductions()
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    public function getScheduleForDate($date)
    {
        return $this->schedule()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    public function attendancePunches()
    {
        return $this->hasMany(AttendancePunch::class);
    }

    /**
     * The signed string printed in this employee's attendance QR badge.
     *
     * An accessor rather than a lookup in the view, so every place that prints
     * a badge signs it the same way and none of them can fall back to the bare
     * id the scanner now rejects.
     */
    public function getQrPayloadAttribute(): string
    {
        return app(\App\Services\AttendanceQrService::class)->payloadFor($this);
    }
}
