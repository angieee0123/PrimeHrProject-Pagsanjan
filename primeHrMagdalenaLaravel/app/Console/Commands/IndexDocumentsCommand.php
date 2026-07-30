<?php

namespace App\Console\Commands;

use App\Services\DocumentProcessingService;
use App\Services\OcrService;
use Illuminate\Console\Command;

/**
 * Builds the searchable text index over uploaded files.
 *
 * Run once to backfill, then on a schedule (or from an upload observer) to
 * keep newly uploaded files searchable.
 */
class IndexDocumentsCommand extends Command
{
    protected $signature = 'ai:index-documents
                            {--force : Re-extract files that were already indexed}';

    protected $description = 'Extract text from uploaded files so the AI Assistant can search their contents';

    public function handle(DocumentProcessingService $processor, OcrService $ocr): int
    {
        $status = $ocr->status();

        $this->info('OCR: ' . ($status['available'] ? 'available' : 'NOT available')
            . ($status['pdf_capable'] ? ' (scanned PDFs supported)' : ' (scanned PDFs not supported)'));

        if (!$status['available']) {
            $this->warn('Scanned files will be parked as ocr_required. ' . $status['install_hint']);
        }

        $this->newLine();
        $this->info('Indexing uploaded files…');

        $tally = $processor->indexAll((bool) $this->option('force'));

        $this->newLine();
        $this->table(
            ['Processed', 'Extracted', 'Needs OCR', 'Failed', 'Skipped'],
            [[
                $tally['processed'],
                $tally['extracted'] ?? 0,
                $tally['ocr_required'] ?? 0,
                $tally['failed'] ?? 0,
                $tally['skipped'],
            ]]
        );

        if (($tally['ocr_required'] ?? 0) > 0 && !$status['available']) {
            $this->warn(($tally['ocr_required']) . ' file(s) are waiting on OCR. Install Tesseract and re-run.');
        }

        return self::SUCCESS;
    }
}
