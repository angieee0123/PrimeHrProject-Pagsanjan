<?php

namespace App\Http\Controllers;

use App\Services\HrChatbotAnswerer;
use Illuminate\Http\Request;

/**
 * The public AI assistant behind the welcome page's chat widget.
 *
 * This is the only assistant endpoint a logged-out visitor can reach, and it is
 * deliberately smaller than `/chatbot/chat`: there is no user to scope to, so
 * no data capability is reachable at all. Questions are answered through
 * `HrChatbotAnswerer::answer(null, ...)`, whose null-user path can never run
 * generated SQL and never reads an employee record — a guest gets the greeting,
 * the curated policy/knowledge-base answers, and a non-SQL general fallback.
 *
 * Stateless on purpose: a visitor is not a user, so nothing is written to
 * `ai_conversations`, and the widget already sends no history.
 */
class PublicChatbotController extends Controller
{
    /** Hard cap on the question, so one visitor cannot flood the prompt. */
    private const MAX_MESSAGE_LENGTH = 500;

    public function __construct(
        private HrChatbotAnswerer $answerer,
    ) {
    }

    public function chat(Request $request)
    {
        $message = trim((string) $request->input('message', ''));

        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            $message = mb_substr($message, 0, self::MAX_MESSAGE_LENGTH);
        }

        if ($message === '') {
            return response()->json(['error' => 'No message provided'], 400);
        }

        $response = $this->answerer->answer(null, $message);

        return response()->json([
            'status' => 'success',
            'response' => $response,
            'full_response' => $response,
            'follow_up_questions' => [],
        ]);
    }
}
