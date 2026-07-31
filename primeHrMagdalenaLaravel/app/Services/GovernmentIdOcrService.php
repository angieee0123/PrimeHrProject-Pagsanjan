<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Best-effort auto-fill for the Add Employee wizard: OCRs an uploaded scan of
 * a government ID and tries to pick the ID number out of the recognised text.
 *
 * This is a convenience, not a source of truth — OCR misreads and ID layouts
 * vary, so the admin always sees the result in an editable text field and can
 * overwrite it by hand. Every method degrades to `number: null` with a
 * human-readable `reason` rather than throwing, matching how OcrService
 * itself reports unavailability.
 */
class GovernmentIdOcrService
{
    /**
     * Keyword(s) that usually precede this ID's number on a scan, and the
     * digit-grouping pattern the number itself tends to follow. The keyword
     * match is tried first (looking a short distance ahead of it); failing
     * that, the pattern is matched anywhere in the recognised text.
     *
     * @var array<string, array{keywords: array<int, string>, pattern: string}>
     */
    private const ID_PATTERNS = [
        'gsis' => [
            'keywords' => ['gsis'],
            'pattern' => '\d{2}[-\s]\d{7}[-\s]\d{1}|\d{9,11}',
        ],
        'philhealth' => [
            'keywords' => ['philhealth', 'phic'],
            'pattern' => '\d{2}[-\s]\d{9}[-\s]\d{1}|\d{12}',
        ],
        'pagibig' => [
            'keywords' => ['pag-ibig', 'pagibig', 'hdmf'],
            'pattern' => '\d{4}[-\s]\d{4}[-\s]\d{4}|\d{12}',
        ],
        'tin' => [
            'keywords' => ['tin', 'tax identification'],
            'pattern' => '\d{3}[-\s]\d{3}[-\s]\d{3}(?:[-\s]\d{3,5})?',
        ],
        'license' => [
            'keywords' => ['prc', 'license no', 'license number', 'professional'],
            'pattern' => '\d{6,8}',
        ],
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function __construct(private OcrService $ocr)
    {
    }

    /**
     * @return array{number: ?string, message: string}
     */
    public function extract(UploadedFile $file, string $idType): array
    {
        if (!array_key_exists($idType, self::ID_PATTERNS)) {
            return ['number' => null, 'message' => 'Unrecognised ID type.'];
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $absolutePath = $file->getRealPath();

        if ($absolutePath === false || !is_readable($absolutePath)) {
            return ['number' => null, 'message' => 'Could not read the uploaded file.'];
        }

        $ocrResult = $extension === 'pdf'
            ? $this->ocr->processScannedPdf($absolutePath)
            : (in_array($extension, self::IMAGE_EXTENSIONS, true)
                ? $this->ocr->processImage($absolutePath)
                : ['success' => false, 'text' => '', 'reason' => "Unsupported file type .{$extension}"]);

        if (!$ocrResult['success']) {
            return [
                'number' => null,
                'message' => 'Could not read the scan automatically (' . ($ocrResult['reason'] ?? 'OCR unavailable') . ') — please type the number in.',
            ];
        }

        $number = $this->parseNumber($ocrResult['text'], $idType);

        return $number !== null
            ? ['number' => $number, 'message' => 'Auto-filled from scan — please verify.']
            : ['number' => null, 'message' => 'Could not find the number on the scan — please type it in.'];
    }

    private function parseNumber(string $text, string $idType): ?string
    {
        $config = self::ID_PATTERNS[$idType];
        $numberPattern = '/(?:' . $config['pattern'] . ')/i';

        // First choice: a number sitting close after one of this ID's keywords.
        foreach ($config['keywords'] as $keyword) {
            $keywordPos = stripos($text, $keyword);
            if ($keywordPos === false) {
                continue;
            }

            $window = substr($text, $keywordPos, strlen($keyword) + 40);
            if (preg_match($numberPattern, $window, $matches)) {
                return trim($matches[0]);
            }
        }

        // Fallback: the first number anywhere in the text matching the shape
        // this ID's number normally takes.
        if (preg_match($numberPattern, $text, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }
}
