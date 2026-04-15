<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'employee_id', 'document_type', 'file_path',
        'upload_date', 'approval_status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
