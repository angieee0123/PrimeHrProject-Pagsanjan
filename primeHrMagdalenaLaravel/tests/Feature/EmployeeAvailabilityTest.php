<?php

namespace Tests\Feature;

use App\Http\Controllers\EmployeeRegistrationController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The wizard's "is this login taken?" check (GET /admin/personnel/check-availability).
 *
 * The username and email both have to be unique in `users`, and the only way an
 * admin used to learn otherwise was a failed submit after six steps of typing.
 * That behaviour is unchanged — `unique:` in store() is still what enforces it —
 * and what is pinned here is the *reporting* the wizard asks for as the admin
 * leaves the field:
 *
 * - a taken username and a taken email are refused, each naming the value;
 * - a free one is allowed, so the green state is not a lie;
 * - the two are looked up in the column they name, not one another (a username
 *   equal to somebody else's *email* must not come back taken);
 * - a blank value is "available" rather than an error, because an empty box on
 *   a freshly opened wizard is not a conflict — `required` is the local
 *   validator's job;
 * - nobody signed in can ask at all.
 *
 * No RefreshDatabase: this project's migrations cannot run on the test
 * connection (see CLAUDE.md), so the only table this route touches — `users` —
 * is built by hand.
 */
class EmployeeAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('roles')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    /**
     * The middleware on the web group is what makes this reachable at all:
     * EnsureUserIsActive reads `status`, EnsureRoleForArea reads `roles` for the
     * `admin` prefix, and EnsureEmailIsVerifiedForArea reads email_verified_at —
     * which is deliberately not mass-assignable, so it is set directly rather
     * than passed to create() and silently dropped.
     */
    private function actingAsAdmin(): User
    {
        $user = User::create([
            'email'    => 'hr@example.test',
            'username' => 'hradmin',
            'roles'    => ['admin'],
            'status'   => 'Active',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user);

        return $user;
    }

    private function existingEmployeeAccount(): User
    {
        return User::create([
            'email'    => 'juan.cruz@example.test',
            'username' => 'cruzjuan',
            'roles'    => ['employee'],
            'status'   => 'Active',
        ]);
    }

    private function ask(string $field, string $value)
    {
        return $this->getJson(route('admin.personnel.check-availability', [
            'field' => $field,
            'value' => $value,
        ]));
    }

    #[Test]
    public function a_taken_username_is_refused_and_names_the_value(): void
    {
        $this->actingAsAdmin();
        $this->existingEmployeeAccount();

        $response = $this->ask('username', 'cruzjuan');

        $response->assertOk()
            ->assertJson(['available' => false])
            ->assertJsonPath('message', EmployeeRegistrationController::takenMessage('username', 'cruzjuan'));

        $this->assertStringContainsString('cruzjuan', $response->json('message'));
    }

    #[Test]
    public function a_taken_email_is_refused_and_names_the_value(): void
    {
        $this->actingAsAdmin();
        $this->existingEmployeeAccount();

        $response = $this->ask('user_email', 'juan.cruz@example.test');

        $response->assertOk()
            ->assertJson(['available' => false])
            ->assertJsonPath('message', EmployeeRegistrationController::takenMessage('email', 'juan.cruz@example.test'));
    }

    #[Test]
    public function a_free_username_and_email_are_allowed(): void
    {
        $this->actingAsAdmin();
        $this->existingEmployeeAccount();

        $this->ask('username', 'santosmaria')
            ->assertOk()
            ->assertJson(['available' => true, 'message' => null]);

        $this->ask('user_email', 'maria.santos@example.test')
            ->assertOk()
            ->assertJson(['available' => true, 'message' => null]);
    }

    #[Test]
    public function each_field_is_looked_up_in_its_own_column(): void
    {
        $this->actingAsAdmin();
        $this->existingEmployeeAccount();

        // 'cruzjuan' is a username and 'juan.cruz@example.test' is an email. A
        // lookup that crossed the two columns would report both of these taken
        // and block a login nobody holds.
        $this->ask('user_email', 'cruzjuan')
            ->assertOk()
            ->assertJson(['available' => true]);

        $this->ask('username', 'juan.cruz@example.test')
            ->assertOk()
            ->assertJson(['available' => true]);
    }

    #[Test]
    public function a_blank_value_is_available_rather_than_an_error(): void
    {
        $this->actingAsAdmin();

        // The wizard posts the field on every blur, including the empty boxes it
        // opens with; an error beside an untouched field reads as a defect.
        $this->ask('username', '')
            ->assertOk()
            ->assertJson(['available' => true, 'message' => null]);

        $this->ask('username', '   ')
            ->assertOk()
            ->assertJson(['available' => true, 'message' => null]);
    }

    #[Test]
    public function an_unknown_field_is_rejected(): void
    {
        $this->actingAsAdmin();

        // Not a 500 and not a silent "available": a page asking about a field
        // this endpoint does not own is a bug worth surfacing.
        $this->ask('password', 'secret123')->assertStatus(422);
    }

    #[Test]
    public function a_guest_cannot_ask_whether_an_account_exists(): void
    {
        $this->existingEmployeeAccount();

        $this->getJson(route('admin.personnel.check-availability', [
            'field' => 'username',
            'value' => 'cruzjuan',
        ]))->assertUnauthorized();
    }
}
