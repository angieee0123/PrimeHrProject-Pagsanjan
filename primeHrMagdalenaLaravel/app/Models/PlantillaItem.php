<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A budgeted position slot — the stable spine of the plantilla.
 *
 * Ported from `class PlantillaItem` in the FastAPI hiring backend.
 *
 * The primary key is a surrogate on purpose. The obvious key would be the PSI
 * item number, and those are not stable across Personnel Services Itemization
 * years: keyed on the number, one continuous position would appear as two
 * unrelated items either side of a PSI cycle and its history would split in half.
 * The number is a versioned attribute instead.
 *
 * Almost nothing lives here — title, grade, office and number are all mutable,
 * so all are versioned. What remains brackets the item's existence: it was
 * created by some authority and may one day be abolished by another.
 */
class PlantillaItem extends Model
{
    use HasUuids;

    protected $table = 'plantilla_items';

    protected $fillable = [
        'creation_authority',
        'created_date',
        'abolished_date',
        'abolition_authority',
        'created_by',
    ];

    protected $casts = [
        'created_date' => 'date',
        'abolished_date' => 'date',
    ];

    public const UPDATED_AT = null;

    public function versions(): HasMany
    {
        return $this->hasMany(PlantillaItemVersion::class, 'item_id')->orderBy('effective_date');
    }

    public function hiringEvents(): HasMany
    {
        return $this->hasMany(PlantillaHiringEvent::class, 'item_id')->orderBy('effective_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The latest version whose effective date has arrived. */
    public function currentVersion(): ?PlantillaItemVersion
    {
        return $this->versions()
            ->whereDate('effective_date', '<=', now()->toDateString())
            ->orderByDesc('effective_date')
            ->first();
    }

    /** A non-null abolition date is a reliable "this slot no longer exists". */
    public function isAbolished(): bool
    {
        return $this->abolished_date !== null;
    }
}
