<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One row per *person*, across every application they have made.
 *
 * Ported from `class Candidate` in the FastAPI hiring backend.
 *
 * Identity is asserted, never inferred: the only automatic match is `user_id`
 * equality. `full_name` is display-only — it comes from resume extraction, which
 * is a guess about document layout, and matching people on it would attach one
 * person's history to another's application.
 *
 * `user_id` is unique and nullable on purpose. MySQL treats every NULL as
 * distinct in a unique index, so the many HR-uploaded candidates coexist while
 * an account owns at most one candidate row. Under the staff-only port this
 * column is NULL for HR-uploaded applications; it is kept so a later
 * applicant-account link needs no migration.
 */
class Candidate extends Model
{
    use HasUuids;

    protected $table = 'candidates';

    protected $fillable = [
        'full_name',
        'normalized_name',
        'user_id',
        'merged_into_id',
        'first_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The surviving record this one was merged into, if any. */
    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(self::class, 'merged_into_id');
    }

    /** Tombstones pointing at this candidate. */
    public function mergedFrom(): HasMany
    {
        return $this->hasMany(self::class, 'merged_into_id');
    }

    public function applicantProfiles(): HasMany
    {
        return $this->hasMany(ApplicantProfile::class);
    }

    public function isMerged(): bool
    {
        return $this->merged_into_id !== null;
    }
}
