# Hiring backend native port (PRIME-HRIS recruitment → Laravel)

> **Status: the staff-facing module is native and live.** `/admin/recruitment`
> now reads and writes this application's own tables through Eloquent. There is
> no HTTP client, no service account, and no second server: the screening
> pipeline runs as a queued job and the lifecycle sweep is a scheduled command.
>
> The data layer (§15) is unchanged. Phase 2 (§16) added the services,
> controllers, job, command, presenter and test suite, and removed the HTTP
> integration (`docs/hiring-backend-integration.md`, now retired). The deeper
> workflows the reference backend also carried — applicant portal, interview and
> scorecard UI, talent pool, plantilla/appointment UI, re-rank consent — have
> their tables and models but **no screens yet**; §16.5 lists them precisely.

---

## 1. What "option 1" means

Copy the behaviour of `PRIME-HRIS-RSP-V5/prime-hris-backend` (FastAPI +
SQLAlchemy + Pydantic) into the HRIS as native Laravel — migrations, Eloquent
models, form requests, API resources, controllers, services, queued jobs, and
PHPUnit tests — then **retire the Python service** for the ported scope.

The frontend remains Blade.

The whole point is one stack, one deploy, one identity system, one database.
The whole cost is translating ~13.6k lines of app code plus ~7.5k lines of tests,
and re-deciding the one slice that has no PHP equivalent (§6).

---

## 2. Size and verdict

| Area | Lines (Python) | Port difficulty |
|---|---|---|
| `app/models/__init__.py` (23 tables) | 1,276 | Low — mechanical |
| `app/schemas/__init__.py` (DTOs) | 1,785 | Low — Form Requests + Resources |
| `app/services/*` (32 files) | ~6,400 | Low except the NLP pair (§6) |
| `app/api/*` (25 routers) | ~4,100 | Low — thin controllers |
| `app/core/*` (config, clock, security, schema_guard) | ~390 | Low |
| `tests/*` (27 files) | 7,553 | Medium — must be re-expressed |

The single load-bearing fact: **the design is not tightly coupled to Python.**
External imports across the whole app are just `fastapi, sqlalchemy, pydantic,
jose, passlib, pdfplumber, docx, gensim, numpy, spacy, PIL, dateutil, alembic`.
Everything except `gensim`/`numpy`/`spacy` has a direct Laravel equivalent, and
two of the document ones are **already installed in this project**.

Realistic estimate: multi-week for a developer familiar with the HRIS, dominated
by (a) writing 23 migrations + models, (b) translating the service rules, and
(c) re-expressing the test suite. The NLP fork (§6) is the only genuine design
work.

---

## 3. Dependency mapping

| Python | Purpose | Laravel equivalent | Status |
|---|---|---|---|
| FastAPI + Pydantic | routing + validation | routes + Form Requests + API Resources | built in |
| SQLAlchemy 2 | ORM | Eloquent | built in |
| Alembic | migrations | Laravel migrations | built in |
| python-jose (JWT) | auth tokens | Sanctum (or session web auth) | installed |
| passlib + bcrypt | password hashing | `Hash::make()` (bcrypt) | built in |
| pdfplumber | PDF text | `smalot/pdfparser` | **already required** |
| python-docx | DOCX text | `phpoffice/phpword` | **already required** |
| Pillow | avatars | `Intervention\Image` (add) | to add |
| python-dateutil | date parsing | Carbon | built in |
| `zoneinfo` (Asia/Manila) | business calendar | `config('app.timezone')` / `Carbon::now('Asia/Manila')` | built in |
| stdlib `threading.Timer` | lifecycle sweep | Laravel scheduler + queued jobs | built in |
| `smtplib` | email | `Mail` (Gmail SMTP config) | built in |
| `hashlib` / `uuid` | hashing / ids | `hash()` / `Str::uuid()` | built in |
| **spaCy** (`en_core_web_md`) | NER (name, org) | **none** | see §6 |
| **gensim** Word2Vec | semantic skill match | **none** | see §6 |
| **numpy** | vector math | **none** | see §6 |

---

## 4. Data layer port

23 tables (`app/models/__init__.py`):

```
users, themes, user_sessions, email_verification_tokens,
vacancies, notifications, candidates, applicant_profiles,
duplicate_flags, rerank_consent_requests, screening_results,
applicant_events, interviews, interview_panelists, interview_scorecards,
interview_reschedule_log, plantilla_items, plantilla_item_versions,
plantilla_hiring_events, appointments, audit_log,
retention_settings, retention_setting_changes
```

