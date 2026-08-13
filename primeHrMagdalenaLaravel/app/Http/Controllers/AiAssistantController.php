<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiConversationStore;
use App\Services\ReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    private const AREA_VIEWS = [
        'admin' => 'admin.aiAssistant.adminAiAssistant',
        'employee' => 'employee.aiAssistant.employeeAiAssistant',
        'mayor' => 'mayor.aiAssistant.mayorAiAssistant',
    ];

    /**
     * The chat endpoint lives in ChatbotController — the AI Assistant page and
     * both chatheads POST to the same `/chatbot/chat` route, so the payload a
     * question returns cannot differ between surfaces. This controller keeps
     * only the page's browsing endpoints: the conversation list, a selected
     * thread, search, delete, and report export.
     */
    public function __construct(
        private ReportPdfService $pdf,
        private AiConversationStore $conversations,
    ) {
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

    /**
     * Export a previously generated report as a PDF.
     */
    public function export(Request $request, string $token)
    {
        $result = $this->pdf->download(Auth::user(), $token);

        if (!$result) {
            abort(404, 'That report has expired. Ask the assistant to generate it again.');
        }

        return $result['pdf']->download($result['filename']);
    }

    public function messages(AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(404);
        }

        $user = Auth::user();

        $messages = $conversation->messages()->get()->map(fn (AiMessage $message) => [
            'role' => $message->role,
            'content' => $message->content,
            'created_at' => $message->created_at,
            'attachments' => $this->conversations->replayAttachments($message, $user),
        ]);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
            ],
            'messages' => $messages,
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
