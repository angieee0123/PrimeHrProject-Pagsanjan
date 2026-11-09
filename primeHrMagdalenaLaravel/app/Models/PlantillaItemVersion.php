<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One effective-dated description of a plantilla item.
 *
 * Ported from `class PlantillaItemVersion` in the FastAPI hiring backend.
 *
 * Two distinct mechanisms produce rows, and conflating them was the modelling
 * trap this design avoids: RECLASSIFIED keeps the number while title and grade
 * change (the incumbent does not move), and RENUMBERED reissues the item under a
 * different PSI number with nothing else changed.
 *
 * There is deliberately no `end_date`: a version ends when the next one begins,
 * so storing it would duplicate `effective_date` on the following row. The final
 * version ends at the item's own `abolished_date`.
 */
class PlantillaItemVersion extends Model
{
    use HasUuids;

    public const REASON_CREATED = 'CREATED';

    public const REASON_RECLASSIFIED = 'RECLASSIFIED';

    public const REASON_RENUMBERED = 'RENUMBERED';

    public const REASON_REALIGNED = 'REALIGNED';

    public const REASON_CORRECTED = 'CORRECTED';

    protected $table = 'plantilla_item_versions';

    protected $fillable = [
        'item_id',
        'psi_item_no',
        'position_title',
        'salary_grade',
        'office',
        'effective_date',
        'change_reason',
        'authority_reference',
        'note',
        'created_by',
    ];

    protected $casts = [
        'salary_grade' => 'integer',
        'effective_date' => 'date',
    ];

    public const UPDATED_AT = null;

    public function item(): BelongsTo
    {
        return $this->belongsTo(PlantillaItem::class, 'item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
