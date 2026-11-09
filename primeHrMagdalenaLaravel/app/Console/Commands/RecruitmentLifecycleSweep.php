<?php

namespace App\Console\Commands;

use App\Services\Recruitment\VacancyLifecycleService;
use Illuminate\Console\Command;

/**
 * The recruitment lifecycle sweep.
 *
 * Moves OPEN positions whose closing date has passed to LAPSED, then retires
 * archived detail pages whose grace window has elapsed. Both halves are
 * idempotent, so running it by hand beside the scheduler is safe.
 *
 * The reference ran this from an in-process timer plus
 * `scripts/run_lifecycle_jobs.py`; here it is one scheduled command, which is
 * the deployment's existing cron path.
 */
class RecruitmentLifecycleSweep extends Command
{
    protected $signature = 'recruitment:lifecycle-sweep';

    protected $description = 'Lapse overdue vacancies and retire expired detail pages';

    public function handle(VacancyLifecycleService $lifecycle): int
    {
        $result = $lifecycle->sweep();

        $this->info(sprintf(
            'Recruitment sweep: %d position(s) lapsed, %d detail page(s) retired.',
            $result['lapsed'],
            $result['retired'],
        ));

        return self::SUCCESS;
    }
}
