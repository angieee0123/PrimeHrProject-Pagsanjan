<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * OCR for scanned documents and images.
 *
 * Uses a local Tesseract install when one is present. There is no hosted
 * fallback wired up: rather than pretend, isAvailable() reports the truth and
 * callers mark the document `ocr_required` so it can be picked up later once
 * an engine is installed.
 *
 * To enable:
 *   macOS  — brew install tesseract
 *   Debian — apt-get install tesseract-ocr poppler-utils
 *
 * poppler-utils (pdftoppm) is what lets a scanned PDF be rasterised into pages
 * before OCR; without it only images can be processed.
 */
class OcrService
{
    private const TIMEOUT_SECONDS = 120;
    private const MAX_PDF_PAGES = 20;

    /**
     * Whether OCR can actually run right now.
     */
    public function isAvailable(): bool
    {
        return $this->binaryPath('tesseract') !== null;
    }

    /**
     * Whether scanned *PDFs* can be processed (needs a rasteriser too).
     */
    public function canProcessPdfs(): bool
    {
        return $this->isAvailable() && $this->binaryPath('pdftoppm') !== null;
    }

    /**
     * OCR an image file.
     *
     * @return array{success: bool, text: string, engine?: string, reason?: string}
     */
    public function processImage(string $absolutePath): array
    {
        if (!$this->isAvailable()) {
            return $this->unavailable('tesseract is not installed on this server');
        }

        if (!is_readable($absolutePath)) {
            return ['success' => false, 'text' => '', 'reason' => 'file is not readable'];
        }

        try {
            // Tesseract writes to <output>.txt; "stdout" makes it use stdout.
            $result = Process::timeout(self::TIMEOUT_SECONDS)
                ->run(['tesseract', $absolutePath, 'stdout', '--psm', '3']);

            if (!$result->successful()) {
                Log::warning('OCR failed', ['path' => $absolutePath, 'error' => $result->errorOutput()]);

                return ['success' => false, 'text' => '', 'reason' => 'tesseract exited with an error'];
            }

            return [
                'success' => true,
                'text' => $this->tidy($result->output()),
                'engine' => 'tesseract',
            ];
        } catch (\Throwable $e) {
            Log::warning('OCR exception', ['path' => $absolutePath, 'error' => $e->getMessage()]);

            return ['success' => false, 'text' => '', 'reason' => $e->getMessage()];
        }
    }

    /**
     * OCR a scanned PDF by rasterising its pages first.
     *
     * @return array{success: bool, text: string, engine?: string, reason?: string}
     */
    public function processScannedPdf(string $absolutePath): array
    {
        if (!$this->canProcessPdfs()) {
            return $this->unavailable(
                $this->isAvailable()
                    ? 'poppler-utils (pdftoppm) is not installed, so scanned PDFs cannot be rasterised'
                    : 'tesseract is not installed on this server'
            );
        }

        $workDir = sys_get_temp_dir() . '/ocr-' . bin2hex(random_bytes(6));

        if (!mkdir($workDir, 0700, true) && !is_dir($workDir)) {
            return ['success' => false, 'text' => '', 'reason' => 'could not create a working directory'];
        }

        try {
            $raster = Process::timeout(self::TIMEOUT_SECONDS)->run([
                'pdftoppm', '-png', '-r', '200',
                '-l', (string) self::MAX_PDF_PAGES,
                $absolutePath, $workDir . '/page',
            ]);

            if (!$raster->successful()) {
                return ['success' => false, 'text' => '', 'reason' => 'could not rasterise the PDF'];
            }

            $pages = glob($workDir . '/page*.png') ?: [];
            sort($pages);

            $text = '';
            foreach ($pages as $page) {
                $result = $this->processImage($page);
                if ($result['success']) {
                    $text .= $result['text'] . "\n\n";
                }
            }

            return [
                'success' => trim($text) !== '',
                'text' => $this->tidy($text),
                'engine' => 'tesseract+pdftoppm',
                'reason' => trim($text) === '' ? 'no text could be recognised' : null,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'text' => '', 'reason' => $e->getMessage()];
        } finally {
            array_map('unlink', glob($workDir . '/*') ?: []);
            @rmdir($workDir);
        }
    }

    /**
     * Setup status, for surfacing in an admin screen.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        return [
            'available' => $this->isAvailable(),
            'pdf_capable' => $this->canProcessPdfs(),
            'tesseract' => $this->binaryPath('tesseract'),
            'pdftoppm' => $this->binaryPath('pdftoppm'),
            'install_hint' => 'macOS: brew install tesseract poppler · Debian: apt-get install tesseract-ocr poppler-utils',
        ];
    }

    /**
     * @return array{success: false, text: string, reason: string}
     */
    private function unavailable(string $reason): array
    {
        return ['success' => false, 'text' => '', 'reason' => $reason];
    }

    private function binaryPath(string $binary): ?string
    {
        try {
            $result = Process::timeout(5)->run(['which', $binary]);
            $path = trim($result->output());

            return $result->successful() && $path !== '' ? $path : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Collapse the ragged whitespace OCR tends to produce.
     */
    private function tidy(string $text): string
    {
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
