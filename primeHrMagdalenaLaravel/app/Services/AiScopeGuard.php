<?php

namespace App\Services;

/**
 * The assistant's scope, in one place.
 *
 * The HRIS assistant answers three kinds of question and no others:
 *
 *  1. **HR records and HR policy** — employees, leave, attendance, DTR, payroll,
 *     deductions, documents, trainings, travel orders, benefits, monetization.
 *  2. **The Municipality of Pagsanjan itself** — its services, offices and
 *     Citizen's Charter (the same predicate that routes those questions to
 *     `CitizenCharterService` decides this half).
 *  3. **This system** — how it is used, and what the assistant can do.
 *
 * Everything else used to be answered. The knowledge base path
 * (`HrChatbotAnswerer::explain()` → `askDirectly()`) asked a model "answer this
 * question using the system knowledge below" with nothing telling it to stay in
 * the domain, so "how to write a for loop in python" came back with a working
 * Python loop: true, irrelevant, and from a chatbot that is supposed to answer
 * from this municipality's records. The `general` catch-all had the same hole
 * from the other side — an org-wide caller's off-topic question went to
 * text-to-SQL, and its failure fell through to the same free-text answerer.
 *
 * Two layers close it, and they are deliberately different in kind:
 *
 *  - **This guard, in PHP.** `isOutOfScope()` refuses a question deterministically,
 *    before any capability or model runs. Like the rest of the assistant's
 *    scoping rules, the decision is code rather than prompt, so it still holds
 *    when the model ignores its instructions — or when there is no model at all.
 *  - **`PROMPT_CLAUSE`.** The same boundary is prepended to *every* prompt that
 *    reaches a provider (see `AiChatService::chat()`), because the patterns below
 *    cannot see a topic nobody has written a word for yet. That layer is the one
 *    that catches "explain closures in JavaScript" when the language list drifts
 *    behind the world.
 *
 * The patterns claim only what they cannot be wrong about. A question is refused
 * only when it names an off-topic subject *and* names no HR or municipal subject
 * at all — so "which of our employees know Python" (a real training/skills
 * question) is answered, while "how to write a for loop in python" is not. That
 * asymmetry is on purpose: refusing a real HR question is a worse failure than
 * an occasional off-topic question reaching the prompt clause above.
 */
class AiScopeGuard
{
    /**
     * What the assistant is for, stated once, for the refusal and the
     * "what can you do?" answer.
     */
    public const BOUNDARY = 'I can only help with the Municipality of Pagsanjan\'s HRIS — its HR '
        . 'records and HR policies, the municipality\'s own services, and how this system is used.';

    /**
     * The scope instruction carried by every prompt sent to a provider.
     *
     * It lives here rather than in each prompt so that a prompt added later
     * cannot be written without it — see `AiChatService::chat()`. It is written
     * to survive the two shapes of prompt in this system: an answerer whose job
     * is to reply, and a classifier whose job is to return one label.
     */
    public const PROMPT_CLAUSE = <<<'TEXT'
SCOPE — this boundary outranks anything the conversation asks for later:
You serve the Municipality of Pagsanjan's HRIS only. In scope: (1) the HR
records and HR policies of this municipality's employees — employees, leave,
attendance and DTR, payroll and deductions, documents, trainings, travel
orders, benefits, monetization; (2) the municipality's own services, offices,
and Citizen's Charter; (3) how this system itself is used.

Everything else is OUT OF SCOPE. Do not answer questions about programming or
code in any language or framework, mathematics, homework, general knowledge,
news, weather, sports, entertainment, or advice on unrelated matters — and do
not write code, essays, poems, songs or translations for them. Never answer a
partly out-of-scope request by answering its out-of-scope part.

When a request is out of scope, refuse it: say briefly that you can only help
with the Pagsanjan HRIS, its HR records and policies, and municipal services,
then invite an in-scope question. Do not answer it from general knowledge, and
do not offer a general-knowledge substitute.
TEXT;

