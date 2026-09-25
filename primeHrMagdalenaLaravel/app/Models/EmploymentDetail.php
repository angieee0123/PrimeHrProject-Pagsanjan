<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EmploymentDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    /**
     * The employment types an employee may hold. Same vocabulary the
     * registration and designation forms validate against.
     */
    public const EMPLOYMENT_TYPES = [
        'Permanent',
        'Temporary',
        'Coterminous',
        'Casual',
        'Contractual',
        'Job Order',
    ];

    /** The one status that carries no plantilla entitlement. */
    public const JOB_ORDER = 'Job Order';

    protected $fillable = [
        'employee_id', 'designation_id', 'department_id', 'employment_status',
        'appointment_date', 'salary_grade', 'step_increment'
    ];

    /**
     * Whether this employment status carries the leave-and-benefits
     * entitlement.
     *
     * Leave credits are a plantilla entitlement and a Job Order is paid by the
     * day, so `PagsanjanLeaveBalanceSeeder` writes one and a Job Order earns
     * none — there is no balance to draw on and no benefits record to read.
     * Every other type in {@see self::EMPLOYMENT_TYPES} is treated as entitled.
     *
     * A row with no status at all is **not** entitled: nothing states that it
     * is, and the employee rail has always hidden leave and benefits from an
     * employee whose status is unknown. Treating "unknown" as entitled would
     * be a page whose link is hidden and whose URL still works, which is the
     * hole this rule closes.
     *
     * This is the single definition of the rule. The shared `isPermanent` view
     * variable, the settings page, the mobile login payload and
     * {@see \App\Http\Middleware\EnsureLeaveAndBenefitsEligible} all read it,
     * so the page a Job Order is refused cannot be a page the rail still
     * offers — and no surface can drift from another on who is entitled.
     */
    public function hasLeaveAndBenefits(): bool
    {
        return $this->employment_status !== null
            && $this->employment_status !== self::JOB_ORDER;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designationRelation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
}
