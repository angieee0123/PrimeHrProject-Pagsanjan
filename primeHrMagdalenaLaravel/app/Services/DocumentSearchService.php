<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentExtraction;
use App\Models\Employee;
use App\Models\GovernmentId;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Feature 2: Intelligent File Retrieval.
 *
 * Finds uploaded files across the system without the user browsing folders.
 * Files live in several places in this app, so we search all of them:
 *   - documents.file_path          (general employee uploads)
 *   - trainings.certificate_path   (training certificates)
 *   - leave_applications.attachment_path / travel_orders.attachment
 *
 * Every result is filtered through AiAccessPolicy first.
 */
class DocumentSearchService
{
    private const MAX_RESULTS = 50;

    /** Cap on the clickable file cards shown in chat — a broad search still
     *  narrates and tables every match, but the thread shouldn't turn into a
     *  wall of thumbnails. */
    private const MAX_FILE_CARDS = 12;

    /** Extension → human label, used for describing hits. */
    private const TYPE_LABELS = [
        'pdf' => 'PDF',
        'doc' => 'Word document',
        'docx' => 'Word document',
        'xls' => 'Excel spreadsheet',
        'xlsx' => 'Excel spreadsheet',
        'csv' => 'CSV',
        'jpg' => 'Image',
        'jpeg' => 'Image',
        'png' => 'Image',
        'gif' => 'Image',
        'webp' => 'Image',
    ];

