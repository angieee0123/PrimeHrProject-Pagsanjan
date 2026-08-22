<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureEmailIsVerifiedForArea;
use App\Http\Middleware\EnsureRoleForArea;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Every account registered gets a verification email, so every account has to
 * be held to it.
 *
 * Before this, `verified` was on six employee routes and nowhere else: an
 * admin, HR officer or the mayor could ignore the link forever and still work
 * the whole system. That is the wrong way round — an unverified address is
 * also where a password reset lands, so the ungated roles were the privileged
 * ones.
 *
 * No database: the middleware reads the request path and the user's
 * `email_verified_at`, both of which an unsaved model can carry, and
 * `RefreshDatabase` does not work in this project.
 */
class EmailVerificationGateTest extends TestCase
{
    private const REACHED_ROUTE = 'passed';

    private function user(bool $verified): User
    {
        $user = new User(['email' => 'maria.santos@pagsanjan.gov.ph']);
        $user->id = 91;
        $user->email_verified_at = $verified ? now() : null;

        return $user;
    }

    /**
     * Run one request through the middleware. The `$next` closure returns a
     * real Response carrying a sentinel body, because the middleware is typed
     * to return one — a bare string would be a test artifact failing as if it
     * were a bug.
     */
    private function gate(string $path, ?User $user, array $headers = []): SymfonyResponse
    {
        $request = Request::create($path, 'GET', [], [], [], array_combine(
            array_map(fn ($k) => 'HTTP_' . str_replace('-', '_', strtoupper($k)), array_keys($headers)),
            array_values($headers),
        ));

        $request->setUserResolver(fn () => $user);

        return (new EnsureEmailIsVerifiedForArea())
            ->handle($request, fn () => new SymfonyResponse(self::REACHED_ROUTE));
    }

    private function reachedRoute(SymfonyResponse $response): bool
    {
        return $response->getContent() === self::REACHED_ROUTE;
    }

    /**
     * The regression this exists to prevent. Each of these is a first path
     * segment that `EnsureRoleForArea` already treats as an area.
     */
    #[Test]
    public function every_area_turns_away_an_unverified_account(): void
    {
        foreach (array_keys(EnsureRoleForArea::AREA_ROLES) as $area) {
            $response = $this->gate("/{$area}/dashboard", $this->user(verified: false));

            $this->assertFalse($this->reachedRoute($response), "/{$area} let an unverified account through.");
            $this->assertSame(
                route('verification.notice'),
                $response->getTargetUrl(),
                "/{$area} did not send an unverified account to the notice.",
            );
        }
    }

    #[Test]
    public function a_verified_account_is_let_through_every_area(): void
    {
        foreach (array_keys(EnsureRoleForArea::AREA_ROLES) as $area) {
            $this->assertTrue(
                $this->reachedRoute($this->gate("/{$area}/dashboard", $this->user(verified: true))),
                "/{$area} blocked a verified account.",
            );
        }
    }

    /**
     * The three roles that were ungated before. Named individually rather than
     * left to the loop above, because "admin, HR and mayor are covered now" is
     * the whole point of the change and should fail by name if it stops being
     * true.
     */
    #[Test]
    public function the_privileged_areas_are_covered(): void
    {
        $this->assertArrayHasKey('admin', EnsureRoleForArea::AREA_ROLES);
        $this->assertArrayHasKey('mayor', EnsureRoleForArea::AREA_ROLES);
        $this->assertContains('hr', EnsureRoleForArea::AREA_ROLES['admin']);

        $this->assertFalse($this->reachedRoute($this->gate('/admin/payroll', $this->user(verified: false))));
        $this->assertFalse($this->reachedRoute($this->gate('/mayor/leave', $this->user(verified: false))));
    }

    /**
     * Gating these would redirect the verification screens to themselves, and
     * strand a user who cannot sign out because sign-out is gated too.
     */
    #[Test]
    public function the_escape_hatches_stay_open(): void
    {
        $unverified = $this->user(verified: false);

        foreach (['/', '/login', '/select-role', '/email/verify', '/logout', '/about'] as $path) {
            $this->assertTrue(
                $this->reachedRoute($this->gate($path, $unverified)),
                "{$path} was gated; an unverified user needs it to get unstuck.",
            );
        }
    }

    #[Test]
    public function a_guest_falls_through_to_the_auth_redirect(): void
    {
        $this->assertTrue($this->reachedRoute($this->gate('/admin/dashboard', null)));
    }

    /**
     * The admin pages are largely fetch calls. Handed a redirect, they parse a
     * login page as JSON and fail with something that names neither the reason
     * nor the fix.
     */
    #[Test]
    public function a_json_caller_gets_a_403_rather_than_a_redirect(): void
    {
        $this->expectException(HttpException::class);

        $this->gate(
            '/admin/personnel',
            $this->user(verified: false),
            ['Accept' => 'application/json'],
        );
    }
}
