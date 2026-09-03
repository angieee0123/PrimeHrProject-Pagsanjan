<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The authorization boundary around notifications.
 *
 * A notification carries somebody's name, their leave dates, the reason their
 * request was refused. The id in the URL is the only thing the client supplies,
 * so every endpoint has to establish for itself that the row belongs to the
 * caller — and the *destination* a notification points at has to be re-checked
 * too, or a stored link becomes a way around the area gates.
 *
 * Tables are built by hand: the project's migrations cannot run on the test
 * connection, so RefreshDatabase is unavailable (see CLAUDE.md).
 */
class NotificationAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->default('Active');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        });

        Notification::flushColumnCache();
        Notification::flushUnreadMemo();
    }

    private function user(array $roles): User
    {
        return User::create([
            'name'              => 'U',
            'username'          => 'u' . uniqid(),
            'email'             => uniqid() . '@example.test',
            'password'          => 'x',
            'roles'             => $roles,
            'status'            => 'Active',
            'email_verified_at' => now(),
        ]);
    }

    private function notificationFor(User $user, array $overrides = []): Notification
    {
        return Notification::create(array_merge([
            'user_id'  => $user->id,
            'type'     => 'leave_request',
            'audience' => 'employee',
            'title'    => 'Leave Request Approved',
            'message'  => 'Your vacation leave for Aug 12 has been Approved.',
            'link'     => '/employee/leave',
            'is_read'  => false,
        ], $overrides));
    }

    /* ================================================================= */

    #[Test]
    public function a_user_cannot_open_another_users_notification(): void
    {
        $owner = $this->user(['employee']);
        $other = $this->user(['employee']);
        $notification = $this->notificationFor($owner);

        $this->actingAs($other)
            ->get("/notifications/{$notification->id}/open")
            ->assertNotFound();

        // And it is still unread — an unauthorised open must not even leave a
        // trace on somebody else's row.
        $this->assertFalse((bool) $notification->fresh()->is_read);
    }

    #[Test]
    public function a_user_cannot_mark_another_users_notification_read(): void
    {
        $owner = $this->user(['employee']);
        $other = $this->user(['employee']);
        $notification = $this->notificationFor($owner);

        $this->actingAs($other)
            ->postJson("/api/notifications/{$notification->id}/mark-read")
            ->assertNotFound();

        $this->assertFalse((bool) $notification->fresh()->is_read);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_notification(): void
    {
        $owner = $this->user(['employee']);
        $other = $this->user(['employee']);
        $notification = $this->notificationFor($owner);

        $this->actingAs($other)
            ->deleteJson("/api/notifications/{$notification->id}")
            ->assertNotFound();

        $this->assertNotNull($notification->fresh());
    }

    #[Test]
    public function marking_all_read_only_touches_the_callers_own_rows(): void
    {
        $owner = $this->user(['employee']);
        $other = $this->user(['employee']);
        $mine = $this->notificationFor($owner);
        $theirs = $this->notificationFor($other);

        $this->actingAs($owner)
            ->postJson('/api/notifications/mark-all-read', ['audience' => 'employee'])
            ->assertOk();

        $this->assertTrue((bool) $mine->fresh()->is_read);
        $this->assertFalse((bool) $theirs->fresh()->is_read);
    }

    #[Test]
    public function the_feed_never_returns_another_users_notifications(): void
    {
        $owner = $this->user(['employee']);
        $other = $this->user(['employee']);
        $this->notificationFor($other, ['message' => 'SECRET-OTHER-PERSON']);

        $response = $this->actingAs($owner)
            ->getJson('/api/notifications/feed?audience=employee')
            ->assertOk();

        $this->assertStringNotContainsString('SECRET-OTHER-PERSON', $response->json('html'));
        $this->assertSame(0, $response->json('unread_count'));
    }

    #[Test]
    public function asking_for_the_admin_feed_without_an_admin_role_gets_the_employee_one(): void
    {
        // The audience parameter narrows, it never widens. An employee naming
        // the admin audience is answered with the bell they actually have —
        // which matters for an account whose roles were later reduced while
        // admin-audience rows from before are still on the table.
        $employee = $this->user(['employee']);
        $this->notificationFor($employee, ['audience' => 'admin', 'message' => 'ADMIN-QUEUE-ITEM']);

        $response = $this->actingAs($employee)
            ->getJson('/api/notifications/feed?audience=admin')
            ->assertOk();

        $this->assertStringNotContainsString('ADMIN-QUEUE-ITEM', $response->json('html'));
        $this->assertSame(0, $response->json('unread_count'));
    }

    #[Test]
    public function opening_a_notification_marks_it_read_and_follows_its_link(): void
    {
        $employee = $this->user(['employee']);
        $notification = $this->notificationFor($employee, ['link' => '/employee/leave?highlight=7']);

        $this->actingAs($employee)
            ->get("/notifications/{$notification->id}/open")
            ->assertRedirect('/employee/leave?highlight=7');

        $this->assertTrue((bool) $notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function a_link_into_an_area_the_caller_may_not_enter_is_refused(): void
    {
        // The strongest form of the rule: even a row genuinely belonging to
        // this employee cannot carry them into /admin. Otherwise a stored link
        // would be a door around EnsureRoleForArea — and the row would already
        // be marked read at the 403.
        $employee = $this->user(['employee']);
        $notification = $this->notificationFor($employee, ['link' => '/admin/personnel']);

        $this->actingAs($employee)
            ->get("/notifications/{$notification->id}/open")
            ->assertRedirect(route('employee.dashboard'));
    }

    #[Test]
    public function an_off_site_link_is_refused(): void
    {
        $employee = $this->user(['employee']);
        $notification = $this->notificationFor($employee, ['link' => 'https://evil.example.com/phish']);

        $this->actingAs($employee)
            ->get("/notifications/{$notification->id}/open")
            ->assertRedirect(route('employee.dashboard'));
    }

    #[Test]
    public function an_admin_may_follow_an_admin_link(): void
    {
        $admin = $this->user(['admin']);
        $notification = $this->notificationFor($admin, [
            'audience' => 'admin',
            'link'     => '/admin/leave?highlight=3',
        ]);

        $this->actingAs($admin)
            ->get("/notifications/{$notification->id}/open")
            ->assertRedirect('/admin/leave?highlight=3');
    }

    #[Test]
    public function every_notification_endpoint_requires_authentication(): void
    {
        $notification = $this->notificationFor($this->user(['employee']));

        $this->get("/notifications/{$notification->id}/open")->assertRedirect('/login');
        $this->getJson('/api/notifications/feed')->assertUnauthorized();
        $this->postJson("/api/notifications/{$notification->id}/mark-read")->assertUnauthorized();
        $this->deleteJson("/api/notifications/{$notification->id}")->assertUnauthorized();
    }
}
