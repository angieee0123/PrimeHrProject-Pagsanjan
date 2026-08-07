<?php

namespace App\Services;

use App\Exceptions\InvalidAttendanceQrException;
use App\Models\Employee;
use Illuminate\Support\Facades\Config;

/**
 * Signs and verifies the payload printed on an employee's attendance QR badge.
 *
 * The badge used to encode a bare employee id, which meant anyone who could
 * count could generate a QR for `42` and punch in as employee 42. Attendance
 * feeds accredited hours, which feeds payroll, so the badge is a payroll
 * credential and has to be unforgeable.
 *
 * A badge is `PHRM1.{employeeId}.{signature}` where the signature is an HMAC
 * over the version and id, keyed by the application key. Nothing secret is
 * encoded — the id is public within HR — so the signature is the entire
 * defence, and a truncated 96-bit tag keeps the code sparse enough to scan
 * from a laminated ID at arm's length.
 *
 * There is deliberately no expiry: a badge is a printed card that stands in
 * for a fingerprint, and cards that stop working on a schedule would be worse
 * than the problem. Rotating `APP_KEY` invalidates every badge at once, which
 * is the intended revocation path if a batch is ever compromised.
 */
class AttendanceQrService
{
    /**
     * Payload version. Bump when the format changes so old badges fail loudly
     * with "reissue this card" rather than silently mis-verifying.
     */
    public const VERSION = 'PHRM1';

    /** Base64url characters of HMAC to keep. 16 chars = 96 bits. */
    private const SIGNATURE_LENGTH = 16;

    /**
     * The payload to encode in this employee's QR badge.
     */
    public function payloadFor(Employee|int $employee): string
    {
        $id = $employee instanceof Employee ? $employee->id : $employee;
        $body = self::VERSION . '.' . $id;

        return $body . '.' . $this->sign($body);
    }

    /**
     * Resolve a scanned string back to an employee.
     *
     * @throws InvalidAttendanceQrException when the code is malformed, unsigned,
     *         signed with a different key, or points at no one.
     */
    public function resolveEmployee(string $scanned): Employee
    {
        $scanned = trim($scanned);

        if ($scanned === '') {
            throw new InvalidAttendanceQrException('Nothing was scanned. Please try again.');
        }

        // Badges printed before signing was introduced encoded the bare id.
        // Accepting them would defeat the whole point of signing, so name the
        // situation precisely instead of failing as "unreadable".
        if (ctype_digit($scanned)) {
            throw new InvalidAttendanceQrException(
                'This is an old unsigned QR card. Please reissue it from Personnel before using the scanner.'
            );
        }

        $parts = explode('.', $scanned);

        if (count($parts) !== 3 || $parts[0] !== self::VERSION || !ctype_digit($parts[1])) {
            throw new InvalidAttendanceQrException('This QR code is not an employee attendance badge.');
        }

        [$version, $id, $signature] = $parts;
        $body = $version . '.' . $id;

        if (!hash_equals($this->sign($body), $signature)) {
            throw new InvalidAttendanceQrException('This QR code failed verification and may be a copy. Please reissue the card.');
        }

        $employee = Employee::find((int) $id);

        if (!$employee) {
            throw new InvalidAttendanceQrException('No employee record matches this badge. It may belong to a deleted record.');
        }

        return $employee;
    }

    /**
     * Whether a scanned string looks like a badge at all — used by the kiosk to
     * ignore stray codes (a product barcode, a poster) without alarming the
     * operator with a failed-verification message.
     */
    public function looksLikeBadge(string $scanned): bool
    {
        return str_starts_with(trim($scanned), self::VERSION . '.');
    }

    private function sign(string $body): string
    {
        $raw = hash_hmac('sha256', $body, $this->secret(), true);

        return substr(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='), 0, self::SIGNATURE_LENGTH);
    }

    /**
     * A key derived from APP_KEY rather than APP_KEY itself, so a badge
     * signature can never be replayed against anything else the app signs.
     */
    private function secret(): string
    {
        $appKey = (string) Config::get('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7)) ?: $appKey;
        }

        return hash_hmac('sha256', 'attendance-qr-badge', $appKey, true);
    }
}
