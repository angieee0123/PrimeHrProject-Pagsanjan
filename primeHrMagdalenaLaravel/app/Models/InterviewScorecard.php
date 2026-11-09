<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One panelist's evaluation of one interview.
 *
 * Ported from `class InterviewScorecard` in the FastAPI hiring backend.
 *
 * Per-interview and per-interviewer, not per-application: a panel of three
 * produces three rows for the same application.
 *
 * **There is deliberately no `overall_average_score` column.** One existed in
 * the reference schema and was dropped once proven inert. The mean is computed
 * from `competency_scores` on every read, so it can never outlive a corrected
 * rating. Do not reintroduce it — `averageScore()` below is the one definition.
 */
class InterviewScorecard extends Model
{
    use HasUuids;

    public const ALIGNMENT_SUPPORTS = 'SUPPORTS';

    public const ALIGNMENT_CONTRADICTS = 'CONTRADICTS';

    public const ALIGNMENT_NEUTRAL = 'NEUTRAL';

    public const RECOMMENDATION_STRONG_YES = 'STRONG_YES';

    public const RECOMMENDATION_YES = 'YES';

    public const RECOMMENDATION_MAYBE = 'MAYBE';

    public const RECOMMENDATION_NO = 'NO';

    protected $table = 'interview_scorecards';

    /** The reference table has no `created_at`/`updated_at`. */
    public $timestamps = false;

    protected $fillable = [
        'interview_id',
        'interviewer_id',
        'competency_scores',
        'strengths_notes',
        'concerns_notes',
        'behavioral_test_alignment',
        'behavioral_test_alignment_notes',
        'recommendation',
        'justification_text',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'competency_scores' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * The mean of the submitted ratings, or null when no rating carries one.
     *
     * Computed on every read rather than stored, so it cannot outlive a
     * corrected rating. This is the only definition of the scorecard average.
     */
    public function averageScore(): ?float
    {
        $ratings = collect($this->competency_scores ?? [])
            ->pluck('rating')
            ->filter(fn ($rating) => is_numeric($rating))
            ->map(fn ($rating) => (float) $rating);

        return $ratings->isEmpty() ? null : round($ratings->avg(), 2);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
