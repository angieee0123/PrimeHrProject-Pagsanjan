<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentExtraction;
use App\Models\Training;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Feature 8: Document Intelligence.
 *
 * Pulls text out of uploaded files and stores it in `document_extractions`,
 * which is what lets the assistant answer "find the contract that mentions X"
 * rather than only matching on filenames.
 *
 * PDF and DOCX extraction is real and runs locally. Scanned files (a PDF with
 * no text layer, or a photo) need OCR, which depends on Tesseract being
 * installed — when it is not, the row is parked as `ocr_required` rather than
 * silently recorded as empty.
 */
class DocumentProcessingService
{
    /** Below this many characters, a PDF is treated as scanned rather than digital. */
    private const TEXT_LAYER_THRESHOLD = 40;

    private const IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff'];

    public function __construct(private OcrService $ocr)
    {
    }

    /**
     * Index every uploaded file that has no current extraction.
     *
     * @return array{processed: int, extracted: int, ocr_required: int, failed: int, skipped: int}
     */
    public function indexAll(bool $force = false): array
    {
        $tally = ['processed' => 0, 'extracted' => 0, 'ocr_required' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($this->sources() as $source) {
            foreach ($source['query']()->cursor() as $record) {
                $path = $record->{$source['path_column']};

                if (empty($path)) {
                    continue;
                }

                $existing = DocumentExtraction::where('source_type', $source['type'])
                    ->where('source_id', $record->id)
                    ->first();

                if ($existing && !$force && $existing->status === 'extracted') {
                    $tally['skipped']++;
                    continue;
                }

                $tally['processed']++;
                $result = $this->extractAndStore($source['type'], $record->id, $record->employee_id, $path);
                $tally[$result] = ($tally[$result] ?? 0) + 1;
            }
        }

        return $tally;
    }

    /**
     * Extract one file and persist the result.
     *
     * @return string One of: extracted, ocr_required, unsupported, failed
     */
    public function extractAndStore(string $sourceType, int $sourceId, ?int $employeeId, string $storedPath): string
    {
        $absolute = $this->absolutePath($storedPath);

        $attributes = [
            'employee_id' => $employeeId,
            'file_path' => $storedPath,
            'file_type' => strtolower(pathinfo($storedPath, PATHINFO_EXTENSION)) ?: null,
            'extracted_at' => now(),
        ];

        if ($absolute === null) {
            return $this->persist($sourceType, $sourceId, $attributes + [
                'status' => 'failed',
                'error' => 'File is recorded in the database but missing from storage',
            ]);
        }

        $attributes['file_size'] = filesize($absolute) ?: null;
        $attributes['file_hash'] = hash_file('sha256', $absolute) ?: null;

        try {
            $result = $this->extract($absolute, $attributes['file_type'] ?? '');
        } catch (\Throwable $e) {
            Log::warning('Document extraction failed', ['path' => $storedPath, 'error' => $e->getMessage()]);

            return $this->persist($sourceType, $sourceId, $attributes + [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        return $this->persist($sourceType, $sourceId, $attributes + [
            'status' => $result['status'],
            'content' => $result['text'] ?: null,
            'metadata' => $result['metadata'] ?? null,
            'extractor' => $result['extractor'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * @return array{status: string, text: string, metadata?: array, extractor?: string, error?: string}
     */
    private function extract(string $absolutePath, string $type): array
    {
        if ($type === 'pdf') {
            return $this->extractPdf($absolutePath);
        }

        if (in_array($type, ['docx', 'doc'], true)) {
            return $this->extractWord($absolutePath);
        }

        if (in_array($type, self::IMAGE_TYPES, true)) {
            return $this->extractImage($absolutePath);
        }

        if (in_array($type, ['txt', 'csv', 'md'], true)) {
            return [
                'status' => 'extracted',
                'text' => (string) file_get_contents($absolutePath),
                'extractor' => 'plain',
            ];
        }

        return [
            'status' => 'unsupported',
            'text' => '',
            'error' => "No extractor for .{$type} files",
        ];
    }

    /**
     * @return array{status: string, text: string, metadata?: array, extractor?: string, error?: string}
     */
    private function extractPdf(string $absolutePath): array
    {
        $pdf = (new PdfParser())->parseFile($absolutePath);
        $pageCount = count($pdf->getPages());
        $text = trim($pdf->getText());

        // A PDF with no usable text layer is a scan; it needs OCR, not a parser.
        if (mb_strlen($text) >= self::TEXT_LAYER_THRESHOLD) {
            return [
                'status' => 'extracted',
                'text' => $text,
                'metadata' => ['pages' => $pageCount, 'scanned' => false],
                'extractor' => 'pdfparser',
            ];
        }

        $ocr = $this->ocr->processScannedPdf($absolutePath);

        if ($ocr['success']) {
            return [
                'status' => 'extracted',
                'text' => $ocr['text'],
                'metadata' => ['pages' => $pageCount, 'scanned' => true],
                'extractor' => $ocr['engine'] ?? 'ocr',
            ];
        }

        return [
            'status' => 'ocr_required',
            'text' => $text,
            'metadata' => ['pages' => $pageCount, 'scanned' => true],
            'error' => $ocr['reason'] ?? 'OCR unavailable',
        ];
    }

    /**
     * @return array{status: string, text: string, metadata?: array, extractor?: string}
     */
    private function extractWord(string $absolutePath): array
    {
        $document = IOFactory::load($absolutePath);
        $text = '';

        foreach ($document->getSections() as $section) {
            $text .= $this->elementsToText($section->getElements());
        }

        return [
            'status' => 'extracted',
            'text' => trim($text),
            'metadata' => ['sections' => count($document->getSections())],
            'extractor' => 'phpword',
        ];
    }

    /**
     * Walks nested elements — text inside tables and lists would otherwise be
     * missed, and that is exactly where scanned form data tends to live.
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

    /**
     * @return array{status: string, text: string, extractor?: string, error?: string}
     */
    private function extractImage(string $absolutePath): array
    {
        $ocr = $this->ocr->processImage($absolutePath);

        if ($ocr['success']) {
            return [
                'status' => 'extracted',
                'text' => $ocr['text'],
                'extractor' => $ocr['engine'] ?? 'ocr',
            ];
        }

        return [
            'status' => 'ocr_required',
            'text' => '',
            'error' => $ocr['reason'] ?? 'OCR unavailable',
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function persist(string $sourceType, int $sourceId, array $attributes): string
    {
        DocumentExtraction::updateOrCreate(
            ['source_type' => $sourceType, 'source_id' => $sourceId],
            $attributes
        );

        return $attributes['status'];
    }

    /**
     * Uploads are referenced in a few shapes: a bare disk path, or a public
     * URL beginning /storage/. Resolve either to something on disk.
     */
    private function absolutePath(string $storedPath): ?string
    {
        $relative = ltrim(preg_replace('#^/?storage/#', '', $storedPath) ?? $storedPath, '/');

        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($relative)) {
                return Storage::disk($disk)->path($relative);
            }
        }

        // Some older records store a path already relative to public/.
        $publicPath = public_path(ltrim($storedPath, '/'));

        return is_file($publicPath) ? $publicPath : null;
    }

    /**
     * Where uploaded files live in this application.
     *
     * @return array<int, array{type: string, path_column: string, query: callable}>
     */
    private function sources(): array
    {
        return [
            [
                'type' => 'document',
                'path_column' => 'file_path',
                'query' => fn () => Document::query()->whereNotNull('file_path'),
            ],
            [
                'type' => 'training_certificate',
                'path_column' => 'certificate_path',
                'query' => fn () => Training::query()->whereNotNull('certificate_path'),
            ],
        ];
    }
}
