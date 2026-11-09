<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One milestone in an application's post-screening lifecycle.
 *
 * Ported from `class ApplicantEvent` in the FastAPI hiring backend.
 *
 * Distinct from `AuditLog`: that is the tamper-evident security trail, gated to
 * admin/auditor across every entity. This is a candidate-facing narrative that
 * HR staff can also read, scoped to one profile.
 */
class ApplicantEvent extends Model
{
    use HasUuids;

    protected $table = 'applicant_events';

    /**
     * This table's only timestamp is `at`, which is the business event time —
     * there is no `created_at`/`updated_at` in the reference.
     */
    public $timestamps = false;

    protected $fillable = [
        'applicant_id',
        'event_type',
        'detail',
        'actor_user_id',
        'actor_name',
        'at',
    ];

    protected $casts = [
        'detail' => 'array',
        'at' => 'datetime',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
