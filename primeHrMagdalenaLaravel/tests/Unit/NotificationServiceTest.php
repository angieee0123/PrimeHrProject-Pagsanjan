<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The four properties the whole notification system rests on.
 *
 *  1. **A notification never breaks the workflow it announces.** Every write
 *     goes through NotificationService::deliver(), which swallows and logs.
 *  2. **A notification is idempotent.** A repeated action leaves one row.
 *  3. **Each audience is its own bell.** An admin work item does not appear in
 *     the employee list, and nothing the mayor is told leaks into either.
 *  4. **Recipients are the right people.** Approvers exclude the actor and any
 *     account that cannot sign in.
 *
 * The tables are built by hand: the project's migrations cannot run on the test
 * connection (2026_04_15_182306_add_timestamps_to_tables emits MySQL-only
 * `ON UPDATE CURRENT_TIMESTAMP`), so RefreshDatabase is not available here.
 */
class NotificationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        // The model memoises which optional columns exist, and the memo
        // survives between tests in one process.
        Notification::flushColumnCache();
        Notification::flushUnreadMemo();
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        // wantsNotification() reads this table on every recipient. Without it
        // present, the recipient lookup throws and NotificationService catches
        // the failure — which is correct behaviour, but it would make every
        // preference assertion below pass for the wrong reason.
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            foreach ([
                ...\App\Models\UserNotificationPreference::ADMIN_KEYS,
                ...\App\Models\UserNotificationPreference::EMPLOYEE_KEYS,
            ] as $key) {
                $table->boolean($key)->default(true);
            }
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 50);
            $table->string('audience', 20)->default('employee');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->string('dedupe_key', 191)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'dedupe_key']);
        });
    }

    private function user(array $roles, string $status = 'Active'): User
    {
        return User::create([
            'name'     => 'Test ' . implode('+', $roles),
            'username' => 'u' . uniqid(),
            'email'    => uniqid() . '@example.test',
            'password' => 'x',
            'roles'    => $roles,
            'status'   => $status,
        ]);
    }

    /* ================================================================= */

    #[Test]
    public function the_same_event_delivered_twice_leaves_one_notification(): void
    {
        $user = $this->user(['employee']);

        $payload = [
            'type'       => 'leave_request',
            'audience'   => 'employee',
            'title'      => 'Leave Request Approved',
            'message'    => 'Your vacation leave has been Approved.',
            'dedupe_key' => 'leave:41:approved',
        ];

        $first  = NotificationService::deliver($user, $payload);
        $second = NotificationService::deliver($user, $payload);

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second->id, 'A repeated approval must not create a second row.');
        $this->assertSame(1, Notification::where('user_id', $user->id)->count());
    }

    #[Test]
    public function the_same_key_for_two_recipients_is_two_notifications(): void
    {
        // Uniqueness is per user, not global: one decision legitimately reaches
        // the filer and every companion under the same event key.
        $a = $this->user(['employee']);
        $b = $this->user(['employee']);

        $payload = [
            'type'       => 'travel_order',
            'title'      => 'Travel Order Approved',
            'message'    => 'Approved.',
            'dedupe_key' => 'travel:9:approved',
        ];

        NotificationService::deliver($a, $payload);
        NotificationService::deliver($b, $payload);

        $this->assertSame(2, Notification::count());
    }

    #[Test]
    public function a_failed_delivery_returns_null_rather_than_throwing(): void
    {
        // This is the property that keeps a leave approval from being rolled
        // back by a notification. The table is dropped out from under the write
        // because that is the shape of the real failures — a migration not yet
        // run, a database that went away mid-request — and it has to come back
        // as null, not as an exception climbing into the controller.
        $user = $this->user(['employee']);
        Schema::drop('notifications');

        $result = NotificationService::deliver($user, [
            'type'    => 'leave_request',
            'title'   => 'Leave Request Approved',
            'message' => 'Your vacation leave has been Approved.',
        ]);

        $this->assertNull($result);
    }

    #[Test]
    public function a_missing_recipient_is_not_an_error(): void
    {
        $this->assertNull(NotificationService::deliver(null, [
            'type' => 'system', 'title' => 'x', 'message' => 'y',
        ]));
    }

    #[Test]
    public function each_audience_is_its_own_bell(): void
    {
        // One person holding several roles has one row set and three lists.
        $user = $this->user(['admin', 'employee', 'mayor']);

        NotificationService::deliver($user, ['type' => 'leave_request', 'audience' => 'admin', 'title' => 'queued', 'message' => 'm']);
        NotificationService::deliver($user, ['type' => 'leave_request', 'audience' => 'employee', 'title' => 'mine', 'message' => 'm']);
        NotificationService::deliver($user, ['type' => 'leave_request', 'audience' => 'mayor', 'title' => 'oversight', 'message' => 'm']);
        NotificationService::deliver($user, ['type' => 'system', 'audience' => 'system', 'title' => 'everyone', 'message' => 'm']);

        $titles = fn (string $audience) => Notification::where('user_id', $user->id)
            ->forAudience($audience)->pluck('title')->sort()->values()->all();

        $this->assertSame(['everyone', 'queued'], $titles('admin'));
        $this->assertSame(['everyone', 'mine'], $titles('employee'));
        $this->assertSame(['everyone', 'oversight'], $titles('mayor'));
    }

    #[Test]
    public function marking_one_bell_read_leaves_the_others_alone(): void
    {
        $user = $this->user(['admin', 'employee']);

        NotificationService::deliver($user, ['type' => 'leave_request', 'audience' => 'admin', 'title' => 'queued', 'message' => 'm']);
        NotificationService::deliver($user, ['type' => 'leave_request', 'audience' => 'employee', 'title' => 'mine', 'message' => 'm']);

        NotificationService::markAllAsRead($user->id, 'admin');

        $this->assertSame(0, Notification::where('user_id', $user->id)->forAdmin()->unread()->count());
        $this->assertSame(1, Notification::where('user_id', $user->id)->forEmployee()->unread()->count());
    }

    #[Test]
    public function approvers_exclude_the_actor_and_inactive_accounts(): void
    {
        $actor    = $this->user(['admin']);
        $other    = $this->user(['hr']);
        $inactive = $this->user(['admin'], 'Inactive');
        $this->user(['employee']);   // not an approver at all
        $this->user(['mayor']);      // watches, does not approve

        Auth::login($actor);

        $ids = NotificationService::approvers()->pluck('id')->all();

        $this->assertSame([$other->id], $ids);
    }

    #[Test]
    public function overseers_are_the_mayor_accounts_only(): void
    {
        $mayor = $this->user(['mayor']);
        $this->user(['admin']);
        $this->user(['employee']);

        $this->assertSame([$mayor->id], NotificationService::overseers()->pluck('id')->all());
    }

    #[Test]
    public function a_recipient_who_opted_out_of_a_category_is_skipped(): void
    {
        // wantsNotification() is opt-out: an account with no preference row at
        // all still receives everything, which is the common case.
        $wants = $this->user(['admin']);
        $optedOut = $this->user(['admin']);

        $optedOut->notificationPreference()->create([
            'leave_requests'   => false,
            'travel_orders'    => true,
        ]);

        $ids = NotificationService::approvers('leave_requests')->pluck('id')->all();

        $this->assertContains($wants->id, $ids, 'No preference row means opted in.');
        $this->assertNotContains($optedOut->id, $ids);

        // The opt-out is per category, not a blanket mute.
        $this->assertContains(
            $optedOut->id,
            NotificationService::approvers('travel_orders')->pluck('id')->all()
        );
    }

    #[Test]
    public function read_state_moves_both_ways(): void
    {
        $user = $this->user(['employee']);
        $notification = NotificationService::deliver($user, ['type' => 'system', 'title' => 'x', 'message' => 'y']);

        $this->assertFalse($notification->is_read);

        $notification->markAsRead();
        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);

        $notification->markAsUnread();
        $this->assertFalse($notification->fresh()->is_read);
        $this->assertNull($notification->fresh()->read_at, 'read_at must be cleared, not left behind.');
    }

    #[Test]
    public function every_category_has_a_label_and_an_icon(): void
    {
        // The card reads these straight out of the constant. A category added
        // without one renders an empty badge.
        foreach (Notification::CATEGORIES as $type => $meta) {
            $this->assertArrayHasKey('label', $meta, "{$type} has no label");
            $this->assertArrayHasKey('icon', $meta, "{$type} has no icon");
            $this->assertCount(2, $meta['hue'], "{$type} needs a two-stop gradient");
            $this->assertNotSame('', trim($meta['icon']), "{$type} has an empty icon");
        }
    }

    #[Test]
    public function an_unknown_category_still_renders(): void
    {
        $user = $this->user(['employee']);
        $notification = NotificationService::deliver($user, [
            'type' => 'not_a_real_category', 'title' => 'x', 'message' => 'y',
        ]);

        $this->assertSame('System', $notification->category_label);
    }
}
