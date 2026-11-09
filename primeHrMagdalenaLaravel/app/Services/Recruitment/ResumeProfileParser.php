<?php

namespace App\Services\Recruitment;

/**
 * Structured profile extraction from anonymized resume text.
 *
 * The reference had two paths: spaCy NER when `en_core_web_md` was installed,
 * and a rule-based fallback (gazetteers + regexes + a Title-Case letterhead
 * heuristic) that it documented as fully functional offline. The native port
 * implements **only the fallback**, which was the decision recorded in
 * docs/hiring-backend-native-port.md §15.1. Nothing here claims a model ran:
 * the screening result's `model_version` says `php-fallback-regex-1.0`.
 *
 * The fidelity that is lost is NER-accurate organisation names; everything the
 * scorer actually reads — education level, training hours, experience years,
 * eligibility, matched skills — is derived by the same rules as before.
 */
class ResumeProfileParser
{
    /** Bilingual (English + Tagalog/Taglish) gazetteers. */
    public const EDUCATION_TERMS = [
        'bachelor', 'bs ', 'ba ', 'college', 'degree', 'master', 'doctorate',
        'vocational', 'high school', 'kolehiyo', 'pagtatapos', 'diploma',
    ];

    public const ELIGIBILITY_TERMS = [
        'cs professional', 'cs sub-professional', 'civil service', 'career service',
        'second level eligibility', 'first level eligibility', 'ra 1080', 'board exam',
        'licensure', 'prc', 'eligibility',
    ];

    /** Lines near the top that are headings, never a person's name. */
    private const NAME_STOPWORDS = [
        'resume', 'curriculum vitae', 'cv', 'application', 'applicant',
        'personal data sheet', 'objective', 'summary', 'career objective',
    ];

    public function __construct(
        private readonly DegreeNormalizer $degrees = new DegreeNormalizer(),
        private readonly ExperienceDateParser $dates = new ExperienceDateParser(),
    ) {
    }

    /**
     * Return a structured profile aligned with the DB `parsed` JSON.
     *
     * @param  array<int, string>  $knownSkills  the vacancy's required skills
     * @return array<string, mixed>
     */
    public function parse(string $text, array $knownSkills = []): array
    {
        $lower = mb_strtolower($text);

        $education = $this->normalizeEducation($lower);
        $eligibility = $this->findTerms($lower, self::ELIGIBILITY_TERMS);

        $trainingHours = $this->maxNumber('/(\d{1,4})\s*(hours|hrs|oras)\b/iu', $text);
        $explicitYears = $this->maxFloat('/(\d{1,2}(?:\.\d)?)\s*(years|yrs|taon)\b/iu', $text);

        // Date-range-derived years supplement an explicit "X years" claim; the
        // larger of the two wins, so neither rule can shrink a candidate.
        $dateYears = $this->dates->totalYearsFromText($text);
        $ranges = $this->dates->rangesForAudit($text);
        $experienceYears = max($explicitYears, $dateYears);

        $skills = [];

        foreach ($knownSkills as $skill) {
            $needle = mb_strtolower(trim((string) $skill));

            if ($needle !== '' && mb_strpos($lower, $needle) !== false) {
                $skills[] = $skill;
            }
        }

        $experienceLabel = $this->formatFloat($experienceYears).' years detected'
            .($ranges !== [] ? ' ('.count($ranges).' range(s) merged)' : '');

        return [
            'education' => $education !== [] ? $education : ['(no education detected)'],
            'experience' => [$experienceLabel],
            'training' => [$trainingHours.' hours detected'],
            'eligibility' => $eligibility !== [] ? $eligibility : ['(no eligibility detected)'],
            'skills' => $skills,
            'raw_experience_years' => $experienceYears,
            'raw_training_hours' => $trainingHours,
            'experience_ranges' => $ranges,
            'experience_present_date' => $ranges !== [] ? BusinessClock::today()->toDateString() : null,
        ];
    }

    /**
     * Best-effort extraction of the applicant's name from raw resume text.
     *
     * With no NER, this is the reference's own fallback: the first short,
     * Title-Case, letters-only line near the top of the document — where a
     * letterhead sits. It is a guess about layout, which is why the result is
     * display-only and never a matching criterion.
     */
    public function extractName(string $text): ?string
    {
        $head = mb_substr($text, 0, 2000);

        foreach (preg_split('/\r\n|\r|\n/', $head) ?: [] as $line) {
            $candidate = trim($line);

            if ($candidate === '' || in_array(mb_strtolower($candidate), self::NAME_STOPWORDS, true)) {
                continue;
            }

            if (str_contains($candidate, '@') || preg_match('/\d/', $candidate) === 1) {
                continue;
            }

            if (preg_match("/^[A-Za-z][A-Za-z.,'\\-\\s]{1,60}[A-Za-z.]$/u", $candidate) === 1) {
                $words = preg_split('/\s+/', $candidate) ?: [];

                if (count($words) >= 2 && count($words) <= 5) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeEducation(string $lower): array
    {
        $raw = $this->findTerms($lower, self::EDUCATION_TERMS);
        $aliasHits = [];

        // Alias hits catch degrees the gazetteer does not name, e.g. "BSES" or
        // a bare "MA". Short alphabetic keys need word boundaries or "ma"
        // matches inside "management" and "ms" inside "systems".
        foreach (array_keys(DegreeAliases::ALIASES) as $alias) {
            $alias = (string) $alias;

            if (mb_strlen($alias) <= 3 && ctype_alpha($alias)) {
                if (preg_match('/\b'.preg_quote($alias, '/').'\b/u', $lower) === 1) {
                    $aliasHits[] = $alias;
                }
            } elseif (mb_strpos($lower, $alias) !== false) {
                $aliasHits[] = $alias;
            }
        }

        $education = $this->degrees->normalize(array_merge($raw, $aliasHits));
        $enviSci = 'bachelor of science in environmental science';

        if (mb_strpos($lower, 'bses') !== false && ! in_array($enviSci, $education, true)) {
            $education = $this->degrees->normalize(array_merge($education, ['bses']));
        }

        return $education;
    }

    /**
     * @param  array<int, string>  $terms
     * @return array<int, string>
     */
    private function findTerms(string $lower, array $terms): array
    {
        $found = [];

        foreach ($terms as $term) {
            if ($term !== '' && mb_strpos($lower, $term) !== false) {
                $found[trim($term)] = true;
            }
        }

        $out = array_keys($found);
        sort($out, SORT_STRING);

        return $out;
    }

    private function maxNumber(string $pattern, string $text): int
    {
        if (preg_match_all($pattern, $text, $matches) === false || $matches[1] === []) {
            return 0;
        }

        return max(array_map('intval', $matches[1]));
    }

    private function maxFloat(string $pattern, string $text): float
    {
        if (preg_match_all($pattern, $text, $matches) === false || $matches[1] === []) {
            return 0.0;
        }

        return max(array_map('floatval', $matches[1]));
    }

    /** `3` → `3.0`, so the audit string matches the reference's Python float. */
    private function formatFloat(float $value): string
    {
        $formatted = (string) $value;

        return str_contains($formatted, '.') ? $formatted : $formatted.'.0';
    }
}
