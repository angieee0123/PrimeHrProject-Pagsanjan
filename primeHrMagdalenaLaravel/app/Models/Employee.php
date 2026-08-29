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

    /**
     * Where this employee stands on scheduling today, and the date that state
     * turns over.
     *
     * The Work Schedules table and the CSV export both read this, so the two
     * cannot disagree about who is covered -- the same reason
     * `currentSchedule()` exists. It resolves four states rather than the
     * three the screen used to show: "Scheduled" previously covered both an
     * employee whose schedule has not started yet and one whose last schedule
     * lapsed months ago, which are opposite problems. The second is now
     * "Expired", and it is the one that needs an HR officer.
     *
     * `start_date` / `end_date` are plain `Y-m-d` strings -- Schedule declares
     * no casts -- so they are compared and sorted as strings, which is
     * correct for that format and is what `currentSchedule()` already does.
     *
     * @return array{state: string, label: string, note: ?string, date: ?string}
     */
    public function scheduleStatus(): array
    {
        $today = now()->format('Y-m-d');

        if ($current = $this->currentSchedule()) {
            return ['state' => 'active', 'label' => 'Active', 'note' => 'Ends', 'date' => $current->end_date];
        }

        $upcoming = $this->schedule
            ->filter(fn (Schedule $s) => $s->start_date && $s->start_date > $today)
            ->sortBy('start_date')
            ->first();

        if ($upcoming) {
            return ['state' => 'upcoming', 'label' => 'Scheduled', 'note' => 'Starts', 'date' => $upcoming->start_date];
        }

        // Both dates are nullable, so a row can exist with nothing to report.
        // That still counts as lapsed -- it just has no date to name.
        $lapsed = $this->schedule
            ->filter(fn (Schedule $s) => (bool) $s->end_date)
            ->sortByDesc('end_date')
            ->first();

        if ($lapsed) {
            return ['state' => 'expired', 'label' => 'Expired', 'note' => 'Ended', 'date' => $lapsed->end_date];
        }

        if ($this->schedule->isNotEmpty()) {
            return ['state' => 'expired', 'label' => 'Expired', 'note' => null, 'date' => null];
        }

        return ['state' => 'none', 'label' => 'Not Set', 'note' => null, 'date' => null];
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

    public function supportingDocuments()
    {
        return $this->hasOne(EmployeeSupportingDocument::class);
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
