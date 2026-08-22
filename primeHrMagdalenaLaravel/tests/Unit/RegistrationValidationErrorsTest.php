<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeRegistrationController;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * A rejected registration must report every field it rejected.
 *
 * The wizard posts six steps in one request, and the old handler flashed
 * `collect($e->errors())->flatten()->first()` — a single sentence. An admin
 * whose username *and* email were both taken fixed the username, resubmitted,
 * and only then learned about the email: one round trip per mistake, each
 * costing six steps of re-entry.
 *
 * These tests pin the two properties that fixed it — nothing is dropped, and
 * every message carries the step that owns it, since the step number is the
 * only part of the message that says where to go.
 *
 * No database: the helpers are pure functions over a validator's error array,
 * and `RefreshDatabase` does not work in this project.
 */
class RegistrationValidationErrorsTest extends TestCase
{
    private function describe(array $errors): array
    {
        return (new ReflectionMethod(EmployeeRegistrationController::class, 'describeValidationErrors'))
            ->invoke(new EmployeeRegistrationController(), $errors);
    }

    private function summarise(array $errors): string
    {
        return (new ReflectionMethod(EmployeeRegistrationController::class, 'validationSummary'))
            ->invoke(new EmployeeRegistrationController(), $errors);
    }

    /**
     * The regression this exists to prevent: reporting only the first failure.
     */
    #[Test]
    public function every_rejected_field_is_reported(): void
    {
        $details = $this->describe([
            'first_name' => ['The first name field is required.'],
            'user_email' => ['The email address has already been taken.'],
            'username' => ['The username has already been taken.'],
        ]);

        $this->assertCount(3, $details);
        $this->assertSame(
            ['first_name', 'user_email', 'username'],
            array_column($details, 'field'),
        );
    }

    /**
     * A field can fail two rules at once; both messages have to survive.
     */
    #[Test]
    public function multiple_messages_on_one_field_are_all_kept(): void
    {
        $details = $this->describe([
            'password' => [
                'The password field is required.',
                'The password must be at least 8 characters.',
            ],
        ]);

        $this->assertCount(2, $details);
        $this->assertSame([2, 2], array_column($details, 'step'));
    }

    /**
     * The list reads as a walk forward through the wizard, whatever order the
     * validator happened to produce.
     */
    #[Test]
    public function details_are_ordered_by_wizard_step(): void
    {
        $details = $this->describe([
            'gsis_file' => ['The GSIS file must be a file of type: pdf, jpg, jpeg, png.'],
            'department' => ['The selected department is invalid.'],
            'first_name' => ['The first name field is required.'],
            'user_email' => ['The email address has already been taken.'],
        ]);

        $this->assertSame([1, 2, 3, 5], array_column($details, 'step'));
        $this->assertSame(
            ['Personal', 'Account', 'Employment', 'Gov IDs'],
            array_column($details, 'step_name'),
        );
    }

    /**
     * `roles.*` reaches the handler keyed as `roles.0`; the step map holds the
     * base name, so without the split this field would lose its label.
     */
    #[Test]
    public function an_array_field_resolves_to_its_base_step(): void
    {
        $details = $this->describe([
            'roles.0' => ['The selected role is invalid.'],
        ]);

        $this->assertSame('roles', $details[0]['field']);
        $this->assertSame(2, $details[0]['step']);
        $this->assertSame('Account', $details[0]['step_name']);
    }

    /**
     * A rule added to store() but not to FIELD_STEPS must still reach the
     * admin — unlabelled, and last, rather than silently leading the list or
     * throwing on the missing key.
     */
    #[Test]
    public function an_unmapped_field_degrades_instead_of_breaking(): void
    {
        $details = $this->describe([
            'mystery_field' => ['Some unmapped rule failed.'],
            'first_name' => ['The first name field is required.'],
        ]);

        $this->assertCount(2, $details);
        $this->assertNull($details[1]['step']);
        $this->assertNull($details[1]['step_name']);
        $this->assertSame('mystery_field', $details[1]['field']);
    }

    #[Test]
    public function the_summary_counts_messages_not_fields(): void
    {
        $this->assertStringContainsString(
            'One field needs attention',
            $this->summarise(['first_name' => ['The first name field is required.']]),
        );

        // Two messages on one field is still two things to fix.
        $this->assertStringContainsString(
            '2 fields need attention',
            $this->summarise(['password' => ['Required.', 'Too short.']]),
        );
    }
}
