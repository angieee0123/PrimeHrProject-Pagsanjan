<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One section of the public welcome page.
 *
 * Read through SiteContentService, never directly from a view — the service
 * is what merges a saved section over its defaults, so a section that has
 * never been edited (or that was saved before a new field existed) still
 * renders every key the Blade expects.
 */
class SiteContent extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    protected $casts = ['value' => 'array'];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
