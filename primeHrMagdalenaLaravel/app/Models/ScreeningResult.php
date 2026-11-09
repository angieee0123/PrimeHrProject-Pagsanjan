<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The weighted fit score and its explanation for one application.
 *
 * Ported from `class ScreeningResult` in the FastAPI hiring backend.
 *
 * `rank_position` is the column name because `rank` is a MySQL reserved word.
 * A re-rank writes a new row; a score is never recomputed in place.
 */
class ScreeningResult extends Model
{
    use HasUuids;

    protected $table = 'screening_results';

    protected $fillable = [
        'applicant_id',
        'vacancy_id',
        'fit_score',
        'subscores',
        'rank_position',
        'explanation',
        'model_version',
    ];

    protected $casts = [
        'fit_score' => 'decimal:2',
        'subscores' => 'array',
        'rank_position' => 'integer',
    ];

    /**
     * No server-side default in the reference (SQLAlchemy applied a Python-side
     * `default=dict`); the empty object keeps the column NOT NULL.
     */
    protected $attributes = [
        'subscores' => '{}',
    ];

    public const UPDATED_AT = null;

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_id');
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }
}
