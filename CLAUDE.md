# PRIMEHRSYSTEM (Magdalena)

Human Resources Information System for the Municipality of Magdalena, with a
natural-language AI Assistant layered over the HR data.

- **Stack**: Laravel 13 · PHP 8.3+ · MySQL · Vite + Tailwind 4 · Blade
- **App root**: `primeHrMagdalenaLaravel/` (the repo root also holds a Flutter
  mobile client in `prime_magdalena_mobile_application/`)
- **Database**: MySQL (`primehrismagdalena`). Tests run on in-memory SQLite.

---

## Setup

```bash
cd primeHrMagdalenaLaravel
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan storage:link          # uploads are served from storage/app/public

composer dev                      # server + queue + logs + vite together
```

---

## Schema notes that bite

These are the column names that differ from what you would guess. Getting them
wrong is the single most common source of bugs in this codebase.

| You might expect | It is actually |
|---|---|
| `departments.department_name` | `departments.name` |
| `designations.designation_title` | `designations.title` |
| `documents.upload_date` / `.approval_status` | `documents.uploaded_at` / `.status` |
| `attendance.attendance_date` | `attendance.date` |
| `leave_applications.leave_date_from` / `_to` | `start_date` / `end_date` |
| `travel_orders.approval_status` | `travel_orders.status` |
| `$employmentDetail->department` | `$employmentDetail->departmentRelation()` |
| `$employmentDetail->designation` | `$employmentDetail->designationRelation()` |
| `$user->is_admin` | no such column — use `$user->hasRole('admin')` |

More things worth knowing:

- **Roles** are a JSON array on `users.roles`. The only valid values are
  `employee`, `hr`, `admin`, `mayor`. There is **no** `manager` role. A user can
  hold several at once.
- **`employees`, `employment_details`, `government_ids`, and `documents` have no
  `updated_at`** — their models set `public $timestamps = false`.
- **`leave_balances` rows are not rewritten each January.** A row is written
  when credits are next computed, so an employee's *current* figures routinely
  sit under an older `year` (employee 8's live VL/SL balances are stored under
  2023). "Current balance" is therefore the latest row per `leave_code`, never
  `where('year', now()->year)` — that filter returns nothing and reads as "you
  have no leave credits". `LeaveBalance::currentFor()` owns this rule; the
  employee leave pages and the AI Assistant both call it so they cannot report
  different credits for the same person. Note the employee **dashboard and
  profile still filter by current year** and so show 0 for such an employee —
  a pre-existing inconsistency, not yet reconciled.
- **`attendance.accredited_hours` is NULL on most rows.** It is only populated
  once payroll computes the day. Treating NULL as "worked zero hours" reports
  almost everyone as absent — use `attendance_type = 'ABSENT'` to count absences.
  The column also mixes units (values of both `8` and `480` appear), so summing
  it is meaningless.
- **ID expiry dates are not stored anywhere.** `government_ids` holds only
  numbers (GSIS, PhilHealth, Pag-IBIG, TIN, license); `legal_requirements` holds
  only `saln_submitted`, `oath_of_office`, `assumption_date`. "Expired IDs"
  cannot be answered without adding columns.
- **Employee photos** live on `employees.photo` as a public URL
  (`/storage/employees/photos/x.png`), not in `documents`.
- **`attendance.attendance_type` and `.remarks` were missing from the model's
  `$fillable`** while both `LeaveApplicationObserver` and `TravelOrderObserver`
  passed them to `Attendance::create()`, so mass assignment silently discarded
  them and every approved leave or travel day was stored as `REGULAR`. Fixed —
  but rows written before the fix are still wrong, and that is the column the
  dashboard, reports, and the AI Assistant all count leave and absence from.
  A backfill from `leave_applications` / `travel_orders` would be needed to
  correct history.

---

## Attendance capture (QR scanner → biometric)

The municipality wants a biometric reader but has not bought one. The QR
scanner is that device's stand-in, built so the swap is a driver change rather
than a rewrite.

```
QR badge scan  ─┐
                ├─→ AttendancePunchService::punch()
biometric ──────┘        └─ writes one slot on `attendance`
   (later)               └─ AttendanceComputationService re-accredits the day
                         └─ AccreditedHoursLog → DailySalaryComputation
                         └─ late/undertime leave deductions
```

Adding the reader means one new caller passing `source: 'biometric'`. Nothing
about schedules, grace, pass slips, or payroll moves.

- **`AttendanceComputationService`** holds `computeAccreditedHours()`,
  `computeTotalHours()`, and `creditPassSlipGapMinutes()`, which used to be
  private to `AttendanceController`. The controller's correction path now
  delegates to it, so a scanned 08:03 arrival earns exactly the grace a manually
  corrected one does. Change accreditation here, never in the controller.
- **`AttendanceQrService`** signs the badge: `PHRM1.{employeeId}.{hmac}`, keyed
  by a value derived from `APP_KEY`. The badge used to encode a bare employee id,
  which made it forgeable by anyone who could count — and attendance feeds
  payroll, so the badge is a payroll credential. Bare-numeric codes are rejected
  by name ("old unsigned card, reissue it") rather than accepted for
  compatibility. **Rotating `APP_KEY` invalidates every printed badge**; that is
  the intended revocation path.
- **`AttendancePunchService`** owns the punch rules:
  - a day already marked `LEAVE`/`TRAVEL_ORDER`/`HOLIDAY` is **refused**, not
    overwritten — the observers own those days and a punch would erase the
    approval's trace from the DTR;
  - a re-read of the same slot within 90 seconds is a **duplicate**, so a camera
    firing twice cannot rewrite an arrival time;
  - **leave deductions are withheld until the day is complete.** An unfinished
    day accredits as an eight-hour absence, so running deductions after a lone
    morning punch charges a full day of leave to someone still at their desk.
    `AccreditedHoursLog` and the salary figure still update on every punch so the
    DTR reads live.
- **`attendance_punches`** logs every punch as captured — slot, timestamp,
  source, device, operator, and the value it replaced. `attendance` only holds
  each slot's current value, which cannot answer what an auditor asks.

The kiosk lives at `/admin/attendance/scanner`, so `EnsureRoleForArea` already
restricts it to `admin`/`hr` — it is staffed, not self-service. **The operator
picks the slot**; `suggestSlot()` only pre-highlights one, because a badge
should not move the button between aiming and scanning. When the wall-mounted
reader arrives and there is no operator, that method becomes the authority.

