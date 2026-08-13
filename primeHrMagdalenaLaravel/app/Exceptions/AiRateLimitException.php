<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The AI provider refused a chat call because a usage limit was hit.
 *
 * Thrown by AiChatService when a provider returns a rate-limit response
 * (HTTP 429, e.g. Groq's "tokens per day" cap), so the user-facing layers —
 * AiQueryService::ask() and HrChatbotAnswerer — can show a helpful message
 * instead of the generic "the provider appears to be unavailable" fallback,
 * which is wrong: the provider is up, the daily budget is spent.
 */
class AiRateLimitException extends RuntimeException
{
    public function __construct(string $provider, string $detail = '')
    {
        $retry = '';

        // Groq body e.g. "Please try again in 34m13.728s."
        if (preg_match('/try again in\s+(\d+)m(?:\d+\.?\d*)?s?/i', $detail, $m)) {
            $retry = ' in about ' . $m[1] . ' minute(s)';
        }

        $message = "I'm sorry — the chat has reached the AI provider's daily token limit"
            . ", so I can't process new questions right now. Please try again{$retry}"
            . ' (the limit resets daily). Questions I can answer directly from the system'
            . ' — like policy, leave types, or how-to steps — still work.';

        parent::__construct($message, 429);
    }

    public function friendlyMessage(): string
    {
        return $this->getMessage();
    }
}
