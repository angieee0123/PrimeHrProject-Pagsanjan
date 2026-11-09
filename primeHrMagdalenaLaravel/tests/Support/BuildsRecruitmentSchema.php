<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Builds only the recruitment tables a test touches.
 *
 * The project's full migration set cannot run on the test connection (2026_04_15
 * emits MySQL-only `ON UPDATE CURRENT_TIMESTAMP`, which SQLite rejects), so the
 * suite's established pattern is to build the handful of tables each test needs
 * by hand. This trait runs the real recruitment migrations rather than
 * hand-writing their DDL, so a migration that is not runnable fails the test
 * instead of being silently skipped.
 *
 * Callers name the tables they need in dependency order — the same order the
 * migration timestamps enforce.
 */
trait BuildsRecruitmentSchema
{
    /** @var array<string, string> migration name => table */
    private const RECRUITMENT_MIGRATIONS = [
        '2026_11_09_120001_create_vacancies_table' => 'vacancies',
        '2026_11_09_120002_create_candidates_table' => 'candidates',
        '2026_11_09_120003_create_applicant_profiles_table' => 'applicant_profiles',
        '2026_11_09_120004_create_duplicate_flags_table' => 'duplicate_flags',
        '2026_11_09_120005_create_rerank_consent_requests_table' => 'rerank_consent_requests',
        '2026_11_09_120006_create_screening_results_table' => 'screening_results',
        '2026_11_09_120007_create_applicant_events_table' => 'applicant_events',
        '2026_11_09_120008_create_interviews_table' => 'interviews',
        '2026_11_09_120009_create_interview_panelists_table' => 'interview_panelists',
        '2026_11_09_120010_create_interview_scorecards_table' => 'interview_scorecards',
        '2026_11_09_120011_create_interview_reschedule_log_table' => 'interview_reschedule_log',
        '2026_11_09_120012_create_plantilla_items_table' => 'plantilla_items',
        '2026_11_09_120013_create_plantilla_item_versions_table' => 'plantilla_item_versions',
        '2026_11_09_120014_create_plantilla_hiring_events_table' => 'plantilla_hiring_events',
        '2026_11_09_120015_create_appointments_table' => 'appointments',
        '2026_11_09_120016_create_audit_log_table' => 'audit_log',
        '2026_11_09_120017_create_retention_settings_table' => 'retention_settings',
        '2026_11_09_120018_create_retention_setting_changes_table' => 'retention_setting_changes',
    ];

    /**
     * @param  array<int, string>  $tables  tables to create, in dependency order
     */
    protected function buildRecruitmentSchema(array $tables): void
    {
        $this->buildUsersTable();

        foreach (self::RECRUITMENT_MIGRATIONS as $migration => $table) {
            if (! in_array($table, $tables, true) || Schema::hasTable($table)) {
                continue;
            }

            $instance = require database_path("migrations/{$migration}.php");
            $instance->up();
        }
    }

    /**
     * The HRIS `users` table, which every recruitment foreign key points at.
     *
     * Only the columns these tests write are needed; the real migration carries
     * far more.
     */
    protected function buildUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('employee_id')->nullable();
            $table->json('roles')->nullable();
            $table->string('status')->default('Active');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
