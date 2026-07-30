<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Services\AiQueryService;
use App\Services\ChartRenderer;
use App\Services\ReportPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Token-authenticated API for the AI Assistant (used by the mobile client).
 *
 * Deliberately thin: all reasoning, permission scoping, and audit logging live
 * in AiQueryService, which the web controller calls too — so the two surfaces
 * cannot drift apart in what they allow.
 */
class AiAssistantController extends Controller
{
    private const MAX_HISTORY_MESSAGES = 20;

    public function __construct(
        private AiQueryService $assistant,
        private ChartRenderer $chartRenderer,
        private ReportPdfService $pdf,
    ) {
    }

    /**
     * POST /api/ai/query
     */
    public function query(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $message = trim($validated['message']);

        $conversation = null;
        if (!empty($validated['conversation_id'])) {
            $conversation = AiConversation::where('user_id', $user->id)
                ->find($validated['conversation_id']);
        }

        $conversation ??= AiConversation::create([
            'user_id' => $user->id,
            'title' => Str::limit($message, 40),
        ]);

        $history = $conversation->messages()
            ->latest('created_at')
            ->take(self::MAX_HISTORY_MESSAGES)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        $conversation->messages()->create(['role' => 'user', 'content' => $message]);

        $result = $this->assistant->ask($user, $message, $history);

        $conversation->messages()->create(['role' => 'assistant', 'content' => $result['answer']]);
        $conversation->touch();

        $payload = [
            'status' => 'success',
            'conversation_id' => $conversation->id,
            'answer' => $result['answer'],
            'intent' => $result['intent'] ?? null,
        ];

        if (!empty($result['data'])) {
            $payload['data'] = $result['data'];
        }

        if (!empty($result['charts'])) {
            $payload['charts'] = $result['charts'];
            $payload['chart_svg'] = collect($result['charts'])
                ->map(fn (array $chart) => $this->chartRenderer->render($chart))
                ->all();
        }

        if (!empty($result['report'])) {
            $payload['report'] = $result['report'];
            $payload['export_token'] = $this->pdf->stash(
                $user,
                $result['report'],
                $result['data'] ?? [],
                $result['charts'] ?? null
            );
        }

        return response()->json($payload);
    }

    /**
     * GET /api/ai/export/{token}
     */
    public function export(Request $request, string $token)
    {
        $result = $this->pdf->download($request->user(), $token);

        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'Report expired or not found'], 404);
        }

        return $result['pdf']->download($result['filename']);
    }

    /**
     * GET /api/ai/conversations
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = AiConversation::where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get(['id', 'title', 'created_at', 'updated_at']);

        return response()->json(['status' => 'success', 'conversations' => $conversations]);
    }

    /**
     * GET /api/ai/conversations/{conversation}
     */
    public function show(Request $request, AiConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'conversation' => ['id' => $conversation->id, 'title' => $conversation->title],
            'messages' => $conversation->messages()->get(['role', 'content', 'created_at']),
        ]);
    }

    /**
     * DELETE /api/ai/conversations/{conversation}
     */
    public function destroy(Request $request, AiConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $conversation->delete();

        return response()->json(['status' => 'success']);
    }
}
