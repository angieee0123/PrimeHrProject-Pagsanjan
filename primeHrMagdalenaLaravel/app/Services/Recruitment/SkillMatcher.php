<?php

namespace App\Services\Recruitment;

/**
 * Skill matching — Jaccard token overlap.
 *
 * The reference used Gensim Word2Vec + cosine when a model was loaded, with
 * this token-overlap fallback beneath it. The port has no Word2Vec, so this is
 * the only path (docs/hiring-backend-native-port.md §6.2, option 1).
 *
 * The consequence is honest and recorded: "communication" and "communication
 * skills" are not a match here, where an embedding would have scored them
 * close. A floor is still applied so a weak overlap cannot contribute partial
 * credit.
 */
class SkillMatcher
{
    /**
     * @param  array<int, string>  $required
     * @param  array<int, string>  $applicantSkills
     * @return array{0: float, 1: array<int, string>} [mean best similarity, matched required skills]
     */
    public function match(array $required, array $applicantSkills): array
    {
        if ($required === []) {
            return [1.0, []];
        }

        if ($applicantSkills === []) {
            return [0.0, []];
        }

        $floor = (float) config('hiring.skill_similarity_floor', 0.60);
        $scores = [];
        $matched = [];

        foreach ($required as $requirement) {
            $best = 0.0;

            foreach ($applicantSkills as $skill) {
                $best = max($best, $this->tokenOverlap((string) $requirement, (string) $skill));
            }

            $scores[] = $best >= $floor ? $best : 0.0;

            if ($best >= $floor) {
                $matched[] = $requirement;
            }
        }

        $mean = $scores === [] ? 0.0 : array_sum($scores) / count($scores);

        return [round($mean, 4), $matched];
    }

    /**
     * Jaccard similarity over whitespace-separated tokens.
     *
     * Identical to the reference's `_token_overlap`, including the whitespace
     * split: punctuation is part of a token, so "records," and "records" are
     * different words.
     */
    public function tokenOverlap(string $a, string $b): float
    {
        $setA = $this->tokens($a);
        $setB = $this->tokens($b);

        if ($setA === [] || $setB === []) {
            return 0.0;
        }

        $intersection = array_intersect_key($setA, $setB);
        $union = $setA + $setB;

        return count($intersection) / count($union);
    }

    /**
     * @return array<string, true>
     */
    private function tokens(string $value): array
    {
        $tokens = [];

        foreach (preg_split('/\s+/u', mb_strtolower(trim($value))) ?: [] as $token) {
            if ($token !== '') {
                $tokens[$token] = true;
            }
        }

        return $tokens;
    }
}
