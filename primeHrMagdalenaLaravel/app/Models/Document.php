<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** The documents table has only `uploaded_at` — no created_at/updated_at. */
    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'document_type', 'file_path',
        'uploaded_at', 'status'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
