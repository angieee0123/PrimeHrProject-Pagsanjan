<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * The AI Assistant orchestrator.
 *
 * Detects what the user is asking for, routes it to the service that owns
 * that capability, and returns a narrated answer plus any structured payload
 * (table rows, chart series, a generated file) for the UI to render.
 *
 * Deliberately stateless: conversation persistence belongs to the controller,
 * so this can be called from the web UI, the API, or a queued job alike.
 */
class AiQueryService
{
    public function __construct(
        private AiAccessPolicy $policy,
        private ConversationMemoryService $memory,
        private EmployeeSearchService $employees,
        private DocumentSearchService $documents,
        private DashboardAssistantService $dashboard,
        private ReportGeneratorService $reports,
        private ChartDataService $charts,
        private WorkflowAssistantService $workflows,
        private SafeSqlService $sql,
        private HrChatbotAnswerer $fallback,
    ) {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     * @return array{answer: string, intent: string, data?: mixed, charts?: mixed, download?: array}
     */
    public function ask(User $user, string $message, array $history = []): array
    {
        $message = trim($message);

        if ($message === '') {
            return ['answer' => 'Ask me anything about employees, leave, attendance, payroll, or uploaded files.', 'intent' => 'empty'];
        }

        if (!$this->policy->canUseAssistant($user)) {
            return [
                'answer' => 'Your account is not active, so the assistant is unavailable. Please contact an administrator.',
                'intent' => 'denied',
            ];
        }

        // Resolve "generate a report for that" against the previous turns
        // before we classify — the pronoun changes what the intent is.
        $resolved = $this->memory->resolve($message, $history);
        $intent = $this->detectIntent($resolved, $user, $history);

        $started = microtime(true);

        try {
            $result = $this->dispatch($intent, $user, $resolved, $message, $history);
        } catch (\Throwable $e) {
            Log::error('AI Assistant failure', [
                'user_id' => $user->id,
                'intent' => $intent,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            $this->audit($user, $intent, $message, 'error', $e->getMessage());

            return [
                'answer' => 'Something went wrong answering that. The error has been logged — please try rephrasing.',
                'intent' => $intent,
            ];
        }

        $this->audit($user, $intent, $message, 'ok', null, (int) ((microtime(true) - $started) * 1000), $result);

        return $result + ['intent' => $intent];
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     * @return array{answer: string, data?: mixed, charts?: mixed, download?: array}
     */
    private function dispatch(string $intent, User $user, string $resolved, string $original, array $history): array
    {
        return match ($intent) {
            'employee_search' => $this->employees->search($user, $resolved, $history),
            'document_search' => $this->documents->search($user, $resolved, $history),
            'dashboard' => $this->dashboard->answer($user, $resolved, $history),
            'report' => $this->reports->generate($user, $resolved, $history),
            'chart' => $this->charts->generate($user, $resolved, $history),
            'workflow' => $this->workflows->handle($user, $resolved, $history),
            'data_query' => $this->dataQuery($user, $resolved, $history),
            default => ['answer' => $this->fallback->answer($user, $original, $history)],
        };
    }

    /**
     * Structured data questions go through generated SQL; if that is blocked
     * or comes back empty-handed, fall through to the existing chatbot brain
     * so the user still gets an answer.
     *
     * @param array<int, array{role: string, content: string}> $history
     */
    private function dataQuery(User $user, string $question, array $history): array
    {
        $result = $this->sql->query($user, $question, $history);

        if (!empty($result['blocked']) || (isset($result['error']) && empty($result['data']))) {
            return ['answer' => $this->fallback->answer($user, $question, $history)];
        }

        return $result;
    }

    /**
     * Classify the question. Pattern matching handles the overwhelming
     * majority for free and deterministically; the model is consulted only
     * when nothing matches, and its answer is constrained to known labels.
     *
     * @param array<int, array{role: string, content: string}> $history
     */
    private function detectIntent(string $message, User $user, array $history): string
    {
        $q = strtolower($message);

        // Order matters: the more specific verbs win over the nouns they contain.
        return match (true) {
            (bool) preg_match('/\b(graph|chart|plot|visuali[sz]e|pie|bar\s+chart|line\s+chart|trend\s+(?:graph|chart))\b/', $q) => 'chart',
            (bool) preg_match('/\b(generate|create|prepare|produce|export|download|draft)\b.*\b(report|summary|payslip|letter|certificate|checklist|preview)\b/', $q) => $this->reportOrWorkflow($q),
            (bool) preg_match('/\b(report)\b/', $q) => 'report',
            (bool) preg_match('/\b(draft|write|compose)\b.*\b(letter|memo|notice|email)\b|\bonboarding\b|\bapproval\s+summary\b/', $q) => 'workflow',
            (bool) preg_match('/\b(file|files|document|documents|upload(?:ed)?|attachment|scan(?:ned)?|pdf|docx?|contract|certificate|photo|picture|image|id\s*card|passport|licen[sc]e)\b/', $q) => 'document_search',
            (bool) preg_match('/\b(how many|how much|count|total|number of|who has|which department|pending|missing|expir\w+|overview|dashboard)\b/', $q) => 'dashboard',
            (bool) preg_match('/\bwhere\s+is\b|\bfind\b|\bshow\s+me\b.*\bemployees?\b|\bemployees?\s+(?:hired|appointed|in|from|with)\b|\bwho\s+is\b/', $q) => 'employee_search',
            (bool) preg_match('/\b(leave|attendance|payroll|salary|deduction|absent|late|overtime|credits?|balance|dtr)\b/', $q) => 'data_query',
            default => $this->classifyWithModel($message, $user, $history),
        };
    }

    /**
     * "Generate a payroll preview" is a workflow; "generate the payroll
     * report" is a report. Split on the noun.
     */
    private function reportOrWorkflow(string $q): string
    {
        return preg_match('/\b(letter|certificate|checklist|onboarding|preview|approval\s+summary|memo|notice)\b/', $q)
            ? 'workflow'
            : 'report';
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function classifyWithModel(string $message, User $user, array $history): string
    {
        if (!AiChatService::isConfigured($user)) {
            return 'general';
        }

        $system = <<<'PROMPT'
Classify the HR question into exactly one label. Reply with the label only —
one word, lowercase, nothing else.

employee_search  — finding people, their department, position, or hire date
document_search  — finding an uploaded file, contract, certificate, ID, photo
dashboard        — counts, totals, "how many", pending items, overview metrics
report           — asking for a generated report of records
chart            — asking for a graph or visualisation
workflow         — drafting a letter, checklist, summary, or preview document
data_query       — any other question answerable from HR records
general          — HR policy, how-to, or conversational
PROMPT;

        $messages = array_slice($history, -4);
        $messages[] = ['role' => 'user', 'content' => $message];

        $label = strtolower(trim((string) AiChatService::chat($user, $system, $messages, 0.0, 12)));
        $label = preg_replace('/[^a-z_]/', '', $label) ?? '';

        $known = ['employee_search', 'document_search', 'dashboard', 'report', 'chart', 'workflow', 'data_query', 'general'];

        return in_array($label, $known, true) ? $label : 'general';
    }

    /**
     * Every assistant interaction lands in the ai_audit channel: who asked,
     * what capability ran, and how much came back. Query text is truncated;
     * result rows are counted, never copied, so the audit log does not become
     * a second uncontrolled copy of HR data.
     *
     * @param array<string, mixed>|null $result
     */
    private function audit(
        User $user,
        string $intent,
        string $message,
        string $outcome,
        ?string $error = null,
        ?int $durationMs = null,
        ?array $result = null,
    ): void {
        Log::channel('ai_audit')->info('assistant.query', array_filter([
            'user_id' => $user->id,
            'username' => $user->username ?? $user->email,
            'roles' => implode(',', $user->roles ?? []),
            'scope' => $this->policy->describeScope($user),
            'intent' => $intent,
            'outcome' => $outcome,
            'question' => mb_substr($message, 0, 300),
            'rows_returned' => is_array($result['data'] ?? null) ? count($result['data']) : null,
            'sql' => $result['sql'] ?? null,
            'duration_ms' => $durationMs,
            'error' => $error,
        ], fn ($v) => $v !== null));
    }
}
