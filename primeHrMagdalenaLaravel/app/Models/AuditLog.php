<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The tamper-evident security trail for recruitment actions.
 *
 * Ported from `class AuditLog` in the FastAPI hiring backend.
 *
 * `user_name` is a denormalized snapshot: the trail must keep saying who acted
 * after the account changes. `action` is a code, not a sentence, so actions stay
 * countable.
 *
 * This is the recruitment log. The HRIS's own `audits` table remains the
 * Laravel-wide audit trail; the two answer different questions.
 */
class AuditLog extends Model
{
    protected $table = 'audit_log';

    /** The reference table's only timestamp is `at`, the event time. */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'entity',
        'entity_id',
        'at',
    ];

    protected $casts = [
        'at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
