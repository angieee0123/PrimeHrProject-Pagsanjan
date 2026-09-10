<?php

namespace App\Services;

/**
 * The credential that makes the public attendance kiosk reachable.
 *
 * The kiosk cannot require a login — the entire point is that an employee walks
 * up and scans without an account in front of them — so its page and its two
 * endpoints are the only unauthenticated write path in the application. What
 * stands in for the login is a bearer secret carried in the URL path.
 *
 * It is *derived* from `APP_KEY` rather than stored, the same way
 * {@see AttendanceQrService} derives its badge key. That choice buys three
 * things: nothing has to be configured on a host we cannot run a shell on, no
 * settings table or migration is needed, and an install that never sets a
 * variable still gets a working kiosk. Rotating `APP_KEY` revokes the old URL
 * in the same stroke that invalidates every printed badge — a shared revocation
 * path is deliberate, because both are payroll credentials and an operator made
 * to remember two revocation procedures will remember neither.
 *
 * The token is a door, not the lock on the punch itself: a punch still requires
 * a badge that verifies against {@see AttendanceQrService}. What the token buys
 * is that guessing `/kiosk/attendance` is not enough to reach an endpoint that
 * writes attendance records, and that every punch can be attributed to a
 * particular kiosk rather than to "someone, somewhere".
 */
class AttendanceKioskService
{
    /** Where the kiosk is mounted, token appended as the last segment. */
    public const PATH = 'kiosk/attendance';

    /**
     * Characters of token to keep. 20 base64url characters = 120 bits, which is
     * far past guessing and still short enough to read off a screen.
     */
    private const TOKEN_LENGTH = 20;

    /** Purpose string — separates this key from every other derived secret. */
    private const KEY_PURPOSE = 'attendance-kiosk';

    /** The kiosk's address, token included. What an admin copies onto a tablet. */
    public function url(): string
    {
        return url(self::PATH . '/' . $this->token());
    }

    public function token(): string
    {
        return AppKeySecret::encode(AppKeySecret::derive(self::KEY_PURPOSE), self::TOKEN_LENGTH);
    }

    /**
     * Whether a token arriving in a URL is this install's.
     *
     * Constant-time via `hash_equals`, and total over its input: a null, empty
     * or wrong-shaped token is simply `false` rather than a type error, because
     * this is fed straight from a route segment a stranger controls.
     */
    public function matches(?string $candidate): bool
    {
        if (! is_string($candidate) || $candidate === '') {
            return false;
        }

        return hash_equals($this->token(), $candidate);
    }
}
