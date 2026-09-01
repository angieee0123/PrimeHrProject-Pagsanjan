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

## Wizard uploads — Gov IDs (Step 5) and the 201 file (Step 6)

The employee registration wizard's Step 6 collects the twelve supporting
documents of a 201 file. It shipped as `image|mimes:jpg,jpeg,png`.

**A 201 file is a folder of forms, not a folder of photographs.** The CSC
publishes CS Form 212 (the PDS) and CS Form 33 as *Excel workbooks*, offices
keep position descriptions and clearances as Word or PDF, and an IPCR exported
from another system comes out as CSV — so the image-only rule rejected the
authoritative copy of nearly every document it asked for, and left a phone
photograph of a printout as the only accepted form of the PDS. Accepted now:
PDF, DOC, DOCX, XLS, XLSX, CSV, JPG, PNG.

`EmployeeSupportingDocument` owns the vocabulary as well as the rows — the
twelve documents, their labels and groups, the accepted extensions, the size
ceiling, the validation rules and the error-message names. Step 6's markup,
`EmployeeRegistrationController::store()` and the personnel update route all
read it, so **the file picker cannot offer a format validation then refuses**.
That mismatch is invisible until an admin has filled in six other steps.

- **`extensions:`, not `mimes:`.** `mimes:csv` resolves to `text/csv` alone and
  most CSVs are detected as `text/plain`, so a form listing CSV as accepted
  refused them. `extensions:` is the gate (it also blocks PHP uploads on its
  own); a generous `mimetypes:` sits behind it, because a list that is too
  narrow rejects real files — the failure being fixed. SVG stays refused: it
  can carry script.
- **The stated ceiling is read from PHP, not typed.** `upload_max_filesize` is
  enforced before Laravel is reached, so a `max:` rule above it never fires and
  the admin is told the upload failed with no way to tell an over-size file
  from a broken one. `maxKb()` is `min(MAX_KB, upload_max_filesize)` and the
  screen states *that*. Same rule as `HrPolicyFactsService` and the welcome
  page's counts.

  **The Apache SAPI serving this app is currently at `upload_max_filesize = 2M`,
  `post_max_size = 8M`** (`/etc/php/*/apache2/php.ini`), so Step 6 honestly
  advertises 2 MB. A scanned multi-page PDS does not fit in 2 MB; raising the
  ini raises the stated limit with no code change.
- **The wizard refuses a submission `post_max_size` would discard.** Over that
  limit PHP throws away the entire body — the CSRF token with it — and Laravel
  answers *419 Page Expired*: seven steps of typing gone, with nothing on
  screen saying why. `wizardDocumentsPayloadError()` sums every file input in
  the form (the photo and the five ID scans count too) and both submit paths
  check it. They have to be checked at the *buttons*: `form.submit()` does not
  fire the form's submit event.
- **Upload filenames are `<time>_<random>_<slug>.<ext>`.** The random segment
  is not decoration — `time()` is identical across the twelve documents in one
  request, so an office MFP scanning every form to `scan.pdf` had the second
  upload overwrite the first, leaving two columns pointing at one file. The
  slug matters more now the uploads are forms: "CS Form 212 (Revised 2017) -
  Dela Cruz #2.xlsx" went verbatim into a `/storage/...` URL, where the `#`
  truncates the link. `EmployeeRegistrationController::handleFileUpload()` is
  public and static so the update route stores photos, ID scans and documents
  by the same rule.

### Step 5 keeps a narrower list on purpose

`GovernmentId` owns the same shape — the five IDs, their labels, extensions,
rules, `accept()` — but accepts **PDF/JPG/PNG only**. Those are ID *scans* that
feed OCR, not forms: a workbook uploaded as a GSIS card produces a file the
auto-fill can never read a number out of. The two steps share a card UI, which
is exactly why each keeps its own list — `GovernmentIdScanFormatsTest::an_id_scan_is_not_a_spreadsheet`
pins that a format valid on Step 6 is refused on Step 5.

**The edit path validated neither the ID scans nor the photo.** Registration
checked them and `admin.personnel.update` did not, so anything a picker could
be talked into offering was written to the public disk on an edit. Both now run
the same rules as registration.

### Both steps are attachment cards

