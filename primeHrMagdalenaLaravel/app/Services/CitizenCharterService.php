<?php

namespace App\Services;

use App\Models\CitizenCharter;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * The Citizen's Charter knowledge base.
 *
 * One active document, imported by an admin under Settings → AI / Chatbot,
 * that every chatbot surface answers municipality-information questions from:
 * the full-page AI Assistant, both floating chatheads, the mobile API, and the
 * public welcome-page widget. The charter is public municipal information, so
 * nothing here is scoped through AiAccessPolicy — the same text answers an
 * employee, an HR officer, and a logged-out visitor.
 *
 * Retrieval is deliberately lexical, not semantic: the question's own words are
 * scored against chunks of the stored text, and the model (when one is
 * configured) narrates only the top chunks. With no provider the answer is the
 * chunks themselves, quoted verbatim — a correct excerpt beats a paraphrase the
 * system cannot verify, and both beat an invented list of "usual" municipal
 * requirements.
 */
class CitizenCharterService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'docx'];

    /** What we ask for; UploadLimits clamps it to what PHP will actually take. */
    public const DESIRED_MAX_KB = 20480;

    private const DISK = 'public';

    private const DIRECTORY = 'citizen-charters';

    /** Below this many characters a PDF is treated as scanned, not digital. */
    private const TEXT_LAYER_THRESHOLD = 40;

    private const CHUNK_SIZE = 900;

    private const MAX_EXCERPT_CHARS = 3000;

    /** Question words too common to score on, English + Tagalog. */
    private const STOPWORDS = [
        'the', 'and', 'for', 'with', 'from', 'that', 'this', 'what', 'whats', 'when',
        'where', 'which', 'while', 'have', 'has', 'had', 'are', 'was', 'were', 'been',
        'can', 'could', 'should', 'would', 'will', 'may', 'need', 'needs', 'want',
        'how', 'much', 'many', 'long', 'take', 'takes', 'get', 'getting', 'apply',
        'about', 'there', 'their', 'they', 'them', 'your', 'yours', 'our', 'ours',
        'ako', 'ko', 'mo', 'namin', 'natin', 'ang', 'ng', 'mga', 'ano', 'anong',
        'paano', 'saan', 'ilan', 'ilang', 'meron', 'mayroon', 'po', 'lang', 'din',
        'rin', 'ba', 'kailangan', 'para', 'kung', 'ito', 'iyan', 'iyon', 'siya',
        'kami', 'kayo', 'sila', 'atin', 'iyo', 'kanila', 'nila', 'nya', 'nya',
    ];

    public function __construct(private ?OcrService $ocr = null)
    {
    }

    /**
     * Validation rules for the Settings import, in the same shape the wizard
     * steps use: `extensions:` gates the format (it blocks executables on its
     * own), a generous `mimetypes:` sits behind it so real files are not
     * refused for a narrow MIME guess, and the ceiling is read from PHP rather
     * than typed — see UploadLimits.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        $maxKb = UploadLimits::perFileKb(self::DESIRED_MAX_KB);

        return [
            'charter' => [
                'required', 'file',
                'extensions:pdf,docx',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,application/octet-stream',
                "max:{$maxKb}",
            ],
        ];
    }

    public static function maxKb(): int
    {
        return UploadLimits::perFileKb(self::DESIRED_MAX_KB);
    }

    /**
     * Store an uploaded charter, extract its text, and — only when extraction
     * produced usable text — make it the active one.
     *
     * A failed import keeps the previous charter in place (and reports the
     * failure) rather than leaving the chatbot with nothing to answer from.
     *
     * @return array{ok: bool, message: string, charter?: CitizenCharter}
     */
    public function import(UploadedFile $file, ?int $uploadedBy = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . Str::random(8) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;
        $storedPath = $file->storeAs(self::DIRECTORY, $filename, self::DISK);

        $charter = CitizenCharter::create([
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'is_active' => false,
            'uploaded_by' => $uploadedBy,
        ]);

        try {
            $extracted = $this->extract(Storage::disk(self::DISK)->path($storedPath), $extension);
        } catch (\Throwable $e) {
            Log::warning('Citizen charter extraction failed', ['path' => $storedPath, 'error' => $e->getMessage()]);
            $charter->update(['status' => 'failed', 'error' => 'Could not read text from this file.']);

            return ['ok' => false, 'message' => 'The file was saved but no readable text could be extracted. The previous charter (if any) is still in use.', 'charter' => $charter->fresh()];
        }

        if ($extracted['status'] !== 'extracted' || trim((string) ($extracted['text'] ?? '')) === '') {
            $charter->update([
                'status' => $extracted['status'],
                'error' => $extracted['error'] ?? 'No readable text found in this file.',
            ]);

            $reason = $extracted['status'] === 'ocr_required'
                ? 'It looks like a scanned document and text recognition (OCR) is not available on this server.'
                : 'No readable text was found in it.';

            return ['ok' => false, 'message' => "The file was saved but cannot be used yet. {$reason} The previous charter (if any) is still in use.", 'charter' => $charter->fresh()];
        }

        $charter->update([
            'content' => $extracted['text'],
            'content_hash' => hash('sha256', (string) $extracted['text']),
            'page_count' => $extracted['pages'] ?? null,
            'status' => 'extracted',
            'extractor' => $extracted['extractor'] ?? null,
            'error' => null,
            'extracted_at' => now(),
        ]);

        $this->activate($charter);

        return ['ok' => true, 'message' => 'Citizen\'s Charter imported. The chatbot now answers from it.', 'charter' => $charter->fresh()];
    }

    /**
     * The new row becomes the only active one; previous rows and their files
     * are removed so storage holds exactly the charter the chatbot reads.
     */
    private function activate(CitizenCharter $charter): void
    {
        foreach (CitizenCharter::where('is_active', true)->where('id', '!=', $charter->id)->get() as $previous) {
            Storage::disk(self::DISK)->delete($previous->stored_path);
            $previous->delete();
        }

        CitizenCharter::where('id', '!=', $charter->id)->update(['is_active' => false]);
        $charter->update(['is_active' => true]);
    }

    /**
     * Remove the charter entirely: row and file. The chatbot then reports that
     * no charter is available rather than answering municipal questions from
     * general knowledge.
     */
    public function remove(): bool
    {
        $removed = false;

        foreach (CitizenCharter::all() as $charter) {
            Storage::disk(self::DISK)->delete($charter->stored_path);
            $charter->delete();
            $removed = true;
        }

        return $removed;
    }

    /**
     * What the Settings screen shows: the active file and whether the chatbot
     * can actually answer from it.
     *
     * @return array{exists: bool, name?: string, size?: string, status?: string, usable?: bool, uploaded_at?: string, pages?: int|null, message?: string}
     */
    public function forSettings(): array
    {
        $active = CitizenCharter::active();

        if (!$active) {
            return ['exists' => false, 'message' => 'No Citizen\'s Charter imported yet.'];
        }

        $usable = $active->status === 'extracted' && trim((string) $active->content) !== '';

        // One prebuilt status line for Settings. Blade's directive regex does
        // not match an `@` directly after a word character, so `@endif@if(`
        // on one line leaves the second directive uncompiled and breaks the
        // whole view — this string keeps that conditional assembly in PHP.
        $bits = [];

        if ($active->file_size) {
            $bits[] = $this->humanSize($active->file_size);
        }

        if ($active->page_count) {
            $bits[] = $active->page_count . ' page(s)';
        }

        if ($active->created_at) {
            $bits[] = 'imported ' . $active->created_at->format('M d, Y h:i A');
        }

        $state = $usable
            ? 'Active — the chatbot answers municipality questions from this file.'
            : 'Saved, but the chatbot cannot answer from it yet: ' . ($active->error ?? 'extraction did not finish.');

        return [
            'exists' => true,
            'name' => $active->original_name,
            'meta' => trim(implode(' · ', $bits) . ' — ' . $state, ' ·— '),
            'status' => $active->status,
            'usable' => $usable,
            'message' => $state,
        ];
    }

    /**
     * Whether the question reads as a municipality-information question — the
     * kind answered from the charter rather than from HR records.
     *
     * Only municipal-service nouns claim a question outright. Generic process
     * nouns (requirements, fees, steps, processing time) count only beside a
     * municipal noun, so "requirements for VL" stays an HR-policy question and
     * "my certificate" stays a file search.
     */
    public function looksLikeCharterQuestion(string $message): bool
    {
        $q = strtolower($message);

        // HR records are never charter questions, however municipal the
        // remaining words sound ("leave clearance", "salary certificate").
        if (preg_match('/\b(leave|vl\b|sl\b|spl\b|payslip|pay\s*slip|payroll|dtr|attendance|late|undertime|overtime|my\s+salary|salary\s+grade|promotion|plantilla)\b/', $q)) {
            return false;
        }

        if (preg_match('/\bcitizen\'?s?\s+charter\b|\bcharter\b|\bmunisipyo\b|\bmunicipal\s+hall\b|\bcity\s+hall\b/', $q)) {
            return true;
        }

        $serviceNouns = '(?:business\s+permit|mayor\'?s?\s+permit|mayors?\s+clearance|barangay\s+clearance|'
            . 'cedula|community\s+tax|\bctc\b|civil\s+registry|birth\s+certificate|death\s+certificate|'
            . 'marriage\s+(?:certificate|license)|building\s+permit|occupancy\s+permit|zoning\s+clearance|'
            . 'sanitary\s+permit|health\s+certificate|tricycle\s+franchise|franchise|real\s+property\s+tax|'
            . 'amilyar|buwis|treasurer\'?s?\s+office|assessor\'?s?\s+office|engineering\s+office|'
            . 'senior\s+citizen(\s+affairs)?|\bpwd\b|solo\s+parent(\s+id)?|4ps|philhealth\s+registration|'
            . 'market\s+stall|slaughterhouse|public\s+market|burial\s+assistance|financial\s+assistance|'
            . 'medical\s+assistance|guarantee\s+letter|civil\s+wedding|kasal|binyag|libing|permiso|lisensya)';

        if (preg_match('/\b' . $serviceNouns . '\b/', $q)) {
            return true;
        }

        // Generic process nouns need a municipal companion: "fees" alone could
        // be a payroll deduction, "requirements" alone an HR checklist.
        $processNouns = '(?:requirements?|requi?rements?|fee?s|bayad|charges?|processing\s+time|gaano\s+katagal|'
            . 'steps?|hakbang|procedure\s+for|paano\s+kumuha|where\s+to\s+(?:get|apply|pay|file)|'
            . 'saan\s+(?:kukuha|magbabayad|mag-aapply)|office\s+hours|contact\s+(?:number|details)\s+of\s+the)';

        $municipalContext = '(?:permit|clearance|certificate|license|office|munisipyo|municipal|barangay|'
            . 'treasurer|assessor|registry|mayor|hall|service|transaction|frontline|assistance|bayad\s+ng)';

        if (preg_match('/\b' . $processNouns . '\b/', $q)
            && preg_match('/\b' . $municipalContext . '\b/', $q)) {
            return true;
        }

        return false;
    }

    /**
     * Answer from the charter, or null when this question is not answerable
     * from it — the caller then falls through to its own path. Null is also
     * returned when no usable charter exists, so a missing import degrades to
     * "no charter" rather than to an invented answer.
     *
     * @param array<int, array{role: string, content: string}> $history
     * @return array{answer: string, follow_ups: array<int, string>}|null
     */
    public function answer(?User $user, string $question, array $history = []): ?array
    {
        $charter = CitizenCharter::current();

        if (!$charter) {
            return null;
        }

        $found = $this->relevantExcerpts($charter, $question);

        if ($found['matched'] === 0) {
            return null;
        }

        $source = "According to the Citizen's Charter ({$charter->original_name}):";
        $narrated = $this->narrate($user, $question, $found['text'], $history);

        return [
            'answer' => $narrated ?? "{$source}\n\n{$found['text']}",
            'follow_ups' => [
                'What services are covered by the Citizen\'s Charter?',
                'What are the requirements for a business permit?',
                'How long does a barangay clearance take to process?',
            ],
        ];
    }

    /**
     * The top matching chunks of the charter text for a question, with how
     * many distinct question terms they matched — the caller's confidence.
     *
     * @return array{text: string, matched: int}
     */
    public function relevantExcerpts(CitizenCharter $charter, string $question): array
    {
        $terms = $this->terms($question);

        if (empty($terms) || trim((string) $charter->content) === '') {
            return ['text' => '', 'matched' => 0];
        }

        $scored = [];

        foreach ($this->chunks($charter->content) as $chunk) {
            $lower = strtolower($chunk);
            $hits = 0;
            $distinct = 0;

            foreach ($terms as $term) {
                $count = substr_count($lower, $term);

                if ($count > 0) {
                    $distinct++;
                    $hits += $count;
                }
            }

            if ($distinct > 0) {
                $scored[] = ['chunk' => $chunk, 'distinct' => $distinct, 'hits' => $hits];
            }
        }

        if (empty($scored)) {
            return ['text' => '', 'matched' => 0];
        }

        usort($scored, fn ($a, $b) => $b['distinct'] <=> $a['distinct'] ?: $b['hits'] <=> $a['hits']);

        $text = '';
        $matchedTerms = [];

        foreach ($scored as $row) {
            $candidate = $text === '' ? $row['chunk'] : $text . "\n\n" . $row['chunk'];

            if (mb_strlen($candidate) > self::MAX_EXCERPT_CHARS) {
                break;
            }

            $text = $candidate;
            $lower = strtolower($row['chunk']);

            foreach ($terms as $term) {
                if (str_contains($lower, $term)) {
                    $matchedTerms[$term] = true;
                }
            }

            if (count($scored) > 3 && count($matchedTerms) >= count($terms)) {
                break;
            }
        }

        return ['text' => trim($text), 'matched' => count($matchedTerms)];
    }

    /**
     * Split stored text into retrievable chunks on blank lines, merging
     * fragments so a service's requirements, fees, and processing time —
     * usually adjacent lines in a charter — score and return together.
     *
     * @return array<int, string>
     */
    public function chunks(string $content): array
    {
        $paragraphs = preg_split('/\R{2,}|\n/', (string) $content) ?: [];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim(preg_replace('/\s+/', ' ', (string) $paragraph) ?? '');

            if ($paragraph === '') {
                continue;
            }

            $candidate = $current === '' ? $paragraph : $current . ' ' . $paragraph;

            if (mb_strlen($candidate) > self::CHUNK_SIZE && $current !== '') {
                $chunks[] = $current;
                $current = $paragraph;
            } else {
                $current = $candidate;
            }
        }

        if (trim($current) !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * Question words worth scoring: lowercased, short tokens and stopwords
     * dropped, duplicates removed.
     *
     * @return array<int, string>
     */
    public function terms(string $question): array
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower($question)) ?: [];
        $terms = [];

        foreach ($words as $word) {
            if (mb_strlen($word) < 3 || in_array($word, self::STOPWORDS, true)) {
                continue;
            }

            $terms[$word] = true;
        }

        return array_keys($terms);
    }

    private function narrate(?User $user, string $question, string $excerpts, array $history): ?string
    {
        $messages = array_slice($history, -4);
        $messages[] = ['role' => 'user', 'content' => "Question: {$question}\n\nCharter excerpts:\n{$excerpts}"];

        $system = <<<'PROMPT'
You are the PRIME HRIS Assistant answering a question about the municipality's services from its Citizen's Charter. The excerpts below are the complete source of truth — already retrieved from the imported charter file.

- Answer ONLY from the excerpts. Never invent a requirement, fee, step, office, or processing time that is not written there.
- If the excerpts do not contain the answer, say so plainly and suggest asking the municipal office directly — do not fill the gap from general knowledge of how LGUs usually work.
- Keep the answer conversational and under 150 words. Name the service, its requirements, fees, and processing time when the excerpts state them.
- Match the user's language (Tagalog or English).
PROMPT;

        return AiChatService::chat($user, $system, $messages, 0.3, 700);
    }

    /**
     * @return array{status: string, text: string, pages?: int, extractor?: string, error?: string}
     */
    private function extract(string $absolutePath, string $type): array
    {
        if ($type === 'pdf') {
            return $this->extractPdf($absolutePath);
        }

        return $this->extractWord($absolutePath);
    }

    /**
     * @return array{status: string, text: string, pages?: int, extractor?: string, error?: string}
     */
    private function extractPdf(string $absolutePath): array
    {
        $pdf = (new PdfParser())->parseFile($absolutePath);
        $pages = count($pdf->getPages());
        $text = trim((string) $pdf->getText());

        if (mb_strlen($text) >= self::TEXT_LAYER_THRESHOLD) {
            return ['status' => 'extracted', 'text' => $text, 'pages' => $pages, 'extractor' => 'pdfparser'];
        }

        if ($this->ocr) {
            $ocr = $this->ocr->processScannedPdf($absolutePath);

            if (!empty($ocr['success'])) {
                return ['status' => 'extracted', 'text' => (string) $ocr['text'], 'pages' => $pages, 'extractor' => $ocr['engine'] ?? 'ocr'];
            }

            return ['status' => 'ocr_required', 'text' => '', 'pages' => $pages, 'error' => $ocr['reason'] ?? 'OCR unavailable'];
        }

        return ['status' => 'ocr_required', 'text' => '', 'pages' => $pages, 'error' => 'This looks like a scanned PDF and text recognition (OCR) is not available on this server.'];
    }

    /**
     * @return array{status: string, text: string, extractor?: string, error?: string}
     */
    private function extractWord(string $absolutePath): array
    {
        // A DOCX is a zip of XML parts. PhpWord's loader builds the entire
        // document — every run, style, and table cell — as PHP objects, which
        // exhausts the default 128M limit on a real multi-hundred-page charter
        // before a single word is read. Streaming document.xml with XMLReader
        // holds one node at a time, so memory stays flat however large the
        // file is. PhpWord remains as the fallback for archives the stream
        // reader cannot open.
        try {
            $text = trim($this->extractWordStreaming($absolutePath));

            if ($text !== '') {
                return ['status' => 'extracted', 'text' => $text, 'extractor' => 'docx-xml'];
            }
        } catch (\Throwable $e) {
            Log::warning('Citizen charter streaming DOCX extraction failed, falling back to PhpWord', [
                'path' => $absolutePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->extractWordViaPhpWord($absolutePath);
    }

    /**
     * Plain text out of a DOCX without loading it: paragraphs break lines,
     * table rows break lines, cells are spaced apart — which keeps a service's
     * requirements, fees, and processing time (usually a table) retrievable as
     * one chunk. Headers, footers, footnotes, and textbox contents are
     * included; field codes and revision markup are not.
     */
    private function extractWordStreaming(string $absolutePath): string
    {
        if (!class_exists(\XMLReader::class)) {
            throw new \RuntimeException('ext-xmlreader is not installed');
        }

        $zip = new \ZipArchive();

        if ($zip->open($absolutePath) !== true) {
            throw new \RuntimeException('Could not open the DOCX archive');
        }

        try {
            $parts = ['word/document.xml'];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);

                if (preg_match('#^word/(header\d+|footer\d+|footnotes|endnotes)\.xml$#', $name)) {
                    $parts[] = $name;
                }
            }

            $text = '';

            foreach ($parts as $part) {
                $text .= $this->readWordXmlPart($absolutePath, $part) . "\n";
            }

            return $text;
        } finally {
            $zip->close();
        }
    }

    private function readWordXmlPart(string $archive, string $part): string
    {
        $reader = new \XMLReader();

        if (!$reader->open('zip://' . $archive . '#' . $part)) {
            throw new \RuntimeException("Could not read {$part} inside the DOCX archive");
        }

        $text = '';

        try {
            while ($reader->read()) {
                if ($reader->nodeType === \XMLReader::ELEMENT) {
                    switch ($reader->localName) {
                        case 't':
                            $text .= $reader->readString();
                            break;
                        case 'tab':
                            $text .= "\t";
                            break;
                        case 'br':
                        case 'p':
                            // Empty <w:p/> carries no END_ELEMENT of its own.
                            if ($reader->isEmptyElement) {
                                $text .= "\n";
                            }
                            break;
                    }
                } elseif ($reader->nodeType === \XMLReader::END_ELEMENT) {
                    switch ($reader->localName) {
                        case 'p':
                        case 'tr':
                        case 'br':
                            $text .= "\n";
                            break;
                        case 'tc':
                            $text .= ' ';
                            break;
                    }
                }
            }
        } finally {
            $reader->close();
        }

        return $text;
    }

    /**
     * @return array{status: string, text: string, extractor?: string, error?: string}
     */
    private function extractWordViaPhpWord(string $absolutePath): array
    {
        $document = WordIOFactory::load($absolutePath);
        $text = '';

        foreach ($document->getSections() as $section) {
            $text .= $this->elementsToText($section->getElements());
        }

        return ['status' => 'extracted', 'text' => trim($text), 'extractor' => 'phpword'];
    }

    /**
     * Walk nested Word elements — charter requirements and fee tables live in
     * tables, which a shallow read would miss entirely.
     *
     * @param array<int, mixed> $elements
     */
    private function elementsToText(array $elements): string
    {
        $text = '';

        foreach ($elements as $element) {
            if (method_exists($element, 'getText')) {
                $value = $element->getText();
                $text .= is_string($value) ? $value . "\n" : '';
            }

            if (method_exists($element, 'getElements')) {
                $text .= $this->elementsToText($element->getElements());
            }

            if (method_exists($element, 'getRows')) {
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $text .= $this->elementsToText($cell->getElements());
                    }
                }
            }
        }

        return $text;
    }

    private function humanSize(?int $bytes): string
    {
        if ($bytes === null) {
            return 'Unknown';
        }

        return match (true) {
            $bytes < 1024 => $bytes . ' B',
            $bytes < 1048576 => round($bytes / 1024, 1) . ' KB',
            default => round($bytes / 1048576, 2) . ' MB',
        };
    }
}
