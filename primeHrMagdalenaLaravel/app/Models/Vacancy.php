<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A requisition: the position being hired for.
 *
 * Ported from `class Vacancy` in the FastAPI hiring backend. The state machine
 * (which move is legal, what LAPSED means, the grace window) belongs to the
 * lifecycle service, not here — this model is the row.
 */
class Vacancy extends Model
{
    use HasUuids;

    /** Created but not published. Never public, never lapses. */
    public const STATUS_DRAFT = 'DRAFT';

    /** Active/published: listed publicly, accepting applications. */
    public const STATUS_OPEN = 'OPEN';

    /** Recruitment paused. Delisted, expected back. Reversible. */
    public const STATUS_ON_HOLD = 'ON_HOLD';

    /** closing_date passed with no recruiter action. Automatic and reversible. */
    public const STATUS_LAPSED = 'LAPSED';

    /** The position was filled. Terminal. */
    public const STATUS_CLOSED = 'CLOSED';

    /** Permanently withdrawn. Terminal, and requires a reason. */
    public const STATUS_CANCELLED = 'CANCELLED';

    /** The only state a candidate-facing listing may show. */
    public const PUBLIC_STATUSES = [self::STATUS_OPEN];

    /** States no further lifecycle move leaves. */
    public const TERMINAL_STATUSES = [self::STATUS_CLOSED, self::STATUS_CANCELLED];

    protected $table = 'vacancies';

    protected $fillable = [
        'position_title',
        'place_of_assignment',
        'plantilla_item_no',
        'salary_grade',
        'monthly_salary',
        'qs_education',
        'qs_training_hours',
        'qs_experience_years',
        'qs_eligibility',
        'competency',
        'required_skills',
        'weights',
        'instructions',
        'status',
        'posting_date',
        'closing_date',
        'requisition_open_date',
        'grace_period_expires_at',
        'lapsed_at',
        'closed_at',
        'reopened_at',
        'reopen_count',
        'ranking_version',
        'close_reason',
        'slots',
        'published_at',
        'on_hold_at',
        'hold_note',
        'cancelled_at',
        'cancel_reason',
        'cancel_reason_detail',
        'detail_retired',
        'assigned_recruiter_id',
        'created_by',
    ];

    protected $casts = [
        'salary_grade' => 'integer',
        'monthly_salary' => 'decimal:2',
        'qs_training_hours' => 'integer',
        'qs_experience_years' => 'decimal:1',
        'required_skills' => 'array',
        'weights' => 'array',
        'posting_date' => 'date',
        'closing_date' => 'date',
        'requisition_open_date' => 'date',
        'grace_period_expires_at' => 'datetime',
        'lapsed_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'published_at' => 'datetime',
        'on_hold_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reopen_count' => 'integer',
        'ranking_version' => 'integer',
        'slots' => 'integer',
        'detail_retired' => 'boolean',
    ];

    /**
     * Python-side defaults from the reference model, reproduced so the object
     * has the same initial state SQLAlchemy gave it.
     *
     * Eloquent does not read a column's DB default back after an insert, so
     * without these a freshly created vacancy would carry `null` status and
     * `null` counters in memory — the DB row would be right while the object in
     * hand was wrong. The column defaults stay as the backstop for raw SQL.
     */
    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'competency' => 'N/A',
        'required_skills' => '[]',
        'weights' => '{}',
        'monthly_salary' => 0,
        'qs_training_hours' => 0,
        'qs_experience_years' => 0,
        'reopen_count' => 0,
        'ranking_version' => 0,
        'slots' => 1,
        'detail_retired' => false,
    ];

    /** The reference has no `updated_at` on this table. */
    public const UPDATED_AT = null;

    public function applicantProfiles(): HasMany
    {
        return $this->hasMany(ApplicantProfile::class);
    }

    public function screeningResults(): HasMany
    {
        return $this->hasMany(ScreeningResult::class);
    }

    public function assignedRecruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_recruiter_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublic(): bool
    {
        return in_array($this->status, self::PUBLIC_STATUSES, true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }
}
