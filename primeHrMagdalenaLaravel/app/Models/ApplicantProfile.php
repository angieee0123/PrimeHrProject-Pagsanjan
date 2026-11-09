<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * One person's application to one vacancy.
 *
 * Ported from `class ApplicantProfile` in the FastAPI hiring backend.
 *
 * This *is* the application record — the schema has no separate applications
 * table. It carries the extracted profile, the redaction report proving PII was
 * stripped before storage, and the two independent HR decisions:
 * `review_status` (accept/disapprove an application) and `selection_status`
 * (shortlist for interview). HIRED is the one selection value no user sets: it
 * is written by the appointment service alongside the `appointments` row.
 */
class ApplicantProfile extends Model
{
    use HasUuids;

    /** Uploaded and waiting for the screening job. */
    public const PARSE_QUEUED = 'PENDING';

    /** The pipeline is reading this document right now. */
    public const PARSE_PARSING = 'PARSING';

    /** Scored. The reference's own vocabulary is DONE, not PARSED. */
    public const PARSE_DONE = 'DONE';

    /** Extraction or parsing failed; a re-upload clears it. */
    public const PARSE_FAILED = 'FAILED';

    public const REVIEW_PENDING = 'PENDING';

    public const REVIEW_ACCEPTED = 'ACCEPTED';

    public const REVIEW_DISAPPROVED = 'DISAPPROVED';

    /** Not yet decided. */
    public const SELECTION_PENDING = 'PENDING';

    /** Shortlisted for interview. */
    public const SELECTION_QUALIFIED = 'QUALIFIED_INTERVIEW';

    /** Rejected at the selection gate; `selection_reason` is then required. */
    public const SELECTION_NOT_QUALIFIED = 'NOT_QUALIFIED';

    /** Appointed — terminal, and the only value not written by Talent Selection. */
    public const SELECTION_HIRED = 'HIRED';

    protected $table = 'applicant_profiles';

    protected $fillable = [
        'vacancy_id',
        'candidate_id',
        'archived_at',
        'archived_reason',
        'origin_kind',
        'origin_profile_id',
        'anon_code',
        'name',
        'source_file_path',
        'content_hash',
        'user_id',
        'parsed',
        'redaction_report',
        'parse_status',
        'review_status',
        'selection_status',
        'selection_reason',
        'questionnaire_answers',
        'questionnaire_submitted_at',
        'behavioral_summary',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'parsed' => 'array',
        'redaction_report' => 'array',
        'questionnaire_answers' => 'array',
        'questionnaire_submitted_at' => 'datetime',
        'behavioral_summary' => 'array',
    ];

    /** Python-side defaults from the reference model — see Vacancy::$attributes. */
    protected $attributes = [
        'origin_kind' => 'ORIGINAL',
        'parse_status' => self::PARSE_QUEUED,
        'review_status' => self::REVIEW_PENDING,
        'selection_status' => self::SELECTION_PENDING,
    ];

    public const UPDATED_AT = null;

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function originProfile(): BelongsTo
    {
        return $this->belongsTo(self::class, 'origin_profile_id');
    }

    public function screeningResult(): HasOne
    {
        return $this->hasOne(ScreeningResult::class, 'applicant_id');
    }

    public function screeningResults(): HasMany
    {
        return $this->hasMany(ScreeningResult::class, 'applicant_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApplicantEvent::class, 'applicant_id')->orderBy('at');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'application_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'application_id');
    }

    /** A re-rank or a superseded resume removed this from the live ranking. */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isHired(): bool
    {
        return $this->selection_status === self::SELECTION_HIRED;
    }
}
