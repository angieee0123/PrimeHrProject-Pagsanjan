<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeRegistrationController;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Registering an employee sends two emails, and they are a pair: the first
 * asks the employee to verify the address, the second carries the credentials
 * they sign in with. Each is useless alone — a verified address with no
 * password opens nothing, and a password on an unverified account is refused
 * by the `verified` middleware.
 *
 * These pin what the two messages actually say, because that is where this
 * broke before: the credentials table shipped a blank row and an internal
 * primary key, and the verification mail was the framework's stock copy with
 * no sender and no mention of the second email.
 *
 * No database — `credentialsFor()` reads unsaved models, and `RefreshDatabase`
 * does not work in this project.
 */
class RegistrationAccountEmailsTest extends TestCase
{
    private function credentials(Employee $employee, User $user, string $password, array $roles): array
    {
        return (new ReflectionMethod(EmployeeRegistrationController::class, 'credentialsFor'))
            ->invoke(new EmployeeRegistrationController(), $employee, $user, $password, $roles);
    }

    private function sampleEmployee(): Employee
    {
        return new Employee([
            'employee_id' => 'MAG-2026-014',
            'first_name'  => 'Maria',
            'middle_name' => 'Reyes',
            'last_name'   => 'Santos',
        ]);
    }

    private function sampleUser(): User
    {
        return new User([
            'username' => 'santosmaria',
            'email'    => 'maria.santos@pagsanjan.gov.ph',
        ]);
    }

    /**
     * The regression: `employees.id` was mailed under the label "Employee id".
     * The employee is asked for the number on their badge everywhere else in
     * this system, and that is `employees.employee_id`.
     */
    #[Test]
    public function the_credentials_email_carries_the_badge_number_not_the_row_id(): void
    {
        $employee = $this->sampleEmployee();
        $employee->id = 91;

        $details = $this->credentials($employee, $this->sampleUser(), 'sekrit123', ['employee']);

        $this->assertSame('MAG-2026-014', $details['Employee ID']);
        $this->assertNotContains('91', $details);
    }

    /**
     * `User::create()` never set `status`, so the value came from the column
     * default and the in-memory model still read null. Every credentials email
     * rendered a labelled row with nothing beside it.
     */
    #[Test]
    public function no_credentials_row_is_ever_blank(): void
    {
        $details = $this->credentials(
            $this->sampleEmployee(),
            $this->sampleUser(),
            'sekrit123',
            ['employee', 'hr'],
        );

        foreach ($details as $label => $value) {
            $this->assertNotSame('', trim((string) $value), "Row '{$label}' is empty.");
        }
    }

    /**
     * Labels are written out rather than derived, because the view used to run
     * the array keys through `ucfirst()` and print "Employee_id".
     */
    #[Test]
    public function credential_labels_are_readable(): void
    {
        $details = $this->credentials(
            $this->sampleEmployee(),
            $this->sampleUser(),
            'sekrit123',
            ['employee', 'hr'],
        );

        $this->assertSame(
            ['Employee ID', 'Name', 'Username', 'Email', 'Password', 'Role'],
            array_keys($details),
        );

        $this->assertSame('Maria Reyes Santos', $details['Name']);
        $this->assertSame('Employee, Hr', $details['Role']);
    }

    /**
     * The plaintext password is the whole point of this email — the employee
     * has no other copy of it.
     */
    #[Test]
    public function the_password_is_sent_as_typed(): void
    {
        $details = $this->credentials(
            $this->sampleEmployee(),
            $this->sampleUser(),
            'Sekrit!123',
            ['employee'],
        );

        $this->assertSame('Sekrit!123', $details['Password']);
    }

    /**
     * Two unexplained emails arriving together — one of them holding a
     * password — is the shape of a phishing attempt. The verification copy has
     * to name the sender and account for the other message, or the employee is
     * right not to click it.
     */
    #[Test]
    public function the_verification_email_explains_the_pair(): void
    {
        $user = new User(['email' => 'maria.santos@pagsanjan.gov.ph']);
        $user->id = 91;

        $mail = (new VerifyEmail())->toMail($user);
        $body = implode(' ', array_merge(
            [$mail->subject, $mail->greeting, $mail->actionText],
            array_map('strval', $mail->introLines),
            array_map('strval', $mail->outroLines),
        ));

        $this->assertStringContainsString('Pagsanjan', $body);
        $this->assertStringContainsString('Human Resources', $body);
        $this->assertStringContainsString('separate email', $body);
        // The framework's stock subject, which says nothing about who sent it.
        $this->assertNotSame('Verify Email Address', $mail->subject);
    }

    /**
     * The order is load-bearing and stated in the copy: verify first, then
     * sign in. The employee dashboard sits behind the `verified` middleware,
     * so signing in first lands them on the verification notice anyway.
     */
    #[Test]
    public function the_verification_email_names_its_action(): void
    {
        $user = new User(['email' => 'maria.santos@pagsanjan.gov.ph']);
        $user->id = 91;

        $mail = (new VerifyEmail())->toMail($user);

        $this->assertSame('Verify email address', $mail->actionText);
        $this->assertStringContainsString('/email/verify/91/', $mail->actionUrl);
    }
}
