<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One interviewer assigned to one interview.
 *
 * Ported from `class InterviewPanelist` in the FastAPI hiring backend.
 *
 * Its own table so panelists can be added without rewriting a blob, and so "is
 * this user on this panel?" is an indexed lookup rather than a JSON scan.
 *
 * Deliberately no `role` column: nothing distinguishes a lead interviewer from a
 * regular panelist, and a column with no reader is not added speculatively.
 */
class InterviewPanelist extends Model
{
    use HasUuids;

    protected $table = 'interview_panelists';

    /** The reference table has no `created_at`/`updated_at`. */
    public $timestamps = false;

    protected $fillable = [
        'interview_id',
        'interviewer_id',
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
