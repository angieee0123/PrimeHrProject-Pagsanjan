<?php

namespace Tests\Unit;

use App\Services\TemporaryPasswordService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The password every account created by an admin is now issued.
 *
 * It replaced a hand-typed value, which means anything wrong here is wrong for
 * every employee registered from that point on: too short and the account is
 * refused by the login screen's own rules, drawn from a predictable source and
 * the credential is guessable, missing a character class and it fails the
 * complexity expectation the rest of the system holds passwords to.
 *
 * No database — the service is pure, and `RefreshDatabase` does not work in
 * this project.
 */
class TemporaryPasswordServiceTest extends TestCase
{
    /**
     * The wizard generates the password and no longer validates one, so the
     * only thing keeping a generated password usable is this service producing
     * a value the server's `min:8` rule accepts.
     */
    #[Test]
    public function it_is_long_enough_for_the_rules_the_rest_of_the_system_enforces(): void
    {
        $this->assertGreaterThanOrEqual(8, TemporaryPasswordService::LENGTH);
        $this->assertSame(TemporaryPasswordService::LENGTH, strlen(TemporaryPasswordService::generate()));
    }

    #[Test]
    public function it_is_alphanumeric_only(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', TemporaryPasswordService::generate());
        }
    }

    /**
     * One character from each pool is guaranteed, which is what makes the
     * generated password satisfy the mixed-case-plus-digit expectation a person
     * typing one satisfies by hand.
     */
    #[Test]
    public function every_password_holds_a_capital_a_small_letter_and_a_digit(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $password = TemporaryPasswordService::generate();

            $this->assertMatchesRegularExpression('/[A-Z]/', $password, "No capital in {$password}");
            $this->assertMatchesRegularExpression('/[a-z]/', $password, "No lowercase in {$password}");
            $this->assertMatchesRegularExpression('/[0-9]/', $password, "No digit in {$password}");
        }
    }

    /**
     * The pools deliberately omit the glyphs that get misread when a password is
     * retyped out of an email: `I`/`l`/`1` and `O`/`0`. A support call about a
     * correct password being rejected is what this prevents.
     */
    #[Test]
    public function it_avoids_the_characters_that_read_as_each_other(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $this->assertDoesNotMatchRegularExpression(
                '/[IlO01]/',
                TemporaryPasswordService::generate(),
            );
        }
    }

    /**
     * Two accounts registered by the same admin must not be able to share a
     * password — that was the whole failure of the hand-typed one, and a
     * generator that returned a constant would reproduce it exactly.
     */
    #[Test]
    public function no_two_passwords_are_the_same(): void
    {
        $passwords = [];

        for ($i = 0; $i < 200; $i++) {
            $passwords[] = TemporaryPasswordService::generate();
        }

        $this->assertCount(200, array_unique($passwords));
    }

    /**
     * The guaranteed characters are drawn first, so they must be shuffled into
     * place afterwards — otherwise positions 1-3 would be uppercase, lowercase,
     * digit in every password the municipality ever issues.
     */
    #[Test]
    public function the_guaranteed_characters_are_not_always_in_the_same_positions(): void
    {
        $first = [];

        for ($i = 0; $i < 60; $i++) {
            $first[] = TemporaryPasswordService::generate()[0];
        }

        $this->assertGreaterThan(
            1,
            count(array_unique($first)),
            'Every password started with the same character.',
        );
    }

    /**
     * A caller asking for something other than the default gets it — the pools
     * are the part that matters, not the length.
     */
    #[Test]
    public function a_requested_length_is_honoured(): void
    {
        $this->assertSame(12, strlen(TemporaryPasswordService::generate(12)));
        $this->assertSame(6, strlen(TemporaryPasswordService::generate(6)));
    }

    /**
     * Below three characters the "one of each class" promise is impossible, so
     * the request is clamped rather than silently returning a password with no
     * digit in it.
     */
    #[Test]
    public function an_impossible_length_is_clamped_rather_than_silently_broken(): void
    {
        $password = TemporaryPasswordService::generate(1);

        $this->assertSame(3, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
        $this->assertMatchesRegularExpression('/[a-z]/', $password);
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
    }
}