A USB scanner gun works too: it types the payload and presses Enter, which the
manual-entry box accepts. Camera decoding uses html5-qrcode from a CDN, matching
how the QR *generator* is already loaded on the Personnel page.

A scan is a real attendance record, not a parallel log: it writes the same
`attendance` row the DTR, accredited hours, and payroll read. `AttendanceScannerTest`
follows one scan from the HTTP request through to `daily_salary_computations`
so that stays true.

```bash
php artisan test tests/Unit/AttendanceQrServiceTest.php \
  tests/Unit/AttendancePunchServiceTest.php tests/Feature/AttendanceScannerTest.php
```

Attendance tests build their tables from `Tests\Support\BuildsAttendanceSchema`
— a punch reaches a long way past `attendance`, and every table in that chain
has to exist for a single scan to complete.

---

## The AI Assistant

One natural-language interface over every HR module, for **every** role —
employees, HR, admin, and the mayor all talk to the same assistant. Users ask
questions; it classifies intent, routes to the service that owns that
capability, scopes results to what the asker may see, and narrates the answer.

Three audiences, one brain. What differs is which capability answers:

- **anyone** → `self_service` (their own records), `how_to` (policy, navigation),
  and `capabilities` ("what can you do?")
- **`admin`/`hr`/`mayor`** → additionally the org-wide capabilities: generated
  SQL, reports, charts, dashboards, cross-employee search

Two things follow from one role serving every audience:

- **Narration addresses the caller.** `AiAccessPolicy::audienceLabel()` decides
  whether a prompt says "an HR administrator" or "an employee viewing their own
  records" — `EmployeeSearchService` and `ReportGeneratorService` are reachable
  by both, so a hard-coded persona there writes to an employee as if they were
  staff reading someone else's file.
- **The capability list comes from the policy, not a prompt.**
  `describeCapabilities()` sits beside the scoping rules it describes, so
  "what can you do?" cannot advertise something the caller would then be
  refused. It costs no model call.

- **Scoping is disclosed, never silent.** Some capabilities *block* a
  non-org-wide caller (`dashboard`, `chart`, `workflow`, generated SQL); the
  rest *narrow* the query instead (`employee_search`, `document_search`,
  `report`). Narrowing silently produces false statements: an employee asking
  "show me everyone in the Mayor's Office" gets their own row and a narration
  reading "1 employee in the Mayor's Office", and an empty result reads as "that
  person has no files" when it means "not yours to see". So those three append
  `AiAccessPolicy::scopeNotice()` to every answer — including the empty and
  fallback branches — and pass `scopePromptNote()` into the narration prompt.
  The notice is appended in PHP rather than left to the prompt, because the
  disclosure has to hold when the model ignores its instructions.

### The assistant never states a rule it did not read

Its knowledge used to be a ~165-line string constant in `HrChatbotAnswerer`
(and a smaller one in `EmployeeChatbotService`) restating the grace period,
480-minute conversion, `÷ 22` LWOP formula, working hours, and the leave types.
Every line was a second copy of a rule that lives somewhere real, and the copy
had already drifted: it named **7 leave types where `leave_types_config` holds
20 active ones**, so Bereavement, Forced, Adoption, Study, Terminal, Wellness
and seven others were answered as though they did not exist. That constant is
also injected into the text-to-SQL prompt, so a stale rule produced a wrong
*query*, not merely a wrong sentence.

`HrPolicyFactsService` now assembles that block at runtime:

| Fact | Read from |
|---|---|
| leave types, limits, attachment + service requirements | `leave_types_config` |
| accrual rate per month | `leave_accrual_rates` |
| 480 min = 1 day, 8 h, half-day | `CscTimeConversionService` constants |
| grace minutes, default schedule | `AttendanceComputationService::GRACE_MINUTES` etc. |
| `÷ 22` daily rate | `DailySalaryComputation::WORKING_DAYS_PER_MONTH` |
| VL-then-SL order | `LateDeductionService::DEDUCTION_ORDER` |
| the caller's working hours | their own `schedules` row, not a stated standard |

