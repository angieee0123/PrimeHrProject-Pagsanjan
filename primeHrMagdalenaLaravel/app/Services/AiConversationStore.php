<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Where a conversation with the assistant is kept.
 *
 * The full-page AI Assistant and both floating chatheads write to the same
 * `ai_conversations` / `ai_messages` pair through this service. The chathead
 * used to keep its thread in the Laravel session instead, which meant it was
 * destroyed by logout (`AuthController` calls `session()->invalidate()`), by
 * the 120-minute session lifetime, and by opening the system in another
 * browser — the widget looked like it remembered, right up until it silently
 * did not. Session storage also held prose only, so a replayed turn dropped the
 * file cards and tables the answer originally carried, and nothing asked in the
 * widget was ever findable from the full page's history or search.
 *
 * Storing and replaying an answer's attachments is a permission decision as
 * much as a serialisation one, so it lives here rather than in either
 * controller: both surfaces must re-authorise a stored turn identically.
 */
class AiConversationStore
{
    /**
     * Prior turns fed back into the prompt as conversation context. One value
     * for every surface — the chathead and the full page can share a thread, so
     * a different window would mean the same question got different context
     * depending on which box it was typed into.
     */
    public const MAX_HISTORY_MESSAGES = 20;

    /**
     * Ceiling on the stored JSON for one turn. A 200-row report is a few tens
     * of kilobytes; past this the rows are dropped and the turn replays as the
     * prose plus its chart, rather than growing the table without bound.
     */
    private const MAX_ATTACHMENT_BYTES = 262144;

    public function __construct(
        private AiAccessPolicy $policy,
        private ChartRenderer $chartRenderer,
        private ReportPdfService $pdf,
    ) {
    }

    /**
     * The user's most recently used conversation, or null if they have none.
     */
    public function latestFor(User $user): ?AiConversation
    {
        return AiConversation::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * The conversation a chathead question belongs to: the newest thread the
     * user has, so the widget picks up where the full page left off instead of
     * starting a thread nobody can find afterwards.
     */
    public function continueLatestOrStart(User $user, string $firstMessage): AiConversation
    {
        return $this->latestFor($user) ?? $this->start($user, $firstMessage);
    }

    public function start(User $user, string $firstMessage): AiConversation
    {
        return AiConversation::create([
            'user_id' => $user->id,
            'title' => Str::limit($firstMessage, 40),
        ]);
    }

    /**
     * Prior turns in prompt shape. Read this *before* recording the new
     * question, or the model is handed the question it is being asked.
     *
     * @return list<array{role: string, content: string}>
     */
    public function history(AiConversation $conversation, int $limit = self::MAX_HISTORY_MESSAGES): array
    {
        // reorder() first: the relation sorts oldest-first, and an appended
        // `latest()` is a *secondary* key that never takes effect — so the
        // limit took the oldest turns and reverse() then handed the model the
        // start of the thread, backwards, as its "recent" context.
        return $conversation->messages()
            ->reorder()
            ->orderByDesc('id')
            ->take($limit)
            ->get()
            ->reverse()
            ->map(fn (AiMessage $message) => ['role' => $message->role, 'content' => $message->content])
            ->values()
            ->all();
    }

    public function recordQuestion(AiConversation $conversation, string $message): AiMessage
    {
        return $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);
    }

    /**
     * Store an answer with everything it showed, so reopening the conversation
     * replays the same tables, charts, and file cards rather than the prose
     * alone. `touch()` keeps the thread at the top of the sidebar and makes it
     * the one a chathead question continues.
     *
     * @param array<string, mixed> $payload
     */
    public function recordAnswer(
        AiConversation $conversation,
        string $response,
        array $payload,
        User $user,
    ): AiMessage {
        $message = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
            'attachments' => $this->storableAttachments($payload, $user),
        ]);

        $conversation->touch();

        return $message;
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
    public function storableAttachments(array $payload, User $user): ?array
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
    public function replayAttachments(AiMessage $message, User $user): ?array
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
}
