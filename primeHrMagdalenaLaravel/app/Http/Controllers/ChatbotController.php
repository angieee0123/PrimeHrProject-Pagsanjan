<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\HrChatbotAnswerer;

class ChatbotController extends Controller
{
    /** Session key under which the running conversation is stored. */
    private const HISTORY_KEY = 'chatbot_conversation';

    /** Messages to keep (user + assistant combined) — bounds session size and prompt length. */
    private const MAX_HISTORY_MESSAGES = 12;

    public function __construct(private HrChatbotAnswerer $answerer)
    {
    }

    public function chat(Request $request)
    {
        if ($request->boolean('reset')) {
            $request->session()->forget(self::HISTORY_KEY);
        }

        $message = trim($request->input('message', ''));

        if (empty($message)) {
            if ($request->boolean('reset')) {
                return response()->json(['response' => null, 'status' => 'success']);
            }
            return response()->json(['error' => 'No message provided'], 400);
        }

        $history = $request->session()->get(self::HISTORY_KEY, []);

        $response = $this->answerer->answer(Auth::user(), $message, $history);
        $this->rememberTurn($request, $history, $message, $response);

        return response()->json(['response' => $response, 'status' => 'success']);
    }

    /**
     * Returns the session-backed conversation so the widget can re-render it
     * after a page navigation instead of resetting to the default greeting.
     */
    public function history(Request $request)
    {
        return response()->json([
            'history' => $request->session()->get(self::HISTORY_KEY, []),
            'status'  => 'success',
        ]);
    }

    /**
     * Append the latest exchange to the session-backed conversation and trim it
     * to MAX_HISTORY_MESSAGES so the prompt context and session payload stay bounded.
     */
    private function rememberTurn(Request $request, array $history, string $userMessage, string $botResponse): void
    {
        $history[] = ['role' => 'user', 'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $botResponse];
        $history   = array_slice($history, -self::MAX_HISTORY_MESSAGES);

        $request->session()->put(self::HISTORY_KEY, $history);
    }
}
