<?php

namespace Tests\Unit;

use App\Models\ApplicantProfile;
use App\Models\Appointment;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\InterviewScorecard;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The recruitment schema, ported from the FastAPI hiring backend.
 *
 * The project's migrations cannot be run by `RefreshDatabase` (2026_04_15's
 * `add_timestamps_to_tables` emits MySQL-only `ON UPDATE CURRENT_TIMESTAMP`, which
 * SQLite rejects), so this test runs **only the 18 recruitment migrations**,
 * after building the one table they depend on by hand. That is the same pattern
 * the rest of the suite uses, and it keeps this test honest: if a migration is
 * not runnable, this fails rather than silently skipping.
 *
 * The assertions are deliberately about the three properties the port plan §10
 * says are load-bearing and easy to lose in translation:
 *   1. NULL-as-distinct uniques (`candidates.user_id`) still read as intended;
 *   2. a soft-deleted interview frees its round, an active duplicate does not;
 *   3. `appointments.live_vacancy_guard` collapses to NULL when disapproved.
 */
class RecruitmentSchemaTest extends TestCase
{
    /** Dependency order — the same order the migration timestamps enforce. */
    private const MIGRATIONS = [
        '2026_11_09_120001_create_vacancies_table',
        '2026_11_09_120002_create_candidates_table',
        '2026_11_09_120003_create_applicant_profiles_table',
        '2026_11_09_120004_create_duplicate_flags_table',
        '2026_11_09_120005_create_rerank_consent_requests_table',
        '2026_11_09_120006_create_screening_results_table',
        '2026_11_09_120007_create_applicant_events_table',
        '2026_11_09_120008_create_interviews_table',
        '2026_11_09_120009_create_interview_panelists_table',
        '2026_11_09_120010_create_interview_scorecards_table',
        '2026_11_09_120011_create_interview_reschedule_log_table',
        '2026_11_09_120012_create_plantilla_items_table',
        '2026_11_09_120013_create_plantilla_item_versions_table',
        '2026_11_09_120014_create_plantilla_hiring_events_table',
        '2026_11_09_120015_create_appointments_table',
        '2026_11_09_120016_create_audit_log_table',
        '2026_11_09_120017_create_retention_settings_table',
        '2026_11_09_120018_create_retention_setting_changes_table',
    ];

