<?php

namespace App\Services\Recruitment;

use App\Models\Candidate;
use App\Models\DuplicateFlag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Duplicate review — suggesting merges, and performing the ones a human
 * approves.
 *
 * **Merging never moves applications.** A merge sets `merged_into_id` on the
 * absorbed candidate and stops; every application stays attached to the
 * candidate it was created under. Reads aggregate across the merge group
 * instead.
 *
 * That is what makes un-merge trivially correct: clearing `merged_into_id`
 * restores the exact prior state, because nothing was ever rearranged. Under a
 * manual-merge-only design every merge is a human judgement call, so some will
 * be wrong, and reversibility has to be structural rather than best-effort.
 *
 * Ported from `services/duplicates.py`.
 */
class DuplicateDetector
{
    public const SAME_RESUME_DIFFERENT_CANDIDATE = 'SAME_RESUME_DIFFERENT_CANDIDATE';

    /** Resolutions the module accepts. Only LINK_CANDIDATES changes identity. */
    public const RESOLUTIONS = ['SUPERSEDE', 'KEEP_EXISTING', 'LINK_CANDIDATES', 'DISMISS'];

    public function __construct(private readonly CandidateResolver $candidates = new CandidateResolver())
    {
    }

    /**
     * Raise a flag wherever one resume's bytes appear under two candidates.
     *
     * Suggestion only: byte equality is not proof of identity, because two
     * people submitting the same downloadable form produce identical bytes.
     * Idempotent — a hash that already has an OPEN flag is skipped, so this is
     * safe to run after every intake.
     *
     * @return array<int, DuplicateFlag>
     */
    public function scan(): array
    {
        $sharedHashes = DB::table('applicant_profiles')
            ->select('content_hash')
            ->whereNotNull('content_hash')
            ->groupBy('content_hash')
            ->havingRaw('COUNT(DISTINCT candidate_id) > 1')
            ->pluck('content_hash');

        $raised = [];

        foreach ($sharedHashes as $hash) {
            $profiles = \App\Models\ApplicantProfile::query()
                ->where('content_hash', $hash)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $first = $profiles->first();

            if ($first === null) {
                continue;
            }

            foreach ($profiles->slice(1) as $other) {
                if ($other->candidate_id === $first->candidate_id) {
                    continue;
                }

                if ($this->alreadyFlagged($first->id, $other->id)) {
                    continue;
                }

                $raised[] = DuplicateFlag::create([
                    'kind' => self::SAME_RESUME_DIFFERENT_CANDIDATE,
                    'candidate_id' => $first->candidate_id,
                    'counterpart_candidate_id' => $other->candidate_id,
                    'vacancy_id' => $other->vacancy_id,
                    'profile_id' => $first->id,
                    'counterpart_profile_id' => $other->id,
                    'detail' => [
                        'existing_hash' => $hash,
                        'incoming_hash' => $hash,
                        'note' => 'Two candidate records hold a byte-identical resume. That is '
                            .'usually one person split across two records, but the same '
                            .'downloadable form submitted by two people looks identical too '
                            .'— so this is never merged automatically.',
                    ],
                ]);
            }
        }

        return $raised;
    }

    /**
     * Apply an admin's decision. Only LINK_CANDIDATES touches identity.
     */
    public function resolve(
        DuplicateFlag $flag,
        string $resolution,
        ?string $note = null,
        ?User $actor = null,
    ): DuplicateFlag {
        if (! in_array($resolution, self::RESOLUTIONS, true)) {
            throw new \InvalidArgumentException('Unknown duplicate resolution: '.$resolution);
        }

        if ($resolution === 'LINK_CANDIDATES') {
            $this->merge($flag->counterpart_candidate_id, $flag->candidate_id);
        }

        $flag->status = $resolution === 'DISMISS' ? DuplicateFlag::STATUS_DISMISSED : DuplicateFlag::STATUS_RESOLVED;
        $flag->resolution = $resolution;
        $flag->resolved_by_id = $actor?->getAuthIdentifier();
        $flag->resolved_by = $actor?->name;
        $flag->resolved_at = BusinessClock::now();

        if ($note !== null && trim($note) !== '') {
            $flag->detail = array_merge((array) ($flag->detail ?? []), ['resolution_note' => trim($note)]);
        }

        $flag->save();

        return $flag;
    }

    /**
     * Mark one candidate as absorbed by another. Reversible; moves nothing.
     */
    public function merge(?string $candidateId, string $intoId): Candidate
    {
        if ($candidateId === null || $candidateId === $intoId) {
            throw new \InvalidArgumentException('A candidate cannot be merged into itself');
        }

        $absorbed = Candidate::find($candidateId);
        $survivor = Candidate::find($intoId);

        if ($absorbed === null || $survivor === null) {
            throw new \InvalidArgumentException('Unknown candidate');
        }

        // Always point at the *final* survivor so chains never form, which is
        // what lets groupIds stay one level deep.
        $survivor = $this->candidates->mergeTarget($survivor);

        if ($survivor->id === $absorbed->id) {
            throw new \InvalidArgumentException('A candidate cannot be merged into itself');
        }

        $absorbed->merged_into_id = $survivor->id;
        $absorbed->save();

        return $survivor;
    }

    /**
     * Restore a merged candidate to standing on its own.
     *
     * Exactly the inverse of `merge`, and this simple only because merging
     * never moved anything.
     */
    public function unmerge(Candidate $candidate): Candidate
    {
        $candidate->merged_into_id = null;
        $candidate->save();

        return $candidate;
    }

    private function alreadyFlagged(string $firstProfileId, string $otherProfileId): bool
    {
        $pair = [$firstProfileId, $otherProfileId];
        sort($pair);

        return DuplicateFlag::query()
            ->where('kind', self::SAME_RESUME_DIFFERENT_CANDIDATE)
            ->where('status', DuplicateFlag::STATUS_OPEN)
            ->get()
            ->contains(function (DuplicateFlag $flag) use ($pair): bool {
                $existing = [$flag->profile_id, $flag->counterpart_profile_id];
                sort($existing);

                return $existing === $pair;
            });
    }
}
