<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One hiring/appointment fact attached to a plantilla item's history.
 *
 * Ported from `class PlantillaHiringEvent` in the FastAPI hiring backend.
 *
 * Records that an attested appointment filled this item, on a date, for a named
 * appointee — the "Vacant → Filled" entry HR reads on the item timeline.
 *
 * Deliberately not a `plantilla_item_versions` row: versions describe the
 * position, an appointment changes occupancy, and the version table's
 * `(item_id, effective_date)` uniqueness would forbid two same-day hires against
 * one multi-slot item.
 *
 * `appointment_id` is UNIQUE and is the logical event identifier, which is what
 * makes reprocessing the same appointment idempotent instead of duplicating it.
 */
class PlantillaHiringEvent extends Model
{
    use HasUuids;

    protected $table = 'plantilla_hiring_events';

    protected $fillable = [
        'item_id',
        'appointment_id',
        'vacancy_id',
        'appointee_name',
        'position_title',
        'nature',
        'effective_date',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public const UPDATED_AT = null;

    public function item(): BelongsTo
    {
        return $this->belongsTo(PlantillaItem::class, 'item_id');
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
