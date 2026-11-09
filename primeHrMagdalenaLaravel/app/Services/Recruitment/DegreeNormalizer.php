<?php

namespace App\Services\Recruitment;

/**
 * Canonicalizes raw education strings before scoring.
 *
 * Ported from `services/degree_normalizer.py`. It preserves the level-versus-
 * field distinction: unrelated programs never collapse into one another.
 *
 * Lookup is resilient on purpose, and the fallbacks are applied in this order
 * because each catches a shape the previous one cannot:
 *
 *   1. punctuation → space, whitespace collapsed, possessives stripped;
 *   2. the raw lower-cased term (covers keys written with punctuation);
 *   3. all spaces removed ("b s e s" → "bses", "m s" → "ms");
 *   4. punctuation and whitespace stripped from the raw value.
 *
 * An unmatched term is preserved lower-cased rather than dropped, because the
 * scorer's own "bachelor"/"college"/"degree" checks read these strings.
 */
final class DegreeNormalizer
{
    /**
     * @param  array<int, string>  $terms
     * @return array<int, string>
     */
    public function normalize(array $terms): array
    {
        if ($terms === []) {
            return [];
        }

        $out = [];
        $seen = [];

        foreach ($terms as $raw) {
            $raw = (string) $raw;
            $key = $this->canonicalizeKey($raw);

            $canonical = DegreeAliases::ALIASES[$key]
                ?? DegreeAliases::ALIASES[mb_strtolower(trim($raw))]
                ?? null;

            if ($canonical === null) {
                $canonical = DegreeAliases::ALIASES[str_replace(' ', '', $key)] ?? null;
            }

            if ($canonical === null) {
                $alt = preg_replace('/[.\s]+/u', '', mb_strtolower(trim($raw)));
                $canonical = $alt === null ? null : (DegreeAliases::ALIASES[$alt] ?? null);
            }

            $value = $canonical ?? mb_strtolower(trim($raw));

            if ($value !== '' && ! isset($seen[$value])) {
                $seen[$value] = true;
                $out[] = $value;
            }
        }

        sort($out, SORT_STRING);

        return $out;
    }

    public function isBachelorCanonical(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), DegreeAliases::BACHELOR_CANONICALS, true);
    }

    private function canonicalizeKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[.\-_\/]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim(str_replace(["'s", '’s'], '', $value));
    }
}
