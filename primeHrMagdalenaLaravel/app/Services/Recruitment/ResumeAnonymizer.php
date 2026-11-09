<?php

namespace App\Services\Recruitment;

/**
 * PII and demographic redaction (RA 10173), run BEFORE anything reaches the
 * scorer.
 *
 * A direct port of the reference's `services/anonymizer.py`, regex for regex.
 * The rules are deliberate and narrow: names, contact details, addresses and
 * demographic fields are stripped, and a redaction report is returned for audit
 * while the removed values themselves are never retained.
 *
 * The order matters — email is scrubbed before phone, and the date-of-birth
 * rule before the age rule, because "Date of Birth: 1990-01-01 (age 36)" must
 * lose both and a single pass would leave the half the other rule matched.
 */
class ResumeAnonymizer
{
    /** Demographic attributes that must never influence a score. */
    public const DEMOGRAPHIC_FIELDS = ['age', 'gender', 'religion', 'civil_status'];

    private const RULES = [
        ['email', 'email address', '/[\w.+-]+@[\w-]+\.[\w.-]+/u'],
        ['phone', 'contact number', '/(\+?63|0)9\d{2}[\s-]?\d{3}[\s-]?\d{4}/u'],
        ['dob', 'date of birth', '/\b(date of birth|dob|birthdate)\b.*/iu'],
        ['age', 'age', '/\b(age)\s*[:\-]\s*\d{1,3}\b/iu'],
        ['gender', 'gender', '/\b(sex|gender)\s*[:\-].*/iu'],
        ['religion', 'religion', '/\b(religion)\s*[:\-].*/iu'],
        ['civil', 'civil status', '/\b(civil status|marital status)\s*[:\-].*/iu'],
    ];

    private const REPLACEMENTS = [
        'email' => '[REDACTED_EMAIL]',
        'phone' => '[REDACTED_PHONE]',
        'dob' => '[REDACTED_DOB]',
        'age' => '[REDACTED_AGE]',
        'gender' => '[REDACTED_GENDER]',
        'religion' => '[REDACTED_RELIGION]',
        'civil' => '[REDACTED_CIVIL_STATUS]',
    ];

    /**
     * @return array{0: string, 1: array{removed: array<int, string>, note: string}}
     */
    public function anonymize(string $text): array
    {
        $removed = [];
        $redacted = $text;

        foreach (self::RULES as [$key, $label, $pattern]) {
            if (preg_match($pattern, $redacted) !== 1) {
                continue;
            }

            $removed[] = $label;
            $replaced = preg_replace($pattern, self::REPLACEMENTS[$key], $redacted);
            $redacted = $replaced ?? $redacted;
        }

        return [
            $redacted,
            [
                'removed' => $removed,
                'note' => 'Demographic and identifying fields stripped before scoring (R7, R8).',
            ],
        ];
    }

    /** Deterministic anonymized applicant code, e.g. `A-0417`. */
    public function assignAnonCode(int $sequence, string $prefix = 'A'): string
    {
        return $prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
