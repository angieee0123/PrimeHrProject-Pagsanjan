# Render Deployment Documentation

This documents how PrimeHR (Laravel) was deployed to Render, why each decision was made, every bug hit along the way, and what to do next time something needs to change.

## Live deployment

| | |
|---|---|
| App URL | https://primehr-laravel.onrender.com |
| Web service dashboard | https://dashboard.render.com/web/srv-d93vcpmq1p3s73aeh1eg |
| Database dashboard | https://dashboard.render.com/d/dpg-d947grhkh4rs73elhjgg-a |
| Branch deployed | `akoCJohn-branch` (auto-deploys on every push) |
| Web plan | Free |
| Database plan | Free Postgres 16 — **expires 2026-08-03** (30-day free trial, see Known Limitations) |
| Region | Oregon (US West) for both web + DB |

---

## 1. Why Docker

The original assumption (baked into the repo's pre-existing `render.yaml`) was that Render could run this as a native PHP web service (`runtime: php`). That's wrong — **Render has no native PHP runtime**. Calling `POST /v1/services` with `runtime: php` fails outright:

```
valid runtimes are: [docker, elixir, go, node, python, ruby, rust, image]
```

So the app is deployed as a **Docker** web service instead, built from `primeHrMagdalenaLaravel/Dockerfile`.

## 2. The Dockerfile

Two-stage build: Node builds the frontend assets, then a PHP image runs the app.

```dockerfile
FROM node:20-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.4-cli
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev unzip zip libxml2-dev libcurl4-openssl-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring curl xml zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link

EXPOSE 10000
CMD php artisan migrate --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
```

Why each non-obvious piece is there (each was a real build failure encountered in order):

1. **PHP 8.4, not 8.3.** `composer.lock` was resolved locally with newer symfony packages that require `php >=8.4`, even though `composer.json` only declares `^8.3`. Rather than regenerate the lock file (risk of shifting other dependency versions), the container just uses a PHP version that satisfies what's already locked.
2. **`libonig-dev`.** `docker-php-ext-install mbstring` needs the oniguruma headers; without it, `configure` fails with `Package 'oniguruma' ... not found`.
3. **`gd` + its dependencies (`libpng-dev`, `libjpeg62-turbo-dev`, `libfreetype6-dev`).** `phpoffice/phpspreadsheet` requires `ext-gd`, which wasn't installed originally.
4. **`pdo_pgsql` + `libpq-dev`.** Needed once the database moved from MySQL to Postgres (see below). `pdo_mysql` is left in too since local development still uses MySQL.
5. **`mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views`.** These three directories are gitignored by Laravel convention (only `storage/framework/testing`, `storage/logs`, `storage/app/public`, and `bootstrap/cache` had committed `.gitignore` placeholders keeping them present in git). On a fresh clone they don't exist, so `php artisan optimize` failed with `View path not found` until they were created explicitly at build time.
6. **`php artisan storage:link`.** Also missing from the original setup — see the "Employee photos / file uploads" section below.
7. **`CMD` runs migrate → optimize → serve on every container start.** This is idempotent (migrate does nothing if there's nothing pending) so it's safe on every redeploy/restart, and keeps prod schema in sync automatically.

## 3. Database: MySQL → Postgres

### Why Postgres

Render's native "databases" offering is **Postgres only** — there is no managed MySQL. Three options were on the table:
- Host MySQL externally (e.g. a free host like Filess.io) and point Render's web service at it
- Use Railway (which does have native MySQL) — ruled out because Railway isn't actually free anymore (requires a card, gives a one-time trial credit, then ~$5/mo minimum)
- Convert the app to Postgres and use Render's free Postgres

**Postgres was chosen** to keep everything on one free platform.

### The migration-history problem

Before converting anything, all 77 migrations were replayed from scratch against a local Postgres instance (installed via `brew install postgresql@16` for testing, since Docker wasn't available locally). This surfaced two categories of pre-existing bugs, **unrelated to Postgres** — they'd have broken a fresh MySQL install too:

1. **`2024_01_01_000000_create_leave_balance_seeder_migration.php`** referenced a table called `accrual_rates`, which has **never existed** in this schema (only `leave_accrual_rates` does), and it ran before `employees`/`leave_types_config`/`leave_balances` even existed in migration order. Confirmed via `migrate:status` against the real local MySQL database that this migration had, in fact, never actually run there either — it was dead code. **Fixed by turning its `up()` into a no-op**, with a comment explaining why.

2. **`2024_01_20_000000_create_travel_orders_table.php`** had foreign keys to `employees` and `users`, but ran before `employees` existed in fresh-install order. Worse, a *second* `create_travel_orders_table` migration exists (`2025_01_27_000001`) with a different/expanded schema for the same table — its current file content doesn't match what actually executed historically (it must have been edited after being applied; Laravel only tracks migrations by filename, not content, so this went unnoticed for a real, already-migrated database).

   **Fixed non-destructively**: the foreign key constraints were split out of the 2024 migration (which now just creates plain `unsignedBigInteger` columns) into a new migration, `2026_06_11_000001_add_deferred_foreign_keys_for_fresh_install.php`, which adds the constraints once `employees`/`users` are guaranteed to exist. Crucially, **no migration filenames were renamed** — renaming would have broken already-migrated databases (local MySQL, and any future ones), since Laravel identifies a migration by its filename in the `migrations` table. Changing a migration's *content* is always safe for databases where it already ran; changing its *filename* is not.

### Actual approach: snapshot the real database, don't replay history

Given the migration history can't be trusted to reconstruct the schema faithfully, the real local MySQL database (already correct, running the real app) was used as ground truth instead of trying to perfect 77 migration files:

1. Attempted **`pgloader`** (`brew install pgloader`) to migrate MySQL → Postgres directly — this is the standard purpose-built tool and worked flawlessly against a local Postgres target (schema + data + indexes + FKs + sequences, 0 errors). It failed, however, against Render's actual remote Postgres over the network with `No SNI information found` — a quirk of pgloader's own TLS/SSL client library against Render's connection proxy, not a Postgres issue per se.
2. **Worked around it** with native tools instead: `pg_dump` the already-correct local Postgres (populated by pgloader) to a plain SQL file, then restore it into Render's Postgres via `psql` — both are the officially-supported Postgres clients and handled Render's TLS proxy without issue.
3. One gotcha: `pgloader` puts everything into a schema **named after the source MySQL database** (e.g. `primehrismagdalena`) rather than Postgres's default `public` schema, which Laravel expects. Fixed locally with:
   ```sql
   DROP SCHEMA public CASCADE;
   ALTER SCHEMA primehrismagdalena RENAME TO public;
   ```
4. Render's free Postgres also has **`ipAllowList` blocking all external connections by default** (`null`, not "everywhere") — had to explicitly `PATCH` it to `[{"cidrBlock":"0.0.0.0/0","description":"everywhere"}]` before `psql`/`pgloader` could even reach it from outside Render's network.

Because the dump included the `migrations` table itself, the new Postgres database ended up with the exact same "already ran" bookkeeping as local MySQL — only the two truly-pending migrations (the dead seeder no-op and the new deferred-FK migration) actually executed against it.

### Raw SQL that only works on MySQL

A full sweep of `app/` and `database/migrations` for MySQL-only SQL functions found several call sites that would break at **runtime** (not just migration time) once Postgres was live: `YEAR()`, `MONTH()`, `DAY()`, `DAYOFWEEK()`, `DATE_FORMAT()`, and a MySQL-only `"double quoted string"` literal (Postgres treats double quotes as identifiers, not strings).

Fixed with a small driver-aware helper, `app/Support/SqlCompat.php`, used from `AdminDashboardController`, `LeaveController`, `EmployeeLeaveController`, `EmployeeDashboardController`, and `Api/MobileDashboardController`:

```php
SqlCompat::year($column);        // YEAR(col) on MySQL, EXTRACT(YEAR FROM col) on Postgres
SqlCompat::month($column);
SqlCompat::day($column);
SqlCompat::isWeekend($column);
SqlCompat::isNotWeekend($column); // DAYOFWEEK(col) NOT IN (1,7) vs EXTRACT(DOW FROM col) NOT IN (0,6)
```

All fixed queries were verified via `tinker` directly against the real migrated data on Postgres, not just checked for syntax.

### Migrations that already ran are untouched

`increase_decimal_precision.sql` and `database/scripts/fix_government_shares.php` also contain MySQL-only SQL (backticks, `GROUP_CONCAT`), but neither is referenced anywhere in `app/` or `routes/` — they're one-off maintenance scripts, not part of the runtime path, so they were left alone.

## 4. Environment variables (Render web service)

Set via the Render dashboard / API (see `render.yaml` for the shape, though the actual live service was created directly via Render's API rather than a Blueprint deploy):

| Key | Value |
|---|---|
| `APP_NAME` | `PrimeHR` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | generated fresh for this deployment (not the same as local) |
| `APP_URL` | `https://primehr-laravel.onrender.com` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Render Postgres internal/external host (see DB dashboard → Connect) |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `primehris` |
| `DB_USERNAME` | `primehr_user` |
| `DB_PASSWORD` | see Render dashboard → Environment (not repeated here) |

`GROQ_API_KEY` is **not** currently set — see the chatbot section below.

## 5. Bugs found only after going live

These didn't show up until actually clicking through the deployed site logged in as a real user — `curl`-ing for HTTP 200 alone wasn't enough to catch them.

### Vite manifest crash on the dashboard

Logging in and hitting `/admin/dashboard` returned a 500. With `APP_DEBUG` temporarily flipped to `true` to see the real error (then flipped back off), the cause was:

```
Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest: resources/css/adminChatbot.css.
```

`resources/css/adminChatbot.css` (and `adminPayroll.css`, `adminLeaveAndBenefits.css`, `resources/js/adminAttendance.js`, `resources/js/adminLeaveAndBenefits.js`) all exist on disk and are referenced via `@vite(...)` in various blade views, but were **never added to `vite.config.js`'s `input` array**, so they were silently missing from the production build manifest. Vite's dev server tolerates this; a production build does not. Fixed by adding all five to `vite.config.js`.

### Mixed content: assets loading over `http://` on an `https://` page

After the Vite fix, asset URLs in the rendered HTML were `http://primehr-laravel.onrender.com/build/assets/...` even though the site is served over HTTPS. Render terminates TLS at its edge proxy and forwards plain HTTP to the container, and Laravel doesn't trust `X-Forwarded-Proto` by default — so `@vite()` (and `url()`/`redirect()` generally) generated `http://` URLs. Render's edge does 301-redirect any `http://` asset request to `https://`, and most modern browsers auto-upgrade this kind of mixed content silently, but it's not something to rely on.

Fixed properly in `bootstrap/app.php` by trusting all proxies for forwarded headers (the standard fix for any app behind a PaaS reverse proxy where the edge IP isn't fixed):

```php
$middleware->trustProxies(
    at: '*',
    headers: Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO,
);
```

Verified after redeploy: all asset URLs correctly render as `https://`.

### Employee photos / file uploads not displaying

Two separate problems, found by actually checking a photo `<img>` tag in the browser:

1. **The `public/storage` symlink never existed in the container.** Locally, `php artisan storage:link` was run once, creating a symlink on disk — but that symlink was never committed to git (correctly — it's meant to be generated, not committed) and the Dockerfile never ran the command that generates it. Fixed by adding `php artisan storage:link` to the Docker build.
2. **The actual uploaded files were never in git at all.** `storage/app/public/` is gitignored by Laravel convention (`storage/app/public/.gitignore` contains `*` / `!.gitignore`) — correct practice for user-uploaded content, but it meant none of the ~92MB of existing employee photos, leave attachments, or attendance-correction documents ever reached GitHub or the Docker build. These were force-added (`git add -f`) and committed as a one-time snapshot, **with explicit user sign-off** given they're real employees' personal photos/documents going into permanent git history (repo is private).

This only fixes *existing* data as of that commit. See Known Limitations — new uploads via the live site still won't survive a redeploy.

## 6. Known limitations

- **Free Postgres expires 2026-08-03** (30-day trial). Needs a plan upgrade or a fresh re-migration before then, or the database (and all data in it) disappears.
- **Ephemeral disk.** Render's free web service has no persistent volume. Any file uploaded through the live app (new employee photo, new leave attachment, etc.) is lost on the next redeploy or restart. The one-time snapshot of existing uploads (see above) doesn't change this — it only backfilled what already existed. A real fix requires S3-compatible object storage (e.g. Cloudflare R2's free tier) instead of the `local` filesystem disk.
- **No cron.** `routes/console.php` schedules `leave:process-monthly-accrual` and `leave:process-year-end-carryover`, but nothing runs `schedule:run` on this deployment — Render's free tier has no built-in scheduler. These need to be triggered manually, or via a paid Render Cron Job, until addressed.
- **Cold starts.** The free web service spins down after ~15 minutes idle; the next request pays a 30–60s+ cold-start penalty.
- **`WEB_CONCURRENCY=1`.** Render auto-set this based on the free tier's CPU allocation — only one request is processed at a time.
- **`php artisan serve` is a dev-grade server**, not a production one (no worker pooling, no HTTP/2). It's what's running because it's simplest, not because it's ideal.
- **Region: Oregon (US West) only** — there's no free-tier region closer to Southeast Asia, so real users there pay a full transpacific round trip on every request.
- **`EmployeeChatbotService.php`'s Groq key (`GROQ_API_KEY`) isn't set** as an env var on Render. `ChatbotController.php` (the admin-facing chatbot) has a hardcoded fallback key and works regardless; the employee-facing one may not.
- **`GOVERNMENT CHATBOT/4. web application/chatbot_to_database.py`** (a separate Python/Flask prototype with its own `render.yaml`) is **not deployed anywhere** and is unrelated to the working chatbot — the live app's chatbot is entirely PHP (`ChatbotController` + `EmployeeChatbotService`), calling Groq directly.

## 7. How to redeploy / make changes

Auto-deploy is on: any push to `akoCJohn-branch` on GitHub triggers a new Render deploy automatically. No manual step needed for ordinary code changes.

To change an environment variable: Render dashboard → the web service → **Environment** tab. Changing an env var also triggers a redeploy.

To run a one-off command against production (e.g. checking data, fixing a row): the Render free plan does **not** include SSH shell access. The approach used throughout this deployment was to run `php artisan` commands locally with the production DB env vars passed inline, e.g.:

```bash
DB_CONNECTION=pgsql \
DB_HOST=<render-postgres-host> \
DB_PORT=5432 \
DB_DATABASE=primehris \
DB_USERNAME=primehr_user \
DB_PASSWORD=<see Render dashboard> \
php artisan migrate:status
```

or connect directly with `psql` (get the exact command from the DB dashboard's **Connect** tab).

## 8. Test credentials

Two accounts currently have a **known temporary password** set directly in the production database for testing purposes (not their real passwords — set only to verify login worked end-to-end):

- `admin@gmail.com` — `TempTest123!`
- `jeremypogi@gmail.com` — `TempTest123!`

Change these if real use of the deployed app begins.
