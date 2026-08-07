<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Employee;
use App\Models\GovernmentId;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Turns a file reference the assistant handed out ("documents:41") back into a
 * real file on disk — and refuses if the caller may not see it.
 *
 * Everything the chat renders as a file card goes through here, in both
 * directions:
 *
 *   building the card  → confirm the row's file actually exists on disk, so we
 *                        never show a card for a record whose file is missing
 *   serving the click  → re-read the row, re-check AiAccessPolicy, stream it
 *
 * The reference is a table plus a row id, never a path. A user cannot rewrite
 * it into "../../.env" or into somebody else's payslip, because the path is
 * only ever read back out of the database row the id points at.
 */
class AiFileResolver
{
    /**
     * The four places uploads live in this app. Anything not listed here is
     * not servable by the assistant — omission is a deliberate deny.
     */
    public const SOURCES = ['documents', 'trainings', 'employee_photo', 'government_ids'];

    /** government_ids keeps one row per employee with up to five scans on it. */
    public const GOV_ID_COLUMNS = [
        'gsis_file_path' => 'GSIS ID scan',
        'philhealth_file_path' => 'PhilHealth ID scan',
        'pagibig_file_path' => 'Pag-IBIG ID scan',
        'tin_file_path' => 'TIN ID scan',
        'license_file_path' => 'Professional License scan',
    ];

    /** Rendered as a picture in the chat rather than as a download card. */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /**
     * Shown in the browser instead of downloading. SVG is deliberately absent:
     * an SVG served inline from our own origin is a script execution vector.
     */
    private const INLINE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'];

    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * Look up a reference and enforce access. The single gate the streaming
     * endpoint sits behind.
     *
     * @return array{success: bool, disk?: string, path?: string, file_name?: string, mime?: string, inline?: bool, status?: int, error?: string}
     */
    public function resolve(User $user, string $source, string $ref): array
    {
        $row = $this->locate($source, $ref);

        if ($row === null) {
            return ['success' => false, 'status' => 404, 'error' => 'That file is not in the system.'];
        }

        if (!$this->policy->canAccessEmployee($user, $row['employee_id'])) {
            return ['success' => false, 'status' => 403, 'error' => 'You do not have permission to open this file.'];
        }

        $disk = $this->diskFor($row['path']);

        if ($disk === null) {
            return ['success' => false, 'status' => 404, 'error' => 'The record exists but the file is missing from storage.'];
        }

        $path = $this->toDiskPath($row['path']);
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return [
            'success' => true,
            'disk' => $disk,
            'path' => $path,
            'file_name' => basename((string) $path),
            'mime' => $this->mimeFor($disk, $path),
            'inline' => in_array($extension, self::INLINE_EXTENSIONS, true),
        ];
    }

    /**
     * Read the stored path (and its owning employee) straight off the database
     * row. Returns null for an unknown source, a missing row, or a row whose
     * file column is empty — all of which mean "no such file", not an error.
     *
     * @return array{employee_id: ?int, path: string}|null
     */
    public function locate(string $source, string $ref): ?array
    {
        return match ($source) {
            'documents' => $this->fromModel(Document::find($this->intRef($ref)), 'file_path'),
            'trainings' => $this->fromModel(Training::find($this->intRef($ref)), 'certificate_path'),
            'employee_photo' => $this->fromEmployeePhoto($this->intRef($ref)),
            'government_ids' => $this->fromGovernmentId($ref),
            default => null,
        };
    }

    /**
     * True when the row this reference points at has a file that is really on
     * disk. Used when building cards so a broken upload never becomes a
     * clickable card that 404s.
     */
    public function existsOnDisk(?string $storedPath): bool
    {
        return $this->diskFor($storedPath) !== null;
    }

    public function isImage(?string $path): bool
    {
        return in_array(strtolower(pathinfo((string) $path, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true);
    }

    /**
     * Which disk holds this file, or null if no disk does. Uploads in this app
     * are written to `public`; older rows may sit on the default disk.
     */
    public function diskFor(?string $storedPath): ?string
    {
        $path = $this->toDiskPath($storedPath);

        if ($path === null || $path === '') {
            return null;
        }

        foreach (['public', 'local'] as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                // Unreadable disk — try the next one.
            }
        }

        return null;
    }

    /**
     * Stored paths come in two shapes: bare disk paths (documents, training
     * certificates) and public URLs (employee photos, government ID scans,
     * which were saved as "/storage/…"). Normalise both to a disk path.
     */
    public function toDiskPath(?string $stored): ?string
    {
        if ($stored === null || trim($stored) === '') {
            return null;
        }

        $path = trim($stored);

        // A fully-qualified URL from an older upload — keep only the path part.
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?: $path;
        }

        $path = ltrim(preg_replace('#^/?storage/#', '', $path) ?? $path, '/');

        // Defence in depth: a traversal segment can only get here through a
        // corrupted row, and it should still not resolve to a real file.
        return str_contains($path, '..') ? null : $path;
    }

    /**
     * @return array{employee_id: ?int, path: string}|null
     */
    private function fromModel(?object $model, string $column): ?array
    {
        if (!$model || empty($model->{$column})) {
            return null;
        }

        return [
            'employee_id' => $model->employee_id ? (int) $model->employee_id : null,
            'path' => (string) $model->{$column},
        ];
    }

    /**
     * @return array{employee_id: ?int, path: string}|null
     */
    private function fromEmployeePhoto(?int $employeeId): ?array
    {
        if ($employeeId === null) {
            return null;
        }

        $employee = Employee::find($employeeId);

        if (!$employee || empty($employee->photo)) {
            return null;
        }

        // The photo's owner is the employee row itself.
        return ['employee_id' => (int) $employee->id, 'path' => (string) $employee->photo];
    }

    /**
     * References look like "12-gsis_file_path": the government_ids row plus
     * which scan on it. The column must be one we published.
     *
     * @return array{employee_id: ?int, path: string}|null
     */
    private function fromGovernmentId(string $ref): ?array
    {
        if (!preg_match('/^(\d+)-([a-z_]+)$/', $ref, $m)) {
            return null;
        }

        [$id, $column] = [(int) $m[1], $m[2]];

        if (!array_key_exists($column, self::GOV_ID_COLUMNS)) {
            return null;
        }

        return $this->fromModel(GovernmentId::find($id), $column);
    }

    private function intRef(string $ref): ?int
    {
        return ctype_digit($ref) ? (int) $ref : null;
    }

    private function mimeFor(string $disk, string $path): string
    {
        try {
            $mime = Storage::disk($disk)->mimeType($path);
        } catch (\Throwable) {
            $mime = false;
        }

        return is_string($mime) && $mime !== '' ? $mime : 'application/octet-stream';
    }
}