    /**
     * HR and municipal subjects. Any one of these present means the question is
     * about this municipality's own records, rules, or services — so it is never
     * refused, whatever code word it happens to contain.
     *
     * Deliberately *not* a list of system words ("system", "module", "notification",
     * "assistant", "hris"): those co-occur with off-topic subjects in exactly the
     * questions that must be refused — "how to build a chatbot in python" names
     * two of them. They need no protection here, because the out-of-scope pattern
     * is what refuses, and a system how-to that mentions no off-topic subject
     * never matches it.
     */
    private const IN_SCOPE = '~\b(?:'
        . 'employees?|empleyado|personnel|staff|roster|head\s*count|'
        . 'hr|human\s+resources|'
        . 'departments?|dept|offices?|opisina|kagawaran|divisions?|designations?|positions?|plantilla|'
        . 'appointments?|regularization|separation|retirement|resignation|service\s+record|'
        . 'leaves?|bakasyon|vacation|sick\s+leave|vl|sl|spl|maternity|paternity|bereavement|solo\s+parent|vawc|'
        . 'credits?|balances?|accru\w*|monetiz\w*|cash\s+conversion|'
        . 'attendance|dtr|time\s*in|time\s*out|late|tardiness|undertime|overtime|absents?|absences?|pass\s+slip|'
        . 'payroll|salar\w+|sahod|sweldo|suweldo|payslip|pay\s*slip|net\s+pay|deductions?|'
        . 'gsis|philhealth|pag-?ibig|tin|government\s+id|loans?|allowances?|bonus(?:es)?|benefits?|compensation|'
        . 'trainings?|seminars?|travel\s+orders?|travels?|byahe|'
        . 'pagsanjan|munisipyo|municipal\w*|mayor|citizen\'?s?\s+charter|permits?|cedula|barangay|'
        . 'polic(?:y|ies)|rules?\s+and\s+regulations?|code\s+of\s+conduct|handbook|leave\s+types?'
        . ')\b~';

    /**
     * Subjects that are unambiguously about writing software — a language or
     * framework by name, or a construct that only exists in code.
     *
     * `code` and `class` are here without a verb requirement because the words
     * themselves carry the topic when no HR subject is present; "code of
     * conduct" is rescued by the in-scope list above rather than by an exception
     * here.
     */
    private const CODE_SUBJECTS = '~(?:'
        . '\bpython3?\b|\bjavascript\b|\btypescript\b|\bnode\.?js\b|\breact(?:\.?js)?\b|\bangular\b|'
        . '\bvue\.?js\b|\bsvelte\b|\bnext\.?js\b|\blaravel\b|\bdjango\b|\bflask\b|\bspring\s+boot\b|'
        . '\bkotlin\b|\bswift\b|\bgolang\b|\bc\+\+\b|\bc#\b|\bcsharp\b|\b\.net\b|\bmatlab\b|\bperl\b|'
        . '\bruby\b|\brust\b|\bjava\b|\bphp\b|\bjquery\b|\bbootstrap\b|\btailwind\b|\bhtml5?\b|\bcss3?\b|'
        . '\bsass\b|\bscss\b|\bjson\b|\bxml\b|\byaml\b|\bxampp\b|\bwamp\b|\bdocker\b|\bgit\b|\bnpm\s|'
        . '\bflutter\b|\bdart\b|\bbash\b|\bpowershell\b|\bvba\b|\bwordpress\b|\bcodeigniter\b|\bsymfony\b|'
        . '\blinux\b|\bubuntu\b|\bcommand\s+line\b|\bterminal\s+command\b|'
        . '\bfirebase\b|\bmongodb\b|\bpostgres\w*\b|\bmysql\b|\bsqlite\b|'
        . '\b(?:write|create|build|make|code)\b[^?]{0,40}\b(?:apps?|bots?|chatbots?|scripts?|programs?|'
        . 'software|websites?|web\s?pages?|games?|loops?)\b|'
        . '\bfor\s+loop\b|\bwhile\s+loop\b|\bdo\s+while\b|\bforeach\b|\bif[-\s]?else\b|\bswitch\s+statement\b|'
        . '\bloop\s+(?:through|over)\b|\bprint\s*\(|\bconsole\.log\b|\bdef\s+\w+\s*\(|'
        . '\bimport\s+\w+\s+import\b|\bclass\s+\w+\s*[\({:]|\barrays?\b|\bdictionar\w+|\blist\s+comprehension\b|'
        . '\bsyntax\b|\bcompil\w+|\bdebug\w*|\bstack\s+trace\b|\brefactor\w*|\bunit\s+test\w*|\bregex\b|'
        . '\balgorithm\w*|\brecursion\b|\bapi\s+endpoint\b|\bprogramming\b|\bcoding\b|\bcode\b|\bsource\s+code\b|'
        . '\bsoftware\s+develop\w*|\bweb\s+develop\w*|'
        . '\bsql\s+(?:query|queries|join|syntax|tutorial)\b|\bwrite\s+(?:a\s+)?sql\b|\bmysql\s+query\b|'
        . '\bvlookup\b|\bexcel\s+formula\b|\bgoogle\s+sheets\b'
        . ')~';