    public function __construct(
        private AiAccessPolicy $policy,
        private SemanticSearchService $semantic,
        private AiFileResolver $resolver,
    ) {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function search(User $user, string $query, array $history = []): array
    {
        $params = $this->parseDocumentQuery($query);

        $rows = $this->searchDocuments($user, $params)
            ->concat($this->searchTrainingCertificates($user, $params))
            ->concat($this->searchEmployeePhotos($user, $params))
            ->concat($this->searchGovernmentIds($user, $params))
            ->sortByDesc('uploaded_at')
            ->take(self::MAX_RESULTS)
            ->values();

        $files = $this->buildFileAttachments($rows);

        return [
            'answer' => $this->narrate($user, $query, $rows, $params, $history, $files),
            'data' => $rows->map(fn (array $r) => collect($r)->except(['file_path', 'url'])->all())->all(),
            'count' => $rows->count(),
            'files' => $files,
        ];
    }

    /**
     * The clickable file cards the chat UI renders — a smaller, browser-ready
     * view distinct from `data` (the full row set used for narration/tables).
     *
     * Two things happen here that matter:
     *
     *  - a row whose file is not actually on disk is dropped, so the chat never
     *    offers a card that 404s when clicked;
     *  - the URL is a reference to the database row (`documents/41`), never a
     *    storage path, so opening it re-runs the permission check.
     *
     * @param Collection<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildFileAttachments(Collection $rows): array
    {
        return $rows
            ->filter(fn (array $r) => $this->resolver->existsOnDisk($r['file_path'] ?? null))
            ->take(self::MAX_FILE_CARDS)
            ->map(function (array $r) {
                $ref = (string) $r['id'];
                $isImage = $this->resolver->isImage($r['file_name']);

                return [
                    'id' => $r['source'] . '-' . $ref,
                    'source' => $r['source'],
                    'ref' => $ref,
                    'name' => $r['file_name'],
                    'type' => strtolower(pathinfo((string) $r['file_name'], PATHINFO_EXTENSION)),
                    'is_image' => $isImage,
                    'label' => $r['document_type'],
                    'employee_name' => $r['employee_name'],
                    'uploaded_at' => $r['uploaded_at'],
                    'size' => $r['file_size'],
                    'url' => $this->fileUrl($r['source'], $ref),
                    'download_url' => $this->fileUrl($r['source'], $ref) . '?download=1',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The authenticated, policy-checked endpoint that streams a file. Built
     * from the row's identity rather than its path — see AiFileController.
     */
    private function fileUrl(string $source, string $ref): string
    {
        return route('ai-assistant.file', ['source' => $source, 'ref' => $ref], false);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseDocumentQuery(string $query): array
    {
        $params = [];

        if (preg_match('/"([^"]+)"/', $query, $m)) {
            $params['employee_name'] = trim($m[1]);
        } elseif (preg_match('/\b(?:for|of|belonging\s+to|related\s+to|about)\s+(?:employee\s+)?([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/', $query, $m)) {
            $params['employee_name'] = trim($m[1]);
        } elseif (preg_match('/\b(?:for|of|belonging\s+to|about)\s+(?:employee\s+)?((?:[a-z]+\s+){0,3}[a-z]+)\s*$/i', $query, $m)) {
            // Same phrasing typed without capitals ("photo of juan dela cruz").
            // Only the tail of the question is considered, and only once the
            // stopwords that would otherwise be read as a name are stripped.
            $name = $this->stripNonNameWords($m[1]);

            if ($name !== '') {
                $params['employee_name'] = $name;
            }
        }

        if (preg_match('/\b(?:employee\s*)?(?:id|no\.?)\s*[:#]?\s*(\d{4}-\d{2,4}|\d+)\b/i', $query, $m)) {
            $params['employee_id'] = $m[1];
        }

        // Document category. Expanded semantically so "medical certificate"
        // also matches rows typed as "med cert" / "health clearance".
        // Trailing (?:s|es)? so "contracts" and "certificates" match the same
        // concept as their singular forms.
        if (preg_match('/\b(contract|agreement|appointment|certificate|training|medical|health|clearance|licen[sc]e|passport|photo|picture|image|id\s*card|government\s*id|gsis|philhealth|pag-?ibig|tin|birth\s*certificate|diploma|transcript|resume|cv|leave\s*form|travel\s*order|payslip|memo|evaluation|disciplinary)(?:s|es)?\b/i', $query, $m)) {
            $params['document_type'] = strtolower(trim($m[1]));
            $params['type_terms'] = $this->semantic->expandTerms($params['document_type']);
        }

        if (preg_match('/\b(latest|most\s+recent|newest|last)\b/i', $query)) {
            $params['latest_only'] = true;
        }

        if (preg_match('/\b(pdf|docx?|xlsx?|image|photo|scan(?:ned)?)\b/i', $query, $m)) {
            $params['extension_hint'] = strtolower($m[1]);
        }

        return $params;
    }

    /**
     * Words that follow "of"/"for" but are never part of a person's name.
     * Without this, "files of the employees" searches for an employee called
     * "the employees" and returns nothing.
     */
    private function stripNonNameWords(string $candidate): string
    {
        $stopwords = [
            'the', 'a', 'an', 'all', 'any', 'our', 'my', 'his', 'her', 'their', 'this', 'that', 'these', 'those',
            'employee', 'employees', 'staff', 'everyone', 'anyone', 'him', 'them', 'us', 'me', 'it',
            'file', 'files', 'document', 'documents', 'photo', 'photos', 'picture', 'pictures', 'image', 'images',
            'scan', 'scans', 'record', 'records', 'upload', 'uploads', 'attachment', 'attachments',
            'department', 'office', 'system', 'database', 'today', 'yesterday',
        ];

        $words = preg_split('/\s+/', trim($candidate)) ?: [];
        $words = array_values(array_filter(
            $words,
            fn (string $w) => $w !== '' && !in_array(strtolower($w), $stopwords, true)
        ));

        return implode(' ', $words);
    }

    /**
     * @param array<string, mixed> $params
     * @return Collection<int, array<string, mixed>>
     */
    private function searchDocuments(User $user, array $params): Collection
    {
        $query = Document::query()->with('employee');

        $this->applyEmployeeFilters($query, $params);

        if (!empty($params['type_terms'])) {
            $terms = $params['type_terms'];

            // Matches on the document's own type/filename, or on text extracted
            // from inside it (see DocumentProcessingService) — which is what
            // makes "the contract that mentions X" findable at all.
            $withMatchingContent = DocumentExtraction::query()
                ->where('source_type', 'document')
                ->where(function (Builder $q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('content', 'like', "%{$term}%");
                    }
                })
                ->pluck('source_id');

            $query->where(function (Builder $q) use ($terms, $withMatchingContent) {
                foreach ($terms as $term) {
                    $q->orWhere('document_type', 'like', "%{$term}%")
                        ->orWhere('file_path', 'like', "%{$term}%");
                }

                if ($withMatchingContent->isNotEmpty()) {
                    $q->orWhereIn('id', $withMatchingContent);
                }
            });
        }

        $this->policy->scopeByEmployeeId($query, $user);

        $documents = $query->orderByDesc('uploaded_at')->limit(self::MAX_RESULTS)->get();

        if (!empty($params['latest_only'])) {
            $documents = $documents->unique(fn (Document $d) => $d->employee_id . '|' . $d->document_type);
        }

        return $documents->map(fn (Document $doc) => [
            'id' => $doc->id,
            'source' => 'documents',
            'employee_id' => $doc->employee_id,
            'employee_name' => $this->employeeName($doc->employee),
            'document_type' => $doc->document_type,
            'file_name' => basename((string) $doc->file_path),
            'file_type' => $this->typeLabel($doc->file_path),
            'file_size' => $this->humanFileSize($doc->file_path),
            'uploaded_at' => $doc->uploaded_at,
            'status' => $doc->status,
            'file_path' => $doc->file_path,
        ])->values();
    }

    /**
     * Training certificates live on their own table rather than in documents.
     *
     * @param array<string, mixed> $params
     * @return Collection<int, array<string, mixed>>
     */
    private function searchTrainingCertificates(User $user, array $params): Collection
    {
        $type = $params['document_type'] ?? null;
        if ($type !== null && !in_array($type, ['certificate', 'training'], true)) {
            return collect();
        }

        $query = Training::query()->with('employee')->whereNotNull('certificate_path');

        $this->applyEmployeeFilters($query, $params);
        $this->policy->scopeByEmployeeId($query, $user);

        return $query->orderByDesc('date_to')->limit(self::MAX_RESULTS)->get()
            ->map(fn (Training $training) => [
                'id' => $training->id,
                'source' => 'trainings',
                'employee_id' => $training->employee_id,
                'employee_name' => $this->employeeName($training->employee),
                'document_type' => 'Training certificate — ' . $training->title,
                'file_name' => basename((string) $training->certificate_path),
                'file_type' => $this->typeLabel($training->certificate_path),
                'file_size' => $this->humanFileSize($training->certificate_path),
                'uploaded_at' => $training->date_to ?? $training->created_at,
                'status' => $training->status,
                'file_path' => $training->certificate_path,
            ])->values();
    }

    /**
     * Feature 9: Image search. Employee photos are not rows in `documents` —
     * they hang off employees.photo as a public URL — so they are searched
     * separately and folded into the same result shape.
     *
     * @param array<string, mixed> $params
     * @return Collection<int, array<string, mixed>>
     */
    private function searchEmployeePhotos(User $user, array $params): Collection
    {
        $wantsPhoto = ($params['document_type'] ?? null) === null
            || in_array($params['document_type'], ['photo', 'picture', 'image', 'id card'], true);

        if (!$wantsPhoto) {
            return collect();
        }

        $query = Employee::query()->whereNotNull('photo')->where('photo', '!=', '');

        if (!empty($params['employee_name'])) {
            $name = $params['employee_name'];
            $query->where(function (Builder $q) use ($name) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$name}%"])
                    ->orWhere('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        if (!empty($params['employee_id'])) {
            $query->where('employee_id', $params['employee_id']);
        }

        $this->policy->scopeEmployeeQuery($query, $user);

        return $query->limit(self::MAX_RESULTS)->get()
            ->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'source' => 'employee_photo',
                'employee_id' => $employee->id,
                'employee_name' => $this->employeeName($employee),
                'document_type' => 'Employee photo',
                'file_name' => basename((string) $employee->photo),
                'file_type' => $this->typeLabel($employee->photo),
                'file_size' => $this->humanFileSize($employee->photo),
                'uploaded_at' => $employee->created_at,
                'status' => 'active',
                'file_path' => $employee->photo,
                'url' => $employee->photo,
            ])->values();
    }

