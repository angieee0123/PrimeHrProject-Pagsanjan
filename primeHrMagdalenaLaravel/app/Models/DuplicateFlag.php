<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A possible duplicate a human has to decide about.
 *
 * Ported from `class DuplicateFlag` in the FastAPI hiring backend.
 *
 * Nothing here is auto-resolved. The only signal raised is a byte-identical
 * `content_hash` held by two candidates, and even that is a suggestion: two
 * people submitting the same downloadable form produce identical bytes.
 */
class DuplicateFlag extends Model
{
    use HasUuids;

    public const STATUS_OPEN = 'OPEN';

    public const STATUS_RESOLVED = 'RESOLVED';

    public const STATUS_DISMISSED = 'DISMISSED';

    /** Two applications carrying the same resume bytes. */
    public const KIND_SAME_RESUME = 'SAME_RESUME_DIFFERENT_CANDIDATE';

    protected $table = 'duplicate_flags';

    protected $fillable = [
        'kind',
        'status',
        'candidate_id',
        'counterpart_candidate_id',
        'vacancy_id',
        'profile_id',
        'counterpart_profile_id',
        'detail',
        'resolution',
        'resolved_by_id',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'detail' => 'array',
        'resolved_at' => 'datetime',
    ];

    /** Python-side default from the reference model — see Vacancy::$attributes. */
    protected $attributes = [
        'status' => self::STATUS_OPEN,
    ];

    public const UPDATED_AT = null;

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function counterpartCandidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'counterpart_candidate_id');
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'profile_id');
    }

    public function counterpartProfile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'counterpart_profile_id');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
