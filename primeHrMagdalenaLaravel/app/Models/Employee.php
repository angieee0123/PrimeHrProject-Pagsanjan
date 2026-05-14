<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
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

    public function getScheduleForDate($date)
    {
        return $this->schedule()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }
}
