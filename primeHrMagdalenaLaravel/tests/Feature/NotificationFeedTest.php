<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The notification panels are server-rendered once per page load, so a new
 * notification only reaches an open page through these endpoints. Three
 * properties have to hold:
 *
 *  1. it is reachable as an authenticated GET and answers with JSON — the panel
 *     polls it, and a redirect or an HTML error page silently freezes the bell
 *     at whatever it showed when the page was opened;
 *  2. it scopes exactly as the panel's own query does — the caller's own rows,
 *     for the requested audience only. `audience` picks a panel, it must never
 *     widen whose notifications come back;
 *  3. its writes scope the same way: clearing one panel's bell must leave the
 *     other's alone, and marking a card read must refuse a row that is not the
 *     caller's.
 *
 * Tables are built by hand: this project's migrations cannot run on the test
 * connection (see tests/Unit/AiFileResolverTest.php).
 */
class NotificationFeedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->text('roles')->nullable();
            // EnsureUserIsActive runs on every request; without a status the
            // middleware answers 403 before the route is ever reached.
            $table->string('status')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type')->nullable();
            $table->string('audience')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    private function makeUser(array $roles): User
    {
        return User::create([
            'username' => 'u' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('secret1234'),
            'roles' => $roles,
            'status' => 'Active',
        ]);
    }

    private function notify(User $user, string $audience, string $title): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => 'leave_request',
            'audience' => $audience,
            'title' => $title,
            'message' => 'body',
            'link' => '/admin/leave',
            'is_read' => false,
        ]);
    }

    #[Test]
    public function it_returns_the_callers_admin_notifications_as_renderable_html(): void
    {
        $admin = $this->makeUser(['admin']);
        $this->notify($admin, 'admin', 'New Leave Request');

        $response = $this->actingAs($admin)->getJson('/api/notifications/feed?audience=admin');

        $response->assertOk();
        $this->assertSame(1, $response->json('unread_count'));
        $this->assertStringContainsString('New Leave Request', $response->json('html'));
        // The card has to carry what the panel's click handler reads off it.
        $this->assertStringContainsString('data-notif-id', $response->json('html'));
    }

    #[Test]
    public function a_notification_created_after_the_page_loaded_is_picked_up(): void
    {
        $admin = $this->makeUser(['admin']);

        $first = $this->actingAs($admin)->getJson('/api/notifications/feed?audience=admin');
        $this->assertSame(0, $first->json('unread_count'));

        // Exactly what an employee filing something does, mid-session.
        $this->notify($admin, 'admin', 'New Pass Slip Request');

        $second = $this->actingAs($admin)->getJson('/api/notifications/feed?audience=admin');
        $this->assertSame(1, $second->json('unread_count'));
        $this->assertStringContainsString('New Pass Slip Request', $second->json('html'));
    }

    #[Test]
    public function it_never_returns_another_users_notifications(): void
    {
        $admin = $this->makeUser(['admin']);
        $otherAdmin = $this->makeUser(['admin']);
        $this->notify($otherAdmin, 'admin', 'Somebody Elses Row');

        $response = $this->actingAs($admin)->getJson('/api/notifications/feed?audience=admin');

        $this->assertSame(0, $response->json('unread_count'));
        $this->assertStringNotContainsString('Somebody Elses Row', $response->json('html'));
    }

    #[Test]
    public function the_audience_parameter_selects_a_panel_and_cannot_widen_the_query(): void
    {
        $user = $this->makeUser(['employee', 'admin']);
        $this->notify($user, 'admin', 'Admin Side Row');
        $this->notify($user, 'employee', 'Employee Side Row');

        $adminFeed = $this->actingAs($user)->getJson('/api/notifications/feed?audience=admin');
        $this->assertStringContainsString('Admin Side Row', $adminFeed->json('html'));
        $this->assertStringNotContainsString('Employee Side Row', $adminFeed->json('html'));

        $employeeFeed = $this->actingAs($user)->getJson('/api/notifications/feed?audience=employee');
        $this->assertStringContainsString('Employee Side Row', $employeeFeed->json('html'));
        $this->assertStringNotContainsString('Admin Side Row', $employeeFeed->json('html'));

        // An unknown audience must fall to the narrower panel, never to "all".
        $junkFeed = $this->actingAs($user)->getJson('/api/notifications/feed?audience=everything');
        $this->assertStringNotContainsString('Admin Side Row', $junkFeed->json('html'));
    }

    #[Test]
    public function the_employee_panel_polls_the_same_endpoint(): void
    {
        $employee = $this->makeUser(['employee']);

        $first = $this->actingAs($employee)->getJson('/api/notifications/feed?audience=employee');
        $this->assertSame(0, $first->json('unread_count'));

        // e.g. HR approving their leave while the page sits open.
        $this->notify($employee, 'employee', 'Leave Request Approved');

        $second = $this->actingAs($employee)->getJson('/api/notifications/feed?audience=employee');
        $this->assertSame(1, $second->json('unread_count'));
        $this->assertStringContainsString('Leave Request Approved', $second->json('html'));
    }

    #[Test]
    public function a_system_broadcast_reaches_both_panels(): void
    {
        $user = $this->makeUser(['employee', 'admin']);
        $this->notify($user, 'system', 'Maintenance Tonight');

        foreach (['admin', 'employee'] as $audience) {
            $feed = $this->actingAs($user)->getJson("/api/notifications/feed?audience={$audience}");
            $this->assertStringContainsString('Maintenance Tonight', $feed->json('html'), $audience);
        }
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->getJson('/api/notifications/feed?audience=admin')->assertUnauthorized();
    }

    #[Test]
    public function clearing_one_panels_bell_leaves_the_other_panel_unread(): void
    {
        $user = $this->makeUser(['employee', 'admin']);
        $adminRow = $this->notify($user, 'admin', 'Admin Side Row');
        $employeeRow = $this->notify($user, 'employee', 'Employee Side Row');

        $this->actingAs($user)
            ->postJson('/api/notifications/mark-all-read', ['audience' => 'employee'])
            ->assertOk();

        $this->assertTrue($employeeRow->fresh()->is_read);
        // The admin bell is not the one that was clicked.
        $this->assertFalse($adminRow->fresh()->is_read);
        $this->assertSame(1, $this->actingAs($user)
            ->getJson('/api/notifications/feed?audience=admin')->json('unread_count'));
    }

    #[Test]
    public function mark_all_read_without_an_audience_still_clears_everything(): void
    {
        $user = $this->makeUser(['employee', 'admin']);
        $adminRow = $this->notify($user, 'admin', 'Admin Side Row');
        $employeeRow = $this->notify($user, 'employee', 'Employee Side Row');

        $this->actingAs($user)->postJson('/api/notifications/mark-all-read')->assertOk();

        // Unlike the feed, an unspecified audience must not narrow: a write that
        // quietly picked one panel would leave the other's bell stuck at a count
        // nothing can reset.
        $this->assertTrue($adminRow->fresh()->is_read);
        $this->assertTrue($employeeRow->fresh()->is_read);
    }

    #[Test]
    public function a_card_click_marks_that_one_notification_read(): void
    {
        $employee = $this->makeUser(['employee']);
        $clicked = $this->notify($employee, 'employee', 'Leave Request Approved');
        $untouched = $this->notify($employee, 'employee', 'Pass Slip Approved');

        $this->actingAs($employee)
            ->postJson("/api/notifications/{$clicked->id}/mark-read")
            ->assertOk();

        $this->assertTrue($clicked->fresh()->is_read);
        $this->assertFalse($untouched->fresh()->is_read);
    }

    #[Test]
    public function a_card_click_cannot_mark_another_users_notification_read(): void
    {
        $employee = $this->makeUser(['employee']);
        $someoneElse = $this->makeUser(['employee']);
        $theirs = $this->notify($someoneElse, 'employee', 'Not Yours');

        $this->actingAs($employee)
            ->postJson("/api/notifications/{$theirs->id}/mark-read")
            ->assertNotFound();

        $this->assertFalse($theirs->fresh()->is_read);
    }
}
