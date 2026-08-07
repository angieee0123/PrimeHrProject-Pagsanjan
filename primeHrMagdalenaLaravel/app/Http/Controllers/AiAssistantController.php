<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use App\Services\AiAccessPolicy;
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

    /**
     * Ceiling on the stored JSON for one turn. A 200-row report is a few tens
     * of kilobytes; past this the rows are dropped and the turn replays as the
     * prose plus its chart, rather than growing the table without bound.
     */
    private const MAX_ATTACHMENT_BYTES = 262144;

    public function __construct(
        private AiQueryService $assistant,
        private ChartRenderer $chartRenderer,
        private ReportPdfService $pdf,
        private AiAccessPolicy $policy,
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
        // than the prose alone. See storableAttachments() for what is left out.
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
            'attachments' => $this->storableAttachments($payload, $user),
        ]);

        $conversation->touch();

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

    /**
     * Reduce a response payload to the part worth keeping on the message row.
     *
     * Left out on purpose:
     *  - `chart_svg`, derived from the chart spec and re-rendered on read, so
     *    a stored turn picks up palette and renderer fixes instead of freezing
     *    the markup that shipped that day;
     *  - `export_token`, which expires after an hour and is bound to the user
     *    who generated it — a fresh one is issued when the turn is replayed.
     *
     * `scope` records what the asker was allowed to see at the time. A user who
     * later loses org-wide access must not keep a readable copy of an org-wide
     * table in their history, so replay compares it against the scope now.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    private function storableAttachments(array $payload, User $user): ?array
    {
        $stored = array_filter([
            'table' => $payload['table'] ?? null,
            'data' => $payload['data'] ?? null,
            'files' => $payload['files'] ?? null,
            'charts' => $payload['charts'] ?? null,
            'report' => $payload['report'] ?? null,
        ]);

        if (empty($stored)) {
            return null;
        }

        $stored['scope'] = $this->policy->describeScope($user);
        $stored['generated_at'] = now()->toIso8601String();

        if (strlen((string) json_encode($stored)) > self::MAX_ATTACHMENT_BYTES) {
            unset($stored['data']);
            $stored['data_omitted'] = true;
        }

        return $stored;
    }

    /**
     * Rebuild a stored turn into the shape the UI renders live responses with:
     * charts re-rendered from their specs, and a fresh export token so the
     * download button on an old answer still works.
     *
     * @return array<string, mixed>|null
     */
    private function replayAttachments(AiMessage $message, User $user): ?array
    {
        $stored = $message->attachments;

        if (empty($stored) || !is_array($stored)) {
            return null;
        }

        // Permissions are re-evaluated on read, never trusted from the row.
        // The payload is withheld unless the scope it was captured under still
        // matches the reader's — a user whose access narrowed must not keep a
        // readable copy of a wider answer in their history. Turns saved before
        // the scope was recorded are unverifiable, so they are withheld too.
        // (File cards go with it; AiFileResolver would refuse them anyway.)
        if (($stored['scope'] ?? null) !== $this->policy->describeScope($user)) {
            return ['withheld' => true, 'generated_at' => $stored['generated_at'] ?? null];
        }

        $replay = [
            'table' => $stored['table'] ?? null,
            'data' => $stored['data'] ?? null,
            'files' => $stored['files'] ?? null,
            'data_omitted' => $stored['data_omitted'] ?? false,
            'generated_at' => $stored['generated_at'] ?? null,
        ];

        if (!empty($stored['charts'])) {
            $replay['chart_svg'] = array_map(
                fn (array $chart) => $this->chartRenderer->render($chart),
                $stored['charts']
            );
        }

        if (!empty($stored['report'])) {
            $replay['export_token'] = $this->pdf->stash(
                $user,
                $stored['report'],
                $stored['data'] ?? [],
                $stored['charts'] ?? null,
                "ai-message:{$message->id}"
            );
        }

        return array_filter($replay, fn ($value) => $value !== null && $value !== false);
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
            'attachments' => $this->replayAttachments($message, $user),
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
