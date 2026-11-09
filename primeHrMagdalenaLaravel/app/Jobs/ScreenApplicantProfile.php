<?php

namespace App\Jobs;

use App\Services\Recruitment\ScreeningPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Screen one uploaded resume.
 *
 * The reference ran the pipeline in a FastAPI `BackgroundTask`; a queued job is
 * the same contract and a better one — it survives a restart, it is retried,
 * and one applicant's failure cannot abort a batch.
 *
 * `run()` opens no shared state: it reads the profile by id and writes in one
 * transaction, so two jobs for one upload batch never interleave.
 */
class ScreenApplicantProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** One resume is a few regex passes; 120s is generous even for a PDF. */
    public int $timeout = 120;

    /** Three attempts, then the profile stays FAILED for a re-upload. */
    public int $tries = 3;

    public function __construct(public readonly string $profileId)
    {
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    public function handle(ScreeningPipeline $pipeline): void
    {
        $pipeline->run($this->profileId);
    }
}
