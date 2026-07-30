<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Feature 4: Semantic Search — matching on meaning rather than exact keywords.
 *
 * This database is MySQL, so pgvector is not available. Rather than ship a
 * fake embedding store, this service does meaning-expansion at query time:
 * a curated HR synonym map handles the vocabulary this system actually uses,
 * and anything unrecognised is expanded once by the LLM and cached.
 *
 * So "maternity leave" also matches pregnancy / parental / maternity benefits,
 * which is the behaviour the spec asks for.
 *
 * If you later add a real embedding provider, implement it behind
 * expandTerms()/rank() and the callers will not need to change.
 */
class SemanticSearchService
{
    private const CACHE_TTL_SECONDS = 604800; // 7 days

    /**
     * Domain vocabulary for this HRIS. Each entry maps a concept to the terms
     * that should also match it. Kept deliberately explicit — these are the
     * words that appear in document_type values and file names here.
     *
     * @var array<string, array<int, string>>
     */
    private const SYNONYMS = [
        'maternity' => ['maternity', 'pregnancy', 'pregnant', 'parental', 'prenatal', 'childbirth', 'ml'],
        'paternity' => ['paternity', 'parental', 'pl'],
        'medical' => ['medical', 'health', 'clinic', 'hospital', 'physician', 'doctor', 'fit to work', 'sick'],
        'certificate' => ['certificate', 'certification', 'cert', 'credential', 'diploma', 'award'],
        'contract' => ['contract', 'agreement', 'appointment', 'memorandum', 'moa', 'employment contract'],
        'training' => ['training', 'seminar', 'workshop', 'course', 'webinar', 'orientation', 'ld', 'learning'],
        'government id' => ['government id', 'gsis', 'philhealth', 'pagibig', 'pag-ibig', 'tin', 'sss', 'umid'],
        'id card' => ['id card', 'identification', 'company id', 'employee id', 'badge'],
        'license' => ['license', 'licence', 'prc', 'professional license', 'driver'],
        'passport' => ['passport', 'travel document'],
        'birth certificate' => ['birth certificate', 'psa', 'nso', 'live birth'],
        'resume' => ['resume', 'cv', 'curriculum vitae', 'biodata', 'personal data sheet', 'pds'],
        'transcript' => ['transcript', 'tor', 'academic record', 'diploma'],
        'leave form' => ['leave form', 'leave application', 'csc form 6', 'application for leave'],
        'travel order' => ['travel order', 'itinerary', 'official travel', 'to'],
        'payslip' => ['payslip', 'pay slip', 'salary slip', 'payroll', 'remittance'],
        'evaluation' => ['evaluation', 'performance', 'appraisal', 'ipcr', 'opcr', 'rating'],
        'disciplinary' => ['disciplinary', 'memo', 'notice to explain', 'nte', 'suspension', 'reprimand'],
        'clearance' => ['clearance', 'nbi', 'police clearance', 'barangay', 'exit clearance'],
        'resignation' => ['resignation', 'separation', 'exit', 'clearance', 'turnover'],
    ];

    /**
     * Expand a single concept into the terms that should also match it.
     *
     * @return array<int, string>
     */
    public function expandTerms(string $term, ?User $user = null): array
    {
        $term = strtolower(trim($term));

        if ($term === '') {
            return [];
        }

        if ($direct = $this->lookupCurated($term)) {
            return $direct;
        }

        return $this->llmExpansion($term, $user);
    }

    /**
     * Expand a whole question into a flat set of search terms.
     *
     * @return array<int, string>
     */
    public function expandQuery(string $query, ?User $user = null): array
    {
        $terms = [];

        foreach (array_keys(self::SYNONYMS) as $concept) {
            if (str_contains(strtolower($query), $concept)) {
                $terms = array_merge($terms, self::SYNONYMS[$concept]);
            }
        }

        if (empty($terms)) {
            $keywords = $this->keywords($query);
            foreach (array_slice($keywords, 0, 3) as $keyword) {
                $terms = array_merge($terms, $this->expandTerms($keyword, $user));
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * Rank rows by how many expanded terms they hit. Used to order results
     * when the database itself cannot express relevance.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, string> $terms
     * @param array<int, string> $fields Row keys to match against.
     * @return array<int, array<string, mixed>>
     */
    public function rank(array $rows, array $terms, array $fields): array
    {
        if (empty($terms)) {
            return $rows;
        }

        $scored = array_map(function (array $row) use ($terms, $fields) {
            $haystack = strtolower(implode(' ', array_map(
                fn (string $field) => (string) ($row[$field] ?? ''),
                $fields
            )));

            $score = 0;
            foreach ($terms as $term) {
                if ($term !== '' && str_contains($haystack, strtolower($term))) {
                    $score++;
                }
            }

            return ['row' => $row, 'score' => $score];
        }, $rows);

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_map(fn (array $entry) => $entry['row'], $scored);
    }

    /**
     * @return array<int, string>|null
     */
    private function lookupCurated(string $term): ?array
    {
        if (isset(self::SYNONYMS[$term])) {
            return self::SYNONYMS[$term];
        }

        // Match on containment either direction: "medical certificate" should
        // pick up both the "medical" and "certificate" concepts.
        $hits = [];
        foreach (self::SYNONYMS as $concept => $synonyms) {
            if (str_contains($term, $concept) || str_contains($concept, $term)) {
                $hits = array_merge($hits, $synonyms);
            }

            foreach ($synonyms as $synonym) {
                if ($synonym === $term) {
                    $hits = array_merge($hits, $synonyms);
                    break;
                }
            }
        }

        return empty($hits) ? null : array_values(array_unique($hits));
    }

    /**
     * Ask the model for synonyms once per term, then cache. Falls back to the
     * bare term if no provider is configured, so search still works offline.
     *
     * @return array<int, string>
     */
    private function llmExpansion(string $term, ?User $user): array
    {
        $key = 'ai:semantic:' . md5($term);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($term, $user) {
            if (!AiChatService::isConfigured($user)) {
                return [$term];
            }

            $system = 'You expand HR search terms into synonyms for a Philippine government HRIS. '
                . 'Reply with ONLY a comma-separated list of 3-8 lowercase search terms, no explanation, no numbering.';

            $answer = AiChatService::chat(
                $user,
                $system,
                [['role' => 'user', 'content' => "Term: {$term}"]],
                0.2,
                120
            );

            if (!$answer) {
                return [$term];
            }

            $terms = array_filter(array_map(
                fn (string $t) => trim(strtolower($t)),
                explode(',', strip_tags($answer))
            ), fn (string $t) => $t !== '' && mb_strlen($t) <= 40);

            $terms[] = $term;

            return array_values(array_unique($terms));
        });
    }

    /**
     * Content words from a question, stopwords removed.
     *
     * @return array<int, string>
     */
    private function keywords(string $query): array
    {
        $stop = ['show', 'find', 'me', 'all', 'the', 'a', 'an', 'of', 'for', 'to', 'in', 'on', 'is', 'are',
            'what', 'which', 'who', 'where', 'give', 'list', 'get', 'about', 'and', 'or', 'with', 'files',
            'file', 'document', 'documents', 'please', 'employee', 'employees'];

        $words = preg_split('/[^a-z]+/i', strtolower($query)) ?: [];

        return array_values(array_filter(
            $words,
            fn (string $w) => mb_strlen($w) > 2 && !in_array($w, $stop, true)
        ));
    }
}
