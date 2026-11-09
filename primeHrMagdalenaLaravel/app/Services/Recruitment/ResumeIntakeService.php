<?php

namespace App\Services\Recruitment;

use App\Exceptions\RecruitmentRuleViolation;
use App\Models\ApplicantProfile;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resume intake: validate, dedupe, store, and register a resume as a PENDING
 * application.
 *
 * Ported from `services/intake.py`. The rules that matter are unchanged:
 *
 *  - **A duplicate that never finished does not block a re-upload.** Only a
 *    copy that is already valid or in flight does; stale FAILED copies are
 *    cleared so a retry is always clean.
 *  - **The anon code is unique across the whole table**, not per vacancy, so
 *    two positions with equal local applicant counts cannot both mint "A-0006".
 *  - **The candidate is resolved before the application exists**, because
 *    `candidate_id` is NOT NULL: an application must know whose it is from the
 *    moment it exists, not be linked up by a later pass that could be missed.
 */
class ResumeIntakeService
{
    /** A duplicate in one of these states blocks a re-upload. */
    public const BLOCKING_STATUSES = [
        ApplicantProfile::PARSE_QUEUED,
        ApplicantProfile::PARSE_PARSING,
        ApplicantProfile::PARSE_DONE,
    ];

    public function __construct(
        private readonly CandidateResolver $candidates = new CandidateResolver(),
        private readonly ResumeAnonymizer $anonymizer = new ResumeAnonymizer(),
    ) {
    }

    /**
     * Validate, dedupe, and persist one resume as a PENDING ApplicantProfile.
     *
     * Returns null when this exact content is already a valid or in-flight
     * duplicate for this vacancy. The caller schedules screening afterwards.
     *
     * @param  int|null  $userId  the applicant account, when there is one. Under
     *                            the staff-only scope an HR upload leaves this
     *                            null; it exists for a later portal.
     */
    public function intake(Vacancy $vacancy, string $filename, string $contents, ?int $userId = null): ?ApplicantProfile
    {
        $filename = $this->safeFilename($filename);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (! in_array($extension, (array) config('hiring.accepted_extensions', ['pdf', 'docx', 'txt']), true)) {
            throw RecruitmentRuleViolation::badRequest(
                'Unsupported format: '.$filename.'. Accepted: PDF, DOCX, TXT.'
            );
        }

        $maxBytes = (int) config('hiring.max_resume_bytes', 10 * 1024 * 1024);

        if (strlen($contents) > $maxBytes) {
            throw RecruitmentRuleViolation::badRequest(
                $filename.' exceeds '.round($maxBytes / 1024 / 1024).' MB.'
            );
        }

        $contentHash = hash('sha256', $contents);

        $stale = ApplicantProfile::query()
            ->where('vacancy_id', $vacancy->id)
            ->where('content_hash', $contentHash)
            ->get();

        foreach ($stale as $existing) {
            if (in_array($existing->parse_status, self::BLOCKING_STATUSES, true)) {
                return null;
            }
        }

        return DB::transaction(function () use ($vacancy, $filename, $contents, $contentHash, $stale, $userId) {
            // Every existing copy failed — clear them out so the retry is clean.
            foreach ($stale as $existing) {
                $this->deleteStoredFile($existing->source_file_path);
                $existing->delete();
            }

            $path = $this->store($vacancy, $filename, $contents);
            $code = $this->nextAnonCode();

            // user_id is the only automatic match; see CandidateResolver.
            $candidate = $this->candidates->resolve($userId);

            return ApplicantProfile::create([
                'vacancy_id' => $vacancy->id,
                'candidate_id' => $candidate->id,
                'anon_code' => $code,
                'source_file_path' => $path,
                'content_hash' => $contentHash,
                'user_id' => $userId,
                'parse_status' => ApplicantProfile::PARSE_QUEUED,
            ]);
        });
    }

    /** The private-disk path of a stored resume. */
    public function absolutePath(ApplicantProfile $profile): string
    {
        return Storage::disk($this->disk())->path($profile->source_file_path);
    }

    public function exists(ApplicantProfile $profile): bool
    {
        return Storage::disk($this->disk())->exists($profile->source_file_path);
    }

    public function deleteStoredFile(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk($this->disk())->exists($path)) {
            Storage::disk($this->disk())->delete($path);
        }
    }

    private function store(Vacancy $vacancy, string $filename, string $contents): string
    {
        $directory = trim((string) config('hiring.resume_directory', 'recruitment/resumes'), '/')
            .'/'.$vacancy->id;

        // Prefix with a random id so two applicants uploading same-named files
        // ("resume.pdf") never overwrite each other's stored content.
        $path = $directory.'/'.Str::lower(Str::random(8)).'_'.$filename;

        Storage::disk($this->disk())->put($path, $contents);

        return $path;
    }

    /**
     * The next `A-0001` code.
     *
     * Derived from a global count rather than a per-vacancy one, and checked
     * against the table because a count can collide after a deletion.
     */
    private function nextAnonCode(): string
    {
        $sequence = ApplicantProfile::query()->count();

        do {
            $sequence++;
            $code = $this->anonymizer->assignAnonCode($sequence);
        } while (ApplicantProfile::query()->where('anon_code', $code)->exists());

        return $code;
    }

    /**
     * The original filename, with any path stripped and unsafe bytes replaced.
     *
     * The reference kept the original name so a reviewer downloading the file
     * got "juan-dela-cruz.pdf" rather than a hash. It is also what the duplicate
     * check reports back, so it is preserved rather than regenerated.
     */
    private function safeFilename(string $filename): string
    {
        $name = basename(str_replace('\\', '/', $filename));
        $name = preg_replace('/[^\pL\pN._\- ]+/u', '_', $name) ?? $name;
        $name = trim($name, " .\t\n\r\0\x0B");

        return $name !== '' ? $name : 'resume.pdf';
    }

    private function disk(): string
    {
        return (string) config('hiring.disk', 'local');
    }
}