General mapping is 1:1 (string-UUID PKs → `$table->uuid('id')->primary()`, JSON
columns → `json()`, `Numeric` → `decimal()`, indexes → `index()`/`unique()`).

**DDL that is not a plain copy — read before writing migrations:**

- `appointments.live_vacancy_guard` is a MySQL **generated column**
  (`models/__init__.py:1163`) — needs `DB::statement` or `->storedAs(...)`.
- `interviews` carries a **CASE-expression unique index** for
  soft-delete-aware rounds (`models/__init__.py:745-750`).
- `retention_settings.jurisdiction_code`, `candidates.user_id`,
  `profiles.content_hash`, `appointments` live-guard all **rely on MySQL
  treating each NULL as distinct** in a unique index. Preserve that reliance
  or the uniqueness guarantees change meaning.
- `users` and `notifications` **name-collide** with existing HRIS tables
  (`migrations` list). If the port shares `primehrismagdalena`, both need a
  prefix (e.g. `recruit_*`) or a separate connection/database.

**Tests:** the HRIS's `RefreshDatabase` is already broken for MySQL-only
migrations (CLAUDE.md "Testing"), and this port adds more MySQL-only DDL. The
existing pattern — build only the tables a test touches via `Schema::create` in
`setUp()` (see `tests/Unit/AiFileResolverTest.php`) — extends, but the 23-table
schema makes it heavier.

---

## 5. Auth and RBAC port

The backend's auth (`core/security.py`, `api/deps.py`) becomes Laravel-native:

| Backend | Laravel |
|---|---|
| `POST /api/auth/login` → HS256 JWT | `AuthController` + Sanctum token, or the existing session guard |
| `require_roles(Role.X)` (`api/deps.py:31`) | route middleware / `hasRole()` / a Gate |
| Roles `HR_ADMIN, HR_STAFF, AUDITOR, APPLICANT` | mapped onto the HRIS role set |
| `UserOut`, `TokenResponse` | API Resource |

**The identity decision must be made first**, because everything else follows:

- The HRIS's valid roles are `employee`, `hr`, `admin`, `mayor` (CLAUDE.md
  "Schema notes"). The backend's roles are a different vocabulary. Map
  `HR_ADMIN→admin`, `HR_STAFF→hr`, `AUDITOR→mayor-or-admin`? — decide explicitly.
