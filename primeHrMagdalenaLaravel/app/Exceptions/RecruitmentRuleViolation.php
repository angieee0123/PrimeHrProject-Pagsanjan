<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A recruitment rule refused an action.
 *
 * The reference service raised `HTTPException` from inside its lifecycle and
 * intake services, which mixed the transport into the rule (and made the rule
 * untestable without a request). This exception keeps the rule in the service
 * and lets the controller choose the response: a page flash for a form, a 404
 * for an unknown record, a 400 for a bad upload.
 *
 * `status` is an HTTP status code because the controller's choice is almost
 * always "behave as that status" — but the message is the sentence written for
 * the officer, so it is safe to flash verbatim.
 */
class RecruitmentRuleViolation extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 409)
    {
        parent::__construct($message);
    }

    public static function notFound(string $message = 'That recruitment record does not exist.'): self
    {
        return new self($message, 404);
    }

    public static function badRequest(string $message): self
    {
        return new self($message, 400);
    }

    public static function conflict(string $message): self
    {
        return new self($message, 409);
    }
}
