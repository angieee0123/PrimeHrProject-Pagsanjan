<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only history of every time an interview's slot moved.
 *
 * Ported from `class InterviewRescheduleLog` in the FastAPI hiring backend.
 *
 * Rescheduling updates the existing `interviews` row in place — same id, same
 * round, same panel, new time — because cancel-and-recreate would break the link
 * between the original booking and the new one. This table keeps that in-place
 * edit honest.
 */
class InterviewRescheduleLog extends Model
{
    use HasUuids;

    protected $table = 'interview_reschedule_log';

    /** The reference table's only timestamp is `changed_at`. */
    public $timestamps = false;

    protected $fillable = [
        'interview_id',
        'previous_scheduled_at',
        'new_scheduled_at',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'previous_scheduled_at' => 'datetime',
        'new_scheduled_at' => 'datetime',
        'changed_at' => 'datetime',
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
