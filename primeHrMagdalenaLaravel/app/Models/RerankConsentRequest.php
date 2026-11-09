<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An applicant's answer to "may we re-rank you for another position?".
 *
 * Ported from `class RerankConsentRequest` in the FastAPI hiring backend.
 *
 * The row exists BEFORE anything is written to the ranking: re-ranking from
 * Position A to Position B is a new processing purpose under RA 10173, so the
 * plan HR approved is parked here and applied only if the candidate says yes.
 *
 * `token` is the bearer credential in the emailed Confirm/Decline links. It must
 * never be returned on any HR-facing response — an HR user who could read it
 * could consent on the candidate's behalf.
 */
class RerankConsentRequest extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_CONFIRMED = 'CONFIRMED';

    public const STATUS_DECLINED = 'DECLINED';

    /** The window lapsed unanswered — a non-response, not a decision. */
    public const STATUS_EXPIRED = 'EXPIRED';

    public const MODE_COPY = 'COPY';

    public const MODE_MOVE = 'MOVE';

    protected $table = 'rerank_consent_requests';

    protected $fillable = [
        'candidate_id',
        'target_vacancy_id',
        'source_profile_id',
        'recipient_user_id',
        'recipient_email',
        'mode',
        'replace_resume',
        'consent_basis',
        'acknowledged',
        'note',
        'status',
        'token',
        'requested_by_id',
        'requested_by_name',
        'sent_at',
        'expires_at',
        'reminder_sent_at',
        'responded_at',
        'response_channel',
        'executed_at',
        'created_profile_id',
        'conflict_code',
        'blocked_reason',
    ];

    /** Python-side defaults from the reference model — see Vacancy::$attributes. */
    protected $attributes = [
        'mode' => self::MODE_COPY,
        'replace_resume' => false,
        'status' => self::STATUS_PENDING,
    ];

    protected $casts = [
        'replace_resume' => 'boolean',
        'acknowledged' => 'array',
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    /**
     * The bearer token is never serialised. An HR user reading consent history
     * must not receive the credential that would let them answer as the
     * candidate — the exact act this table exists to make impossible.
     */
    protected $hidden = ['token'];

    public const UPDATED_AT = null;

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function targetVacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class, 'target_vacancy_id');
    }

    public function sourceProfile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'source_profile_id');
    }

    public function createdProfile(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'created_profile_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
