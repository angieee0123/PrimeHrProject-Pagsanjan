<?php

namespace App\Support;

/**
 * Finds a TrueType file that dompdf is actually allowed to embed.
 *
 * The printed Pass Slip sets its two titles in Berlin Sans FB Demi and its
 * fallback letterhead in Old English Text MT. Both ship with Windows/Office
 * rather than with this project, and neither is vendored: serving one from
 * `public/` would redistribute it, which the licence does not allow.
 *
 * Two constraints have to be satisfied at once:
 *
 *  - **dompdf refuses anything outside its chroot**, which the Laravel wrapper
 *    sets to the application root. Pointing `@font-face` straight at
 *    `C:\Windows\Fonts\BRLNSDB.TTF` fails *silently* — the title simply comes
 *    out in the fallback sans — so the file has to sit under the app.
 *  - The file must not become part of the repository or be reachable over
 *    HTTP.
 *
 * So a font installed on the machine is copied once into `storage/app/fonts`,
 * which is inside the chroot, is not web-served, and is already covered by
 * `storage/app/.gitignore`. That is a local cache of a font the machine is
 * licensed for, not redistribution. Embedding it in the generated PDF is
 * separately allowed by these fonts' own OS/2 fsType (0x0008, editable
 * embedding) — verified before relying on it.
 *
 * A font that is installed nowhere resolves to null and the caller falls back.
 */
final class EmbeddableFont
{
    /** Where the cached copies live: inside the chroot, outside the web root. */
    private const CACHE = 'app/fonts';

    /** Checked in order; the first hit wins. */
    private static function searchPaths(string $file): array
    {
        $system = rtrim(getenv('SystemRoot') ?: 'C:\\Windows', '\\/') . DIRECTORY_SEPARATOR . 'Fonts';

        return [
            $system . DIRECTORY_SEPARATOR . $file,
            '/usr/share/fonts/truetype/msttcorefonts/' . $file,
            '/usr/share/fonts/truetype/' . $file,
            '/usr/local/share/fonts/' . $file,
            ($_SERVER['HOME'] ?? '') . '/.fonts/' . $file,
            '/Library/Fonts/' . $file,
        ];
    }

    /**
     * An absolute, forward-slashed path dompdf will accept, or null.
     *
     * Safe to call on every render: once cached it is a single is_file().
     */
    public static function path(string $file): ?string
    {
        // Already vendored by the office, or already cached.
        foreach ([public_path('fonts/' . $file), storage_path(self::CACHE . '/' . $file)] as $known) {
            if (is_file($known)) {
                return self::normalise($known);
            }
        }

        foreach (self::searchPaths($file) as $candidate) {
            if (!is_file($candidate) || !is_readable($candidate)) {
                continue;
            }

            $cached = storage_path(self::CACHE . '/' . $file);

            if (!is_dir(dirname($cached))) {
                @mkdir(dirname($cached), 0755, true);
            }

            // A failed copy is not fatal: the caller just falls back.
            if (@copy($candidate, $cached)) {
                return self::normalise($cached);
            }
        }

        return null;
    }

    /** dompdf wants forward slashes even on Windows. */
    private static function normalise(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