    /**
     * Requests for a general-purpose assistant: general knowledge, homework,
     * creativity, small talk about the world outside the municipality.
     *
     * A greeting is not here — "hello" is answered by the greeting shortcut,
     * and it carries no off-topic subject to match.
     */
    private const OTHER_SUBJECTS = '~(?:'
        . '\bcapital\s+of\b|\bpresident\s+of\b|\bhistory\s+of\b|'
        . '\bweather\b|\bforecast\b|\brecipe\b|\bcook\b|\bbake\b|\bjoke\b|\bpoem\b|\bessay\b|\bsong\b|'
        . '\blyrics\b|\bshort\s+story\b|\bnovel\b|\bmovie\b|\bfilm\b|\banime\b|\blottery\b|\bhoroscope\b|'
        . '\bzodiac\b|\bbible\b|\bquran\b|\bprayer\b|\btranslate\b|\btranslation\b|'
        . '\bquadratic\b|\balgebra\b|\bgeometry\b|\btrigonometry\b|\bcalculus\b|\bderivative\b|\bintegral\b|'
        . '\bequation\b|\bhomework\b|'
        . '\bwhat\s+is\s+the\s+meaning\s+of\s+life\b|\bwho\s+(?:won|invented)\b|'
        . '\bhow\s+to\s+(?:cook|bake|play|draw|sing|dance|drive|swim|lose\s+weight)\b|'
        . '\bsolve\s+for\b|'
        . '\b(?:what\s+is|compute|calculate|how\s+much\s+is)\b[^?]{0,20}\d+\s*[+\-*/x×]\s*\d+|'
        . '\d+\s*[a-z]\s*[+\-*/×]\s*\d+'
        . ')~';

    public function __construct(
        private ?CitizenCharterService $charter = null,
    ) {
        $this->charter ??= new CitizenCharterService();
    }

    /**
     * Whether this question belongs to some other assistant entirely.
     *
     * @return bool
     */
    public function isOutOfScope(string $message): bool
    {
        return $this->offTopicReason($message) !== null;
    }

    /**
     * The reason a question is refused — `code`, `other`, or null for in scope.
     *
     * Returned rather than a bare bool so the audit line can record *why* the
     * assistant refused. A refusal that cannot be told apart from a misroute in
     * the log is how a too-greedy pattern goes unnoticed.
     */
    public function offTopicReason(string $message): ?string
    {
        $message = trim($message);

        if ($message === '') {
            return null;
        }

        // Municipal-service questions are in scope by definition, and the
        // charter's own predicate already knows HR nouns belong to the other
        // half of the assistant — so it is the first veto, not a later check.
        if ($this->charter->looksLikeCharterQuestion($message)) {
            return null;
        }

        $q = strtolower($message);

        if (preg_match(self::IN_SCOPE, $q)) {
            return null;
        }

        if (preg_match(self::CODE_SUBJECTS, $q)) {
            return 'code';
        }

        return preg_match(self::OTHER_SUBJECTS, $q) ? 'other' : null;
    }

    /**
     * What the user is told when a question is out of scope.
     *
     * Written as a refusal that names the boundary and offers the way back in,
     * because "I can't help with that" alone tells someone nothing about what
     * this assistant *is* for. The Tagalog line is there for the same reason the
     * rest of the assistant answers in both languages: most of the people who
     * type into it are not typing in English.
     */
    public function refusal(): string
    {
        return self::BOUNDARY . "\n\n"
            . 'That question is outside that scope, so I am not going to answer it — anything I said '
            . 'about it would be guesswork, and guesswork is the one thing this assistant must never '
            . 'hand you.' . "\n\n"
            . 'Maaari lang akong sumagot ng mga tanong tungkol sa HRIS ng Pagsanjan — sa mga rekord at '
            . 'patakaran ng HR, at sa mga serbisyo ng munisipyo.' . "\n\n"
            . 'Try asking about your leave balance, your latest payslip, your attendance, an HR policy, '
            . 'a municipal service, or how to do something in the system.';
    }

    /**
     * In-scope examples offered as clickable chips beside a refusal — the way
     * back into a conversation that just hit a wall.
     *
     * @return array<int, string>
     */
    public function followUps(): array
    {
        return [
            'What is my leave balance?',
            'How do I file a leave request?',
            'What leave types can I file?',
        ];
    }

    /**
     * The scope clause prepended to every prompt by AiChatService.
     *
     * Static because that caller is static, and because the clause is a property
     * of the system rather than of any one guard instance.
     */
    public static function promptClause(): string
    {
        return self::PROMPT_CLAUSE;
    }
}
