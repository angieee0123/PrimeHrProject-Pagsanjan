<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

/**
 * Retention for the notifications table.
 *
 * Two rules, and the second is the one that matters:
 *
 * - Only **read** notifications are pruned. An unread notification is work
 *   nobody has looked at yet; deleting it on a timer would silently drop
 *   something an employee was still owed, however old it is.
 * - Nothing here is a record of anything. The leave application, the travel
 *   order, the payslip and the audit log all outlive the notification that
 *   announced them, which is what makes a retention window safe at all — the
 *   notification is a pointer, and deleting a pointer loses nothing.
 *
 * Set NOTIFICATION_RETENTION_DAYS=0 to keep everything.
 */
class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune
                            {--days= : Override the configured retention window}
                            {--dry-run : Report what would be deleted without deleting it}';

    protected $description = 'Delete read notifications older than the retention window (unread ones are always kept)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('notifications.retention.read_days', 180));

        if ($days <= 0) {
            $this->info('Retention is disabled (days <= 0). Nothing pruned.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);

        $query = Notification::read()->where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} read notification(s) created before {$cutoff->toDateString()}.");

            return self::SUCCESS;
        }

        // Chunked so a first run on a long-lived install does not build one
        // enormous delete and hold the table while it runs.
        $deleted = 0;
        while (($batch = $query->take(1000)->delete()) > 0) {
            $deleted += $batch;
        }

        $this->info("Deleted {$deleted} read notification(s) created before {$cutoff->toDateString()}. Unread notifications were kept.");

        return self::SUCCESS;
    }
}
