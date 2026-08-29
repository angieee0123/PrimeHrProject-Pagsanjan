<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EmployeeSupportingDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'employee_id',
        'pds_file_path',
        'appointment_form_file_path',
        'position_description_file_path',
        'medical_certificate_file_path',
        'nbi_clearance_file_path',
        'financial_clearance_file_path',
        'neuro_exam_file_path',
        'licenses_file_path',
        'performance_eval_file_path',
        'commendation_file_path',
        'disciplinary_file_path',
        'other_records_file_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
