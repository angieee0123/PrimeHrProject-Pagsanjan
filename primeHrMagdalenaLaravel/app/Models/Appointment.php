<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The terminal fact of a successful recruitment: this application ended in an
 * appointment to a position, effective on a date.
 *
 * Ported from `class Appointment` in the FastAPI hiring backend.
 *
 * This is the seam to Personnel Administration, not a personnel record: it can
 * observe a hire but not a separation, so it owns the *opening* fact of a
 * plantilla occupancy and never the closing one.
 *
 * It is deliberately not itself the occupancy row. A CSC attestation can be
 * disapproved and retroactively invalidate the appointment; if this row were the
 * occupancy row, a disapproval would mean voiding an occupancy period, breaking
 * the append-only guarantee. Keeping them apart lets attestation stay mutable
 * here while occupancy stays immutable there.
 *
 * Everything about the position is a snapshot: a position can be reclassified in
 * place, and item numbers are not stable across PSI years, so a live join to the
 * position would silently rewrite history.
 */
class Appointment extends Model
{
    use HasUuids;

    public const ATTESTATION_PENDING = 'PENDING';

    public const ATTESTATION_ATTESTED = 'ATTESTED';

    public const ATTESTATION_DISAPPROVED = 'DISAPPROVED';

    /** Nature-of-appointment codes; `nature` is a code, never free text. */
    public const NATURE_ORIGINAL = 'ORIGINAL';

    public const NATURE_PROMOTION = 'PROMOTION';

    public const NATURE_TRANSFER = 'TRANSFER';

    public const NATURE_REEMPLOYMENT = 'REEMPLOYMENT';

    public const NATURE_REAPPOINTMENT = 'REAPPOINTMENT';

    protected $table = 'appointments';

    /**
     * `live_vacancy_guard` is intentionally absent: it is a STORED generated
     * column (`CASE WHEN attestation_status <> 'DISAPPROVED' THEN vacancy_id
     * END`). Writing to it is an error, so it must never be mass-assignable.
     */
    protected $fillable = [
        'application_id',
        'vacancy_id',
        'candidate_id',
        'appointee_name',
        'plantilla_item_no',
        'position_title',
        'salary_grade',
        'nature',
        'effective_date',
        'attestation_status',
        'attestation_reference',
        'attestation_note',
        'attestation_recorded_at',
        'attestation_recorded_by',
        'created_by',
    ];

    protected $casts = [
        'salary_grade' => 'integer',
        'effective_date' => 'date',
        'attestation_recorded_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function application(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'application_id');
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function attestationRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attestation_recorded_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * A live appointment is one that has not been disapproved. This mirrors the
     * generated column exactly, so reading the guard and reading this agree.
     */
    public function isLive(): bool
    {
        return $this->attestation_status !== self::ATTESTATION_DISAPPROVED;
    }
}