    private const TABLES = [
        'vacancies', 'candidates', 'applicant_profiles', 'duplicate_flags',
        'rerank_consent_requests', 'screening_results', 'applicant_events',
        'interviews', 'interview_panelists', 'interview_scorecards',
        'interview_reschedule_log', 'plantilla_items', 'plantilla_item_versions',
        'plantilla_hiring_events', 'appointments', 'audit_log',
        'retention_settings', 'retention_setting_changes',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // The FK target. The recruitment tables reference the HRIS's own users
        // table under the staff-only scope; only the columns this test writes
        // are needed here.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->json('roles');
            $table->timestamps();
        });

        foreach (self::MIGRATIONS as $name) {
            $migration = require database_path("migrations/{$name}.php");
            $migration->up();
        }
    }

    protected function tearDown(): void
    {
        foreach (array_reverse(self::MIGRATIONS) as $name) {
            $migration = require database_path("migrations/{$name}.php");
            $migration->down();
        }

        Schema::dropIfExists('users');

        parent::tearDown();
    }

    private function user(): User
    {
        return User::create([
            'name' => 'HR Officer',
            'email' => 'hr-' . Str::random(8) . '@example.test',
            'password' => 'password',
            'roles' => ['hr'],
        ]);
    }

    private function vacancy(): Vacancy
    {
        return Vacancy::create([
            'position_title' => 'Administrative Officer I',
            'place_of_assignment' => "Mayor's Office",
            'qs_education' => "Bachelor's degree relevant to the job",
            'qs_eligibility' => 'Career Service Professional',
            'posting_date' => '2026-11-01',
            'closing_date' => '2026-11-30',
            'required_skills' => ['records management'],
            'weights' => ['education' => 0.3, 'experience' => 0.7],
        ]);
    }

    private function profile(?Vacancy $vacancy = null): ApplicantProfile
    {
        $candidate = Candidate::create(['full_name' => 'Juan Dela Cruz', 'first_seen_at' => now()]);

        return ApplicantProfile::create([
            'vacancy_id' => ($vacancy ?? $this->vacancy())->id,
            'candidate_id' => $candidate->id,
            'anon_code' => 'ANON-' . Str::random(6),
            'source_file_path' => 'resumes/juan.pdf',
        ]);
    }

    #[Test]
    public function every_ported_table_is_created(): void
    {
        foreach (self::TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    #[Test]
    public function a_vacancy_round_trips_with_a_uuid_key_and_json_casts(): void
    {
        $vacancy = $this->vacancy();

        $this->assertTrue(Str::isUuid($vacancy->id), 'The primary key should be a UUID string.');
        $this->assertSame('OPEN', $vacancy->status, 'A new vacancy defaults to OPEN.');
        $this->assertSame(['records management'], $vacancy->required_skills);
        $this->assertSame(['education' => 0.3, 'experience' => 0.7], $vacancy->weights);
        $this->assertSame(1, $vacancy->slots);
        $this->assertNotNull($vacancy->created_at);
        $this->assertNull(Vacancy::UPDATED_AT, 'The reference table has no updated_at.');
        $this->assertTrue($vacancy->isPublic());
        $this->assertFalse($vacancy->isTerminal());
    }

    #[Test]
    public function a_candidate_without_an_account_coexists_with_many_others(): void
    {
        // The unique index on user_id must treat every NULL as distinct —
        // otherwise the many HR-uploaded candidates would collide.
        Candidate::create(['full_name' => 'No Account A', 'first_seen_at' => now()]);
        Candidate::create(['full_name' => 'No Account B', 'first_seen_at' => now()]);

        $this->assertSame(2, Candidate::count());
    }

    #[Test]
    public function one_account_cannot_own_two_candidates(): void
    {
        $user = $this->user();

        Candidate::create(['full_name' => 'First', 'user_id' => $user->id, 'first_seen_at' => now()]);

        $this->expectException(QueryException::class);

        Candidate::create(['full_name' => 'Second', 'user_id' => $user->id, 'first_seen_at' => now()]);
    }

    #[Test]
    public function a_soft_deleted_interview_frees_its_round_but_an_active_one_does_not(): void
    {
        $profile = $this->profile();

        $first = Interview::create([
            'application_id' => $profile->id,
            'round' => 1,
            'interview_type' => 'PANEL',
            'scheduled_at' => '2026-12-05 09:00:00',
        ]);

        $this->assertSame('SCHEDULED', $first->status);

        // A second *active* round 1 is refused by the functional unique index.
        try {
            Interview::create([
                'application_id' => $profile->id,
                'round' => 1,
                'interview_type' => 'PANEL',
                'scheduled_at' => '2026-12-06 09:00:00',
            ]);
            $this->fail('An active duplicate round should have been refused.');
        } catch (QueryException) {
            // expected
        }

        $first->delete();
        $this->assertSoftDeleted('interviews', ['id' => $first->id]);

        // The soft-deleted row's round is NULL in the index expression, so the
        // number is free for reuse (gap-fill).
        $replacement = Interview::create([
            'application_id' => $profile->id,
            'round' => 1,
            'interview_type' => 'PANEL',
            'scheduled_at' => '2026-12-07 09:00:00',
        ]);

        $this->assertSame(1, $replacement->round);
    }

    #[Test]
    public function the_live_vacancy_guard_collapses_to_null_when_disapproved(): void
    {
        $profile = $this->profile();
        $vacancy = $profile->vacancy;

        $base = [
            'application_id' => $profile->id,
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $profile->candidate_id,
            'appointee_name' => 'Juan Dela Cruz',
            'position_title' => $vacancy->position_title,
            'nature' => Appointment::NATURE_ORIGINAL,
            'effective_date' => '2026-12-01',
        ];

        $live = Appointment::create($base + ['attestation_status' => Appointment::ATTESTATION_ATTESTED]);
        $this->assertSame($vacancy->id, $live->fresh()->live_vacancy_guard);

        $disapproved = Appointment::create($base + ['attestation_status' => Appointment::ATTESTATION_DISAPPROVED]);
        $this->assertNull($disapproved->fresh()->live_vacancy_guard);

        $this->assertTrue($live->fresh()->isLive());
        $this->assertFalse($disapproved->fresh()->isLive());
    }

    #[Test]
    public function a_generated_column_is_never_mass_assigned(): void
    {
        $appointment = new Appointment();

        $this->assertFalse(
            $appointment->isFillable('live_vacancy_guard'),
            'Writing a generated column is an error, so it must not be mass-assignable.'
        );
    }

    #[Test]
    public function the_scorecard_average_is_computed_not_stored(): void
    {
        $profile = $this->profile();
        $interview = Interview::create([
            'application_id' => $profile->id,
            'round' => 1,
            'interview_type' => 'PANEL',
            'scheduled_at' => '2026-12-05 09:00:00',
        ]);

        $scorecard = InterviewScorecard::create([
            'interview_id' => $interview->id,
            'interviewer_id' => $this->user()->id,
            'competency_scores' => [
                ['key' => 'integrity', 'rating' => 5],
                ['key' => 'communication', 'rating' => 4],
                ['key' => 'no_answer', 'rating' => null],
            ],
        ]);

        $this->assertSame(4.5, $scorecard->averageScore());
        $this->assertNull((new InterviewScorecard())->averageScore());
    }

    #[Test]
    public function a_consent_request_never_serialises_its_token(): void
    {
        $profile = $this->profile();

        $consent = \App\Models\RerankConsentRequest::create([
            'candidate_id' => $profile->candidate_id,
            'target_vacancy_id' => $this->vacancy()->id,
            'source_profile_id' => $profile->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertArrayNotHasKey('token', $consent->toArray());
        $this->assertSame('PENDING', $consent->status);
        $this->assertSame('COPY', $consent->mode);
    }

    #[Test]
    public function a_retention_change_records_a_null_previous_value_only_on_first_set(): void
    {
        $setting = \App\Models\RetentionSetting::create([
            'jurisdiction_code' => 'DEFAULT',
            'retention_months' => 24,
        ]);

        $this->assertSame('DEFAULT', $setting->jurisdiction_code);

        $first = \App\Models\RetentionSettingChange::create([
            'setting_id' => $setting->id,
            'jurisdiction_code' => 'DEFAULT',
            'old_retention_months' => null,
            'new_retention_months' => 24,
            'at' => now(),
        ]);

        $this->assertNull($first->old_retention_months);
        $this->assertIsInt($first->id);
    }
}
