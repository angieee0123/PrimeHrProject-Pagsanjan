<?php

namespace App\Services\Recruitment;

use App\Models\ApplicantProfile;
use App\Models\AuditLog;
use App\Models\ScreeningResult;
use App\Models\Vacancy;
use Illuminate\Support\Carbon;

/**
 * The array shape every recruitment Blade view already expects.
 *
 * The views were written against the JSON the HTTP client returned, and they
 * are right: a page should not care whether its rows came from a remote API or
 * from Eloquent. Keeping that contract in one presenter means the port changed
 * the data source and nothing else — no view had to learn Carbon, and there is
 * one place that decides how a date or a score is written.
 */
final class RecruitmentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function vacancy(
        Vacancy $vacancy,
        ?int $applicantCount = null,
        ?int $screenedCount = null,
        ?string $archiveReason = null,
    ): array {
        $cancelReason = $vacancy->cancel_reason;

        return [
            'id' => $vacancy->id,
            'position_title' => $vacancy->position_title,
            'place_of_assignment' => $vacancy->place_of_assignment,
            'plantilla_item_no' => $vacancy->plantilla_item_no,
            'salary_grade' => $vacancy->salary_grade,
            'monthly_salary' => $vacancy->monthly_salary,
            'qs_education' => $vacancy->qs_education,
            'qs_training_hours' => $vacancy->qs_training_hours,
            'qs_experience_years' => $vacancy->qs_experience_years,
            'qs_eligibility' => $vacancy->qs_eligibility,
            'competency' => $vacancy->competency,
            'required_skills' => (array) ($vacancy->required_skills ?? []),
            'weights' => (array) ($vacancy->weights ?? []),
            'instructions' => $vacancy->instructions,
            'status' => $vacancy->status,
            'posting_date' => self::date($vacancy->posting_date),
            'closing_date' => self::date($vacancy->closing_date),
            'requisition_open_date' => self::date($vacancy->requisition_open_date),
            'grace_period_expires_at' => self::dateTime($vacancy->grace_period_expires_at),
            'lapsed_at' => self::dateTime($vacancy->lapsed_at),
            'closed_at' => self::dateTime($vacancy->closed_at),
            'reopened_at' => self::dateTime($vacancy->reopened_at),
            'reopen_count' => (int) ($vacancy->reopen_count ?? 0),
            'ranking_version' => (int) ($vacancy->ranking_version ?? 0),
            'close_reason' => $vacancy->close_reason,
            'slots' => (int) ($vacancy->slots ?? 1),
            'published_at' => self::dateTime($vacancy->published_at),
            'on_hold_at' => self::dateTime($vacancy->on_hold_at),
            'hold_note' => $vacancy->hold_note,
            'cancelled_at' => self::dateTime($vacancy->cancelled_at),
            'cancel_reason' => $cancelReason,
            'cancel_reason_label' => $cancelReason === null
                ? null
                : (VacancyLifecycleService::CANCEL_REASONS[$cancelReason] ?? $cancelReason),
            'cancel_reason_detail' => $vacancy->cancel_reason_detail,
            'detail_retired' => (bool) $vacancy->detail_retired,
            'applicant_count' => $applicantCount ?? $vacancy->applicantProfiles()->count(),
            'screened_count' => $screenedCount ?? ScreeningResult::where('vacancy_id', $vacancy->id)->count(),
            'created_at' => self::dateTime($vacancy->created_at),
            'archive_reason' => $archiveReason,
        ];
    }

    /**
     * One application as the screening table reads it.
     *
     * @return array<string, mixed>
     */
    public static function profile(ApplicantProfile $profile): array
    {
        $result = $profile->screeningResult;

        return [
            'id' => $profile->id,
            'vacancy_id' => $profile->vacancy_id,
            'anon_code' => $profile->anon_code,
            'name' => $profile->name,
            'parse_status' => $profile->parse_status,
            'review_status' => $profile->review_status,
            'selection_status' => $profile->selection_status,
            'selection_reason' => $profile->selection_reason,
            'fit_score' => $result?->fit_score,
            'subscores' => (array) ($result->subscores ?? []),
            'parsed' => (array) ($profile->parsed ?? []),
            'redaction_report' => (array) ($profile->redaction_report ?? []),
            'behavioral_summary' => (array) ($profile->behavioral_summary ?? []),
            'questionnaire_submitted_at' => self::dateTime($profile->questionnaire_submitted_at),
            'created_at' => self::dateTime($profile->created_at),
            'archived_at' => self::dateTime($profile->archived_at),
        ];
    }

    /**
     * One ranked row: the score, the vacancy's words, and the applicant.
     *
     * @return array<string, mixed>
     */
    public static function ranked(ScreeningResult $result, ApplicantProfile $profile, ?Vacancy $vacancy = null): array
    {
        $vacancy ??= $profile->vacancy;

        return [
            'applicant_id' => $profile->id,
            'anon_code' => $profile->anon_code,
            'name' => $profile->name,
            'vacancy_id' => $result->vacancy_id,
            'position_title' => $vacancy?->position_title,
            'place_of_assignment' => $vacancy?->place_of_assignment,
            'fit_score' => $result->fit_score,
            'rank_position' => $result->rank_position,
            'subscores' => (array) ($result->subscores ?? []),
            'parsed' => (array) ($profile->parsed ?? []),
            'explanation' => $result->explanation,
            'model_version' => $result->model_version,
            'redaction_report' => (array) ($profile->redaction_report ?? []),
            'selection_status' => $profile->selection_status,
            'selection_reason' => $profile->selection_reason,
            'questionnaire_submitted_at' => self::dateTime($profile->questionnaire_submitted_at),
            'behavioral_summary' => (array) ($profile->behavioral_summary ?? []),
        ];
    }

    /**
     * One row of the approval queue.
     *
     * @return array<string, mixed>
     */
    public static function approval(ApplicantProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'vacancy_id' => $profile->vacancy_id,
            'position_title' => $profile->vacancy?->position_title,
            'anon_code' => $profile->anon_code,
            'name' => $profile->name,
            'parse_status' => $profile->parse_status,
            'review_status' => $profile->review_status,
            'fit_score' => $profile->screeningResult?->fit_score,
            'created_at' => self::dateTime($profile->created_at),
        ];
    }

    /**
     * One audit row.
     *
     * @return array<string, mixed>
     */
    public static function audit(AuditLog $entry): array
    {
        return [
            'id' => $entry->id,
            'user_id' => $entry->user_id,
            'user_name' => $entry->user_name,
            'action' => $entry->action,
            'entity' => $entry->entity,
            'entity_id' => $entry->entity_id,
            'at' => self::dateTime($entry->at),
        ];
    }

    private static function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        return (string) $value;
    }

    private static function dateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateTimeString();
        }

        return (string) $value;
    }
}