One module (`employeeWizardDocuments.js`) drives every card on both steps, over
any container marked `data-attachment-cards`; the accepted extensions and the
ceiling are read off that container, never restated in JS. Each card reports
what is attached, its size, a format badge (image picks preview as a thumbnail),
and a Remove button — a bare file input can be replaced but never emptied. A
wrong format or an over-size file is caught on pick, not after the whole wizard
is submitted.

**The OCR read-back lives in that module too, not beside the rest of the
wizard.** Wired separately its `change` listener ran *first*, so a file the card
was about to reject had already been uploaded to `/government-ids/extract` and
came back reporting an OCR failure for what was really a wrong format. It now
runs only on a file the card accepted.

Step 6's twelve cards additionally carry the migration's own three groups —
twelve undifferentiated file inputs is a list nobody reads to the end of.

`tests/Unit/SupportingDocumentFormatsTest.php` and
`tests/Unit/GovernmentIdScanFormatsTest.php` pin the picker/rule agreement, the
CSV cases, the two lists staying different, the ceilings and the filename
properties.

## CSV exports

Every Export button in the admin area hands out a document, not a grid of
values. `CsvReportWriter` owns the letterhead all of them wear — the republic
line, the office name and address, the report title, which filters produced
the file, when and by whom, the totals, and the RA 10173 notice — so a file
found on somebody's laptop a year later still identifies itself.

- **The office identity is read, not typed.** The name and address come from
  `SiteContentService`, so renaming the municipality under Admin → Website
  Content renames it on every exported file. Same rule as
  `HrPolicyFactsService`.
