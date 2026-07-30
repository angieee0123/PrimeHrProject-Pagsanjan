<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Services\HrChatbotAnswerer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    /** Prior turns fed back into the prompt as conversation context. */
    private const MAX_HISTORY_MESSAGES = 20;

    private const AREA_VIEWS = [
        'admin' => 'admin.aiAssistant.adminAiAssistant',
        'employee' => 'employee.aiAssistant.employeeAiAssistant',
        'mayor' => 'mayor.aiAssistant.mayorAiAssistant',
    ];

    public function __construct(private HrChatbotAnswerer $answerer)
    {
    }

    public function index(Request $request)
    {
        $area = $request->segment(1); // admin | employee | mayor

        $conversations = AiConversation::where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->get();

        return view(self::AREA_VIEWS[$area], [
            'conversations' => $conversations,
            'area' => $area,
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        $message = trim($validated['message']);

        $conversation = null;
        if (!empty($validated['conversation_id'])) {
            $conversation = AiConversation::where('user_id', Auth::id())
                ->find($validated['conversation_id']);
        }

        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => Auth::id(),
                'title' => Str::limit($message, 40),
            ]);
        }

        $history = $conversation->messages()
            ->latest('created_at')
            ->take(self::MAX_HISTORY_MESSAGES)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        $response = $this->answerer->answer(Auth::user(), $message, $history);

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
        ]);

        $conversation->touch();

        return response()->json([
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'response' => $response,
            'status' => 'success',
        ]);
    }

    public function messages(AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(404);
        }

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
            ],
            'messages' => $conversation->messages()->get(['role', 'content', 'created_at']),
            'status' => 'success',
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json(['results' => [], 'status' => 'success']);
        }

        $matchingConversationIds = AiConversation::where('user_id', Auth::id())
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhereHas('messages', function ($mq) use ($q) {
                        $mq->where('content', 'like', "%{$q}%");
                    });
            })
            ->orderByDesc('updated_at')
            ->get();

        $results = $matchingConversationIds->map(function (AiConversation $conversation) use ($q) {
            $snippetMessage = $conversation->messages()
                ->where('content', 'like', "%{$q}%")
                ->latest('created_at')
                ->first();

            return [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updated_at' => $conversation->updated_at,
                'snippet' => $snippetMessage?->content,
            ];
        });

        return response()->json(['results' => $results, 'status' => 'success']);
    }

    public function destroy(AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(404);
        }

        $conversation->delete();

        return response()->json(['status' => 'success']);
    }
}
