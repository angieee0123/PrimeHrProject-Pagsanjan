<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use App\Services\PasswordResetCodeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The forgot-password flow, pinned at the property level.
 *
 * This screen shipped as a mockup: the verification code was the literal
 * '123456' compiled into the Blade file, and the final step announced
 * "Password reset successfully!" without a request leaving the browser. So the
 * assertions that matter most here are the plain ones — that a code is random,
 * that a wrong one is refused, and that a completed reset actually changes the
 * hash in `users` — because each of those was false in the version this
 * replaces.
 */
class PasswordResetCodeServiceTest extends TestCase
{
    private PasswordResetCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        Notification::fake();

        $this->service = new PasswordResetCodeService();
    }

    /**
     * Two tables built by hand on the in-memory SQLite connection. The project's
     * migrations cannot run here — 2026_04_15_182306_add_timestamps_to_tables
     * emits MySQL-only `ON UPDATE CURRENT_TIMESTAMP` — so RefreshDatabase is
     * not available. Same approach as AiFileResolverTest.
     */
    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('Active');
            $table->text('roles')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('ticket_hash')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('verified_at')->nullable();
        });
    }

    private function user(string $email = 'juan@example.com', string $status = 'Active'): User
    {
        return User::create([
            'name'     => 'Juan Cruz',
            'email'    => $email,
            'password' => 'old-password-123',
            'status'   => $status,
            'roles'    => ['employee'],
        ]);
    }

    /**
     * Pull the code out of the notification the way the employee would read it
     * out of their inbox — there is nowhere else to get it, which is the point.
     */
    private function mailedCode(User $user): string
    {
        $code = null;

        Notification::assertSentTo($user, PasswordResetCodeNotification::class,
            function ($notification) use (&$code) {
                $reflected = new \ReflectionProperty($notification, 'code');
                $code = $reflected->getValue($notification);
                return true;
            });

        return $code;
    }

    #[Test]
    public function it_mails_a_code_and_stores_only_its_hash(): void
    {
        $user = $this->user();

        $this->service->sendCode('juan@example.com');

        $code = $this->mailedCode($user);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $row = DB::table('password_reset_codes')->where('email', 'juan@example.com')->first();
        $this->assertNotNull($row);

        // The digits themselves are never written down. A table dump must not
        // be a list of live reset credentials.
        $this->assertNotSame($code, $row->code_hash);
        $this->assertTrue(Hash::check($code, $row->code_hash));
    }

    #[Test]
    public function the_code_is_not_the_hard_coded_one_the_mockup_used(): void
    {
        // Ten issues; '123456' turning up every time would mean the literal
        // survived the rewrite somewhere.
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $user = $this->user("staff{$i}@example.com");
            $this->service->sendCode("staff{$i}@example.com");
            $codes[] = $this->mailedCode($user);
        }

        $this->assertGreaterThan(1, count(array_unique($codes)));
        $this->assertNotEquals(array_fill(0, 10, '123456'), $codes);
    }

    #[Test]
    public function an_unknown_address_is_silently_ignored(): void
    {
        $this->service->sendCode('nobody@example.com');

        Notification::assertNothingSent();
        $this->assertDatabaseCountIs(0);
    }

    #[Test]
    public function an_inactive_account_gets_no_code(): void
    {
        $this->user('dormant@example.com', 'Inactive');

        $this->service->sendCode('dormant@example.com');

        Notification::assertNothingSent();
        $this->assertDatabaseCountIs(0);
    }

    #[Test]
    public function a_correct_code_yields_a_ticket_and_a_wrong_one_does_not(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $code = $this->mailedCode($user);

        $wrong = $code === '000000' ? '111111' : '000000';
        $this->assertNull($this->service->verifyCode('juan@example.com', $wrong));

        // A wrong guess costs an attempt but must not invalidate the real code.
        $ticket = $this->service->verifyCode('juan@example.com', $code);
        $this->assertIsString($ticket);
        $this->assertNotEmpty($ticket);
    }

    #[Test]
    public function a_code_is_burned_after_the_attempt_budget(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $code = $this->mailedCode($user);

        for ($i = 0; $i < PasswordResetCodeService::MAX_ATTEMPTS; $i++) {
            $this->assertNull($this->service->verifyCode('juan@example.com', '000000'));
        }

        // Even the right digits are worthless now — a six-digit secret has to be
        // spendable, not merely expiring, or it can be walked through.
        $this->assertNull($this->service->verifyCode('juan@example.com', $code));
    }

    #[Test]
    public function a_code_cannot_be_spent_twice(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $code = $this->mailedCode($user);

        $this->assertIsString($this->service->verifyCode('juan@example.com', $code));
        $this->assertNull($this->service->verifyCode('juan@example.com', $code));
    }

    #[Test]
    public function an_expired_code_is_refused(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $code = $this->mailedCode($user);

        DB::table('password_reset_codes')->where('email', 'juan@example.com')->update([
            'created_at' => now()->subMinutes(PasswordResetCodeService::CODE_TTL_MINUTES + 1),
        ]);

        $this->assertNull($this->service->verifyCode('juan@example.com', $code));
    }

    #[Test]
    public function a_valid_ticket_actually_changes_the_password(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $ticket = $this->service->verifyCode('juan@example.com', $this->mailedCode($user));

        $this->assertTrue(
            $this->service->resetPassword('juan@example.com', $ticket, 'brand-new-password')
        );

        // The whole point. The mockup returned success here and wrote nothing.
        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-password', $user->password));
        $this->assertFalse(Hash::check('old-password-123', $user->password));

        // And the pending row is gone, so the ticket cannot be replayed.
        $this->assertDatabaseCountIs(0);
    }

    #[Test]
    public function a_forged_ticket_cannot_reset_a_password(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $this->service->verifyCode('juan@example.com', $this->mailedCode($user));

        $this->assertFalse(
            $this->service->resetPassword('juan@example.com', 'not-the-real-ticket', 'attacker-password')
        );

        $user->refresh();
        $this->assertTrue(Hash::check('old-password-123', $user->password));
    }

    #[Test]
    public function knowing_only_the_email_cannot_reset_a_password(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');

        // No code was ever verified, so there is no ticket to present.
        $this->assertFalse(
            $this->service->resetPassword('juan@example.com', '', 'attacker-password')
        );

        $user->refresh();
        $this->assertTrue(Hash::check('old-password-123', $user->password));
    }

    #[Test]
    public function an_expired_ticket_is_refused(): void
    {
        $user = $this->user();
        $this->service->sendCode('juan@example.com');
        $ticket = $this->service->verifyCode('juan@example.com', $this->mailedCode($user));

        DB::table('password_reset_codes')->where('email', 'juan@example.com')->update([
            'verified_at' => now()->subMinutes(PasswordResetCodeService::TICKET_TTL_MINUTES + 1),
        ]);

        $this->assertFalse($this->service->resetPassword('juan@example.com', $ticket, 'brand-new-password'));

        $user->refresh();
        $this->assertTrue(Hash::check('old-password-123', $user->password));
    }

    #[Test]
    public function a_resend_within_the_cooldown_does_not_send_again(): void
    {
        $this->user();

        $this->service->sendCode('juan@example.com');
        $this->service->sendCode('juan@example.com');

        // Otherwise "Resend Code" held down is a mail-bomb aimed at one inbox
        // from a form that needs no login to reach.
        Notification::assertSentTimes(PasswordResetCodeNotification::class, 1);
    }

    #[Test]
    public function the_address_is_matched_case_insensitively(): void
    {
        $user = $this->user('juan@example.com');

        $this->service->sendCode('  JUAN@Example.com ');
        $code = $this->mailedCode($user);

        $ticket = $this->service->verifyCode('Juan@EXAMPLE.com', $code);
        $this->assertIsString($ticket);
        $this->assertTrue($this->service->resetPassword('juan@example.com', $ticket, 'brand-new-password'));
    }

    private function assertDatabaseCountIs(int $expected): void
    {
        $this->assertSame($expected, DB::table('password_reset_codes')->count());
    }
}
