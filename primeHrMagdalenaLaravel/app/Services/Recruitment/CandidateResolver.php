<?php

namespace App\Services\Recruitment;

use App\Models\ApplicantProfile;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;

/**
 * Candidate identity resolution — which person an application belongs to.
 *
 * **The whole design in one line: match only on what the applicant asserted.**
 *
 * `user_id` is the sole automatic match. It means an authenticated,
 * email-verified account holder applied as themselves. Every other signal the
 * system retains is inferred rather than asserted:
 *
 *   - email / phone / government ID — destroyed at intake by the anonymizer
 *     before anything is persisted. Not weak: absent.
 *   - `ApplicantProfile.name` — written by the parser, which guesses from a
 *     document's letterhead.
 *   - `content_hash` — a SHA-256 of the uploaded bytes. Same *document* is not
 *     the same *person*.
 *
 * So HR-uploaded applications each get their own candidate, and combining them
 * is a human decision made later through Duplicate Review. That over-splits on
 * purpose: an over-split is visible and fixable with a merge, while an
 * under-split silently attaches one person's disqualifying history to somebody
 * else's application — in CSC merit-based hiring, a due-process problem.
 *
 * Deliberately absent: any fuzzy-name scoring or confidence threshold. There is
 * no signal here worth thresholding, and a number would lend the guess an air of
 * precision.
 */
class CandidateResolver
{
    /** Casefold and collapse whitespace. A search key only, never a match. */
    public function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * Follow `merged_into_id` to the surviving record.
     *
     * A new application from an account whose candidate was merged away must
     * attach to the survivor, not the tombstone. The cycle guard exists because
     * merges are performed by people, and A→B→A is one mis-click away.
     */
    public function mergeTarget(Candidate $candidate): Candidate
    {
        $seen = [];
        $current = $candidate;

        while ($current->merged_into_id !== null && ! in_array($current->id, $seen, true)) {
            $seen[] = $current->id;

            $next = Candidate::find($current->merged_into_id);

            if ($next === null) {
                break;
            }

            $current = $next;
        }

        return $current;
    }

    /**
     * The candidate an incoming application belongs to.
     *
     * Tier 1 — `user_id` present and already known: that candidate (following
     * any merge). This is the only path that reuses an existing row.
     * Tier 2 — everything else: a brand-new candidate. The deliberate
     * over-split.
     */
    public function resolve(?int $userId = null): Candidate
    {
        if ($userId !== null) {
            $existing = Candidate::query()->where('user_id', $userId)->first();

            if ($existing !== null) {
                return $this->mergeTarget($existing);
            }
        }

        return Candidate::create([
            'user_id' => $userId,
            'first_seen_at' => BusinessClock::now(),
        ]);
    }

    /** Point an application at its candidate, creating one if needed. */
    public function attach(ApplicantProfile $profile): Candidate
    {
        $candidate = $this->resolve($profile->user_id ? (int) $profile->user_id : null);
        $profile->candidate_id = $candidate->id;

        return $candidate;
    }

    /**
     * Give the candidate a display name once the pipeline has extracted one.
     *
     * Only fills a blank. A later application's extracted name does not
     * overwrite an earlier one: both are guesses of equal standing, and letting
     * the newest win would make the directory's display name change under an
     * admin for no reason they could see.
     */
    public function adoptName(ApplicantProfile $profile): void
    {
        if (! $profile->name || ! $profile->candidate_id) {
            return;
        }

        $candidate = Candidate::find($profile->candidate_id);

        if ($candidate === null || $candidate->full_name) {
            return;
        }

        $candidate->full_name = $profile->name;
        $candidate->normalized_name = $this->normalizeName($profile->name);
        $candidate->save();
    }

    /**
     * The candidate plus everything merged into it.
     *
     * One level deep by design: merges always target the surviving record, so
     * chains do not form in normal operation.
     *
     * @return array<int, string>
     */
    public function groupIds(string $candidateId): array
    {
        $absorbed = Candidate::query()
            ->where('merged_into_id', $candidateId)
            ->pluck('id')
            ->all();

        return array_merge([$candidateId], $absorbed);
    }
}
