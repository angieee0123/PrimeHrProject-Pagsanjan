<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A requisition: the position being hired for.
 *
 * Ported from `PRIME-HRIS-RSP-V5/prime-hris-backend/app/models/__init__.py`
 * (`class Vacancy`) — the native port, staff-only scope. See
 * `docs/hiring-backend-native-port.md`.
 *
 * The six-state lifecycle (DRAFT / OPEN / ON_HOLD / LAPSED / CLOSED / CANCELLED)
 * is a rule owned by `App\Services\Recruitment\VacancyLifecycleService` in a
 * later phase, not by an enum here: LAPSED is set by a sweep and reversed by a
 * human, so the legal moves belong in one place rather than in a column type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('position_title');
            $table->string('place_of_assignment');
            $table->string('plantilla_item_no', 100)->nullable();
            $table->smallInteger('salary_grade')->nullable();
            $table->decimal('monthly_salary', 10, 2)->default(0);

            // Quality standards (CSC QS) — the qualification bar the ranking is
            // measured against.
            $table->string('qs_education');
            $table->smallInteger('qs_training_hours')->default(0);
            $table->decimal('qs_experience_years', 4, 1)->default(0);
            $table->string('qs_eligibility');

            $table->string('competency')->nullable()->default('N/A');
            // Required skills and the weighted fit formula. JSON, not child
            // tables: both are always read whole and neither is queried by key.
            $table->json('required_skills');
            $table->json('weights');
            $table->text('instructions')->nullable();

            // ── Lifecycle (§10 of the port plan) ────────────────────────────
            // OPEN is the only public state. LAPSED is automatic and
            // reversible; CLOSED and CANCELLED are terminal.
            $table->string('status', 20)->default('OPEN');

            // Public-facing post date. RESETS on reopen so a revived
            // requisition sorts as fresh.
            $table->date('posting_date');
            $table->date('closing_date');
            // Internal audit date. Set once, NEVER reset — this is the field
            // that answers "how long has this requisition really been open?"
            // once a reopen has moved posting_date.
            $table->date('requisition_open_date')->nullable();

            // Candidate-facing grace window: the detail page keeps serving the
            // "no longer accepting" state until this passes, so a bookmarked
            // link never dead-ends. Stored so an in-flight position keeps the
            // window it was given even if the constant is retuned.
            $table->dateTime('grace_period_expires_at')->nullable();

            // Lifecycle timestamps — the audit trail reopening must not erase.
            $table->dateTime('lapsed_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('reopened_at')->nullable();
            $table->integer('reopen_count')->default(0);
            // Optimistic concurrency token for rehire: a plan approved against
            // one ranking is refused if the ranking moved underneath it.
            $table->integer('ranking_version')->default(0);
            $table->string('close_reason')->nullable();

            // Authorized headcount. A count check under a row lock enforces the
            // cap; a UNIQUE index cannot express "at most N".
            $table->integer('slots')->default(1);

            // When DRAFT became OPEN — distinct from posting_date, which is
            // resettable on reopen.
            $table->dateTime('published_at')->nullable();

            // ON_HOLD. Cleared on resume; its history lives in audit_log, so
            // non-null is a reliable "is currently paused" test.
            $table->dateTime('on_hold_at')->nullable();
            $table->string('hold_note')->nullable();

            // CANCELLED. cancel_reason is a CODE from a controlled list, not
            // free text, so cancellations stay countable by cause. The
            // elaboration is mandatory when the code is OTHER.
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason', 40)->nullable();
            $table->string('cancel_reason_detail')->nullable();

            // True once the grace window has elapsed and the sweep retired the
            // page. Candidate-facing output is identical either side of it;
            // it exists so retirement is an observable event.
            $table->boolean('detail_retired')->default(false);

            // Who to notify when this lapses. Falls back to created_by, then to
            // every HR admin, so a lapse is never silent.
            $table->foreignId('assigned_recruiter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();

            // The lifecycle sweep's hot query: "OPEN positions whose
            // closing_date has passed".
            $table->index(['status', 'closing_date'], 'idx_vacancies_status_closing');
            // The retirement sweep: LAPSED/CLOSED rows inside their window.
            $table->index(['detail_retired', 'grace_period_expires_at'], 'idx_vacancies_grace_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
