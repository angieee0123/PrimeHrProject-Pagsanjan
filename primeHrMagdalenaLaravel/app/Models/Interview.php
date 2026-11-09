<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One scheduled interview event for one application.
 *
 * Ported from `class Interview` in the FastAPI hiring backend.
 *
 * `status` is the gate: scorecards are only legal once a recruiter has moved the
 * interview to COMPLETED. The interview service owns that state machine; this
 * model is the row.
 *
 * Soft delete is audit-compliant removal — a hiring record is never hard
 * deleted. It also frees the round number for reuse, which is what the
 * functional unique index on `(application_id, round)` expresses: only active
 * rows collide. A deleted row keeps its history for auditors, while CANCELLED is
 * terminal and *keeps* its round.
 */
class Interview extends Model
{
    use HasUuids;
    use SoftDeletes;

    public const STATUS_SCHEDULED = 'SCHEDULED';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'interviews';

    protected $fillable = [
        'application_id',
        'round',
        'interview_type',
        'scheduled_at',
        'status',
        'reschedule_count',
        'created_by',
        'deleted_by',
    ];

    /** Python-side defaults from the reference model — see Vacancy::$attributes. */
    protected $attributes = [
        'status' => self::STATUS_SCHEDULED,
        'reschedule_count' => 0,
    ];

    protected $casts = [
        'round' => 'integer',
        'scheduled_at' => 'datetime',
        'reschedule_count' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'application_id');
    }

    public function panelists(): HasMany
    {
        return $this->hasMany(InterviewPanelist::class);
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(InterviewScorecard::class);
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(InterviewRescheduleLog::class)->orderBy('changed_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }
}
