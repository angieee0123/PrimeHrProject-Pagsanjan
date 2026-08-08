<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AiConversationStore;
use App\Services\AiQueryService;

class ChatbotController extends Controller
{
    /**
     * Set by "Clear conversation" so the *next* question opens a fresh thread.
     *
     * Clearing must not delete the conversation: the chathead continues the
     * user's newest thread, which the full-page AI Assistant also writes to, so
     * a delete here would destroy history the user never asked to lose. Losing
     * this flag with the session is harmless — at worst the widget carries on
     * in the thread it was already in.
     */
    private const START_NEW_KEY = 'chatbot_start_new_thread';

    /**
     * The floating widget goes through the same assistant as the full page.
     * It previously called HrChatbotAnswerer directly, which generates SQL
     * without the domain rules — that is what produced answers like counting
     * an employee with one absence as having ten.
     */
    public function __construct(
        private AiQueryService $assistant,
        private AiConversationStore $conversations,
    ) {
    }

    public function chat(Request $request)
    {
        $user = Auth::user();

        if ($request->boolean('reset')) {
            $request->session()->put(self::START_NEW_KEY, true);
        }

        $message = trim($request->input('message', ''));

        if (empty($message)) {
            if ($request->boolean('reset')) {
                return response()->json(['response' => null, 'status' => 'success']);
            }
            return response()->json(['error' => 'No message provided'], 400);
        }

        // The widget continues the user's newest conversation rather than
        // keeping one of its own, so a question asked from the chathead is the
        // same thread — and the same remembered context — as one asked on the
        // AI Assistant page, and is still there after logging out and back in.
        $conversation = $request->session()->pull(self::START_NEW_KEY, false)
            ? $this->conversations->start($user, $message)
            : $this->conversations->continueLatestOrStart($user, $message);

        // Read before the new question is recorded, or the model is handed the
        // question it is being asked.
        $history = $this->conversations->history($conversation);

        $this->conversations->recordQuestion($conversation, $message);

        $result = $this->assistant->ask($user, $message, $history);
        $response = $result['answer'];

        // The full answer, not a prose summary of it. The widgets previously
        // received only `response`/`table`/`data`, so a file search in the
        // chathead lost its file cards and every answer lost its suggested
        // follow-ups — the same question gave a visibly poorer answer here than
        // on the AI Assistant page, from the identical AiQueryService call.
        $payload = array_filter([
            'conversation_id' => $conversation->id,
            'response' => $response,
            'intent' => $result['intent'] ?? null,
            'table' => $result['table'] ?? null,
            'data' => $result['data'] ?? null,
            'files' => $result['files'] ?? null,
            'charts' => $result['charts'] ?? null,
            'follow_ups' => $result['follow_ups'] ?? null,
            'status' => 'success',
        ], fn ($value) => $value !== null);

        $this->conversations->recordAnswer($conversation, $response, $payload, $user);

        return response()->json($payload);
    }

    /**
     * Returns the stored conversation so the widget can re-render it after a
     * page navigation — or after a logout, a new browser, or a session that
     * timed out overnight, none of which the session-backed version survived.
     *
     * Attachments are replayed through AiConversationStore, which re-checks the
     * scope the answer was captured under against the reader's scope now.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $conversation = $this->conversations->latestFor($user);

        if (!$conversation) {
            return response()->json(['history' => [], 'status' => 'success']);
        }

        $history = $conversation->messages()->get()->map(fn (AiMessage $message) => array_filter([
            'role' => $message->role,
            'content' => $message->content,
            'created_at' => $message->created_at?->toIso8601String(),
            'attachments' => $this->conversations->replayAttachments($message, $user),
        ], fn ($value) => $value !== null));

        return response()->json([
            'conversation_id' => $conversation->id,
            'history' => $history,
            'status'  => 'success',
        ]);
    }
}
