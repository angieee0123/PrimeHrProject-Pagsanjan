<?php

namespace App\Services\Recruitment;

use App\Models\Vacancy;

/**
 * Rule-based explanation generator.
 *
 * The transparency guarantee of the whole module: a rank is only defensible if
 * the candidate — or an auditor — can read why. A port of
 * `services/explainer.py`, producing the same sentences from the same
 * subscores, with the date-range audit note preserved.
 */
class ScreeningExplainer
{
    /**
     * @param  array<string, mixed>  $parsed
     * @param  array<string, float>  $subscores
     * @param  array<int, string>  $matchedSkills
     */
    public function build(array $parsed, array $subscores, array $matchedSkills, Vacancy $vacancy): string
    {
        $required = (array) ($vacancy->required_skills ?? []);
        $unmatched = array_values(array_filter(
            $required,
            static fn (string $skill) => ! in_array($skill, $matchedSkills, true)
        ));

        $parts = [];

        $education = (float) ($subscores['education'] ?? 0);
        $educationLabel = (array) ($parsed['education'] ?? ['degree']);
        $educationLabel = (string) ($educationLabel[0] ?? 'degree');

        if ($education >= 1.0) {
            $parts[] = 'Education: '.$educationLabel.' satisfies the required "'.$vacancy->qs_education.'".';
        } elseif ($education > 0) {
            $parts[] = 'Education: partial match to "'.$vacancy->qs_education.'" ('
                .((int) round($education * 100)).'% credit).';
        } else {
            $parts[] = 'Education: does not meet "'.$vacancy->qs_education.'".';
        }

        // Audit note when date ranges were used: it is what makes "3.0 years"
        // checkable rather than a number the model produced.
        $experienceNote = '';

        if (! empty($parsed['experience_ranges'])) {
            $present = (string) ($parsed['experience_present_date'] ?? '');
            $experienceNote = ' (computed from '.count((array) $parsed['experience_ranges'])
                .' date range(s); Present='.$present.')';
        }

        $parts[] = 'Experience: '.((int) round(((float) ($subscores['experience'] ?? 0)) * 100))
            .'% of the '.$vacancy->qs_experience_years.'-year requirement'.$experienceNote.'.';

        $parts[] = 'Training: '.((int) round(((float) ($subscores['training'] ?? 0)) * 100))
            .'% of the '.$vacancy->qs_training_hours.'-hour requirement.';

        if (((float) ($subscores['eligibility'] ?? 0)) >= 1.0) {
            $eligibility = (array) ($parsed['eligibility'] ?? ['eligibility']);
            $eligibility = (string) ($eligibility[0] ?? 'eligibility');
            $parts[] = 'Eligibility: '.$eligibility.' — meets "'.$vacancy->qs_eligibility.'".';
        } else {
            $parts[] = 'Eligibility: does not meet "'.$vacancy->qs_eligibility.'".';
        }

        $skillLine = 'Skills: matched '.count($matchedSkills).' of '.count($required);

        $skillLine .= $unmatched !== []
            ? ' — unmatched: '.implode(', ', $unmatched).'.'
            : '.';

        $parts[] = $skillLine;

        return implode(' ', $parts);
    }
}
