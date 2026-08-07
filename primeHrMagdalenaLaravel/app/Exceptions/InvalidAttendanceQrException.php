<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A scanned code is not a valid employee badge.
 *
 * The message is written to be read aloud by a kiosk operator to the person
 * standing in front of them, so it says what to do next rather than naming
 * the cryptographic reason.
 */
class InvalidAttendanceQrException extends RuntimeException
{
}
