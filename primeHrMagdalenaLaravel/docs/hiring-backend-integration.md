# Hiring backend integration (PRIME-HRIS recruitment → HRIS Blade)

> **Status: retired (historical record).** This design has been replaced by the
> native port in `docs/hiring-backend-native-port.md`. The FastAPI service is no
> longer contacted, `PrimeHrisClient` and `PrimeHrisFailure` are deleted, and the
> `PRIME_HRIS_*` environment variables have been removed. Everything below
> records what the HTTP integration did and why it was chosen at the time; it is
> kept so the migration decisions stay auditable.

>
> The two things it does **not** cover are operational, not code: the hiring
> service must be running, and the dedicated service account must exist in its
> `users` table. Until both are true the module renders its "not connected"
> state rather than a broken page.

---

## 1. Decision

The FastAPI hiring backend is **kept as-is and run alongside** the HRIS. Laravel
does **not** port its logic and does **not** call it from the browser. Instead,
Laravel talks to it **server-to-server** through a single HTTP client service
class, and Blade renders whatever that service returns.

This is the same pattern the HRIS already uses for LLM providers
(`app/Services/AiChatService.php`), and it follows the project convention
*"Controllers stay thin; business logic lives in `app/Services/`."*

```
Blade view
  ← data from
Controller (thin)  ── calls ──→  App\Services\PrimeHrisClient
                                      ├─ cached JWT (from /api/auth/login)
                                      ├─ Http::withToken(...)->get/post(http://<host>:8001/api/...)
                                      └─ error / degradation policy
```

Why this way:

- Only some endpoints are wanted, so a full port (or even a partial one) is wasted
  work and a second copy of rules that can drift.
- Server-side calls mean **CORS never applies** and the JWT never reaches the
  browser.
- The hiring backend's auth, RBAC, and audit logging stay authoritative for
  recruitment.

---

## 2. What the backend is (facts the integration depends on)

Read from `PRIME-HRIS-RSP-V5/prime-hris-backend`:

| Fact | Where |
|---|---|
| FastAPI app, all routes under `/api` | `app/main.py:56-81` |
| Run locally: `uvicorn app.main:app --reload --port 8001` | `app/main.py:1-5` |
| Health check: `GET /api/health` (no auth) | `app/main.py:106` |
| Auth: JWT HS256 in `Authorization: Bearer`, from `POST /api/auth/login` | `app/api/auth.py:29`, `app/core/security.py:28` |
| Roles: `HR_ADMIN`, `HR_STAFF`, `AUDITOR`, `APPLICANT` | `app/core/security.py:13-18` |
| Token lifetime `JWT_EXPIRES_MINUTES`, default 480 | `app/core/config.py:27` |
| RBAC guard `require_roles(...)` on every non-public route | `app/api/deps.py:31-42` |
| CORS allows a **single** origin (`FRONTEND_ORIGIN`, default `localhost:5173`) | `app/main.py:48-54` |
| Uploads on the API host disk, served only through auth-guarded `FileResponse` | `.env.example:12-20` |
| DB is separate (`prime_hris`), its own `users` table | `.env.example:5`, `app/models/__init__.py:44` |
| Startup refuses to serve if the DB is behind the migration head | `app/main.py:94`, `app/core/schema_guard.py` |

Everything except `/api/health`, `/api/auth/*`, and `GET /api/vacancies/public`
+ `GET /api/vacancies/public/{id}` requires a valid token. Therefore Laravel must
authenticate as a hiring-backend user.

---

## 3. Prerequisites

- [ ] Run the hiring backend as its own process/service, reachable from the
      Laravel host (default `http://127.0.0.1:8001`). Its DB must exist and be
      migrated (`alembic upgrade head`) — the schema guard refuses to start
      otherwise.
