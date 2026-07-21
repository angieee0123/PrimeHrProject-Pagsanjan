<?php
namespace Tests\Unit;
use Tests\TestCase;                       // boots the app (no DB / RefreshDatabase)
use App\Http\Middleware\EnsureRoleForArea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureRoleForAreaTest extends TestCase
{
    private function probe(?array $roles, string $uri): int
    {
        $mw  = new EnsureRoleForArea();
        $req = Request::create($uri, 'GET');
        if ($roles !== null) {
            $user = new User();
            $user->roles = $roles;
            $req->setUserResolver(fn () => $user);
        }
        try {
            $mw->handle($req, fn ($r) => new Response('ok', 200));
            return 200;
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }
    }

    public function test_employee_blocked_from_admin_and_mayor()
    {
        $this->assertSame(403, $this->probe(['employee'], '/admin/dashboard'));
        $this->assertSame(403, $this->probe(['employee'], '/admin/payroll'));
        $this->assertSame(403, $this->probe(['employee'], '/mayor/dashboard'));
    }
    public function test_employee_allowed_own_area()
    {
        $this->assertSame(200, $this->probe(['employee'], '/employee/dashboard'));
    }
    public function test_admin_and_hr_allowed_admin_area()
    {
        $this->assertSame(200, $this->probe(['admin'], '/admin/payroll'));
        $this->assertSame(200, $this->probe(['hr'], '/admin/personnel'));
    }
    public function test_admin_blocked_from_employee_and_mayor_area()
    {
        $this->assertSame(403, $this->probe(['admin'], '/employee/dashboard'));
        $this->assertSame(403, $this->probe(['admin'], '/mayor/dashboard'));
    }
    public function test_multi_role_user_reaches_both()
    {
        $this->assertSame(200, $this->probe(['admin','employee'], '/admin/dashboard'));
        $this->assertSame(200, $this->probe(['admin','employee'], '/employee/dashboard'));
    }
    public function test_guest_defers_to_auth_middleware()
    {
        $this->assertSame(200, $this->probe(null, '/admin/dashboard'));
    }
    public function test_public_paths_unaffected()
    {
        $this->assertSame(200, $this->probe(['employee'], '/login'));
        $this->assertSame(200, $this->probe(['employee'], '/'));
    }
}
