<?php

namespace App\Console\Commands;

use App\Services\CitizenCharterService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

/**
 * Import the Citizen's Charter from a file on the server's own disk.
 *
 * The Settings-screen upload is an HTTP upload, so it is bound by PHP's
 * upload_max_filesize (2M on this project's server) — a real multi-megabyte
 * charter cannot arrive that way without editing php.ini and restarting the
 * web server. Reading a local path has no such ceiling: copy the charter
 * onto the server (or point at a file already there) and run:
 *
 *   php artisan charter:import "C:\charters\Citizens Charter ARTA 2026.pdf"
 *
 * The same service — and the same replace-only-on-clean-extraction rule —
 * runs as behind the upload button, so either path leaves the chatbot
 * answering from the identical text.
 */
class ImportCitizenCharterCommand extends Command
{
    protected $signature = 'charter:import
                            {path : Local path to the charter file (PDF or DOCX)}
                            {--user= : User id recorded as the uploader}';

    protected $description = 'Import the Citizen\'s Charter the AI chatbot answers municipality questions from';

    public function handle(CitizenCharterService $charters): int
    {
        $this->raiseMemoryForImport();

        $path = (string) $this->argument('path');

        if (!is_file($path) || !is_readable($path)) {
            $this->error("Cannot read file: {$path}");

            return self::FAILURE;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (!in_array($extension, CitizenCharterService::ALLOWED_EXTENSIONS, true)) {
            $this->error('Only these formats are accepted: ' . implode(', ', CitizenCharterService::ALLOWED_EXTENSIONS));

            return self::FAILURE;
        }

        // Wrap the file the way an HTTP upload would arrive, but over a copy:
        // UploadedFile::storeAs() moves (not copies) its source, and the
        // original on disk is the admin's file to keep.
        $copy = tempnam(sys_get_temp_dir(), 'charter-import') . '.' . $extension;
        copy($path, $copy);

        $file = new UploadedFile(
            $copy,
            basename($path),
            mime_content_type($path) ?: null,
            UPLOAD_ERR_OK,
            true,
        );

        $result = $charters->import($file, $this->option('user') !== null ? (int) $this->option('user') : null);

        if (!$result['ok']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $charter = $result['charter'];
        $chars = mb_strlen((string) $charter->content);

        $this->info($result['message']);
        $this->line("File: {$charter->original_name} ({$charter->page_count} page(s), {$chars} characters indexed)");

        return self::SUCCESS;
    }

    /**
     * Parsing a whole file into objects (PDF pages, the PhpWord fallback)
     * needs headroom the default 128M CLI limit does not have. This is an
     * explicit admin action, not a web request, so claim a bounded 512M when
     * the configured limit is lower — `php -d memory_limit=…` still wins for
     * anything larger.
     */
    private function raiseMemoryForImport(): void
    {
        $bytes = $this->memoryBytes((string) ini_get('memory_limit'));

        if ($bytes < 0 || $bytes >= 512 * 1024 * 1024) {
            return;
        }

        if (@ini_set('memory_limit', '512M') !== false) {
            $this->line('Import memory raised to 512M for this run.');
        }
    }

    private function memoryBytes(string $raw): int
    {
        $raw = trim($raw);

        if ($raw === '' || $raw === '-1') {
            return -1; // unlimited
        }

        $unit = strtolower(substr($raw, -1));
        $value = (float) $raw;

        return (int) match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
