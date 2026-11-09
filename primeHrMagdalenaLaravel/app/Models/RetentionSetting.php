<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * How long candidate records are kept, as a centrally-set policy value.
 *
 * Ported from `class RetentionSetting` in the FastAPI hiring backend.
 *
 * One row per jurisdiction, plus the literal `DEFAULT` row that answers for
 * every jurisdiction without one of its own. Retention is compliance-driven
 * under RA 10173, so it is defined by HR Compliance/Legal and merely entered
 * here by an HR admin — the RBAC gate on the write endpoint is what enforces
 * that.
 *
 * **`jurisdiction_code` is NOT NULL and unique.** MySQL treats every NULL as
 * distinct in a unique index, so a nullable column would silently permit more
 * than one "system-wide default" row.
 */
class RetentionSetting extends Model
{
    use HasUuids;

    /** The row that answers for every jurisdiction without its own. */
    public const DEFAULT_JURISDICTION = 'DEFAULT';

    protected $table = 'retention_settings';

    protected $fillable = [
        'jurisdiction_code',
        'retention_months',
        'authorization_reference',
        'updated_by_id',
    ];

    /** Python-side default from the reference model — see Vacancy::$attributes. */
    protected $attributes = [
        'jurisdiction_code' => self::DEFAULT_JURISDICTION,
    ];

    protected $casts = [
        'retention_months' => 'integer',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(RetentionSettingChange::class, 'setting_id')->orderBy('at');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
