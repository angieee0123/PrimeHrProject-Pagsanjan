<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only history of every retention-window change.
 *
 * Ported from `class RetentionSettingChange` in the FastAPI hiring backend.
 *
 * A separate table because `audit_log` records action/entity/entity_id and has
 * nowhere to hold an old→new value pair; encoding them into the 50-character
 * `action` string would make them unaggregatable.
 *
 * `old_retention_months` is NULL only for the first value ever set for a
 * jurisdiction — there was no previous value, which is a different fact from
 * "the previous value was zero".
 */
class RetentionSettingChange extends Model
{
    protected $table = 'retention_setting_changes';

    /** The reference table's only timestamp is `at`, the event time. */
    public $timestamps = false;

    protected $fillable = [
        'setting_id',
        'jurisdiction_code',
        'old_retention_months',
        'new_retention_months',
        'old_authorization_reference',
        'new_authorization_reference',
        'changed_by_id',
        'changed_by_name',
        'at',
    ];

    protected $casts = [
        'old_retention_months' => 'integer',
        'new_retention_months' => 'integer',
        'at' => 'datetime',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(RetentionSetting::class, 'setting_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
