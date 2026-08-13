<?php

namespace App\Services;

use App\Exceptions\AiRateLimitException;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        private EmployeeChatbotService $selfService,
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

        $started = microtime(true);

        $resolved = $message;
        $intent = 'general';

        try {
            // Resolve "generate a report for that" against the previous turns
            // before we classify — the pronoun changes what the intent is.
            $resolved = $this->memory->resolve($message, $history);
            $intent = $this->detectIntent($resolved, $user, $history);

            $result = $this->dispatch($intent, $user, $resolved, $message, $history);
        } catch (AiRateLimitException $e) {
            Log::warning('AI Assistant rate limited', [
                'user_id' => $user->id,
                'intent' => $intent,
            ]);

            $this->audit($user, $intent, $message, 'rate_limited');

            return [
                'answer' => $e->friendlyMessage(),
                'intent' => $intent,
            ];
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

        $result = $this->attachTableSpec($result, $resolved);

        $this->audit($user, $intent, $message, 'ok', null, (int) ((microtime(true) - $started) * 1000), $result);

        return $result + ['intent' => $intent];
    }

    /**
     * Give the UI something to render as a table whenever an answer carries
     * rows. Report and workflow handlers already describe their own columns;
     * ad-hoc results (generated SQL, searches) get theirs inferred from the
     * first row, so "generate a table of…" produces an actual table rather
     * than a paragraph listing the values.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function attachTableSpec(array $result, string $question): array
    {
        $rows = $result['data'] ?? null;

        if (!is_array($rows) || empty($rows) || !is_array($rows[0] ?? null)) {
            return $result;
        }

        // A file search already renders each row as a clickable card, so a
        // table beneath them repeats the same file name, type, size, and date
        // in a wider, less useful form. Suppress it — but only when every row
        // actually became a card: buildFileAttachments() drops rows whose file
        // is missing from storage and caps the rest, and those rows exist
        // nowhere else, so a partial card set still needs its table.
        $files = $result['files'] ?? null;

        if (is_array($files) && count($files) === count($rows)) {
            return $result;
        }

        if (!empty($result['report']['columns'])) {
            $result['table'] = $result['report'];

            return $result;
        }

        $result['table'] = [
            'key' => 'result',
            'title' => $this->titleFor($question),
            'columns' => array_map(
                fn (string $key) => [
                    'key' => $key,
                    'label' => Str::headline($key),
                    'align' => is_numeric($rows[0][$key] ?? null) ? 'right' : 'left',
                ],
                array_keys($rows[0])
            ),
            'totals' => ['Rows' => count($rows)],
            'row_count' => count($rows),
        ];

        return $result;
    }

    /**
     * A short heading for an ad-hoc result, derived from the question itself.
     */
    private function titleFor(string $question): string
    {
        $title = trim(preg_replace('/^\s*(please\s+)?(can\s+you\s+)?(generate|create|show|give|list|make|build|produce)\s+(me\s+)?(a\s+|an\s+|the\s+)?(table|list|report)?\s*(of|for|with)?\s*/i', '', $question) ?? $question);
        $title = rtrim($title, " ?.");

        return $title === '' ? 'Results' : Str::ucfirst($title);
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
            'self_service' => $this->ownRecords($user, $resolved),
            'how_to' => ['answer' => $this->fallback->explain($user, $resolved, $history)],
            'capabilities' => $this->capabilities($user),
            default => ['answer' => $this->fallback->answer($user, $original, $history)],
        };
    }

    /**
     * "What can you do?" — usually the first thing anyone asks.
     *
     * Answered from AiAccessPolicy rather than a prompt, for two reasons: the
     * list then cannot promise a capability the caller is not permitted to use,
     * and it costs no model call. The follow-ups are live examples the user can
     * click straight into.
     *
     * @return array{answer: string, follow_ups: array<int, string>}
     */
    private function capabilities(User $user): array
    {
        $lines = array_map(
            fn (string $item) => "- {$item}",
            $this->policy->describeCapabilities($user)
        );

        $orgWide = $this->policy->hasOrgWideAccess($user);

        $opening = $orgWide
            ? "I'm the PRIME HRIS Assistant. You have organisation-wide access, so I can help with:"
            : "I'm the PRIME HRIS Assistant. I can help you with:";

        $closing = $orgWide
            ? "\n\nAsk in plain language — English or Tagalog. I can also draw charts and export any table to PDF."
            : "\n\nAsk in plain language — English or Tagalog. For privacy I can only show your own records, "
                . 'not other employees\'.';

        return [
            'answer' => $opening . "\n" . implode("\n", $lines) . $closing,
            'follow_ups' => $orgWide
                ? ['How many employees are on leave today?', 'Generate an attendance summary report', 'What is my leave balance?']
                : ['What is my leave balance?', 'Show my latest payslip', 'How do I file a leave request?'],
        ];
    }

    /**
     * Answer a question about the caller's own HR records.
     *
     * This is the path that makes the assistant useful to an employee. It does
     * not generate SQL, so it does not need the org-wide permission that
     * generated SQL does: every query behind it filters on this employee's own
     * id. `admin`/`hr`/`mayor` reach it too — "what is my leave balance" means
     * their own record regardless of what else they may see.
     *
     * @return array{answer: string, follow_ups?: array<int, string>}
     */
    private function ownRecords(User $user, string $question): array
    {
        $employeeId = $this->policy->ownEmployeeId($user);

        $employee = $employeeId === null ? null : Employee::find($employeeId);

        // An account with no linked employee row has no "own" records to show.
        // Say so plainly rather than falling through to a path that would
        // answer from someone else's data.
        if (!$employee) {
            return [
                'answer' => 'Your account is not linked to an employee record, so I cannot look up your '
                    . 'personal HR information. Please ask HR to link your account.',
            ];
        }

        return $this->selfService->assist($employee, $question);
    }

    /**
     * Structured data questions go through generated SQL. If the statement
     * merely failed to work, fall through to the chatbot brain so the user
     * still gets an answer.
     *
     * A *blocked* result is different, and must not fall through. Blocking is a
     * permission decision — SafeSqlService refuses either because the caller
     * lacks org-wide access or because the statement was unsafe. Routing that
     * to another answerer would mean the denial itself is what triggers the
     * second attempt, so the user is answered precisely because they were told
     * no. Return the refusal instead.
     *
     * @param array<int, array{role: string, content: string}> $history
     */
    private function dataQuery(User $user, string $question, array $history): array
    {
        $result = $this->sql->query($user, $question, $history);

        if (!empty($result['blocked'])) {
            return $result;
        }

        if (isset($result['error']) && empty($result['data'])) {
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
            // First, because it is unambiguous and usually the opening question.
            $this->wantsCapabilities($q) => 'capabilities',
            (bool) preg_match('/\b(graph|chart|plot|visuali[sz]e|pie|bar\s+chart|line\s+chart|trend\s+(?:graph|chart))\b/', $q) => 'chart',
            // Checked before the stored-file rule because "file" is a verb at
            // least as often as it is a noun here: "how do I file a leave
            // application" is a how-to, but the file-noun list matches "file"
            // and would answer it with a document search.
            $this->wantsHowTo($q) => 'how_to',
            // Questions about what the *rules* are, as opposed to what the
            // records say. Placed here for two reasons, both of which produced
            // wrong answers before it existed:
            //
            //  - it must beat the stored-file rule, because "anong uri ng leave
            //    ang pwede kong i-file" and "can I file bereavement leave"
            //    both contain "file" and were answered with a document search;
            //  - it must beat data_query, whose noun list claims "leave",
            //    "late" and "credits", so "what leave types are available" was
            //    sent down the generated-SQL path and *refused* for every
            //    employee — a policy question they are entitled to an answer to.
            $this->wantsPolicy($q) => 'how_to',
            // "show me his 201 file", "list all documents of Juan" — asking to
            // see a stored file beats both the report and the table rules,
            // which would otherwise swallow "list"/"download" and answer with
            // a table of rows instead of the file itself.
            $this->wantsStoredFile($q) => 'document_search',
            (bool) preg_match('/\b(generate|create|prepare|produce|export|download|draft|issue)\b.*\b(report|summary|payslip|letter|certificate|checklist|preview)\b/', $q) => $this->reportOrWorkflow($q),
            (bool) preg_match('/\b(report)\b/', $q) => 'report',
            // "table"/"list of" asks for tabular output over arbitrary criteria,
            // which is the generated-SQL path — the table itself is attached to
            // every row-bearing answer downstream.
            (bool) preg_match('/\b(table|tabulate|spreadsheet|list\s+(?:of|all|the)|breakdown\s+of)\b/', $q) => 'data_query',
            (bool) preg_match('/\b(draft|write|compose)\b.*\b(letter|memo|notice|email)\b|\bonboarding\b|\bapproval\s+summary\b/', $q) => 'workflow',
            // "What is my leave balance?" — the caller's own records. Must beat
            // both the dashboard rule ("how many leave credits do I have") and
            // the data_query rule ("my attendance this month"), which would
            // otherwise send an employee down a path they are not permitted to
            // use and get them refused for asking about themselves.
            $this->isSelfReferential($q) => 'self_service',
            // Office-holder questions are always a person lookup — "who is the
            // mayor", "sino ang mayor", "who's the HR officer". Checked here so
            // the "who's" contraction and the Tagalog form, which the
            // `\bwho\s+is\b` rule below cannot see, still route to
            // employee_search instead of falling to the model or the generated
            // SQL path (which used to answer "who's the mayor" with a roster).
            (bool) preg_match(
                '/\b(?:who\s+is|who\'s|sino)\b.{0,25}\b(vice\s+mayor|mayor|administrator|hr\s*(?:officer|head|personnel)?|human\s+resources)\b/',
                $q
            ) => 'employee_search',
            (bool) preg_match('/\b(how many|how much|count|total|number of|who has|which department|pending|missing|expir\w+|overview|dashboard)\b/', $q) => 'dashboard',
            // Transactional nouns beat the employee-lookup rule below, which
            // would otherwise claim "employees with more than 5 late arrivals"
            // on its `employees?\s+with` branch — and EmployeeSearchService
            // only does name, department, and hire-date lookups, so it cannot
            // count anything. A question naming attendance, leave, or payroll
            // is about those records even when it also says "employees".
            (bool) preg_match('/\b(leave|attendance|payroll|salary|deduction|absent|late|overtime|credits?|balance|dtr)\b/', $q) => 'data_query',
            (bool) preg_match('/\bwhere\s+is\b|\bfind\b|\bshow\s+me\b.*\bemployees?\b|\bemployees?\s+(?:hired|appointed|in|from|with)\b|\bwho\s+is\b/', $q) => 'employee_search',
            default => $this->classifyWithModel($message, $user, $history),
        };
    }

    /**
     * Whether the user is asking to *see a stored file*, as opposed to asking
     * a question about records.
     *
     * Two ways to qualify:
     *  - naming a kind of upload outright ("her PhilHealth scan", "the 201 file")
     *  - a display verb plus something showable ("show me Ana's photo")
     *
     * "how many documents were uploaded" is deliberately excluded — that is a
     * count, and the dashboard owns it.
     */
    private function wantsStoredFile(string $q): bool
    {
        if (preg_match('/\b(how\s+many|count|total\s+number)\b/', $q)) {
            return false;
        }

        // "draft a certificate of employment" asks us to *produce* a document,
        // which is the workflow capability — without this, the noun list below
        // matches "certificate" and answers with a search for existing ones.
        // Only unambiguous authoring verbs count: "download the certificate"
        // and "export the contract" are still retrieval.
        if (preg_match('/\b(draft|write|compose|issue)\b/', $q)) {
            return false;
        }

        $fileNouns = '(?:file|files|document|documents|attachment|attachments|upload(?:s|ed)?|scan(?:s|ned)?|'
            . 'pdf|docx?|xlsx?|photo|photos|picture|pictures|image|images|headshot|'
            . 'contract|certificate|certificates|diploma|transcript|resume|cv|passport|licen[sc]e|'
            . 'id\s*card|government\s*id|gsis|philhealth|pag-?ibig|tin|saln|clearance|201\s*file)';

        if (preg_match('/\b' . $fileNouns . '\b/', $q)) {
            return true;
        }

        return (bool) preg_match(
            '/\b(show|display|open|view|preview|see|send|give|get|fetch|attach|pull\s+up)\b.{0,40}\b(id|ids|copy|copies)\b/',
            $q
        );
    }

    /**
     * Whether the user is asking what the assistant itself can do.
     *
     * "help" only counts on its own: "help me find Juan" is a search, and
     * answering that with a capability list would be useless.
     */
    private function wantsCapabilities(string $q): bool
    {
        return (bool) preg_match(
            '/\bwhat\s+(?:can|could)\s+(?:you|i|we)\s+(?:do|ask|help|search)\b'
            . '|\bwhat\s+(?:do|are)\s+you\b|\bwho\s+are\s+you\b'
            . '|\bwhat\s+(?:questions|things)\s+can\b'
            . '|\bano(?:ng)?\s+(?:ang\s+)?(?:kaya|magagawa|pwede)\b'
            . '|^\s*(?:help|tulong)\s*[?!.]*$/',
            $q
        );
    }

    /**
     * Whether the user is asking how something works or how to do it, as
     * opposed to asking for a value out of the database.
     *
     * Tagalog is matched alongside English here because the knowledge base
     * behind this intent already answers in both, and a question typed as
     * "paano mag-file ng leave?" would otherwise match no pattern at all and
     * fall through to the model classifier.
     */
    private function wantsHowTo(string $q): bool
    {
        // "how many" / "how much" are counts, not instructions — the dashboard
        // owns those, and \s+(?:do|can|…) already excludes them.
        return (bool) preg_match(
            '/\bhow\s+(?:do|does|can|should|would)\s+(?:i|we|you|an?\s+employee)\b'
            . '|\bhow\s+to\b'
            . '|\b(?:pa?ano|papaano)\b'
            . '|\bwhat\s+is\s+the\s+(?:process|procedure|policy|rule|step)/',
            $q
        );
    }

    /**
     * Whether the question asks what a rule *is* — the leave types this system
     * offers, how credits accrue, how late is deducted, the grace period, LWOP,
     * working hours, or whether a leave may be filed at all.
     *
     * These are answered from HrPolicyFactsService, which reads the live
     * `leave_types_config` and the service constants, so they cost no model
     * call and cannot drift from the system. Crucially they are answerable for
     * *every* role: an employee asking "what leave can I file?" is asking about
     * policy, not about anyone's records, and refusing it for lack of org-wide
     * access was simply wrong.
     */
    private function wantsPolicy(string $q): bool
    {
        // Subjects whose answer is the rulebook however the asker phrases it.
        // "What leave types can I file?" reads as self-referential — "leave …
        // can I" — but there is no personal figure in it; the answer is the
        // configured list either way. Guarding these behind isSelfReferential()
        // sent that exact question to the stored-file rule, which matched the
        // verb "file" and replied with a document search.
        if (preg_match(
            '/\bleave\s+types?\b|\btypes?\s+of\s+leave\b|\bkinds?\s+of\s+leave\b'
            . '|\buri\s+ng\s+leave\b|\banong\s+(?:mga\s+)?leave\b'
            . '|\b(?:leave|credits?|vl|sl)\b.{0,20}\baccru\w+'
            . '|\baccru\w+.{0,20}\b(?:leave|credits?|vl|sl)\b'
            . '|\bgrace\s*period\b'
            . '|\blwop\b|\bleave\s+without\s+pay\b'
            . '|\bworking\s+hours?\b|\boras\s+ng\s+trabaho\b'
            . '|\b(?:can|may|pwede|entitled\s+to)\b.{0,40}\b(?:file|avail|apply\s+for)\b.{0,25}\bleave\b/',
            $q
        )) {
            return true;
        }

        // Deduction *mechanics* are policy, but the same words asked about
        // oneself are a computation: "how is late deducted" is the rulebook,
        // "how much did my late cost" is a sum over this employee's own
        // accredited-hours log, which EmployeeChatbotService performs. Only the
        // impersonal form belongs here.
        if ($this->isSelfReferential($q)) {
            return false;
        }

        return (bool) preg_match(
            '/\b(?:late|undertime)\b.{0,30}\b(?:deduct\w+|comput\w+|calculat\w+)\b'
            . '|\b(?:deduct\w+|comput\w+|calculat\w+)\b.{0,30}\b(?:late|undertime)\b/',
            $q
        );
    }

    /**
     * Whether the question is about the caller's own HR records.
     *
     * Deliberately narrow: the self-reference has to attach to a personal HR
     * noun. A blanket match on "my" would capture "my department's headcount"
     * from an HR officer and wrongly narrow it to their own row.
     */
    private function isSelfReferential(string $q): bool
    {
        $ownNouns = '(?:leave|vl|sl|spl|credits?|balances?|payslip|pay\s*slip|salary|sweldo|sahod|'
            . 'net\s*pay|deductions?|attendance|dtr|absences?|late|undertime|overtime|'
            . 'trainings?|seminars?|travel\s*orders?|profile|records?|info(?:rmation)?|details)';

        // "my leave balance", "aking payslip", "my remaining VL credits"
        if (preg_match('/\b(?:my|mine|aking|akin)\b(?:\s+\w+){0,3}\s*\b' . $ownNouns . '\b/', $q)) {
            return true;
        }

        // "how many leave credits do I have", "what leave can I still use"
        if (preg_match('/\b' . $ownNouns . '\b.{0,40}\b(?:do|did|can|will|should)\s+i\b/', $q)) {
            return true;
        }

        // "do I have any pending leave", "I have how many VL left"
        if (preg_match('/\bi\s+have\b.{0,40}\b' . $ownNouns . '\b/', $q)) {
            return true;
        }

        // Tagalog puts the possessor after the noun and drops "my" entirely:
        // "ano ang mga leave credits na meron AKO", "ilang VL ang natitira sa
        // AKIN", "leave ko". Without these the question reads as org-wide and
        // goes to generated SQL — which answered "no rows" to an employee whose
        // own leave page was showing 136 days of credits.
        if (preg_match(
            '/\b' . $ownNouns . '\b.{0,40}\b(?:meron|mayroon|natitira|natirang|naiwan|ako|akin|ko)\b/',
            $q
        )) {
            return true;
        }

        // "meron ba akong leave credits", "natitirang VL ko"
        //
        // "ilan"/"ilang" ("how many") is deliberately absent: it is a bare
        // quantifier, not a possessor, and including it claimed "ilan ang leave
        // applications na pending" — an organisation-wide count — as a question
        // about the asker's own credits. The genuinely personal phrasings that
        // open with "ilan" carry a possessor later ("…natitira sa akin"), which
        // the rule above already matches.
        if (preg_match(
            '/\b(?:meron|mayroon|natitira|natirang|naiwan)\b.{0,40}\b' . $ownNouns . '\b/',
            $q
        )) {
            return true;
        }

        return (bool) preg_match('/\bwho\s+am\s+i\b|\bmy\s+(?:profile|info|account|details)\b/', $q);
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
self_service     — the asker's OWN records ("my leave balance", "my payslip")
how_to           — how to do something in the system, or what a policy says
capabilities     — what the assistant itself can do
data_query       — any other question answerable from HR records
general          — conversational, or none of the above
PROMPT;

        $messages = array_slice($history, -4);
        $messages[] = ['role' => 'user', 'content' => $message];

        $label = strtolower(trim((string) AiChatService::chat($user, $system, $messages, 0.0, 12)));
        $label = preg_replace('/[^a-z_]/', '', $label) ?? '';

        $known = ['employee_search', 'document_search', 'dashboard', 'report', 'chart', 'workflow',
            'self_service', 'how_to', 'capabilities', 'data_query', 'general'];

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
