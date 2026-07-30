<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Services\AiQueryService;
use App\Services\ChartRenderer;
use App\Services\ReportPdfService;
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

    public function __construct(
        private AiQueryService $assistant,
        private ChartRenderer $chartRenderer,
        private ReportPdfService $pdf,
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

        $user = Auth::user();
        $result = $this->assistant->ask($user, $message, $history);
        $response = $result['answer'];

        // Only the prose is persisted as the turn — replaying a stored table
        // months later would show stale figures. The structured payload is
        // for this response only.
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
        ]);

        $conversation->touch();

        $charts = $result['charts'] ?? null;

        $payload = [
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'response' => $response,
            'intent' => $result['intent'] ?? null,
            'status' => 'success',
        ];

        if (!empty($result['data'])) {
            $payload['data'] = $result['data'];
        }

        // Column definitions for the table the UI renders under the reply.
        if (!empty($result['table'])) {
            $payload['table'] = $result['table'];
        }

        if (!empty($charts)) {
            $payload['charts'] = $charts;
            $payload['chart_svg'] = collect($charts)
                ->map(fn (array $chart) => $this->chartRenderer->render($chart))
                ->all();
        }

        // Anything with rows or a chart can be exported; stash it so the
        // download button has something to exchange.
        if (!empty($result['table']) || !empty($charts)) {
            $report = $result['report'] ?? $result['table'] ?? [
                'title' => $charts[0]['title'] ?? 'Chart',
                'columns' => $this->inferColumns($result['data'] ?? []),
                'totals' => [],
            ];

            $payload['report'] = $report;
            $payload['export_token'] = $this->pdf->stash($user, $report, $result['data'] ?? [], $charts);
        }

        return response()->json($payload);
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

    /**
     * Build column definitions for a payload that came back without them
     * (chart tables, ad-hoc SQL results).
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{key: string, label: string}>
     */
    private function inferColumns(array $rows): array
    {
        if (empty($rows) || !is_array($rows[0] ?? null)) {
            return [];
        }

        return array_map(
            fn (string $key) => ['key' => $key, 'label' => Str::headline($key)],
            array_keys($rows[0])
        );
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
