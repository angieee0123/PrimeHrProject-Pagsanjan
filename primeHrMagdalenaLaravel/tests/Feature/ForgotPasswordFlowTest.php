<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The three POST steps, driven the way the browser drives them.
 *
 * PasswordResetCodeServiceTest covers the rules; this covers the wiring — that
 * the routes exist, that the JSON shape the Blade page reads is the JSON shape
 * the controller writes, and that a password typed into step 3 lands in
 * `users`. The page shipped as a mockup whose steps never left the browser, so
 * "a request actually reaches the server and changes something" is the
 * regression worth owning a test.
 */
class ForgotPasswordFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Built by hand: the project's migrations cannot run on the test
        // connection (see PasswordResetCodeServiceTest for why).
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

        Notification::fake();
    }

    private function user(): User
    {
        return User::create([
            'name'     => 'Juan Cruz',
            'email'    => 'juan@example.com',
            'password' => 'old-password-123',
            'status'   => 'Active',
            'roles'    => ['employee'],
        ]);
    }

    private function mailedCode(User $user): string
    {
        $code = null;
        Notification::assertSentTo($user, PasswordResetCodeNotification::class,
            function ($n) use (&$code) {
                $code = (new \ReflectionProperty($n, 'code'))->getValue($n);
                return true;
            });

        return $code;
    }

    #[Test]
    public function the_whole_flow_changes_the_password(): void
    {
        $user = $this->user();

        $this->postJson(route('password.forgot.send'), ['email' => 'juan@example.com'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $code = $this->mailedCode($user);

        $ticket = $this->postJson(route('password.forgot.verify'), [
            'email' => 'juan@example.com',
            'code'  => $code,
        ])->assertOk()->assertJson(['ok' => true])->json('ticket');

        $this->assertIsString($ticket);

        $this->postJson(route('password.forgot.reset'), [
            'email'                 => 'juan@example.com',
            'ticket'                => $ticket,
            'password'              => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertOk()->assertJson(['ok' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-password', $user->password));
    }

    #[Test]
    public function an_unknown_address_gets_the_same_reply_as_a_real_one(): void
    {
        $this->user();

        $real    = $this->postJson(route('password.forgot.send'), ['email' => 'juan@example.com'])->assertOk();
        $unknown = $this->postJson(route('password.forgot.send'), ['email' => 'nobody@example.com'])->assertOk();

        // Byte-identical, or this public form is a checker for which of the
        // municipality's addresses hold accounts.
        $this->assertSame($real->json(), $unknown->json());
        $this->assertSame($real->status(), $unknown->status());
    }

    #[Test]
    public function step_three_is_refused_without_a_ticket_from_step_two(): void
    {
        $user = $this->user();

        $this->postJson(route('password.forgot.send'), ['email' => 'juan@example.com'])->assertOk();

        // Skipping straight to the reset endpoint, which is what the mockup's
        // client-side "verified" state amounted to.
        $this->postJson(route('password.forgot.reset'), [
            'email'                 => 'juan@example.com',
            'ticket'                => 'made-up',
            'password'              => 'attacker-password',
            'password_confirmation' => 'attacker-password',
        ])->assertStatus(422)->assertJson(['ok' => false]);

        $user->refresh();
        $this->assertTrue(Hash::check('old-password-123', $user->password));
    }

    #[Test]
    public function a_wrong_code_is_refused(): void
    {
        $user = $this->user();
        $this->postJson(route('password.forgot.send'), ['email' => 'juan@example.com'])->assertOk();

        $code = $this->mailedCode($user);
        $wrong = $code === '000000' ? '111111' : '000000';

        $this->postJson(route('password.forgot.verify'), [
            'email' => 'juan@example.com',
            'code'  => $wrong,
        ])->assertStatus(422)->assertJson(['ok' => false]);
    }

    #[Test]
    public function the_reset_endpoint_enforces_the_password_rules_the_page_states(): void
    {
        $user = $this->user();
        $this->postJson(route('password.forgot.send'), ['email' => 'juan@example.com'])->assertOk();
        $ticket = $this->postJson(route('password.forgot.verify'), [
            'email' => 'juan@example.com',
            'code'  => $this->mailedCode($user),
        ])->json('ticket');

        $this->postJson(route('password.forgot.reset'), [
            'email'                 => 'juan@example.com',
            'ticket'                => $ticket,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422)->assertJsonValidationErrors('password');

        $this->postJson(route('password.forgot.reset'), [
            'email'                 => 'juan@example.com',
            'ticket'                => $ticket,
            'password'              => 'long-enough-password',
            'password_confirmation' => 'different-password',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    #[Test]
    public function the_page_itself_still_renders(): void
    {
        $this->get(route('password.forgot'))
            ->assertOk()
            ->assertSee('Send Verification Code', false);
    }
}
