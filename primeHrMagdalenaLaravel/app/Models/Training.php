<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'employee_id', 'title', 'conducted_by', 'date_from', 'date_to',
        'hours', 'position_type', 'venue', 'cert_no', 'ref_doc_no',
        'certificate_path', 'status', 'verified_by', 'verified_at', 'rejected_reason',
    ];

    protected $casts = [
        'date_from'   => 'date',
        'date_to'     => 'date',
        'verified_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * L&D category for breakdown (Leadership, Technical, Core/Foundation).
     */
    public function ldCategory(): string
    {
        $title = strtolower($this->title ?? '');

        if (preg_match('/leadership|governance|management|executive|director|mayor|chief|supervisory|managerial|strategic|planning/', $title)) {
            return 'leadership';
        }

        if (preg_match('/technical|computer|cyber|it |information|engineering|records|data|digital|software|network|system/', $title)) {
            return 'technical';
        }

        return match ($this->position_type) {
            'Managerial', 'Supervisory' => 'leadership',
            'Technical'                   => 'technical',
            default                       => 'core',
        };
    }
}