    /**
     * Government ID scans (GSIS, PhilHealth, Pag-IBIG, TIN, professional
     * license) — one `government_ids` row per employee, but up to five
     * scans, so each populated column becomes its own result row.
     *
     * @param array<string, mixed> $params
     * @return Collection<int, array<string, mixed>>
     */
    private function searchGovernmentIds(User $user, array $params): Collection
    {
        $wantsGovId = ($params['document_type'] ?? null) === null
            || in_array($params['document_type'], ['government id', 'id card', 'license', 'gsis', 'philhealth', 'pag-ibig', 'pagibig', 'tin'], true);

        if (!$wantsGovId) {
            return collect();
        }

        $query = GovernmentId::query()->with('employee');

        $this->applyEmployeeFilters($query, $params);
        $this->policy->scopeByEmployeeId($query, $user);

        $scanColumns = [
            'gsis_file_path' => 'GSIS ID scan',
            'philhealth_file_path' => 'PhilHealth ID scan',
            'pagibig_file_path' => 'Pag-IBIG ID scan',
            'tin_file_path' => 'TIN ID scan',
            'license_file_path' => 'Professional License scan',
        ];

        $rows = collect();

        foreach ($query->get() as $govId) {
            foreach ($scanColumns as $column => $label) {
                $path = $govId->{$column};
                if (!$path) {
                    continue;
                }

                $rows->push([
                    'id' => $govId->id . '-' . $column,
                    'source' => 'government_ids',
                    'employee_id' => $govId->employee_id,
                    'employee_name' => $this->employeeName($govId->employee),
                    'document_type' => $label,
                    'file_name' => basename($path),
                    'file_type' => $this->typeLabel($path),
                    'file_size' => $this->humanFileSize($path),
                    'uploaded_at' => $govId->employee?->created_at,
                    'status' => 'active',
                    'file_path' => $path,
                    'url' => $path,
                ]);
            }
        }

        return $rows->values();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function applyEmployeeFilters(Builder $query, array $params): void
    {
        if (!empty($params['employee_name'])) {
            $name = $params['employee_name'];
            $query->whereHas('employee', function (Builder $q) use ($name) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$name}%"])
                    ->orWhere('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        if (!empty($params['employee_id'])) {
            $employeeNumber = $params['employee_id'];
            $query->whereHas('employee', fn (Builder $q) => $q->where('employee_id', $employeeNumber));
        }
    }

    private function employeeName($employee): ?string
    {
        if (!$employee) {
            return null;
        }

        return trim(implode(' ', array_filter([$employee->first_name, $employee->last_name])));
    }

    private function typeLabel(?string $path): string
    {
        $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return self::TYPE_LABELS[$ext] ?? ($ext !== '' ? strtoupper($ext) : 'Unknown');
    }

    /**
     * Files in this app are written to the public disk; AiFileResolver falls
     * back to the default disk before giving up, so we do not report a size of
     * "Unknown" for perfectly readable files. It also normalises the two path
     * shapes in use (bare disk paths and stored "/storage/…" URLs).
     */
    private function humanFileSize(?string $path): string
    {
        $disk = $this->resolver->diskFor($path);

        if ($disk === null) {
            return 'Unknown';
        }

        try {
            $bytes = Storage::disk($disk)->size((string) $this->resolver->toDiskPath($path));
        } catch (\Throwable) {
            return 'Unknown';
        }

        return match (true) {
            $bytes < 1024 => $bytes . ' B',
            $bytes < 1048576 => round($bytes / 1024, 1) . ' KB',
            default => round($bytes / 1048576, 2) . ' MB',
        };
    }

    /**
     * Resolve a stored file for download, enforcing ownership first.
     * Delegates to AiFileResolver so there is exactly one gate in front of
     * assistant-served files.
     *
     * @return array{success: bool, disk?: string, path?: string, file_name?: string, error?: string}
     */
    public function resolveForDownload(User $user, int $documentId): array
    {
        return $this->resolver->resolve($user, 'documents', (string) $documentId);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @param array<string, mixed> $params
     * @param array<int, array{role: string, content: string}> $history
     * @param array<int, array<string, mixed>> $files
     */
    private function narrate(User $user, string $query, Collection $rows, array $params, array $history, array $files): string
    {
        $notice = $this->policy->scopeNotice($user);

        if ($rows->isEmpty()) {
            $hint = !empty($params['employee_name'])
                ? " for {$params['employee_name']}"
                : '';

            // For a self-scoped caller "no files found for Pedro Santos" is not
            // a statement about Pedro's files — it means the search never left
            // this user's own records. Say which.
            return "I could not find any uploaded files{$hint} matching that description. "
                . 'Try naming the employee, or the kind of document (contract, certificate, medical, ID).'
                . ($notice ? "\n\n{$notice}" : '');
        }

        $scopeNote = $this->policy->scopePromptNote($user);

        $system = <<<PROMPT
You are the PRIME HRIS Assistant reporting the result of a file search. The
list below is the complete result set from the HR database, already filtered to
files this user may access. It is your ONLY source of truth.

{$scopeNote}

- Say how many files matched and name the most relevant ones.
- For each, give the employee, document type, and upload date.
- If the user asked for the "latest", lead with the newest one.
- Use ONLY file names, employees, and dates that appear in the data. Do not
  invent a file, a date, a path, or a download link, and do not guess at what a
  file contains — you have not read it.
- If the data does not answer the question, say so plainly rather than filling
  the gap.
- The files themselves are shown to the user as cards beneath your reply, so do
  not write out links.
- Under 150 words, conversational, no raw JSON.
PROMPT;

        $payload = json_encode(
            $rows->take(20)->map(fn (array $r) => collect($r)->except(['file_path', 'url'])->all())->all(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        $missing = $rows->count() - count($files);
        $note = $missing > 0
            ? "\n\nNote: {$missing} of these records have no file present in storage — mention that they are recorded but the file is missing."
            : '';

        $messages = $history;
        $messages[] = [
            'role' => 'user',
            'content' => "Question: {$query}\n\nMatched {$rows->count()} file(s):\n{$payload}{$note}",
        ];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 700);

        // Last line of defence against a model that names a file we never found.
        // The prompt already forbids it; this makes the guarantee structural.
        if (!$answer || $this->mentionsUnknownFile($answer, $rows)) {
            $answer = $this->fallbackNarration($rows);
        }

        return $answer . ($notice ? "\n\n{$notice}" : '');
    }

    /**
     * True when the narration contains something shaped like a file name that
     * is not in the result set. Only filename-like tokens are checked — prose
     * about the search itself is left alone.
     *
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function mentionsUnknownFile(string $answer, Collection $rows): bool
    {
        if (!preg_match_all('/[\w\-. ()]+\.(pdf|docx?|xlsx?|csv|jpe?g|png|gif|webp|bmp)\b/i', $answer, $matches)) {
            return false;
        }

        $known = $rows->map(fn (array $r) => strtolower(trim((string) $r['file_name'])))->all();

        foreach ($matches[0] as $mentioned) {
            $mentioned = strtolower(trim($mentioned));

            // A model may shorten a stored name; accept a mention that any real
            // file name contains, and reject anything with no basis in the data.
            $grounded = false;
            foreach ($known as $name) {
                if ($name !== '' && ($name === $mentioned || str_contains($name, $mentioned))) {
                    $grounded = true;
                    break;
                }
            }

            if (!$grounded) {
                Log::channel('ai_audit')->warning('assistant.ungrounded_file_mention', [
                    'mentioned' => $mentioned,
                    'result_count' => $rows->count(),
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function fallbackNarration(Collection $rows): string
    {
        $lines = $rows->take(10)->map(function (array $row) {
            $date = $row['uploaded_at'] ? ' · ' . \Illuminate\Support\Carbon::parse($row['uploaded_at'])->format('M d, Y') : '';

            return "- {$row['file_name']} ({$row['document_type']}) — {$row['employee_name']}{$date}";
        })->implode("\n");

        $more = $rows->count() > 10 ? "\n…and " . ($rows->count() - 10) . ' more.' : '';

        return "Found {$rows->count()} file(s):\n{$lines}{$more}";
    }
}
