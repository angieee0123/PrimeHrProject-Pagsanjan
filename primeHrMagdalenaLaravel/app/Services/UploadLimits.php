<?php

namespace App\Services;

/**
 * What this server will actually accept, as opposed to what a rule asks for.
 *
 * PHP enforces `upload_max_filesize` and `post_max_size` *before* the request
 * reaches Laravel, so a `max:` rule above the former can never fire and a form
 * larger than the latter never arrives at all. A screen that states a limit the
 * server does not honour tells the admin their upload failed with no way to
 * tell an over-size file from a broken one — so the limits are read from the
 * system rather than typed, the same rule `HrPolicyFactsService` follows for HR
 * policy and `SiteContentService` for the homepage counts.
 *
 * Every upload surface in the wizard goes through here so the ID scans, the
 * photo and the 201-file documents cannot state three different ceilings.
 */
class UploadLimits
{
    /**
     * The per-file ceiling in kilobytes: what was asked for, or what PHP will
     * take, whichever is smaller.
     */
    public static function perFileKb(int $desiredKb): int
    {
        $ini = self::iniKilobytes('upload_max_filesize');

        return $ini > 0 ? min($desiredKb, $ini) : $desiredKb;
    }

    /**
     * `post_max_size` in kilobytes — the ceiling on the whole submission.
     *
     * The worse of the two failures: over this limit PHP discards the entire
     * body, the CSRF token with it, and Laravel answers 419 Page Expired. The
     * form is gone and nothing on screen says why. Returns 0 when unset or
     * unlimited, which callers read as "nothing to warn about".
     */
    public static function postMaxKb(): int
    {
        return self::iniKilobytes('post_max_size');
    }

    /** "10 MB", "1.5 MB", "512 KB" — a size as a screen should state it. */
    public static function label(int $kb): string
    {
        if ($kb < 1024) {
            return $kb . ' KB';
        }

        return rtrim(rtrim(number_format($kb / 1024, 1, '.', ''), '0'), '.') . ' MB';
    }

    /** Parses a PHP ini shorthand size ("2M", "8192K", "1G") into kilobytes. */
    public static function iniKilobytes(string $directive): int
    {
        $raw = trim((string) ini_get($directive));
        if ($raw === '' || $raw === '0' || $raw === '-1') {
            return 0; // unset, or "no limit"
        }

        $unit = strtolower(substr($raw, -1));
        $value = (float) $raw;

        return (int) match ($unit) {
            'g' => $value * 1024 * 1024,
            'm' => $value * 1024,
            'k' => $value,
            default => $value / 1024, // plain bytes
        };
    }
}