- **One controller per page, one method per tab**, because a tab exports what
  that tab is about and never the neighbouring tab with columns hidden:
  `PersonnelExportController` (Employee Records masterlist · Work Schedules),
  `DepartmentExportController` (office directory · plantilla of positions),
  `TrainingExportController` (the CSC PDS Section IV verification queue),
  `LeaveBenefitsExportController` (six tabs),
  `AdminReportsExportController` (seven tabs — see below),
  `TravelOrderExportController` and `PassSlipExportController` (three tabs each
  — Pending carries days-pending and no approver, Approved names who signed it,
  Disapproved carries the reason in full — plus an `all()` register beside
  them: every status in one file with a Status column and the decision columns
  blank where nobody has decided. That file is the exception the per-tab rule
  buys, not a replacement for it, and both buttons send the *same* toolbar
  filters — "Export All" means every status, not every record, or the file
  would contradict the parameter block it prints at the top of itself), plus
  `AttendanceController`'s
  three (`exportSummary` for the Attendance Summary tab, `exportDetailedRecords`
  for the Detailed Time Record tab's Export All, `exportDetailedDTR` for one
  employee's DTR). The attendance three keep their methods on
  `AttendanceController` because they are built from `calculateEmployeeAttendance()`
  and `buildDetailedRecords()`, which are private to it — moving the export out
  would mean moving the day computation, which payroll reads.
- **The filters on screen are sent as query params** and printed back in the
  file's parameter block — every one of them, spelled "All Departments" rather
  than left blank, so a reader can tell "this covers everything" from "this
  cell did not get written". The endpoint re-runs the query server-side; it
  never scrapes the rendered table, which is what used to cap these files at
  the columns the screen happens to show.
- **A file that names nobody gets no privacy warning.** `notes()` takes
  `containsPersonalData: false` for the department and designation exports —
  an office name and a plantilla item name no one, and a privacy warning
  printed on a file that carries no personal data is how a real one stops
  being read.
- **`fputcsv()` is called with an explicit empty `$escape`** (`CsvReportWriter::ESCAPE`).
  PHP 8.4 deprecates omitting it, and a deprecation notice raised mid-stream
  prints *into* the CSV after the headers have gone out — a corrupted download
  rather than a logged warning.
- **Dates in a table cell are ISO; dates in a sentence are long form.**
  `CsvReportWriter::date()` / `dateTime()` write `2026-08-26`; `longDate()`
  writes "August 26, 2026" for the letterhead, the parameter block and
  summaries. The exports were split between the two spellings with nothing
  recording a decision — Payroll and Deductions wrote ISO, Leave & Benefits,
  Travel Order and Pass Slip wrote `M d, Y` — so two tabs of the same system
  handed out two different date columns. The tie-breaker is the spreadsheet,
  not the reader: `M d, Y` is text Excel sorts **alphabetically** (Apr, Aug,
  Dec), so a register sorted by date comes back in month-name order, which
  looks sorted and is not, and it parses as a date only under an English
  locale. Format through the helper, never inline —
  `tests/Unit/CsvDateFormatTest.php` pins that, and lists the controllers it
  covers. `PersonnelExportController` and `TrainingExportController` still
  format inline and are deliberately excluded; fold them in when those pages
  are next worked on.
- **A filter option that covers two stored values must cover both in the
  export.** `salary_computations.status` defaults to `draft`, and Payslip
  Management's `filterPayslips()` normalises `draft` to `pending` — so the
  option labelled "Pending/Draft" lists both on screen. The export ran
  `where('status', 'pending')` and returned none of them, under a parameter
  block that named itself "Pending / Draft": an empty register that reads as
  "nothing is awaiting approval". `PayrollExportController::statusesFor()`
  owns the mapping so the query and the label cannot disagree.

- **A figure the report states must mean what the page means by it.** The
  training export carries *Hours Claimed* and *Hours Credited* as separate
  columns, because a rejected submission credits 0 to CSC PDS Section IV
  however many hours it declared — one "Hours" column would make a rejected
  submission read as credited in the one report whose subject is which hours
  count.

  The pass slip export separates *Time Away* from *Office Minutes Covered* for
  the same reason: a 1 PM–9 PM slip is eight hours away but only four hours of
  paid office time, and a single "duration" column would put the wrong figure
  beside a deduction. The office figure is `PassSlipComplianceService`'s, read
  against the schedule in force on the slip's own date — the same service the
  DTR computes from, so the report and the time record cannot disagree.

Figures a report states are derived where they are owned: department headcount
is `withCount('employmentDetails')`, never a stored `personnel_count`, and a
schedule's status comes from `Employee::scheduleStatus()` — the same method the
table reads, so the file and the screen cannot disagree about who is
unscheduled.

### The employee's own three exports

Attendance, Payslip and Training each have an Export button on the employee
side, and all three used to ignore the toolbar directly above them.

- **Attendance** serialised `detailedRecords` in the browser — the array as it
  came back from the fetch, *before* the View dropdown or the topbar search
  touched it. Filtering down to six late days still downloaded the whole month.
- **Payslip** had no handler at all: the button rendered, it hovered, and
  clicking it did nothing.
- **Training** linked to an endpoint taking no parameters that always returned
  every *verified* record, so narrowing to the rejected submissions downloaded
  the verified ones.

All three are now server-side endpoints wearing the `CsvReportWriter`
letterhead, and each **re-runs the query its own page renders from** rather
than re-deriving one: `EmployeeAttendanceController::export()` calls
`fetchDetailedRecords()`, and `EmployeePayslipController::export()` calls
`filtered()`, the same method `index()` paginates. The export methods stay on
the page controllers for that reason — the same argument that keeps the
admin DTR exports on `AttendanceController`.

- **The filters are split by where the page's own filtering happens, and the
  export follows.** Payslip paginates server-side, so its period and status
  filters are a GET form (a browser-side filter would only ever narrow the five
  rows on the current page) and reach the export in the URL the server already
  rendered. Training renders every row, so its date range joins the chips and
  the position select in `filterPermanentTraining()`, and the button builds the
  query string at click time. Attendance sends the date range, the View chip
  and the search term.
- **A filter written twice has to be pinned twice.** The page decides in
  JavaScript and the file decides in PHP — both halves keep working when they
  drift, and only their *agreement* breaks. `EmployeeAttendanceController::recordState()`
  mirrors `renderDetailedDTR()`'s row classification (leave outranks the
  weekend; Incomplete outranks Late), `matchesView()` mirrors `applyDtrChip()`,
  and `inDateRange()` mirrors `trainingInDateRange()`.
  `tests/Unit/EmployeeExportFilterTest.php` pins each pair.
- **Date ranges are overlap tests, not containment.** A pay period running
  16 July – 15 August belongs in an August register and a seminar running
  30 July – 1 August belongs in an August filter; testing only the start date
  drops them under a parameter block that says August is covered.
