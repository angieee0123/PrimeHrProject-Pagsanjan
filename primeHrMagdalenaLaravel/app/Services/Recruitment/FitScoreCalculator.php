<?php

namespace App\Services\Recruitment;

use App\Models\Vacancy;

/**
 * CSC-weighted Fit Score engine.
 *
 *     FitScore = 100 * ( w_edu·S_edu + w_trn·S_trn + w_exp·S_exp
 *                      + w_elig·S_elig + w_skl·S_skl )
 *
 * A direct port of `services/scorer.py`. Every subscore is a ratio against the
 * vacancy's own qualification standard, so two positions with different bars
 * produce comparable 0–100 numbers.
 */
class FitScoreCalculator
{
    /** Related-field partial credit when the degree is not an exact QS match. */
    public const RELATED_FIELD_CREDIT = 0.7;

    public function __construct(private readonly SkillMatcher $matcher = new SkillMatcher())
    {
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array{fit: float, subscores: array<string, float>, matched: array<int, string>}
     */
    public function compute(array $parsed, Vacancy $vacancy): array
    {
        $education = $this->educationScore($parsed, (string) $vacancy->qs_education);

        $requiredHours = (int) ($vacancy->qs_training_hours ?? 0);
        $training = $requiredHours === 0
            ? 1.0
            : min(1.0, ((float) ($parsed['raw_training_hours'] ?? 0)) / $requiredHours);

        $requiredYears = (float) ($vacancy->qs_experience_years ?? 0);
        $experience = $requiredYears == 0.0
            ? 1.0
            : min(1.0, ((float) ($parsed['raw_experience_years'] ?? 0)) / $requiredYears);

        $eligibility = $this->eligibilityScore($parsed);

        [$skills, $matched] = $this->matcher->match(
            (array) ($vacancy->required_skills ?? []),
            (array) ($parsed['skills'] ?? []),
        );

        $subscores = [
            'education' => round($education, 4),
            'training' => round($training, 4),
            'experience' => round($experience, 4),
            'eligibility' => round($eligibility, 4),
            'skills' => round($skills, 4),
        ];

        $weights = (array) ($vacancy->weights ?? []);
        $fit = 100.0 * (
            ((float) ($weights['education'] ?? 0)) * $education
            + ((float) ($weights['training'] ?? 0)) * $training
            + ((float) ($weights['experience'] ?? 0)) * $experience
            + ((float) ($weights['eligibility'] ?? 0)) * $eligibility
            + ((float) ($weights['skills'] ?? 0)) * $skills
        );

        return ['fit' => round($fit, 2), 'subscores' => $subscores, 'matched' => $matched];
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    private function educationScore(array $parsed, string $qsEducation): float
    {
        $detected = mb_strtolower(implode(' ', (array) ($parsed['education'] ?? [])));

        if ($detected === '' || str_contains($detected, 'no education')) {
            return 0.0;
        }

        if (str_contains($detected, 'bachelor')
            || str_contains($detected, 'bs ')
            || str_contains($detected, 'ba ')
            || str_contains($detected, 'degree')) {
            $qs = mb_strtolower($qsEducation);

            return (str_contains($qs, 'bachelor') || str_contains($qs, 'degree'))
                ? 1.0
                : self::RELATED_FIELD_CREDIT;
        }

        if (str_contains($detected, 'college')) {
            return 0.5;
        }

        return self::RELATED_FIELD_CREDIT;
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    private function eligibilityScore(array $parsed): float
    {
        $detected = mb_strtolower(implode(' ', (array) ($parsed['eligibility'] ?? [])));

        if ($detected === '' || str_contains($detected, 'no eligibility')) {
            return 0.0;
        }

        return (str_contains($detected, 'professional')
            || str_contains($detected, 'civil service')
            || str_contains($detected, 'eligibility'))
            ? 1.0
            : 0.0;
    }
}
