<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

/**
 * Derives a purpose-specific secret from `APP_KEY`.
 *
 * `APP_KEY` is the only secret guaranteed to exist on every install, and more
 * than one feature needs a key of its own rather than `APP_KEY` itself — a
 * value signed for one purpose must never verify against another. The QR badge
 * signature and the attendance kiosk token are both such secrets.
 *
 * They are derived here, in one place, because the arithmetic is not obvious:
 * `APP_KEY` is usually stored `base64:`-prefixed and has to be decoded before
 * it is a key at all. Two copies of that would eventually disagree — one
 * decoding the prefix and one hashing the literal string — and the symptom
 * would be two features quietly deriving different keys from the same
 * `APP_KEY`, which is the kind of bug that only shows up as "the badges
 * stopped working". One implementation cannot disagree with itself.
 */
final class AppKeySecret
{
    /**
     * Raw HMAC key bytes for a named purpose.
     *
     * Deterministic for a given `APP_KEY`, and completely different for every
     * purpose string: both properties are relied on by callers.
     */
    public static function derive(string $purpose): string
    {
        $appKey = (string) Config::get('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7)) ?: $appKey;
        }

        return hash_hmac('sha256', $purpose, $appKey, true);
    }

    /**
     * A URL-safe token of `$length` characters, encoded from raw bytes.
     *
     * base64url rather than hex so a 120-bit token fits in 20 characters — it
     * is typed into a kiosk URL and pasted into a browser, and every character
     * spent on hex is one an operator can mistype.
     */
    public static function encode(string $raw, int $length): string
    {
        return substr(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='), 0, $length);
    }
}
