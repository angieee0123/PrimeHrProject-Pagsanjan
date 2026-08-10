<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One user's personal palette. Absence of a row *is* the "use the system
 * theme" state — there is no `enabled` flag to fall out of step with the
 * data, and "Reset to system theme" is a delete.
 */
class UserThemeSetting extends Model
{
    protected $table = 'user_theme_settings';

    protected $fillable = [
        'user_id',
        'theme',
        'custom_theme_primary',
        'theme_secondary',
        'theme_accent',
        'theme_muted',
        'sidebar_style',
        'topbar_style',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