- **The search box is applied across the whole filtered set, not the page.**
  Payslip's box narrows only the rows currently paginated onto the screen; the
  export applies the same term to every matching payslip, because "export what
  I filtered to" stops meaning the visible rows the moment the filter outlives
  page 1. Matched against the fields each row is *built* from, not its rendered
  text.
- **`salary_computations.status` defaults to `draft`, and draft means what
  pending means.** The employee's badge tested for `'pending'` alone and
  labelled an untouched draft "Processed" — a false statement about somebody's
  pay. `EmployeePayslipController::STATUS_GROUPS` now owns the mapping and the
  badge, the Status filter and the export all read it, so the three cannot
  disagree. Same grouping as `PayrollExportController::statusesFor()`.
- **Totals are counted over the exported rows**, not the whole period, so the
  file adds up to its own table. Attendance's "Days Present" is nonetheless the
  Present KPI card's definition — any day with a punch — so the summary and the
  card above the button agree.
- **Training keeps Hours Claimed and Hours Credited as separate columns**, the
  same rule as `TrainingExportController`: a rejected or pending submission
  credits 0 to CSC PDS Section IV however many hours it declared. That is also
  what lets one file serve both purposes now that the button honours the status
  chips — the Hours Credited column is the PDS figure whatever the filter was
  set to, which is why "Export to PDS" could become "Export CSV" without
  losing the PDS.
- Attendance drops the screen's **Total Hours** column: it is `accredited_minutes / 60`
  on every branch, so beside Accredited Hours it was the same number twice.

### Admin Reports exports

The Reports page's only button was `onclick="window.print()"`, labelled
"Export / Print". Printing the page is not an export: the rendered table carries
percentage *bars* rather than numbers and status *chips* rather than words, and
nothing on it says which municipality issued the file or what period it covers
once it leaves the screen. It is now **Export CSV** (the open tab) beside a
separate **Print**.

- **`AdminReportService` was extracted so the file and the page are one
  computation.** The seven reports used to be private methods on
  `AdminReportsController`, which was fine while the page was their only
  reader. An export that re-derives its own figures is an export that can
  disagree with the cards above the button it was clicked from. Both
  controllers now read the service; `one()` builds a single tab without paying
  for the other six.
- **The period is sent as query params and the report is re-run server-side**,
  the same rule as every other export here. `resolvePeriod()` clamps
  `year`/`month`/`semi` in one place for both surfaces.
- **A filter that did not apply says so.** Headcount is a live snapshot and
  Training is filtered by year, so their parameter blocks print
  "Not applicable (live snapshot)" / "(filtered by year)" rather than a
  pay period they did not honour — a reader has to be able to tell an
  inapplicable filter from an unwritten one, and a Headcount file headed
  "August 1–15" misdescribes itself.
- **Recruitment and Performance export a stated absence, not an empty table.**
  Neither has a backing table in this schema. A letterhead over a blank grid
  reads as "nobody was hired this period", which is a false statement about the
  municipality's records — so the file carries a `REPORT STATUS` block saying
  the capability does not exist yet and why, and takes
  `containsPersonalData: false`. This is the case the missing Deductions
  *Transactions* endpoint avoided by not existing; here the button is on
  screen, so a dead button is what would need explaining.
- **Money is a plain number, hours drop trailing zeros**, matching
  `PayrollExportController` — the column header carries "(PHP)" so a
  spreadsheet totals the column instead of reading it as text.
- Each file carries a totals line in the table's own columns *and* a summary
  block, because a register is checked column-by-column against a printout and
  a summary block cannot be read that way. The Deductions file reprints the
  page's itemised-vs-gross-less-net reconciliation warning as a
  `RECONCILIATION:` note, since a reader totalling that column against a
  payslip needs to know why the two can differ.

---

## Forgot password

`/password/forgot` is a three-step wizard — email address, six-digit code
mailed to it, new password — and **all three steps used to be answered in the
browser**. Step 2 compared the typed code against the literal `123456`
compiled into `user/forgot-password.blade.php`, which anyone could read from
View Source for any address. Step 3 showed "Password reset successfully!" and
redirected to sign-in **without a request leaving the page**, so the password
never changed and the user was sent to a form their old password still opened.
There was no POST route, no mailable and no storage; `AuthController` only
served the view. Same failure mode as the employee chathead, and worse in kind:
a false confirmation that a credential changed.

`PasswordResetController` (thin) + `PasswordResetCodeService` (every rule) now
own it. What holds it up:

- **The three steps are one rule set, not three endpoints.** The code step 1
  mails is the code step 2 spends; the ticket step 2 mints is the *only* thing
  step 3 accepts. Keeping them in one service is what stops a link in that
  chain quietly going unchecked.
- **The browser carries a ticket, never a "verified" flag.** A client-side
  boolean is exactly what the mockup's step 3 trusted. The ticket is random,
  stored hashed, and checked before any password is written.
- **The screen never says whether an address is registered.**
  `sendCode()` returns `void` on every path — unknown address, deactivated
  account and real account are indistinguishable, because a public form that
  answers "no such account" is an enumeration oracle aimed at the
  municipality's staff directory. `ForgotPasswordFlowTest` asserts the two
  replies are byte-identical.
- **Codes are hashed at rest and spendable, not merely expiring.** Six digits
  is a small space, so `MAX_ATTEMPTS` burns the code after five wrong guesses;
  a correct guess clears it too, so the same digits cannot mint a second
  ticket. Route throttles sit on top of a per-address resend cooldown — the
  cooldown is what stops "Resend Code" being a mail-bomb aimed at one inbox
  from a form needing no login.
- **A failed send deletes the code it just issued** rather than leaving a live
  credential on a row nobody can read.
- **Inactive accounts get nothing**, because `AuthController::login()` refuses
  them a session anyway — a recovery ending at the same refusal.
- **A completed reset rotates `remember_token`**, so a stolen "remember me"
  cookie dies with the old password.
- Storage is a dedicated `password_reset_codes`, **not** Laravel's
  `password_reset_tokens`: the framework's broker table is one signed link with
  no attempt counter, and sharing it would collide on its `email` primary key
  the first time anyone calls `Password::sendResetLink()`.
- Server-side the password rule is `min:8` — matching registration and
  Settings → Change Password, since a password this system already accepts must
  not be one it refuses to restore. The on-screen list now separates *Required*
  from *Recommended* rather than stating four rules and enforcing one.

```bash
php artisan test tests/Unit/PasswordResetCodeServiceTest.php \
  tests/Feature/ForgotPasswordFlowTest.php
```

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

### You choose before you edit

The editor has two states and never shows both. **Overview** is the landing
screen: one card per section, each carrying a `SECTION_BLURBS` sentence saying
which part of the site it is ("the thin strip above everything", "the big
banner at the very top") plus an Edited/Original badge. **Editor** is one
section's form, with the rail alongside for hopping between sections.

Landing straight in a twenty-field form was what made this overwhelming — you
had to recognise the section you wanted from ten abstract names *while* looking
at somebody else's form. The section names are the editor's vocabulary, not the
visitor's; the blurbs are what let somebody find "where the phone number lives"
without opening four panels.

The chosen section lives in the URL hash, so `/admin/website#contact` deep-links
and a refresh — including the reload after "Reset to original" — keeps your
place. An unrecognised hash falls back to the overview rather than a blank
screen. `WebsiteContentTest` asserts every section has a blurb and that no blurb
exists for a section that is not in the rail.

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

### The sign-in screens share the welcome page's chrome

The gov bar, the navbar brand block and the footer were the *same markup*
pasted into four files — `welcome`, `login`, `forgot-password`, `select-role`.
Only the welcome copy read from `SiteContentService`, so renaming the
municipality changed the homepage and left the three sign-in screens saying
whatever it used to be, under a hard-coded `© 2025`.

They are now three components — `x-public-govbar`, `x-public-brand`,
`x-public-footer` — each reading the section it renders. `x-public-footer`
takes `:links` because the welcome page carries the Privacy / Terms / Sitemap
row and the auth screens do not; that is the only difference between them.
The year is rendered from `date('Y')`, never stored.

`WebsiteContentTest` saves a sentinel into `govbar`, `brand` and `footer` and
asserts all four pages render it, so a fifth public page cannot quietly paste
the strings again.

The **compliance tag rows are deliberately not shared**. They look alike but
say different things: the hero advertises BIR / GSIS / CSC / ARTA, login adds
RA 10173 (Data Privacy Act), and forgot-password's are about recovery
("Secure Recovery", "Encrypted"). Wiring them to one list would silently drop
the privacy claim from the sign-in form.

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
