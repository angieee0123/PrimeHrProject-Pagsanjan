<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bell contents for the Pagsanjan demo dataset: 22 notifications covering
 * every audience the panels render.
 *
 * The rows mirror what is currently in the database — six admin-queue items
 * (leave, travel, pass slip, monetization, training, payslip request), eleven
 * employee decisions (approvals, one disapproval with its reason, payroll,
 * an attendance correction, an account welcome), and one system broadcast
 * fanned out to the same five accounts — staggered Sep 10–14, 2026 so the
 * bell, the feed and the history page all sort against real spacing, with a
 * few rows already read so the unread badge is not the whole list.
 *
 * Recipients are resolved, never hard-coded: the admin/HR account takes the
 * queue and the first four employee accounts take the personal rows, which is
 * what reproduces the current user 1–5 layout on any fresh seed. Links go
 * through `NotificationService::link()` so they carry this install's host
 * rather than the `http://localhost` the current rows were written with;
 * `related_id` stays null throughout because these rows illustrate the bell,
 * not real workflow records, and a highlight pointing at a record that does
 * not exist opens the wrong page.
 *
 * Reruns prune only this seeder's own `demo-seed:` keys, so real
 * notifications written by the workflows survive a re-seed.
 */
class PagsanjanNotificationSeeder extends Seeder
{
    /** Prefix shared by every row this seeder owns. */
    private const KEY_PREFIX = 'demo-seed:';

