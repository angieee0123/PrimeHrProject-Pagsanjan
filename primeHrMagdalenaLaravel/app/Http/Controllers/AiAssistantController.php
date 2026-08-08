<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiConversationStore;
use App\Services\AiQueryService;
use App\Services\ChartRenderer;
use App\Services\ReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    private const AREA_VIEWS = [
        'admin' => 'admin.aiAssistant.adminAiAssistant',
        'employee' => 'employee.aiAssistant.employeeAiAssistant',
        'mayor' => 'mayor.aiAssistant.mayorAiAssistant',
    ];

    /**
     * Storing, replaying and re-authorising a turn lives in
     * AiConversationStore, shared with ChatbotController — the chatheads write
     * to these same conversations, so the two surfaces cannot drift on what a
     * saved answer keeps or on who may read it back.
     */
    public function __construct(
        private AiQueryService $assistant,
        private ChartRenderer $chartRenderer,
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

        $user = Auth::user();

        if (!$conversation) {
            $conversation = $this->conversations->start($user, $message);
        }

        $history = $this->conversations->history($conversation);

        $this->conversations->recordQuestion($conversation, $message);

        $result = $this->assistant->ask($user, $message, $history);
        $response = $result['answer'];

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

        // Suggested next questions, when the capability that answered offers
        // them. Static prompt text, so there is nothing here to scope.
        if (!empty($result['follow_ups'])) {
            $payload['follow_ups'] = $result['follow_ups'];
        }

        // Files and images the answer is about. Each carries a link back to
        // AiFileController, which re-checks permission when it is opened —
        // these are references to database rows, not storage paths.
        if (!empty($result['files'])) {
            $payload['files'] = $result['files'];
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

        // The turn is stored with everything it showed, so reopening the
        // conversation replays the same tables, charts, and file cards rather
        // than the prose alone. See AiConversationStore::storableAttachments()
        // for what is left out.
        $this->conversations->recordAnswer($conversation, $response, $payload, $user);

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