- [ ] Decide the base URL and record it in Laravel config (never hardcode).
- [ ] Create a **dedicated service account** in the hiring backend's `users`
      table (do not reuse a person's login). Its role caps everything the
      integration can do — see §5.
- [ ] Decide which endpoints are actually wanted (§6).

---

## 4. Configuration changes

### 4.1 `.env` (Laravel)

```dotenv
PRIME_HRIS_BASE_URL=http://127.0.0.1:8001
PRIME_HRIS_SERVICE_EMAIL=hrmo-integration@pagsanjan.gov.ph
PRIME_HRIS_SERVICE_PASSWORD=change-me
PRIME_HRIS_TIMEOUT=30
```

### 4.2 `config/services.php`

Add a block beside the existing `groq` entry (`config/services.php:38-41`):

```php
'prime_hris' => [
    'base_url' => env('PRIME_HRIS_BASE_URL', 'http://127.0.0.1:8001'),
    'email'    => env('PRIME_HRIS_SERVICE_EMAIL'),
    'password' => env('PRIME_HRIS_SERVICE_PASSWORD'),
    'timeout'  => (int) env('PRIME_HRIS_TIMEOUT', 30),
],
```

---

## 5. Identity: the service account

A single row in the hiring backend's `users` table that Laravel signs in as.
Because `require_roles` checks that user's role (`app/api/deps.py:31-40`), the
chosen role is a hard ceiling:

| Role | What the integration could then do |
|---|---|
| `HR_ADMIN` | Everything: create/publish/close/lapse vacancies, upload & screen, rankings, audit |
| `HR_STAFF` | Read ranks/approvals, upload resumes on existing vacancies; no vacancy mutations |
| `AUDITOR` | Read-only, audit log |

**Consequences to accept deliberately:**

- **Audit attribution collapses.** The backend writes `user_name` from the
  authenticated account (`app/api/deps.py:45-56`), so every Laravel-originated
  action is logged there as the service account. Per-officer attribution is kept
  on this side, in the `hiring_audit` channel
  (`storage/logs/hiring_audit-*.log`, 90 days) — every write and every resume
  preview the module performs is written there with the officer's id, address
  and roles. The two logs answer different questions and neither replaces the
  other: the service's says what happened to a vacancy, this one says who did it.
- **One role for the whole integration.** Per-action roles need multiple
  accounts.
- **Credential custody.** The password lives in Laravel config/env and can create
  or close positions. Rotating it (or the backend's `JWT_SECRET`) revokes the
  integration in one step.
- **Token expiry.** The client must re-login on 401 or near `JWT_EXPIRES_MINUTES`.

---

## 6. Endpoints to expose (pick the wanted subset)

Public / no auth:

- `GET /api/health`
- `GET /api/vacancies/public`
- `GET /api/vacancies/public/{vacancy_id}`

Authenticated (`HR_ADMIN` / `HR_STAFF` / `AUDITOR` unless noted):

- `GET /api/vacancies`, `GET /api/vacancies/{id}`, `GET /api/vacancies/archive`
- `POST /api/vacancies` (HR_ADMIN), `POST /api/vacancies/{id}/publish|hold|resume|cancel|close|reopen` (HR_ADMIN)
- `POST /api/applications/{vacancy_id}/resumes` (multipart; HR_ADMIN/HR_STAFF)
- `GET /api/applications/{vacancy_id}/profiles`
- `GET /api/applications/profiles/{profile_id}/resume` (blob)
- `GET /api/rankings`, rankings by vacancy, `POST .../rerank`
- `GET /api/approvals`, `POST /api/approvals/{id}/accept|disapprove`
- `GET /api/dashboard`
- `GET /api/audit` (HR_ADMIN/AUDITOR)

**Mark the wanted set here before implementation:**

- [x] `GET /api/health` — not proxied; the module has no "test connection"
      button, and a failed call already distinguishes "unreachable" from
      "refused" without a separate probe.
- [x] `GET /api/vacancies` (+ `?status=`), `GET /api/vacancies/{id}`,
      `GET /api/vacancies/archive` (+ `?reason=`)
- [x] `POST /api/vacancies` (HR_ADMIN) — the create form
- [x] `POST /api/vacancies/{id}/publish|hold|resume|cancel|close|reopen` (HR_ADMIN)
- [x] `POST /api/vacancies/{vacancy_id}/resumes` (multipart; HR_ADMIN/HR_STAFF)
- [x] `GET /api/vacancies/{vacancy_id}/profiles`
- [x] `GET /api/vacancies/profiles/{profile_id}/resume` (blob, proxied)
- [x] `GET /api/vacancies/{vacancy_id}/rankings`, `GET /api/rankings`
- [x] `GET /api/approvals` (+ `?review_status=`),
      `POST /api/approvals/{id}/accept|disapprove`
- [x] `GET /api/dashboard`
- [x] `GET /api/audit` (HR_ADMIN/AUDITOR)
- [x] `GET /api/auth/me` — shown on the overview so the service account's role,
      which is the ceiling on everything here, is visible rather than inferred
      from a 403.

Deliberately **not** wired: `PUT /api/vacancies/{id}` (editing a posting's
terms), and the interview, scorecard, plantilla, appointment, talent-pool,
rerank-consent, candidate, duplicate and notification endpoint families. Those
are separate workflows with their own surfaces; each would be its own decision
rather than a line in this list, and the backend's own UI still covers them.

---

## 7. The service class wrapper

New file: `app/Services/PrimeHrisClient.php`. Responsibilities:

1. **Session.** `login()` → `POST /api/auth/login` with the service account;
   cache the returned JWT (`Cache::put`) under a key, TTL below the token
   lifetime. On a 401, re-login once and retry. Never log the token or password.
2. **Typed methods**, one per endpoint, replacing raw paths with verbs, e.g.
   `publicVacancies()`, `vacancy(string $id)`, `listVacancies()`,
   `createVacancy(array $data)`, `uploadResumes(string $vacancyId, array $files)`,
   `rankings(?string $vacancyId)`, `acceptApplication(string $id)`, `dashboard()`.
3. **Error policy** (mirror `AiChatService`): every method returns `?array` and
   records *why* it returned nothing on `lastFailure()` — never an empty list,
   which would read as "no vacancies". The reasons are kept distinct because
   they call for different pages: `not_configured`, `unauthenticated`,
   `not_permitted`, `rejected` (the backend's own `detail`, carried through
   verbatim) and `unreachable` (the only retryable one). A 403 is surfaced as
   "not permitted", not as an outage.
4. **Transient (non-cached) calls.** Never cache vacancy/ranking reads that HR
   acts on — the backend owns that state.
5. **File proxying.** Resume uploads are multipart; resume downloads are blob
   responses streamed through the wrapper and re-served by Laravel (never linked
   to the backend directly).

The contract is uniform on purpose. A `null`-for-reads / throw-for-writes split
would make every caller remember which half it was in, and both halves need the
same message: the overview renders it as a banner, the write endpoints flash it.

Identifiers taken from the URL are `rawurlencode`d into a single path segment
before they reach the backend, so a crafted id cannot climb to another endpoint.

Suggested shape (illustrative; the implemented contract is in §14):

```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PrimeHrisClient
{
    public function publicVacancies(): ?array
    {
        return $this->request('get', '/api/vacancies/public');
    }

    public function createVacancy(array $data): ?array
    {
        return $this->request('post', '/api/vacancies', $data);
    }

    private function request(string $method, string $path, array $data = [], bool $retry = true): ?array
    {
        $response = Http::timeout(config('services.prime_hris.timeout'))
            ->withToken($this->token())
            ->{strtolower($method)}($this->url($path), $data);

        if ($response->status() === 401 && $retry) {
            $this->forgetToken();
            return $this->request($method, $path, $data, retry: false);
        }

        return $response->successful() ? $response->json() : null;
    }

    // login()/token()/forgetToken()/url() omitted
}
```

---

## 8. Routing and controllers

Expose the wanted data to Blade through HRIS routes, gated by the HRIS's own
roles (`EnsureRoleForArea` / `hasRole`). The module sits on the existing
`/admin/recruitment` prefix, which already means "admin or HR" — see
`EnsureRoleForArea::AREA_ROLES`.

- Thin controllers that inject `PrimeHrisClient`, call one method, and return a
  Blade view or JSON.
- Do **not** add new valid HRIS roles. `applicant` is not in the allowed set
  (`employee`/`hr`/`admin`/`mayor`), so any applicant-facing portal is out of
  scope for this integration and would be a separate decision.
- Writes that the backend restricts to `HR_ADMIN` are restricted to the HRIS
  `admin` role as well (`HiringController::requireManager`), and the lifecycle
  buttons are rendered only for the moves the current status allows.

The implemented route file is `routes/web.php`'s Recruitment block:

```php
Route::middleware(['auth'])->prefix('admin/recruitment')->group(function () {
    Route::get('/', [HiringDashboardController::class, 'index'])->name('admin.recruitment');

    Route::get('/vacancies', [$vacancies, 'index'])->name('admin.recruitment.vacancies');
    Route::get('/vacancies/create', [$vacancies, 'create'])->name('admin.recruitment.create');
    Route::post('/vacancies', [$vacancies, 'store'])->name('admin.recruitment.store');
    Route::get('/vacancies/archive', [$vacancies, 'archive'])->name('admin.recruitment.archive');
    Route::get('/vacancies/{id}', [$vacancies, 'show'])->name('admin.recruitment.show');

    Route::post('/vacancies/{id}/{transition}', ...);   // publish|hold|resume|cancel|close|reopen
    Route::post('/vacancies/{id}/resumes', [$vacancies, 'uploadResumes']);

    Route::get('/rankings', [$applicants, 'rankings']);
    Route::get('/approvals', [$applicants, 'approvals']);
    Route::post('/approvals/{id}/accept', [$applicants, 'accept']);
    Route::post('/approvals/{id}/disapprove', [$applicants, 'disapprove']);
    Route::get('/resumes/{id}', [$applicants, 'resume']);

    Route::get('/audit', [$audit, 'index']);
});
```

(The real file declares each transition as its own route so the URL names the
move; the loop above is a summary, not the code.)

---

## 9. Blade usage

- Controllers pass plain arrays/objects from `PrimeHrisClient` to the view.
- Blade **renders only**; it never calls the API and never holds the token.
- Follow the HRIS's existing table/card styling and the theme variables — no
  colour literals (CLAUDE.md "Theming").
- A failed call renders an empty/degraded state with a clear message, not a
  broken page.

The implemented views live in `resources/views/admin/recruitment/` and reuse the
existing system rather than inventing a look: `glass-shell`, `stats-grid` /
`stat-card`, `filter-card`, `table-section` / `payroll-table`, `tab-nav` /
`tab-link`, `badge-status` (through `<x-hiring-status>`), and the recruitment
module's own `job-grid` / `job-card` cards. The one stylesheet they add to is
`resources/css/admin/adminRecruitment.css`, which was already loaded by the admin
layout; its new rules use theme variables only.

Three presentation decisions worth keeping:

- **Status vocabulary lives in `App\Services\HiringPresenter`.** The API sends
  codes (`ON_HOLD`, `NOT_QUALIFIED`), never labels; one map means the archive and
  the detail page cannot call the same state two different things. An unknown
  code is headline-cased in the neutral badge rather than dropped.
- **Lifecycle actions are `<details>` forms, not modals.** Each move needs at
  most a note or a date, a disclosure works without JavaScript, and it cannot
  leave a half-filled dialog on screen after a validation redirect. Destructive
  ones use the app's own `data-confirm` dialog (shared/confirmDialog.js).
- **A failure is never drawn as zeroes.** "0 open vacancies" is a claim; an
  unreachable service renders `partials/unavailable.blade.php` instead, whose
  copy follows the failure kind and whose "Try again" only appears for the kind
  that retrying could fix.

---

## 10. Security notes

- The service-account password and JWT live only in Laravel config/env and cache.
  Never echo them to a view or a log.
- Do not paste raw backend URLs into pages; route everything through the client.
- Resume downloads go through the wrapper (bytes), because the backend serves
  them only via auth-guarded `FileResponse` — there is no static path to link.
- If browser-side calls are ever considered instead: the backend's CORS is a
  single origin (`app/main.py:48-54`) and would have to change, and the token
  would land in the page. Avoid this.

Two additions the implementation makes:

- The proxied resume response carries `X-Content-Type-Options: nosniff`,
  `Cache-Control: private, no-store`, and a `Content-Disposition` built from a
  `basename()`d filename — a bearer-token file must not be sniffed into
  something executable, must not sit in a shared machine's cache, and its name
  must not be able to inject a header.
- The JWT is cached under a key derived from the base URL, so re-pointing the
  integration cannot present the old host's token to the new one.

---

## 11. Testing

- Unit-test `PrimeHrisClient` with `Http::fake()`: login flow, token caching,
  401 re-login-once, 403 vs 5xx handling, timeout degradation.
- Feature-test the HRIS routes with the client faked, asserting role gating and
  that the view renders returned rows.
- Note the HRIS's `RefreshDatabase` is broken for MySQL-only migrations
  (CLAUDE.md "Testing"); build only the tables a test touches, as
  `tests/Unit/AiFileResolverTest.php` does. `PrimeHrisClient` tests need no HRIS
  tables at all.

Implemented as:

```bash
php artisan test tests/Unit/PrimeHrisClientTest.php tests/Feature/HiringModuleTest.php
```

`PrimeHrisClientTest` needs no tables. `HiringModuleTest` builds only the four
tables the admin chrome itself reads on any page (`users`, `notifications`,
`site_contents`, `system_ai_settings`) — the module stores nothing locally.

---

## 12. Implementation checklist

- [ ] Backend running, DB migrated, reachable from Laravel. — **operational,
      not done by this change**: run `uvicorn app.main:app --port 8001` and
      `alembic upgrade head` on the API host.
- [ ] Dedicated service account created; role chosen and recorded. —
      **operational**: create it in the hiring backend's `users` table. The role
      is the ceiling on everything below; `HR_ADMIN` is what the create and
      lifecycle endpoints need.
- [x] `config/services.php` + `.env` entries added.
- [x] `app/Services/PrimeHrisClient.php` written (login, cache, retry, verbs).
- [x] Wanted endpoints implemented as typed methods.
- [x] HRIS routes + thin controllers added and role-gated.
- [x] Blade views render the data (theme variables, no literals).
- [x] `Http::fake()` unit tests + route feature tests.
- [x] Confirm resume/upload proxying if those endpoints are in scope.
- [x] Run `php artisan test` and the project lint/typecheck.

---

## 13. Out of scope (recorded, not planned)

- Porting any hiring-backend logic into Laravel (that was the rejected option 1/2).
- An applicant-facing portal inside the HRIS (`applicant` is not a valid HRIS
  role).
- Changing the hiring backend's CORS or auth model.
- Sharing identity between the two `users` tables.
- Editing a published vacancy's terms (`PUT /api/vacancies/{id}`), and the
  interview, scorecard, plantilla, appointment, talent-pool, rerank-consent,
  candidate, duplicate and in-app-notification families. Each is a workflow of
  its own and none is needed to screen and shortlist; the backend's own UI still
  covers them.

---

## 14. What was built

| Concern | Where |
|---|---|
| HTTP client: sign-in, token cache, retry, typed verbs, file proxying | `app/Services/PrimeHrisClient.php` |
| Why a call returned nothing, in five distinct kinds | `app/Services/PrimeHrisFailure.php` |
| Status labels, badges, dates, money, weights — the shared vocabulary | `app/Services/HiringPresenter.php` |
| Shared gate (writes are `admin`-only) and the officer log | `app/Http/Controllers/Hiring/HiringController.php` |
| Overview | `HiringDashboardController` → `admin/recruitment/index.blade.php` |
| List, archive, detail, create, lifecycle, upload | `HiringVacancyController` → `vacancies` / `archive` / `vacancy` / `create` views |
| Rankings, approvals, resume proxy | `HiringApplicantController` → `rankings` / `approvals` views |
| Activity log (admin only) | `HiringAuditController` → `audit` view |
| Config | `config/services.php` (`prime_hris`), `.env.example` |
| Officer attribution log | `config/logging.php` (`hiring_audit`, 90 days) |
| Routes | `routes/web.php`, the Recruitment block |
| Styles | `resources/css/admin/adminRecruitment.css` (new "Hiring module" section) |
| Tests | `tests/Unit/PrimeHrisClientTest.php`, `tests/Feature/HiringModuleTest.php` |

**Role gating.** `/admin/recruitment` is inside the `admin` area, so
`EnsureRoleForArea` limits it to admin and HR. Within that, the lifecycle moves,
vacancy creation and the activity log are `admin` only — mirroring the backend's
HR_ADMIN-only endpoints — while screening work (resume upload,
accept/disapprove, resume preview) is open to HR staff, which is what the
backend's HR_STAFF role allows. The controllers enforce this; the hidden buttons
are tidiness.

**The one thing the integration cannot fix.** Every action taken here is
attributed in the hiring backend to the single service account, because that is
the account that authenticates. The officer behind each action is written to the
`hiring_audit` channel by `HiringController::recordOfficerAction()` — including
resume previews, because opening somebody else's personal document is worth an
entry. The activity page says this on the page itself rather than leaving a
reader to assume the log names the officer.

**Filtering, and why it is client-side.** The vacancy, archive, approvals and
activity lists are fetched whole and narrowed in PHP. The tabs on those pages
show how many rows are in *every* state, not just the one being viewed, and a
server-filtered response can only describe itself. A municipal recruitment pool
is dozens of positions; the whole list is the cheap side of that trade.