    public function run(): void
    {
        $adminId = (int) (User::whereJsonContains('roles', 'admin')->value('id') ?? 1);

        $employeeIds = User::whereNotNull('employee_id')
            ->where('id', '!=', $adminId)
            ->orderBy('id')
            ->limit(4)
            ->pluck('id')
            ->all();

        if (count($employeeIds) < 4) {
            $this->command->warn('Pagsanjan roster accounts not found — run PagsanjanEmployeeSeeder first.');

            return;
        }

        [$u2, $u3, $u4, $u5] = $employeeIds;

        $plan = [
            // ---- Admin queue: work waiting for HR's review ----
            [
                'user' => $adminId, 'type' => 'leave_request', 'audience' => 'admin',
                'title' => 'New Leave Request',
                'message' => 'Aldrin G. Macalalad submitted a Vacation Leave request for 3 day(s) for Sep 10 – Sep 12, 2026 and it is waiting for your review.',
                'link' => NotificationService::link('admin.leave') ?? '/admin/leave',
                'key' => 'demo-seed:leave:submitted:1', 'read' => false, 'at' => '2026-09-10 12:57:37',
            ],
            [
                'user' => $adminId, 'type' => 'travel_order', 'audience' => 'admin',
                'title' => 'New Travel Order Request',
                'message' => 'Lorelie L. Ambrosio submitted travel order TO-2026-014 to Santa Cruz (Sep 14 – Sep 15, 2026) with 2 companion(s).',
                'link' => NotificationService::link('admin.travelorder') ?? '/admin/travelorder',
                'key' => 'demo-seed:travel:forwarded:1', 'read' => false, 'at' => '2026-09-10 18:57:37',
            ],
            [
                'user' => $adminId, 'type' => 'pass_slip', 'audience' => 'admin',
                'title' => 'New Pass Slip Request',
                'message' => 'May A. Escote filed pass slip PS-2026-031 for Sep 14, 2026: Medical check-up at provincial hospital.',
                'link' => NotificationService::link('admin.passslip') ?? '/admin/passslip',
                'key' => 'demo-seed:passslip:submitted:1', 'read' => false, 'at' => '2026-09-11 04:57:37',
            ],
            [
                'user' => $adminId, 'type' => 'monetization', 'audience' => 'admin',
                'title' => 'New Monetization Request',
                'message' => 'Melarose P. Nadera filed monetization request MON-2026-007 for 10 day(s), computed at P12,450.00.',
                'link' => NotificationService::link('admin.leave', ['tab' => 'monetization']) ?? '/admin/leave?tab=monetization',
                'key' => 'demo-seed:monetization:submitted:1', 'read' => true, 'at' => '2026-09-11 12:57:37',
            ],
            [
                'user' => $adminId, 'type' => 'training', 'audience' => 'admin',
                'title' => 'New Training Submission',
                'message' => 'Kevin Mar Z. Moreno submitted a training record for verification: Records Management and Archiving.',
                'link' => NotificationService::link('admin.training') ?? '/admin/training',
                'key' => 'demo-seed:training:submitted:1', 'read' => false, 'at' => '2026-09-12 00:57:37',
            ],
            [
                'user' => $adminId, 'type' => 'request', 'audience' => 'admin',
                'title' => 'Payslip Request',
                'message' => 'Victor D. Mirando Jr. requested a payslip: Copy of August 2026 payslip for loan application.',
                'link' => NotificationService::link('admin.requests') ?? '/admin/requests',
                'key' => 'demo-seed:request:submitted:1', 'read' => true, 'at' => '2026-09-12 10:57:37',
            ],

            // ---- The admin's own employee bell ----
            [
                'user' => $adminId, 'type' => 'leave_request', 'audience' => 'employee',
                'title' => 'Leave Request Approved',
                'message' => 'Your Vacation Leave request for Sep 03 – Sep 05, 2026 has been Approved.',
                'link' => NotificationService::link('employee.leave') ?? '/employee/leave',
                'key' => 'demo-seed:leave:approved:u1', 'read' => false, 'at' => '2026-09-12 16:57:37',
            ],
            [
                'user' => $adminId, 'type' => 'payroll', 'audience' => 'employee',
                'title' => 'Payslip Available',
                'message' => 'Your payslip for Aug 16 - Aug 31, 2026 is now available.',
                'link' => NotificationService::link('employee.payslip') ?? '/employee/payslip',
                'key' => 'demo-seed:payroll:u1', 'read' => false, 'at' => '2026-09-12 22:57:37',
            ],

            // ---- First roster employee: leave, payslip, training verdict ----
            [
                'user' => $u2, 'type' => 'leave_request', 'audience' => 'employee',
                'title' => 'Leave Request Approved',
                'message' => 'Your Sick Leave request for Sep 08, 2026 has been Approved.',
                'link' => NotificationService::link('employee.leave') ?? '/employee/leave',
                'key' => 'demo-seed:leave:approved:u2', 'read' => false, 'at' => '2026-09-13 06:57:37',
            ],
            [
                'user' => $u2, 'type' => 'payroll', 'audience' => 'employee',
                'title' => 'Payslip Available',
                'message' => 'Your payslip for Aug 16 - Aug 31, 2026 is now available.',
                'link' => NotificationService::link('employee.payslip') ?? '/employee/payslip',
                'key' => 'demo-seed:payroll:u2', 'read' => true, 'at' => '2026-09-13 10:57:37',
            ],
            [
                'user' => $u2, 'type' => 'training', 'audience' => 'employee',
                'title' => 'Training Verified',
                'message' => "Your training record 'Customer Service Excellence' has been Verified and now counts toward your PDS Section IV hours.",
                'link' => NotificationService::link('employee.training') ?? '/employee/training',
                'key' => 'demo-seed:training:verified:u2', 'read' => false, 'at' => '2026-09-13 14:57:37',
            ],

            // ---- Second roster employee: travel and pass slip decisions ----
            [
                'user' => $u3, 'type' => 'travel_order', 'audience' => 'employee',
                'title' => 'Travel Order Approved',
                'message' => 'Your travel order TO-2026-011 to Calamba (Sep 09 – Sep 10, 2026) has been Approved.',
                'link' => NotificationService::link('employee.travelorder') ?? '/employee/travelorder',
                'key' => 'demo-seed:travel:approved:u3', 'read' => false, 'at' => '2026-09-13 18:57:37',
            ],
            [
                'user' => $u3, 'type' => 'pass_slip', 'audience' => 'employee',
                'title' => 'Pass Slip Approved',
                'message' => 'Your pass slip PS-2026-028 for Sep 11, 2026 has been Approved.',
                'link' => NotificationService::link('employee.passslip') ?? '/employee/passslip',
                'key' => 'demo-seed:passslip:approved:u3', 'read' => false, 'at' => '2026-09-13 22:57:37',
            ],

            // ---- Third roster employee: a refusal and a correction ----
            [
                'user' => $u4, 'type' => 'leave_request', 'audience' => 'employee',
                'title' => 'Leave Request Disapproved',
                'message' => 'Your Vacation Leave request for Sep 18, 2026 has been Disapproved. Reason: Peak workload during audit week.',
                'link' => NotificationService::link('employee.leave') ?? '/employee/leave',
                'key' => 'demo-seed:leave:disapproved:u4', 'read' => false, 'at' => '2026-09-14 02:57:37',
            ],
            [
                'user' => $u4, 'type' => 'attendance', 'audience' => 'employee',
                'title' => 'Attendance Corrected',
                'message' => 'Your attendance record for Sep 09, 2026 has been corrected by HR. Check your Daily Time Record for the updated hours.',
                'link' => NotificationService::link('employee.attendance') ?? '/employee/attendance',
                'key' => 'demo-seed:attendance:corrected:u4', 'read' => true, 'at' => '2026-09-14 04:57:37',
            ],

            // ---- Fourth roster employee: monetization and the welcome ----
            [
                'user' => $u5, 'type' => 'monetization', 'audience' => 'employee',
                'title' => 'Monetization Request Approved',
                'message' => 'Your monetization request MON-2026-004 for P8,300.00 has been Approved. The printable form is available from the request detail view.',
                'link' => NotificationService::link('employee.leave', ['tab' => 'monetization']) ?? '/employee/leave?tab=monetization',
                'key' => 'demo-seed:monetization:approved:u5', 'read' => false, 'at' => '2026-09-14 06:57:37',
            ],
            [
                'user' => $u5, 'type' => 'account', 'audience' => 'employee',
                'title' => 'Welcome to HRIS',
                'message' => 'Your employee account has been created by the HR office. Review your profile and tell HR about anything that needs correcting.',
                'link' => NotificationService::link('employee.profile') ?? '/employee/profile',
                'key' => 'demo-seed:account:created:u5', 'read' => true, 'at' => '2026-09-14 08:57:37',
            ],
        ];

        // One broadcast, fanned out to the same five accounts. The dedupe key
        // is deliberately shared: it is unique per recipient, not globally.
        $broadcastAt = [
            $adminId => '2026-09-14 09:57:37',
            $u2 => '2026-09-14 10:57:37',
            $u3 => '2026-09-14 11:57:37',
            $u4 => '2026-09-14 12:27:37',
            $u5 => '2026-09-14 12:45:37',
        ];

        foreach ($broadcastAt as $userId => $at) {
            $plan[] = [
                'user' => $userId, 'type' => 'system', 'audience' => 'system',
                'title' => 'Payroll cutoff reminder',
                'message' => 'Heads up: the September 1–15 payroll cutoff is Sep 18. File any corrections before then so they make it in.',
                'link' => null,
                'key' => 'demo-seed:system:cutoff-sep15', 'read' => false, 'at' => $at,
            ];
        }

        $hasDedupe = Schema::hasColumn('notifications', 'dedupe_key');
        $hasAudience = Schema::hasColumn('notifications', 'audience');

        DB::transaction(function () use ($plan, $hasDedupe, $hasAudience) {
            if ($hasDedupe) {
                Notification::where('dedupe_key', 'like', self::KEY_PREFIX . '%')->delete();
            }

            foreach ($plan as $row) {
                $created = Carbon::parse($row['at'], 'UTC');

                // Built rather than mass-assigned: created_at/updated_at are
                // not fillable, and the staggered timestamps are the point —
                // the bell sorts newest-first, so one shared timestamp would
                // sort against nothing.
                $notification = new Notification([
                    'user_id' => $row['user'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'link' => $row['link'],
                    'related_id' => null,
                    'related_type' => null,
                    'is_read' => $row['read'],
                    'read_at' => $row['read'] ? $created->copy()->addHours(2) : null,
                ]);

                if ($hasAudience) {
                    $notification->audience = $row['audience'];
                }

                if ($hasDedupe) {
                    $notification->dedupe_key = $row['key'];
                }

                $notification->created_at = $created;
                $notification->updated_at = $created;
                $notification->save();
            }
        });

        $this->command->info(sprintf(
            'Pagsanjan notifications: %d rows for 5 accounts (%d admin queue, %d employee, %d system broadcast).',
            count($plan),
            6,
            11,
            5
        ));
    }
}
