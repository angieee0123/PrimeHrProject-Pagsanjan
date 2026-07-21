# Fresh-install migration repair — RESOLVED

> **Status: fixed & verified.** `php artisan migrate:fresh` now runs to completion
> on a clean MySQL database and reproduces the live schema exactly (all 50 tables,
> 0 column differences aside from a set of harmless unused `created_at/updated_at`
> columns noted at the bottom). Verified by diffing a throwaway fresh-migrated
> database against the live `primehrismagdalena` schema. The live/dev database was
> never touched.

## What was broken

The migration history had accumulated several classes of fresh-install failures
(the live DB survived only because it was built up incrementally over time):

1. **Duplicate `Schema::create('travel_orders')`** — two migrations created the
   same table. *(Fixed earlier: removed the stale `2025_01_27_000001`.)*
2. **Mis-ordered ALTERs (13)** — `ALTER`/data migrations timestamped *before* the
   `CREATE` of the table they touch (the CREATE migrations were re-scaffolded to
   later 2026 dates, e.g. `employees` at `2026_04_13`).
3. **Duplicate-column ALTER** — `add_admin_verification_columns_to_trainings`
   re-added `timestamps()` the current `trainings` CREATE already ships.
4. **Missing CREATE migrations (6 tables)** — `personal_access_tokens` (Sanctum),
   `user_settings`, `chat_history`, `employee_loans`, `payroll_deductions`,
   `deduction_loan_items` existed in the live DB but had **no** CREATE migration in
   the repo, so a fresh install was missing them entirely.

## How it was fixed (non-disruptive to existing databases)

- **Guards on the mis-ordered migrations** — each now returns early via
  `Schema::hasTable()` (and `hasColumn()` where needed) so on a fresh install it
  skips (its table doesn't exist yet), and on an existing DB it's already run.
  Also deferred the `employee_requests.employee_id` FK and made the deferred-FK
  migration SQLite-safe.
- **`2026_07_12_000000_apply_deferred_schema_changes`** — a catch-up migration,
  dated after every CREATE, that re-applies the net effect of the deferred ALTERs
  (attendance columns/types, accredited-hours-log tracking columns, the
  leave_types_config PK swap + leave_accrual_rates FK swap, leave decimal
  precision, deduction_types flag). Every step is idempotent, so it's a **no-op**
  on the existing DB. MySQL-targeted; skipped on SQLite.
- **`2026_07_12_000001_create_missing_tables`** — recreates the 6 missing tables
  from the live schema, each guarded by `hasTable()` (no-op where they exist).

Both new migrations were verified to re-run cleanly as no-ops against a database
that already has the final schema (simulating the live DB), so they are safe to
`php artisan migrate` on the existing install.

## Remaining cosmetic note (pre-existing, harmless)

~11 tables (address/contact/education/… 201-file sub-tables, plus `employees`)
have `created_at/updated_at` in their CREATE migrations but **not** in the live
DB. A fresh install therefore gets these (unused) timestamp columns while the
live DB lacks them. This is a pre-existing CREATE-vs-live drift, not introduced
here, and is harmless (the models don't write them). Left as-is deliberately —
removing `timestamps()` from those CREATE migrations would be the alternative if
exact parity is ever required.

## Verify

```bash
# throwaway DB, never the real one
php -r '$p=new PDO("mysql:host=127.0.0.1","root","admin");$p->exec("DROP DATABASE IF EXISTS scratch");$p->exec("CREATE DATABASE scratch");'
DB_DATABASE=scratch php artisan migrate:fresh --force   # runs clean, end to end
```
