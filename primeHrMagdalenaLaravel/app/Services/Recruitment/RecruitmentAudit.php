<?php

namespace App\Services\Recruitment;

use App\Models\ApplicantEvent;
use App\Models\ApplicantProfile;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * The recruitment audit trail and the candidate-journey timeline.
 *
 * Two different records, deliberately:
 *
 *   - `audit_log` is the tamper-evident security trail: who did what, to which
 *     record, when. It keeps a *snapshot* of the actor's name so the trail still
 *     names them after the account changes.
 *   - `applicant_events` is the candidate-facing narrative: one milestone per
 *     thing that happened to one application.
 *
 * The HTTP integration could only ever credit actions to its single service
 * account. This port writes the real officer, which is the main thing the
 * native version buys: the log no longer needs a second file channel to answer
 * "who pressed the button".
 */
final class RecruitmentAudit
{
    /**
     * Record one action.
     *
     * @param  array<string, mixed>  $context  Extra detail for the log channel,
     *                                         never stored on the row.
     */
    public static function record(
        ?User $actor,
        string $action,
        ?string $entity = null,
        ?string $entityId = null,
        ?Carbon $at = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor?->getAuthIdentifier(),
            'user_name' => self::actorName($actor),
            'action' => mb_substr($action, 0, 50),
            'entity' => $entity === null ? null : mb_substr($entity, 0, 50),
            'entity_id' => $entityId === null ? null : mb_substr($entityId, 0, 36),
            'at' => $at ?? BusinessClock::now(),
        ]);
    }

    /**
     * Append a milestone to one application's journey.
     *
     * Flushes but does not commit: the caller owns the transaction so the event
     * and the state change land together.
     *
     * @param  array<string, mixed>|null  $detail
     */
    public static function recordEvent(
        ApplicantProfile $profile,
        string $eventType,
        ?array $detail = null,
        ?User $actor = null,
    ): ApplicantEvent {
        return ApplicantEvent::create([
            'applicant_id' => $profile->id,
            'event_type' => $eventType,
            'detail' => $detail,
            'actor_user_id' => $actor?->getAuthIdentifier(),
            'actor_name' => self::actorName($actor),
            'at' => BusinessClock::now(),
        ]);
    }

    private static function actorName(?User $actor): ?string
    {
        if ($actor === null) {
            return null;
        }

        $name = trim((string) ($actor->name ?? ''));

        return $name !== '' ? $name : ($actor->email ?? null);
    }
}