Those constants were promoted from literals *specifically* so this service can
read them — a bare `+ 5` or `/ 22` beside a prompt saying "5 minutes" / "÷ 22"
is the drift this design removes. Only navigation ("Leave Management > File
Leave Application") stays hard-coded, because menu paths live in Blade views
and there is no table to read them from. **If a fact cannot be read from the
system, it does not belong in the prompt** — a missing config table makes the
assistant say so rather than fall back to a list.

Two consequences worth keeping:

- **Per-person arithmetic is never left to the model.** "How much leave did my
  late cost?" is answered by `EmployeeChatbotService::computedDeductionAnswer()`,
  which sums the employee's own `accredited_hours_log` minutes and converts them
  with `CscTimeConversionService`. The prompt used to carry a worked example and
  let the model apply it — arithmetic on a real person's pay, from a remembered
  rule.
- **Policy questions are their own intent.** `AiQueryService::wantsPolicy()` is
  checked after `how_to` but **before** the stored-file and `data_query` rules,
  because "can I file bereavement leave" matched the file-noun list (answered
  with a document search) and "what leave types are available" matched
  `data_query`'s noun list — which *refused* every employee for lack of
  org-wide access, for a question about policy that has no records in it.
  `wantsPolicy()` returns false for self-referential questions so "how much
  leave did **my** late cost" still reaches the calculator above.

`tests/Unit/HrPolicyFactsTest.php` pins the anti-drift property directly: edit
`annual_limit`, and the answer changes.

Answers may carry `follow_ups` — suggested next questions, rendered as clickable
chips. They are static prompt text with nothing to scope, they are not stored on
the turn (stale suggestions help nobody), and the UI keeps them on the newest
turn only.

Intent routing lives in one `match(true)` in `AiQueryService::detectIntent()` and
**its order is load-bearing** — `how_to` is checked before the stored-file rule
because "how do I *file* a leave" would otherwise match the file-noun list, and
`self_service` before `dashboard`/`data_query` so "how many VL credits do I
have" is not treated as an org-wide count. `tests/Unit/AiQueryRoutingTest.php`
pins these; add a case there before reordering anything.

### Request flow

```
question
   │
   ├─ ConversationMemoryService   resolve "generate a report" → "…of Juan's leave"
   ├─ AiQueryService              classify intent (patterns first, model as fallback)
   ├─ AiAccessPolicy              scope every query to the caller's permissions
   │                              self_service → own records; how_to → no query

   ├─ <capability service>        run the actual query
   ├─ AiChatService               narrate the rows in plain language
   └─ ai_audit log                who asked what, which capability, how many rows
```

### Services

| Service | Owns |
|---|---|
| `AiQueryService` | Orchestrator: intent detection, routing, audit logging |
| `AiAccessPolicy` | **The single source of truth for permissions.** All scoping goes through it |
| `AiConversationStore` | **Where a thread is kept.** Conversation lookup, prompt history, and storing/replaying a turn's attachments — shared by the full page and both chatheads |
| `HrPolicyFactsService` | **The single source of truth for HR rules the assistant states.** Reads them from the live config |
| `AiChatService` | LLM calls. Resolves provider per-user → org default → `.env` Groq |
| `EmployeeSearchService` | Employee / department / hire-date lookups |
| `DocumentSearchService` | Files across `documents`, training certificates, employee photos |
| `AiFileResolver` | Turns a `source:id` file reference back into bytes, permission-checked |
| `SemanticSearchService` | Meaning-based expansion (maternity → pregnancy, parental…) |
| `SafeSqlService` | Natural-language → validated read-only SQL |
| `EmployeeChatbotService` | **Self-service:** the caller's own leave, payslip, DTR, training, travel |
| `HrChatbotAnswerer` | HR policy + system how-to from a curated knowledge base; general fallback |
| `DashboardAssistantService` | Counts, absenteeism, pending approvals, headcount |
| `ReportGeneratorService` | 7 report types with columns and totals |
| `ChartDataService` | Chart specs (form + series + palette) |
| `ChartRenderer` | Chart spec → inline SVG (works in the browser *and* in PDFs) |
| `WorkflowAssistantService` | Approval summaries, payroll preview, onboarding, HR letters |
| `DocumentProcessingService` | Text extraction from PDF/DOCX/images into `document_extractions` |
| `OcrService` | Tesseract when installed; reports honestly when not |
| `ReportPdfService` | Report → PDF, via a user-bound expiring token |

### Entry points

```
POST   /{admin|employee|mayor}/ai-assistant/message      web UI
GET    /{admin|employee|mayor}/ai-assistant/export/{token}
GET    /ai-assistant/file/{source}/{ref}                 file/image results
POST   /api/ai/query                                     mobile (sanctum)
GET    /api/ai/export/{token}
GET    /api/ai/file/{source}/{ref}
```

Both surfaces call the same `AiQueryService`, so permissions and audit logging
cannot drift between them.

### The chatheads run the same brain as the full page

There are three chat surfaces and they must not diverge:

| Surface | Path |
|---|---|
| Full-page AI Assistant | `aiAssistant.js` → `AiAssistantController` → `AiQueryService` |
| Admin chathead (floating) | `adminChatbot.js` → `/chatbot/chat` → `ChatbotController` → `AiQueryService` |
| Employee chathead (floating) | `employeeChatbot.blade.php` → `/chatbot/chat` → same |

The employee chathead **used to answer from a hard-coded `if/else` that never
contacted the server** — it told every employee their vacation balance was 12.5
days, quoted a payslip for "Jun 16-30, 2025", and reported a 4.8/5.0 performance
rating from a table this schema does not have. It shipped on ten employee pages,
and none of the grounding work above reached it because it ran no server code at
all. It now `fetch`es `/chatbot/chat` like the admin one. **Neither widget may
ever answer from a local string** — an invented balance is worse than no answer,
so a failed request says the records could not be reached.

`ChatbotController` returns the whole answer (`files`, `charts`, `follow_ups`),
not just `response`: both widgets render file cards and follow-up chips, so the
same question cannot give a visibly poorer answer in the chathead than on the
page.

Suggestion chips must name questions the system can answer. "Performance" and
"HR contact" were offered while no performance table and no HR-contact record
exist; they are gone.

### One conversation store, three surfaces

`AiConversationStore` owns where a thread is kept and what survives being kept
there. Both `AiAssistantController` and `ChatbotController` go through it, so
the two surfaces cannot drift on what a saved answer keeps or on who may read
it back.

The chatheads used to keep their thread in the **Laravel session**, which meant
it did not survive `AuthController`'s `session()->invalidate()` on logout, the
120-minute `SESSION_LIFETIME`, or a different browser — the widget looked like
it remembered right up until it silently did not. Session storage also held
`role`/`content` only, so a replayed turn dropped the file cards and tables the
answer originally carried, and nothing asked in a widget was findable from the
full page's history or search. Chathead turns are now rows in
`ai_conversations` / `ai_messages` like everything else.

- **A chathead question continues the caller's newest conversation**
  (`continueLatestOrStart()`), so the widget and the full page are one thread.
  Note the full page still lands on its welcome screen and forks a new
  conversation on refresh — so what the chathead continues is whatever was last
  used, which after a page refresh is that refresh's new thread.
- **"Clear conversation" starts a new thread; it never deletes.** The thread is
  shared with the full page, so deleting would destroy history the user did not
  ask to lose. A session flag (`chatbot_start_new_thread`) makes the *next*
  question open a fresh conversation; losing that flag with the session is
  harmless. Both widgets' confirmation sheets say this — they used to promise a
  removal that no longer happens.
- **`history()` re-authorises on read** through `replayAttachments()`, the same
  as the full page: a turn saved under org-wide scope is withheld from an
  account that has since narrowed.
- **`history()` must `reorder()` before `orderByDesc('id')`.** The `messages()`
  relation sorts oldest-first, so an appended `latest()` becomes a *secondary*
  key that never takes effect — the limit then took the oldest turns and
  `reverse()` handed the model the start of the thread, backwards, as its
  "recent" context. That bug shipped in the full page before the extraction.
- The relation itself breaks `created_at` ties with `id`, because a question and
  its answer are written in the same second and would otherwise replay with the
  assistant speaking first.

`tests/Unit/AiConversationStoreTest.php` pins all of this.

### LLM configuration

There is **no separate API key for the assistant**. It uses `AiChatService`,
which resolves in this order:

1. the calling user's own provider + key (Settings → AI/Chatbot)
2. the org-wide default in `system_ai_settings`
3. `GROQ_API_KEY` from `.env`

Supported providers: `groq` (default), `openai`, `anthropic`. Every service
degrades to a deterministic fallback narration if no provider is configured —
the data still comes back, just without prose.

### Security

- **Read-only by construction.** `SafeSqlService` rejects anything that is not a
  single `SELECT`, blocks 20 keyword families, blocks MySQL executable comments
  (`/*! … */`, which MySQL *runs*), enforces a table allow-list that excludes
  `users`, `personal_access_tokens`, `sessions`, and `password_reset_tokens`, and
  caps every result at 200 rows.
- **Permission-aware retrieval.** `admin`/`hr`/`mayor` see the organisation;
  everyone else is scoped to their own employee record. A user with no linked
  employee row gets an impossible predicate (`1 = 0`), never an unfiltered query.
- **A refusal ends the request.** `AiQueryService::dataQuery()` returns a blocked
  result as-is. It must never fall through to another answerer: blocking is a
  permission decision, so re-routing it would make the denial itself the trigger
  for a second, unscoped attempt. Only a query that *failed* falls back.
- **The general fallback is gated too.** `HrChatbotAnswerer` is the assistant's
  catch-all, which makes its text-to-SQL a security boundary rather than a
  convenience. It runs SQL only for callers `canRunGeneratedSql()` allows, passes
  every statement through `SafeSqlService::validate()` + `enforceRowCap()`, and
  describes only `SafeSqlService::allowedTables()` to the model — so `users` and
  `personal_access_tokens` are never even named in the prompt. Callers without
  that permission still get the policy shortcuts and knowledge-base answer.
- **Assistant endpoints are throttled** at 20 requests/minute (both web areas,
  `/api/ai/query`, and both chatbot widgets). One question can spend several
  provider calls against a shared org key.
- **Audit trail** at `storage/logs/ai_audit-*.log`, 90-day retention. Records the
  asker, roles, scope, intent, row count, and duration — counts, never the rows
  themselves, so the log does not become a second copy of HR data.
- **Export tokens** are bound to the generating user and expire after an hour.
  Replayed history turns re-issue theirs under a stable per-message name so one
  turn holds one cache entry however often the thread is reopened.
- **Stored turns are re-authorised on read.** `ai_messages.attachments` keeps the
  table, rows, file cards, and chart specs of an answer, along with the
  `AiAccessPolicy` scope the asker held. `messages()` withholds the payload
  unless that scope still matches the reader's, so a user whose access narrows
  does not keep a readable copy of a wider answer in their history.
- **Files are never linked straight to `/storage`.** A file card in chat points
  at `AiFileController` and carries a *database reference* (`documents/41`,
  `government_ids/7-gsis_file_path`) rather than a path, so there is nothing to
  traverse. `AiFileResolver` re-reads the row and re-checks `AiAccessPolicy` on
  every fetch — a link copied out of the chat is useless to someone who may not
  see that employee. Served files carry `X-Content-Type-Options: nosniff`, and
  SVG is never served inline.

Run the security suite with:

```bash
php artisan test tests/Unit/SafeSqlValidationTest.php tests/Unit/AiAccessPolicyTest.php \
  tests/Unit/AiFileResolverTest.php tests/Unit/AiQueryRoutingTest.php
```

### Document content search

Uploaded files are only searchable by *content* once indexed:

```bash
php artisan ai:index-documents          # backfill
php artisan ai:index-documents --force  # re-extract everything
```

PDF and DOCX extraction runs locally. Scanned files need Tesseract
(`brew install tesseract poppler`); without it they are parked as
`ocr_required` rather than silently indexed as empty.

### Charts

`ChartDataService` picks the form from the data's job — bar for magnitude, line
for trend, stacked bar for part-to-whole, grouped bar for comparisons. Rules
held to deliberately: never a dual-axis chart, categorical hues assigned in
fixed order and never cycled (the tail folds into "Other" past 8), and every
chart ships beside its data table. A measure that cannot share the axis (a count
of days beside pesos) goes on the spec as `table_extra` — table-only columns —
rather than becoming a second axis.

**Chart requests are parsed bilingually**, like the rest of the assistant:
`detectSubject()` matches "sahod"/"suweldo" as well as "salary", and
`detectGranularity()` reads "kada linggo" as well as "weekly". Granularity is
checked finest-first, because "weekly sa buwan ng January–August" names months
for the *range*, not the bucket — check monthly first and the chart silently
comes back monthly. `tests/Unit/ChartRequestParsingTest.php` pins this.

Payroll charts read from one of two tables and they are not interchangeable:
`salary_computations` holds whole periods and a true `net_pay`, so it answers
organisation-wide monthly expense but can never be cut finer than a period;
`daily_salary_computations` holds one row per worked day, so it buckets to any
grain and filters to one employee — but its `daily_gross_pay` is
basic + OT − late − undertime, i.e. pay **earned before** GSIS/PhilHealth/
Pag-IBIG and loans. It is labelled "Pay earned", never "net pay". The palette is validated for colour-vision
deficiency; the accompanying table is also what satisfies the contrast relief
requirement for the lighter hues. Slot 0 — the single-series hue — is the admin
dashboard's own brand colour, so a one-series chart from the assistant matches
the cards on that page. That colour now **follows the active theme**
(`SystemTheme::series()`), so slot 0 is emerald under the Emerald palette. The
categorical tail stays fixed: those hues exist to tell one department from
another, and re-tinting them toward the theme is how a legend stops being
readable.

Tables and charts the assistant generates wear the **admin dashboard's card
design**, not a chat-only look: `.table-section` / `.chart-card` shell, a
`.table-header` title block, `.payroll-table`'s gold-underlined micro-caps
header with zebra rows, and a `.table-footer` strip. `ChartRenderer` matches the
dashboard's Chart.js settings too — Poppins, 11px ticks in `--gp-text-soft`,
gridlines in `--gp-bg-tint`, circular legend swatches, and 2.5px trend lines at
tension 0.4 over a 10% area fill. Keep the two in step when either moves.

Two constraints on that fill: it is a flat `fill-opacity`, never a gradient
(dompdf's SVG library paints `url(#…)` fills solid **black**), and the chart
title rides on the SVG as `data-chart-title` so a replayed turn — re-rendered
from its stored spec, shipping the markup alone — still gets a card header.

---

## Testing

```bash
php artisan test
```

Note: 4 tests in `tests/Feature/ChatbotControllerTest.php` fail with 403 and did
so before the AI Assistant work — they are unrelated to it. `ExampleTest` fails
for the same pre-existing reason (`system_ai_settings` is missing on the test
connection).

**`RefreshDatabase` does not work in this project.** The migration
`2026_04_15_182306_add_timestamps_to_tables` emits MySQL-only
`CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, which SQLite rejects, so the
migration set cannot run on the test connection at all. Tests that need tables
build the few they touch with `Schema::create` in `setUp()` — see
`tests/Unit/AiFileResolverTest.php`.

---

## The public welcome page is editable

`welcome.blade.php` is the only page an unauthenticated visitor can read, and
every word of it used to be a literal in that file — the announcements, the
service catalogue, the vision and mission, the phone numbers. Changing one
advisory meant editing Blade and redeploying, so nobody did: the shipped page
carried June 2025 announcements, a hard-coded `© 2025`, and hero figures of
"17 Offices" and "348 Personnel" against a database holding 26 and 12.

`SiteContentService` now owns that copy, edited at **Admin → Website Content**
(`/admin/website`). Three rules hold:

- **Defaults are the old page, verbatim.** A section that has never been saved
  renders exactly what the hard-coded version did, so installing this changed
  nothing until somebody edited something.
- **A saved section is merged over its defaults, never swapped for them** — but
  *lists are replaced wholesale*. Adding a field must not blank it out on an
  install that saved the section earlier; deleting the fourth announcement must
  not let the default's fourth reappear underneath.
- **Counts are read, not typed.** The hero's departments and personnel figures
  come from the tables that own them, the same reasoning as
  `HrPolicyFactsService`. A figure whose table cannot be read is *omitted*, not
  shown as `0` — "0 Government Personnel" on the municipality's homepage is
  worse than one fewer statistic.

### The rail is split by *why* something changes

`SECTION_GROUPS` is the source of truth for the editor's navigation, and
`sections()` derives the flat allow-list from it — a section cannot be added to
one and forgotten in the other. Two groups:

- **Everyday updates** — it changed because the world changed. Announcements,
  contact details, hero text, about, the gov bar. Nothing to learn.
- **Page setup** — it changed because the site is being redesigned. Logo,
  services, the HRIS call-to-action, footer, chatbot. These carry rules: icons
  from a fixed list, links matching a pattern, files with size limits.

Announcements leads the whole rail and opens by default. It is a repeatable
list, which by the rule above would read as setup — but posting an advisory is
the reason this editor exists, and it is the only content that goes stale on
its own.

Where an *everyday* section holds something structural, that part folds into a
`<details class="wc-advanced">`: the hero's compliance tags and About's profile
facts. About's **paragraphs stay in the open** — rewording prose is an everyday
job. A section already under Page setup gets no disclosure; the whole section
is the advanced case.

`<details>` rather than a JS toggle because **collapsing must not drop the
fields on save** — a closed block still submits everything inside it, which is
the one way this could have silently broken saving. `WebsiteContentTest` guards
the grouping (no section listed twice, none missing a partial or defaults,
announcements first); the submit-while-collapsed property was verified in a
browser.

**The editor never accepts markup.** Every field is a length-capped plain
string; icons and announcement tags must be members of a fixed vocabulary
(`ICONS`, `CHIP_ICONS`, `ANNOUNCEMENT_TAGS`); links match `#anchor` or
`https?://` and nothing else, which is what keeps `javascript:` and `data:`
URIs out. Names become SVG only inside the `x-public-icon` component. There is
deliberately no rich-text field: a public page rendering admin-supplied HTML is
a stored-XSS surface aimed at every visitor to the municipality's website.
`WebsiteContentController` re-checks `hasRole('admin')` on every endpoint —
the hidden sidebar row is tidiness, not the permission.

Note `x-public-icon` is a **component**, not an `@include`: variables defined
inside an include do not escape back to the including view, and the first
version of this 500'd the homepage for exactly that reason.

### The municipal seal is uploadable

`SiteContentService::logoUrl()` is the only way any view should reference the
logo. It was `/municipal-of-pagsanjan-logo.jpg` in **18 places** — the welcome
page, all three sidebars, the three sign-in screens, both payslip modals — plus
two `public_path()` reads in the PDF services. Replacing it meant replacing a
file on the server.

- **The upload lives under its own key (`LOGO_KEY`), not inside `brand`.**
  `put()` replaces a section wholesale — that is what lets an admin delete the
  last row of a list — so a logo stored in `brand` would be dropped every time
  somebody saved the brand *text* form, which has no file input to resubmit it.
- **The filename is generated, never taken from the upload** (`logo-<random>.<ext>`
  on the public disk). `image` + an extension allow-list + a dimension check
  mean the file has to decode as one of four raster formats. **SVG is refused**
  — it can carry script, and this renders on a page anonymous visitors read.
- **`logoPath()` re-checks the disk on every read.** If the file is gone, callers
  fall back to the shipped logo rather than emit a broken image on the homepage.
- **`logoDataUri()` derives the MIME type.** `LeaveFormDataService` and
  `PassSlipFormDataService` embed the bytes for dompdf and both hard-coded
  `image/jpeg`, which would have broken the printed forms the first time anyone
  uploaded a PNG.
- Replacing the logo deletes the file it replaced; "Use the original" deletes
  the upload and drops back to `DEFAULT_LOGO`.

`tests/Feature/WebsiteContentTest.php` pins the authorisation, the vocabularies,
the URL scheme filter, and that markup saved into a field comes back escaped.

---

## Notifications

Every approval workflow announces itself through **one** service. There is no
second notification system: Laravel's `notifications` table here is the app's
own (`App\Models\Notification`), not the framework's `DatabaseNotification`
shape, and `App\Notifications\*` is used only for the two *emails* (credentials,
password reset), which are a different channel with a different audience.

```
action succeeds  →  NotificationService::<event>()  →  deliver()  →  notifications row
                                                                          │
                          bell polls /api/notifications/feed  ←───────────┘
                                     │
                    click → /notifications/{id}/open → mark read → authorised redirect
```

### Three bells, one table

`notifications.audience` decides which bell a row appears in: `admin` (work
queued for HR to act on), `employee` (your own records), `mayor` (oversight of
what was decided), `system` (broadcast — shows in all three). One person can
hold several roles, so this is what stops an HR officer's own leave approval
landing in the queue they work from, and vice versa.

- **The mayor had no bell at all.** The panel existed as two pasted copies —
  an admin one and an employee one — and the third area was simply left out,
  so nothing in the system could tell the mayor anything. All three are now
  `@include('partials.notificationPanel', ['audience' => …])`; the per-area
  files are three lines each, kept only because ~30 pages include them by name.
- **The mayor is told what was *decided*, never what is queued.** The mayor's
  area is read-only, so a pending item in that list would imply an action those
  screens do not offer. Leave, travel order and pass slip decisions carry a
  `mayor` copy alongside the employee's; monetization does not, because there is
  no mayor monetization page to link to and **a notification must never point
  somewhere its recipient cannot go**.

### Four properties every notification has

They hold because every writer goes through `NotificationService::deliver()`.

- **A notification can never break what it announces.** `deliver()` catches
  everything and logs it. It is also why the call sites moved: a `deliver()`
  *inside* a `DB::transaction` is still inside it, and `LeaveController` was
  writing the bell before `DB::commit()` — so an employee could be told their
  leave could not be filed because the system could not tell HR about it. Every
  call now sits after the commit. `payrollGenerated()` already did.
- **It is idempotent.** Every writer passes a `dedupe_key` naming the event
  (`leave:41:approved`), unique per *recipient* — not globally, because one
  decision legitimately reaches the filer and every companion under the same
  key. A double-clicked Approve leaves one row.
- **Recipients are resolved in one place.** `approvers()` and `overseers()` are
  the only definitions of who handles and who watches. Both drop inactive
  accounts (they cannot sign in, so the row would never be read) and **the
  actor** — an HR officer filing on an employee's behalf does not need "New
  Leave Request" for the request they just typed.
- **It says which record, in the words the screens use.** `statusLabel()` maps
  the stored `rejected` to "Disapproved", because that is what every tab, badge
  and printed form in this system calls it — telling an employee their request
  was "Rejected" sends them looking for a word that is on none of their pages.
  `dateRange()` and `reasonClause()` put the period and the refusal reason in
  the sentence; "your leave request" cannot be told apart from the other two
  the employee has open.

### A click is a server decision

`GET /notifications/{id}/open` marks the row read and *then* chooses the
destination. The panel used to mark it read by `fetch` and jump to a URL read
out of a DOM attribute. Now the link is re-read from the row and refused if it
is off-site (an open redirect waiting for a writer) or in an area this account
may not enter — otherwise a stored link would be a door around
`EnsureRoleForArea`, and the row would already be read at the 403. A
notification with no link redirects *back*, not to a dashboard nobody asked for.

Cards are real `<a>` elements for the same reason, which also makes them
keyboard-reachable and middle-clickable as the onclick div never was.

**Monetization links carry `tab` but never `highlight`.** Both leave pages read
`highlight` as a *leave application* id — the admin handler clicks
`[data-leave-app-id="…"]` — so a monetization id there opens whichever leave
application shares the number.

### Everything is scoped to the caller's own rows

Every query in `NotificationController` starts at
`where('user_id', Auth::id())`, and the `audience` a client sends can only
narrow that further. `audience()` re-checks it against the caller's real roles,
so an account whose access was later narrowed cannot name the admin bell and
read admin-audience rows it still owns. An id belonging to someone else is a
**404**, not a 403 — "no such notification" is the truthful answer from where
that caller stands.

`markAllAsRead()` is the one place an unrecognised audience widens rather than
narrows (it clears everything): narrowing a *read* shows somebody less, which is
safe, but silently narrowing a *write* leaves a badge stuck at a count nothing
can reset.

### History, and what is kept

There are two doors to it and they are deliberately different things: the bell
is a floating button pinned top-right that opens a dropdown of the newest few,
and **`partials/navNotificationRow.blade.php` is the sidebar row** that opens
the full page. One partial for all three rails, sitting *ungrouped* beside
Dashboard and AI Assistant in each — the admin rail collapses its groups, and an
unread badge inside a shut section is a badge nobody sees. Same position in
every rail, so somebody holding two roles does not have to find it twice.

Its badge and the bell's both come from `Notification::unreadCountFor()`, which
memoises per request: the two sit on one page, and a rail saying 3 beside a bell
saying 5 is worse than neither showing one. They cap at the same 99+ for the
same reason. On a collapsed rail the numeric badge is swapped for a dot over the
icon — the label it normally sits beside is `display:none` there, and a count
floating in the empty half of the row reads as belonging to nothing.

"View all notifications" is a real page per area — `/{admin,employee,mayor}/notifications`
— registered in a loop so the role gate on the prefix decides access, the same
shape as the AI assistant's routes. It paginates (a year of approvals is not a
page), filters by read state and category, and offers per-row mark-read /
mark-unread / delete. Category chips are built from the categories the reader
actually has: a chip that always returns nothing reads as a bug.

`notifications:prune` (scheduled weekly) deletes **read** notifications older
than `config('notifications.retention.read_days')`, default 180. Unread ones are
never pruned at any age — an unread notification is work nobody has looked at.
This is only safe because the notification is not the record: the leave
application, the travel order and the audit log all outlive it.

### Presentation

`Notification::CATEGORIES` owns the label, icon and badge gradient for each
category, read by the card partial — one definition for the bell, the polled
feed and the history page. Those gradients are the only fixed colours in the
feature; everything else is a theme variable. Same rule as the charts: a
category hue exists to tell leave from travel at a glance, and re-tinting it
toward the palette is how that stops working.

Unread is marked three ways — a left rule, a filled ground and a dot — so the
distinction survives a monochrome screen and colour-vision deficiency, which a
background tint alone does not.

### Adding a notification to a new module

1. Add a method to `NotificationService` that calls `deliver()` or
   `deliverMany(approvers(…) | overseers(…), …)`.
2. Give it a `dedupe_key` naming the event, and a `link` to a page the audience
   can open.
3. Call it **after** the commit, never inside the transaction.
4. If the category is new, add it to `Notification::CATEGORIES` — the card reads
   the label and icon straight out of it, and `NotificationServiceTest` fails on
   a category missing either.

```bash
php artisan test tests/Unit/NotificationServiceTest.php \
  tests/Feature/NotificationAccessTest.php
```

---

## The Leave & Travel Calendar

Two surfaces over the same idea, and they are deliberately one calendar:

| Surface | Question it answers | Route · view |
|---|---|---|
| Admin / mayor | *Who is out?* | `/{admin,mayor}/leave-calendar` → `partials/leaveCalendar/calendar.blade.php` (`.lc-*`) |
| Employee | *When am I out?* | `/employee/leave-calendar` → `employee/leaveCalendar/leaveCalendar.blade.php` (`.ec-*`) |

Both render full-page and inside a floating-button modal (`?embed=1` →
`layouts/calendarEmbed`), and both offer Month / Week / Day. Scoping is the
controllers': `EmployeeLeaveCalendarController` never queries anything but
`Auth::user()->employee`; `AdminLeaveCalendarController` is the org-wide one and
`MayorLeaveCalendarController` inherits it.

### One colour and shape vocabulary, in one file

`resources/css/shared/leaveCalendarTokens.css` holds it, and **both**
stylesheets `@import` it. It is the same green/amber/blue as the busy-date
pickers (`busyDatesCalendar.js`), so a marker means the same thing wherever it
is drawn — which matters because one person can hold both roles and see both
calendars.

Those three hues are **fixed, not derived from the active theme** — same rule
as the chart categorical hues and the notification category gradients: they
exist to tell approved leave from pending from travel, and re-tinting them
toward the palette is how that stops working. Everything that is *not* an
identity hue — surfaces, rules, ink, the today ring — is a theme variable, so
the calendar still follows Settings → Appearance.

**Colour is never the only cue.** Each record also carries a shape, so the grid
survives a monochrome screen and colour-vision deficiency:

| | leave | travel | pending |
|---|---|---|---|
| admin marker | circle | squircle | dashed ring |
| employee pill | round dot | square dot | dashed outline |

The legend draws those marks *as the grid draws them* rather than three plain
circles, so the key can be matched to a cell without reading the words. Its
last item names the dash itself, because that cue marks a pending travel order
as well as a pending leave and there is no fourth colour to look for.

### The whole month, at one glance, with nothing cut off

The goal is one sentence: open the calendar and see the first of the month to
the last without scrolling **inside** it, while a date holding more records
takes the height it needs. Four rules carry that, and each is load-bearing:

- **Rows size to their content.** Both grids run `grid-auto-rows:
  minmax(var(--cal-row-min), auto)`, so a week row is at least the floor, at
  least as tall as the busiest date in it, and free to grow past both. The
  embed used `1fr`, which divides the height into equal rows whatever is in
  them — the row holding a busy Tuesday got exactly the empty week's height and
  its markers went under the cell's edge. **`--cal-row-min` is declared on the
  grid (`.lc-days` / `.ec-days`), never on the cell**: `grid-auto-rows` resolves
  against the container, so a cell-level override silently moves only
  `min-height` and leaves the tracks at the default.
- **Cells do not clip.** `overflow: hidden` is gone from `.lc-day` / `.ec-day`.
  The admin's marker row wraps onto a second line, the employee's pill labels
  wrap instead of ellipsing, and the cell grows to hold them. A face sliced in
  half by a cell border was never a smaller answer, only a wrong one.
- **How many records a cell shows is measured, not typed.**
  `resources/js/shared/calendarFit.js` owns it, for both surfaces and both
  contexts. The cap it replaces was `nth-child(n+4)` / `nth-child(n+3)` in CSS:
  a fixed three and two that knew nothing about the window, so they hid a fourth
  person on a screen with room for eight and still overflowed a short one. Each
  week row now gets the largest count the month as a whole can still pay for, so
  a date with two records shows both in a month where another date has fourteen.
- **The budget is the modal's height, or the page's fold.** Inside
  `.lc-embed-wrap` the grid is `flex: 1`, so the height it is handed *is* the
  budget. On the full page nothing bounds it, so the budget is what is left of
  the viewport below the grid's own top — because "the whole month at one
  glance" is not satisfied by a calendar you scroll the *page* to see the end
  of. Measured from the document, so it does not change with scroll position.
- **The floor adapts, then the cell itself.** Six times `--cal-row-min` is
  taller than the modal's grid on an ordinary laptop, so the month overflowed
  before a single record had been counted — capping records cannot fix a floor
  that overflows. The pass lowers the floor (never raises it), and if one record
  a date still will not fit it squeezes the cell a step at a time: `compact`
  (smaller date badge and marker), then `dense` — which is the type-mark
  treatment the ≤768px (admin) and ≤560px (employee) rules already use, applied
  by the height a row can have rather than only by the width it has. A cell out
  of height has the same problem as one out of width, so it gets the same
  answer. Each step is re-measured, never adjusted by a guess: those sizes live
  in CSS beside the rest of the calendar.

**An empty month is the case to test first.** October has no leave and no travel
in it, and it was the one month that still scrolled after all of the above — the
pass returned early when a month held no records, so the adaptive floor never
ran and five rows stayed at their full 118px. A month with nothing in it still
has five or six week rows to fit, and they are what overflows. The early return
is now on `rows`, not on records.

Two things follow that are easy to get wrong:

- **Hidden records stay in the DOM** (`hidden`, not removed). The admin tooltip,
  the `.lc-day-count` badge and the employee's day popover are all built from
  them, so "+3" has to open a list that really does hold three more.
- **The "+N" chip ships `hidden` with no number.** How many are hidden is not
  knowable server-side — it depends on the window the reader opened the calendar
  in — so Blade renders the chip and the fit pass fills it in. With JavaScript
  off it stays hidden and every record shows, which is why the CSS default has
  to be "show everything" rather than a cap.
- **The chip is costed by where it actually sits.** The admin's rides at the end
  of the marker row and takes its own line only when it no longer fits beside
  them; the employee's sits under the stack, except at `dense`, where it moves
  into the date row's spare corner and costs the cell nothing. `measure()` reads
  its `position` rather than assuming, so CSS stays the one place that decides.
  Costing it as free is what let a cell claim a height the chip then sat 6px
  below.

`minmax(floor, auto)` is not a guarantee on its own, and this is the subtlety
the fit pass exists for: a grid track only grows to its content **while there is
free space to grow into**. Give the grid less height than the sum of its rows'
contents and every track stays at the floor and the content spills out of the
cell instead. That is why "does the sum of the rows fit the grid" is the exact
question the pass asks before letting a cell show one more record — and why,
when not even one record a date fits, it stops pinning the grid to the modal's
height (`.is-cal-overflowing` → `flex: 0 0 auto`) so the rows can size
themselves and `.lc-embed-wrap` scrolls instead of cropping. That scroll is a
fallback for a window that has run out of height, never the mechanism.

Week view has no chip and no cap: it has the height to list everyone, so
truncating there would hide rows that already fit. Its columns carry a 220px
floor rather than a fixed 320px height — a week with one record should not print
200px of empty card under it.

The admin month markers also **no longer overlap**. A −14px pile is how a face,
a ring colour and a dashed outline all end up half-covered by the next avatar;
they sit in a wrapping row of whole marks now. Pending markers are dashed rather
than faded — the old `opacity: .62` dimmed the very state a reader is looking
for.

### Responsive: what each width gives up

Widest-first, and each step surrenders what the width can no longer pay for.
The load-bearing step is the last one:

- **≤1180** stat strip folds to two columns; avatars 26px.
- **≤900** week view becomes one tall column per day; avatars 24px; the
  employee's month pills drop their dot, because the pill's own tint and dashed
  outline already say type and status and those 13px are two more letters of
  the leave's name.
- **≤768** the month marker stops being a face and becomes a **type mark** — a
  green circle, an amber dashed circle, a blue square. A 26px avatar is
  unreadable in an 80px cell and a row of them does not fit. The count badge
  still states the real total and the date still opens the day view, which is
  where the names are. The "+N" chip shrinks with the marks rather than being
  switched off: the fit pass *measures* that element, so one set to
  `display: none` is a truncation it cannot cost.
- **≤720 / ≤560** cell padding, type and the stat strip compact; the employee's
  "+N more" drops the word and keeps the number; the admin's count badge moves
  to the cell corner, where it takes no width from the date.

Two things at ≤768 are corrections to the shared glass system rather than
choices. `.glass-shell .filter-card-fields` stacks into a column there, which is
right for a bar of full-width selects and wrong for the month navigator — four
small controls and a segmented switch, stacked into a 300px tower above a
calendar with no room left; only `.lc-nav` / `.ec-nav` opt out. And the control
card is laid out as **plain blocks** there: as nested wrapping flex containers
it measured ~90px taller than the sum of its parts, and that slack printed as an
empty band under the Filter button.

### Checking it

There is no PHPUnit case for the calendar; it is markup, CSS and one measuring
pass, none of which PHPUnit can see. It was verified in a real browser
(Playwright + Chromium) against the built stylesheets, driving both surfaces at
full page and in the modal across desktop, tablet and phone widths, and
asserting three properties directly: the calendar element never scrolls, no
record's box escapes its own cell, and a busy row is measurably taller than a
quiet one. Two bugs only that harness could have found — the "+N" chip wrapping
onto an uncosted second line, and an empty week row being costed at zero instead
of a full track — are why it is worth rebuilding rather than reasoning about.

The sweep that has to stay green is eight month shapes — 4/5/6-week, empty and
loaded, up to a pathological 25 records on one date — across both surfaces, full
page and modal, at 1440x900, 1366x768, tablet and phone: 80 scenarios. Three
bugs only that harness could have found are why it is worth rebuilding rather
than reasoning about:

- the "+N" chip wrapping onto a line nothing had costed;
- an empty week row costed at zero instead of a full track;
- and the empty-month early return above, which no amount of reading the
  capping logic would have shown, because the capping logic was correct.

When changing a marker size, a breakpoint or anything the pass measures,
re-check the five that interact: a date with more records than fit, a long leave
name, a six-week month in the modal on a 768px-tall window, a month with no
records at all, and a 390px screen.

---

## Theming

The whole UI's colour comes from one seed colour, generated by
`SystemTheme::derive()` and injected as a `:root` block by each layout via
`SystemTheme::activeCss()`.

**Precedence is personal → global → application default.** Every signed-in
user can pick their own palette under Settings → Appearance; administrators
additionally set the organisation default in the same tab. A row in
`user_theme_settings` (one per user) *is* "I have a personal theme" — there
is no enabled flag, "Reset to system theme" is a delete, and an admin
changing the global palette can therefore never overwrite someone's own
choice. `tests/Unit/ThemePrecedenceTest.php` pins that rule.

`AppearanceController` owns all five endpoints. It accepts only a palette
key from `SystemTheme::PALETTES` plus four six-digit hexes — never CSS —
and the global endpoints re-check `hasRole('admin')` server-side rather than
relying on the section being hidden. `SystemTheme::flushCache()` clears the
per-request memo; call it after any write that changes the active palette.

**Never write a colour literal in CSS or in a JS template again.** Use a
variable — `var(--gp-pri)`, `var(--theme-neutral-300)`, `var(--theme-danger)`,
`rgba(var(--theme-shadow-rgb), .08)`. A literal is a colour that will not
follow the theme, which is what left ~1,200 navy-lavender values scattered
across 35 stylesheets while only the sidebar recoloured.

- **The generator guarantees legibility, not fidelity.** A seed is a
  direction, not a fill: lightness is clamped and then contrast-walked until
  white text clears WCAG AA, so `#ffe600` becomes a deep gold. Foregrounds
  come from `readableOn()` — never assume white. `tests/Unit/SystemThemeTest.php`
  pins this against seven hostile seeds; add a case there before relaxing it.
- **`glassSystem.css` `:root` holds the default-navy fallbacks**, generated
  from `resolve('default')`. An unresolved custom property is *no colour at
  all*, not the old literal, so any layout that skips the theme block relies
  on that block. Regenerate it rather than hand-editing.
- **Three places cannot use `var()`** and must go through
  `resources/js/shared/themeColors.js`: Chart.js options, canvas 2D
  (`ctx.fillStyle`, the QR renderer), and documents written with
  `window.open()` — a popup never inherits the parent's theme block.
- **Semantic hues are fixed** (`SEMANTIC_HUES`); only their lightness and
  tint partners are derived. A green that drifts toward the theme stops
  reading as "approved". Same rule for categorical chart hues.

## Conventions

- Controllers stay thin; business logic lives in `app/Services/`.
- Permission decisions belong in `AiAccessPolicy`, never inline in a service.
- Prefer Eloquent relations over raw joins, but note the relation names above.
- When adding a table the assistant should read, add it to
  `SafeSqlService::ALLOWED_TABLES` — omission is a deliberate deny.