- **`APPLICANT` has no HRIS equivalent.** An applicant portal (self-register,
  email verification, own-application view) is a **public auth surface the HRIS
  does not have**. Either add a new role/guard (a deliberate scope expansion, in
  tension with CLAUDE.md's stated role list) or drop the applicant-portal scope.
- The backend has its **own `email_verification_tokens`** and register flow
  (`api/auth.py:74-122`) — porting it duplicates an auth subsystem.

Recommendation: port the **staff-facing** scope first (HR/recruiter workflows),
and treat the applicant portal as a separate, later decision.

---

## 6. The NLP layer — the one real fork

This is where "copy the code" stops working. `requirements-nlp.txt` documents
that the pipeline has a full ML path and a **fully functional fallback path**.

### 6.1 `nlp_parser.py` — spaCy NER

Used for: ORG entity extraction in `parse_profile` (`nlp_parser.py:110-114`) and
PERSON name extraction (`extract_name`, line 142-149).

Fallback (already written, no ML needed): gazetteer term matching, training /
experience regexes (`TRAINING_RE`, `EXPERIENCE_RE`), and a Title-Case-letterhead
name heuristic (`NAME_LINE_RE`, line 151-158). **All portable to PHP.**

Port cost: low. Fidelity lost: NER-accurate names and orgs become
heuristic/absent.

### 6.2 `matcher.py` — Gensim Word2Vec + cosine

`skill_match_score` computes max cosine similarity between required and applicant
skills, floor `SKILL_SIMILARITY_FLOOR` (`matcher.py:51-80`).

Fallback: **Jaccard token overlap** (`_token_overlap`, line 43-48). Portable.

Three sub-options, pick one:

1. **PHP fallback only** — simplest; skill matching drops to token overlap.
   Fewer partial-credit matches; defensible but lower quality.
2. **Read the Word2Vec model in PHP** — `.kv` is a simple format; load phrase
   vectors and do cosine without numpy. Real but bounded work; keeps quality.
3. **Keep a minimal Python "screening" sidecar** — only `extract→parse→match→score`
   stays in Python; Laravel owns everything else and calls it as a job. Smallest
   Python surface that preserves ML.

### 6.3 Already portable (pure logic, no ML)

`anonymizer` (regex PII scrub), `scorer` (formula), `explainer`,
`degree_normalizer` + `degree_aliases` (alias map), `date_range_parser`
(regex/Carbon), `behavioral` (rubric), `summary`, `scorecards`,
`talent_pool`, `duplicates`, `candidates`, `retention`, `rehire`,
`rerank_consent`, `plantilla`, `appointments`, `interviews`, `notifications`,
`presence`, `themes`, `avatar`.

---

## 7. Service-by-service port inventory

| Python service | Owns | Portable? |
|---|---|---|
| `lifecycle.py` | 6-state vacancy machine, lapse/retire sweep | yes |
| `appointments.py` | record appointment + CSC attestation, slot capacity | yes |
| `plantilla.py` | versioned plantilla items + hiring events | yes |
| `intake.py` | resume intake, size/hash, dedupe | yes (Storage) |
| `pipeline.py` | extract→anonymize→parse→score→explain→rerank | yes except §6 |
| `extraction.py` | PDF/DOCX/TXT text | yes (pdfparser/phpword) |
| `anonymizer.py` | PII redaction | yes (regex) |
| `nlp_parser.py` | structured profile | **fork (§6.1)** |
| `matcher.py` | skill similarity | **fork (§6.2)** |
| `scorer.py` | weighted fit score | yes |
| `explainer.py` | human-readable rationale | yes |
| `degree_normalizer.py`, `degree_aliases.py` | degree canonicalization | yes |
| `date_range_parser.py` | experience date ranges | yes (Carbon) |
| `behavioral.py` | BTQ rubric summary | yes |
| `candidates.py` | person-level identity, merge | yes |
| `duplicates.py` | shared-resume flags | yes |
| `rehire.py` | re-rank/rehire plan + execute | yes |
| `rerank_consent.py` | RA 10173 consent lifecycle | yes |
| `scorecards.py` | interview scorecard rules | yes |
| `interviews.py` | interview state machine | yes |
| `summary.py` | panel rollups, divergence | yes |
| `talent_pool.py` | unsuccessful-candidate pool | yes |
| `notifications.py` | in-app + email notifications | yes (DB + Mail) |
| `mailer.py` | SMTP templates | yes (Mail) |
| `presence.py` | session heartbeat/series | yes |
| `retention.py` | retention policy + change log | yes |
| `avatar.py` | profile avatars | yes (Intervention) |
| `clock.py` | Asia/Manila business calendar | yes (Carbon) |
| `scheduler.py` | in-process sweeps | yes (scheduler/jobs) |
| `themes.py` | accent palettes registry | yes — **overlaps HRIS theming** |

Note `themes.py` and `presence.py` overlap features the HRIS already has
(`SystemTheme`, notification presence). Port must reconcile, not duplicate.

---

## 8. API layer port

25 routers → thin controllers. Endpoint families:

- Auth: `login`, `me`, `register`, `verify-email`, `resend-verification`
- Vacancies: list/public/detail/archive + create/publish/hold/resume/cancel/
  close/reopen/status
- Applications: resume upload, profiles, resume download
- Rankings: list, by vacancy, rerank, version
- Approvals: list, accept, disapprove
- Dashboard, Audit
- Talent Selection, Talent Pool
- Interviews, Scorecards (+ authoring)
- Notifications (in-app)
- Profiles, Avatars, Sessions (heartbeat/presence), Retention Settings
- Candidates, Duplicates
- Appointments, Hiring (eligibility/closure), Plantilla, Re-rank consent

Each becomes a route + controller method + Form Request + API Resource. Request
validation moves from Pydantic validators (`schemas/__init__.py`) to
`FormRequest::rules()`; response shaping moves to Resources.

---

## 9. Background work, mail, scheduler

- FastAPI `BackgroundTasks` running `pipeline.run_screening` → **Laravel queued
  job**, one per applicant (the Python code already opens one session per
  applicant for isolation — `pipeline.py:37-49`; the job should mirror that).
- `scheduler.py`'s three sweeps (lifecycle, session prune, consent) → **Laravel
  `Schedule`** entries calling the ported services.
- `scripts/run_lifecycle_jobs.py` → an artisan command for the same cron path.
- `mailer.py` Gmail SMTP → `Mail` with the same templates and the
  "log instead of send when SMTP unset" dev behaviour (`.env.example:52-54`).

---

## 10. Cross-cutting behaviour to preserve deliberately

These are load-bearing rules, not incidental code — port them exactly or the
system changes meaning:

- **Position lifecycle** (`lifecycle.py:1-31`): LAPSED is automatic and
  reversible; CLOSED/CANCELLED are terminal; OPEN is the only public state;
  grace window floored at 7 days.
- **Identity assertions** (`candidates.py`, `models/__init__.py:334-368`): a
  person is matched only on `user_id`, never on inferred names; merges are soft
  tombstones and reversible.
- **NULL-as-distinct uniques** (§4) for `candidates.user_id`,
  `content_hash`, `jurisdiction_code`, appointment live-guard.
- **Rank eligibility** (`pipeline.py:26-34`): only HR-uploaded or ACCEPTED
  portal applications hold a rank; ineligible still keep a Fit Score.
- **Re-rank consent** (`rerank_consent.py`): nothing is written to a ranking on
  HR's say-so alone; consent rows freeze the whole approved plan.
- **Business calendar** (`clock.py`): calendar-day questions in Asia/Manila,
  instants in UTC.

---

## 11. Testing

27 test files / 7,553 lines encode the guarantees. They must be re-expressed as
PHPUnit tests (feature + unit), including the golden cases for lifecycle,
plantilla, consent, retention, and scorecards.

Constraints: the HRIS `RefreshDatabase` is broken (MySQL-only migration set), so
tests build their own tables. A 23-table schema makes that expensive — consider
a dedicated test schema/connection strategy as part of the port, not after.

---

## 12. Phased plan

1. **Decide identity** (§5): role mapping and whether the applicant portal is in
   scope. Nothing else is stable until this is. — **done, see §15.1**:
   staff-only, no applicant role.
2. **Decide the NLP fork** (§6.2): PHP fallback / PHP Word2Vec / Python sidecar.
   — **done, see §15.1**: PHP fallback only.
3. **Schema**: 23 migrations + models, resolving table collisions and MySQL-only
   DDL; verify `migrate:fresh` on a throwaway DB. — **done for the 18 in-scope
   tables, see §15.2–§15.4**; the other 5 are deliberately not ported (§15.3).
4. **Services** in dependency order: `clock` → `anonymizer`/`extraction` →
   `nlp_parser`/`matcher`/`scorer`/`explainer` → `intake`/`pipeline`/
   `candidates` → `lifecycle`/`appointments`/`plantilla`/`rehire`/
   `rerank_consent` → `interviews`/`scorecards`/`summary`/`talent_pool` →
   `notifications`/`mailer`/`presence`/`retention`.
5. **Controllers + routes + requests + resources**, auth and RBAC first.
6. **Background jobs + scheduler + artisan command.**
7. **Blade views** using theme variables.
8. **Port the tests**; get the golden suite green.
9. **Cut over**, then retire the Python service for ported scope.

---

## 13. Risks / open questions

- **NLP fidelity** — the only part that cannot be copied. Highest-risk item.
- **Two user tables / two auth systems** — the port forces a merge or a bridge.
  `APPLICANT` is new to the HRIS.
- **Table collisions** (`users`, `notifications`) if sharing a database.
- **MySQL-only DDL + broken `RefreshDatabase`** — testing cost is real.
- **Overlap** with existing HRIS features (theming, presence, notifications) —
  must reconcile rather than duplicate.
- **Ongoing maintenance** — after the port the backend stops evolving with the
  Python reference, so future upstream changes must be re-implemented by hand.
- **Where does the *rest* of PRIME-HRIS live?** If only a subset of endpoints is
  wanted, a full port is far more work than the value returned — which is why
  HTTP integration is the current choice.

---

## 14. When option 1 is worth it

Choose the native port only if most of the following are true:

- You want a single deploy, single database, and single identity system.
- You need deep, transactional coupling between recruitment and HRIS data.
- You can accept the documented NLP fallback (or fund a sidecar).
- You intend to own and evolve the recruitment module long-term in PHP.
- You can absorb the test-suite translation.

Otherwise the HTTP integration (`docs/hiring-backend-integration.md`) gets the
wanted endpoints at a fraction of the cost.

---

## 15. What was built (phase 1: the data layer)

§12 steps 1–3 are done. Steps 4–9 — services, controllers, requests/resources,
jobs, Blade views, and the re-expressed test suite — are **not**, so nothing in
this port executes a recruitment rule yet, and the HTTP integration keeps serving
`/admin/recruitment` unchanged.

### 15.1 The two decisions §12 puts first

| Decision | Taken | Consequence |
|---|---|---|
| Identity (§5) | **Staff-only.** `HR_ADMIN→admin`, `HR_STAFF→hr`, `AUDITOR→mayor` for oversight / `admin` for the audit page. No `applicant` role. | No applicant portal, no second registration/email-verification subsystem, and no merge of two `users` tables. The HRIS's own accounts are the only identities. |
| NLP fork (§6.2) | **PHP fallback only** — gazetteer/regex profile parsing + Jaccard token overlap. | Skill matching drops to token overlap (fewer partial-credit matches). Phase 4 must implement the fallback path; it must not claim a model is loaded. |

### 15.2 The 18 ported tables

| Table | Model |
|---|---|
| `vacancies` | `App\Models\Vacancy` |
| `candidates` | `App\Models\Candidate` |
| `applicant_profiles` | `App\Models\ApplicantProfile` |
| `duplicate_flags` | `App\Models\DuplicateFlag` |
| `rerank_consent_requests` | `App\Models\RerankConsentRequest` |
| `screening_results` | `App\Models\ScreeningResult` |
| `applicant_events` | `App\Models\ApplicantEvent` |
| `interviews` | `App\Models\Interview` |
| `interview_panelists` | `App\Models\InterviewPanelist` |
| `interview_scorecards` | `App\Models\InterviewScorecard` |
| `interview_reschedule_log` | `App\Models\InterviewRescheduleLog` |
| `plantilla_items` | `App\Models\PlantillaItem` |
| `plantilla_item_versions` | `App\Models\PlantillaItemVersion` |
| `plantilla_hiring_events` | `App\Models\PlantillaHiringEvent` |
| `appointments` | `App\Models\Appointment` |
| `audit_log` | `App\Models\AuditLog` |
| `retention_settings` | `App\Models\RetentionSetting` |
| `retention_setting_changes` | `App\Models\RetentionSettingChange` |

Migrations: `database/migrations/2026_11_09_120001`–`120018`, one per table, in
dependency order.

**No prefix was needed.** None of the 18 names collides with the HRIS's existing
tables (checked against `information_schema`), so the collisions the plan warned
about — `users` and `notifications` — are among the five tables below that are
not ported at all.

### 15.3 The five tables deliberately not ported

| Reference table | Why |
|---|---|
| `users` | The HRIS identity system is authoritative. Every reference `users.id` foreign key is remapped to the HRIS `bigint unsigned` `users.id`; the recruitment tables gain no second account table. |
| `themes` | Overlaps `SystemTheme` (CLAUDE.md "Theming"), which already owns per-user and org palettes. A second registry would contradict it. |
| `user_sessions` | Presence telemetry for the backend's own dashboard; the HRIS owns sessions, and nothing in the ported scope reads it. |
| `email_verification_tokens` | Part of the applicant-portal auth subsystem, which staff-only scope drops. |
| `notifications` | Name-collides with the HRIS table, and the HRIS already has exactly one notification system. Phase 4 should emit through `NotificationService`, not duplicate delivery, read-state and audience handling. |

Two columns that belonged to the applicant portal are **kept but unused**:
`candidates.user_id` and `applicant_profiles.user_id`. Both are nullable and
foreign-key to the HRIS `users`; under this scope they stay NULL for every
HR-uploaded application. They exist so a later applicant-account link is an
additive change rather than a migration of the identity model.

### 15.4 DDL that is not a plain copy

- **`appointments.live_vacancy_guard`** is a MySQL **STORED generated column**
  (`CASE WHEN attestation_status <> 'DISAPPROVED' THEN vacancy_id END`), declared
  with `->storedAs()`. It carries **no UNIQUE constraint**, exactly as in the
  reference: multi-slot vacancies permit more than one live appointment, so "at
  most *slots*" is a count check under a row lock in the appointment service,
  which no unique index can express. NULL-as-distinct is what lets every
  disapproved row collapse without colliding.
- **`uq_interviews_application_round_active`** is a **functional unique index**:
  `(application_id, (CASE WHEN deleted_at IS NULL THEN round ELSE NULL END))`.
  Blueprint cannot express it, so it is created with `DB::statement`. It gives
  soft-delete-aware rounds — an active `(application, round)` collides, a deleted
  one frees the number for gap-fill — without a second table or a deferred check.
- **NULL-as-distinct uniques** are preserved where the reference relies on them:
  `candidates.user_id` (many HR-uploaded candidates, at most one row per account)
  and `retention_settings.jurisdiction_code` (NOT NULL and unique, so a nullable
  column cannot silently permit two "system-wide default" rows).
- **`interview_scorecards` has no `overall_average_score`.** The reference dropped
  it as inert, and the port computes the mean on read
  (`InterviewScorecard::averageScore()`), so it cannot outlive a corrected rating.

### 15.5 Fidelity notes

- **UUID keys** use the `HasUuids` trait (`uuid7`), not `uuid4`. Same `char(36)`
  column and same call-site behaviour, with better index locality.
- **`UPDATED_AT = null`** on the 15 tables whose reference has only `created_at`,
  so Eloquent writes 1 column where the original wrote 1.
- **Python-side defaults are reproduced in `$attributes`** (status, counters,
  JSON empties, booleans). Eloquent does not read a DB default back after insert,
  so without these a freshly created row would carry `null` status in memory while
  its stored row was correct. Column defaults remain as the backstop for raw SQL.
- **`$fillable` is explicit on every model**, never `$guarded = []` — the same
  rule that the `attendance_type` mass-assignment bug in CLAUDE.md exists to
  enforce.
- **`RerankConsentRequest` hides `token`** from serialisation, because it is the
  credential that lets a candidate answer; an HR user who could read it could
  consent on their behalf.
- `plantilla_hiring_events.appointment_id` stays a plain **unique string**, not a
  foreign key, exactly as in the reference: it is the logical event identifier
  that makes reprocessing idempotent.

### 15.6 Verification

```bash
php artisan test tests/Unit/RecruitmentSchemaTest.php
```

`RecruitmentSchemaTest` (10 tests, 45 assertions) runs **only the 18 recruitment
migrations** on the SQLite test connection, after building the one table they
depend on. That is the project's existing pattern, forced by `RefreshDatabase`
being unusable here; running the migrations rather than hand-building 18 tables
means a migration that is not runnable fails the test instead of being skipped.

The MySQL-only constructs were verified separately on a **throwaway MySQL 8.0.45
database** (never `primehrismagdalena`):

- `php artisan migrate:fresh` applies the whole migration set, and
  `migrate:rollback --step=18` reverses the 18 cleanly;
- 25 Eloquent round-trip assertions passed, covering: the generated column
  collapsing to NULL on DISAPPROVED and carrying the vacancy when ATTESTED; an
  active duplicate `(application, round)` refused while a soft-deleted one is
  reusable; many NULL `candidates.user_id` rows coexisting while one account owns
  at most one; JSON casts surviving a reload; and no `overall_average_score`
  column existing.

### 15.7 What phase 4 owns next

Services in the plan's dependency order: `clock` → `anonymizer`/`extraction` →
`nlp_parser`/`matcher`/`scorer`/`explainer` → `intake`/`pipeline`/`candidates` →
`lifecycle`/`appointments`/`plantilla`/`rehire`/`rerank_consent` →
`interviews`/`scorecards`/`summary`/`talent_pool` →
`notifications`/`mailer`/`presence`/`retention`.

Two constraints carry over from the decisions above:

- `nlp_parser` and `matcher` implement the **fallback** path only (gazetteer,
  regexes, Title-Case name heuristic; Jaccard token overlap). `model_version` on
  a screening result must name what actually ran, never a model that did not.
- `notifications` and `mailer` deliver through the HRIS's existing
  `NotificationService` and `Mail` configuration, not a second system.

The state machines (`lifecycle`, `interviews`) and the consent gate
(`rerank_consent`) are behaviours, so they belong in services — not in the
models, whose constants are vocabulary only (`Vacancy::STATUS_*`,
`Interview::STATUS_*`, and so on).

---

## 16. What phase 2 built (the native module)

### 16.1 The request path today

```
Browser
  │  Blade form / link (session auth, EnsureRoleForArea: admin + hr)
  ▼
Hiring\{Dashboard,Vacancy,Applicant,Audit}Controller
  │  reads/writes Eloquent
  ▼
App\Services\Recruitment\*
  ├─ VacancyLifecycleService     six-state machine, grace window, sweep
  ├─ ResumeIntakeService         validate, dedupe, store, PENDING profile
  ├─ ScreeningPipeline           extract → anonymize → parse → score → explain → rerank
  ├─ HiringDashboardService      the overview's figures
  └─ RecruitmentPresenter        the array shape the Blade views already spoke
  │
  ├─ App\Jobs\ScreenApplicantProfile        queued, retried, per applicant
  └─ recruitment:lifecycle-sweep            scheduled hourly
```

No HTTP client, no token, no CORS, no second identity. `PrimeHrisClient`,
`PrimeHrisFailure`, their test, the `services.prime_hris` config block, the
`PRIME_HRIS_*` environment variables and the `hiring_audit` log channel are all
deleted.

### 16.2 The services

All under `app/Services/Recruitment/`.

| Service | Owns | Ported from |
|---|---|---|
| `BusinessClock` | the one calendar; grace expiry; "is it past closing" | `core/clock.py` |
| `VacancyLifecycleService` | six states, legal moves, grace window, lapse/retire sweep | `services/lifecycle.py` |
| `RecruitmentAudit` | `audit_log` rows and candidate-journey events | `api/deps.py` |
| `ResumeTextExtractor` | PDF / DOCX / TXT text | `services/extraction.py` |
| `ResumeAnonymizer` | PII + demographic redaction and anon codes | `services/anonymizer.py` |
| `DegreeNormalizer` + `DegreeAliases` | HR-approved degree canonicalization | `services/degree_normalizer.py`, `degree_aliases.py` |
| `ExperienceDateParser` | date-range → years, month precision, overlap merge | `services/date_range_parser.py` |
| `ResumeProfileParser` | education / training / experience / eligibility / skills, name heuristic | `services/nlp_parser.py` (fallback path only) |
| `SkillMatcher` | Jaccard token overlap with the reference floor | `services/matcher.py` (fallback path only) |
| `FitScoreCalculator` | the CSC-weighted 0–100 Fit Score | `services/scorer.py` |
| `ScreeningExplainer` | the plain-language justification | `services/explainer.py` |
| `CandidateResolver` | identity resolution, merge-following, name adoption | `services/candidates.py` |
| `ResumeIntakeService` | bytes → hash → dedupe → private storage → PENDING application | `services/intake.py` |
| `ScreeningPipeline` | the orchestrator and the rerank | `services/pipeline.py` |
| `DuplicateDetector` | shared-resume flags, reversible merge/unmerge | `services/duplicates.py` |
| `HiringDashboardService` | overview KPIs, bands, per-position counts | `api/dashboard.py` |
| `RecruitmentPresenter` | the array contract the existing Blade views read | `app/schemas/__init__.py` (response shapes) |

### 16.3 Rules preserved deliberately

These are behaviours, not incidental code; they are pinned by tests.

- **The six-state machine.** DRAFT is never public and never lapses; LAPSED is
  automatic and reversible; CLOSED and CANCELLED are terminal and say so
  ("Create a new vacancy instead"). A resumed hold does **not** increment
  `reopen_count`; a reopen does, and keeps `lapsed_at` as history.
- **A closing date means the whole day.** `isPastClosing` compares date-to-date
  and uses `<`, so a position closing today is still accepting applications. The
  grace window is anchored to the end of the closing day and floored at 7 days.
- **Redaction runs before scoring.** Email, phone, date of birth, age, gender,
  religion and civil status are stripped, and a report of what was removed is
  stored. The extracted name is display data only and never a matching criterion.
- **Identity is asserted, never inferred.** The only automatic match is
  `user_id`; every HR-uploaded application gets its own candidate. Duplicate
  detection raises a flag and never merges automatically; a merge moves nothing
  and is reversible.
- **A rank is gated.** Only HR-uploaded (`user_id` NULL) or explicitly ACCEPTED
  applications hold a `rank_position`; everyone still keeps a Fit Score. Ranks
  stay contiguous 1..N.
- **A duplicate that failed does not block a retry.** Stale FAILED copies are
  deleted so the re-upload is clean; a PENDING/PARSING/DONE copy blocks.
- **The anon code is global.** `A-####` comes from a global sequence, not a
  per-vacancy count, so two vacancies cannot mint the same code.
- **A screening result names what ran.** `model_version` is
  `php-fallback-regex-1.0`; nothing claims spaCy or Word2Vec.

### 16.4 Deliberate deviations from the reference

- **Calendar convention.** The reference stores naive UTC and converts to Manila
  for calendar questions. Laravel already stores timestamps in
  `config('app.timezone')` (Asia/Manila here), so the port uses that one calendar
  everywhere. There is no UTC/Manila split to get wrong. `BusinessClock` is still
  the only place a calendar question is answered.
- **One screening row per application.** The reference would add a second
  `screening_results` row if a background task ran twice. The queued job can
  retry, so the port uses `updateOrCreate` keyed by `applicant_id`; a retry
  corrects the score rather than leaving two competing rows. The "never
  recompute in place" rule still holds where it was about: a *rerank* writes a
  new `rank_position`, never a new score.
- **No notifications yet.** The reference notified recruiters and candidates on
  lapse/close/cancel. Under the staff-only scope there is no candidate account,
  and the HRIS notification audience model is a decision of its own. `§16.5`
  records it as remaining work rather than shipping a second notification path.
- **Approvals is empty by design.** Only portal applications appear there, and
  the staff-only decision removed the portal. The query is faithful so a later
  portal needs no change; the page says what it is for.
- **The officer is the author.** `audit_log.user_name` now names the HR officer
  who acted, not an integration account, so the old second `hiring_audit` file
  channel is unnecessary and removed.

### 16.5 What still has no screen

The 18 tables and models for these exist and are tested at the schema level.
Only the staff-facing screening workflow above has controllers and views.

| Workflow | Tables ready | Missing |
|---|---|---|
| Applicant portal (self-register, apply, own status) | `candidates.user_id`, `applicant_profiles.user_id` | a public auth surface, which the HRIS does not have |
| Interviews + scorecards | `interviews`, `interview_panelists`, `interview_scorecards`, `interview_reschedule_log` | scheduling UI, panel assignment, authoring UI |
| Talent pool + suggestions | `applicant_profiles.selection_*` | pool screen, suggestion history |
| Plantilla + appointments | `plantilla_items`, `plantilla_item_versions`, `plantilla_hiring_events`, `appointments` | register UI, appointment form, CSC attestation |
| Re-rank consent | `rerank_consent_requests` | consent request/send/answer UI |
| Talent selection (qualify/reject) | `applicant_profiles.selection_status` | the selection screen and its event timeline |
| Duplicate review | `duplicate_flags` | the review screen (`DuplicateDetector` is ready) |

### 16.6 Verification

```bash
php artisan test tests/Unit/RecruitmentScreeningTest.php \
  tests/Unit/RecruitmentLifecycleTest.php \
  tests/Unit/RecruitmentPipelineTest.php \
  tests/Unit/RecruitmentSchemaTest.php \
  tests/Feature/HiringModuleTest.php
```

At the time of writing: **48 tests, 242 assertions, all green.**

- `RecruitmentScreeningTest` — redaction, degrees, date ranges, parser, matcher,
  scorer and explanation, with no database.
- `RecruitmentLifecycleTest` — every transition, the sweep, the grace floor, and
  the refusal wording.
- `RecruitmentPipelineTest` — a real resume through intake → score → rank, plus
  duplicate and failed-retry behaviour.
- `RecruitmentSchemaTest` — the 18 migrations (unchanged from phase 1).
- `HiringModuleTest` — the HTTP routes: role gates, create, publish/hold/resume,
  terminal refusal, upload → DONE → rank 1, private resume download, audit page,
  and a structural assertion that the page renders inside
  `<main class="main-content">` (a child view missing `@section('content')` is
  echoed before the layout and sits behind the fixed sidebar).

Operationally the 18 migrations must be applied (`php artisan migrate`), and the
queue worker and scheduler must run (`composer dev` starts both). The upload
request returns before screening finishes; the vacancy page shows the profile as
**Queued** until the worker picks it up.


