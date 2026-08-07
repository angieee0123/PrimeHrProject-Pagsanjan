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

---

## The AI Assistant

One natural-language interface over every HR module. Users ask questions; the
assistant classifies intent, routes to the service that owns that capability,
scopes results to what the asker may see, and narrates the answer.

### Request flow

```
question
   │
   ├─ ConversationMemoryService   resolve "generate a report" → "…of Juan's leave"
   ├─ AiQueryService              classify intent (patterns first, model as fallback)
   ├─ AiAccessPolicy              scope every query to the caller's permissions
   ├─ <capability service>        run the actual query
   ├─ AiChatService               narrate the rows in plain language
   └─ ai_audit log                who asked what, which capability, how many rows
```

### Services

| Service | Owns |
|---|---|
| `AiQueryService` | Orchestrator: intent detection, routing, audit logging |
| `AiAccessPolicy` | **The single source of truth for permissions.** All scoping goes through it |
| `AiChatService` | LLM calls. Resolves provider per-user → org default → `.env` Groq |
| `EmployeeSearchService` | Employee / department / hire-date lookups |
| `DocumentSearchService` | Files across `documents`, training certificates, employee photos |
| `AiFileResolver` | Turns a `source:id` file reference back into bytes, permission-checked |
| `SemanticSearchService` | Meaning-based expansion (maternity → pregnancy, parental…) |
| `SafeSqlService` | Natural-language → validated read-only SQL |
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
php artisan test tests/Unit/SafeSqlValidationTest.php tests/Unit/AiAccessPolicyTest.php tests/Unit/AiFileResolverTest.php
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
chart ships beside its data table. The palette is validated for colour-vision
deficiency; the accompanying table is also what satisfies the contrast relief
requirement for the lighter hues.

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

## Conventions

- Controllers stay thin; business logic lives in `app/Services/`.
- Permission decisions belong in `AiAccessPolicy`, never inline in a service.
- Prefer Eloquent relations over raw joins, but note the relation names above.
- When adding a table the assistant should read, add it to
  `SafeSqlService::ALLOWED_TABLES` — omission is a deliberate deny.
