<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureLeaveAndBenefitsEligible;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * A Job Order earns no leave credits, so the employee rail has never offered
 * "Leave & Benefits" to one — but the rail was the only thing keeping them out.
 * `/employee/leave` answered to anyone with the employee role, and the File
 * Leave and monetization endpoints behind it were open with it.
 *
 * Two properties are pinned here, and they have to hold together: the rule
 * (`EmploymentDetail::hasLeaveAndBenefits()`, which the hidden link is built
 * from) and the gate that enforces it on the routes. A gate reading a second
 * copy of the rule is how a Job Order ends up refused a page the rail still
 * offers, or offered one they can open.
 *
 * No database: every model is unsaved and its relations are set with
 * `setRelation()`, the same approach `EnsureRoleForAreaTest` uses — and
 * `RefreshDatabase` does not work in this project.
 */
class LeaveAndBenefitsAccessTest extends TestCase
{
    private const REACHED_ROUTE = 'passed';

    /** Routes the entitlement owns: the page and everything its tabs call. */
    private const GATED_ROUTES = [
        'employee.leave',
        'leave.store',
        'leave.cancel',
        'monetization.store',
        'monetization.show',
        'monetization.cancel',
        'monetization.print-form',
        'monetization.download-form',
    ];

    /**
     * The rest of the employee area is theirs to use and must stay open — a
     * Job Order still files travel orders and pass slips, and the calendar is
     * the one page that lists what they themselves filed.
     */
    private const UNGATED_ROUTES = [
        'employee.dashboard',
        'employee.attendance',
        'employee.payslip',
        'employee.travelorder',
        'employee.passslip',
        'employee.leaveCalendar',
    ];

    // ── The rule ──────────────────────────────────────────────────────────

    public static function entitledStatuses(): array
    {
        return [
            'Permanent'    => ['Permanent'],
            'Temporary'    => ['Temporary'],
            'Coterminous'  => ['Coterminous'],
            'Casual'       => ['Casual'],
            'Contractual'  => ['Contractual'],
        ];
    }

    #[DataProvider('entitledStatuses')]
    public function test_every_status_except_job_order_is_entitled(string $status)
    {
        $this->assertTrue((new EmploymentDetail(['employment_status' => $status]))->hasLeaveAndBenefits());
    }

    public function test_job_order_is_not_entitled()
    {
        $this->assertFalse((new EmploymentDetail(['employment_status' => EmploymentDetail::JOB_ORDER]))->hasLeaveAndBenefits());
    }

    public function test_an_unstated_status_is_not_entitled()
    {
        // Not entitled, because nothing states that it is: the rail hides leave
        // and benefits from an employee whose status is unknown, and the gate
        // has to agree with the link or the link is a lie.
        $this->assertFalse((new EmploymentDetail())->hasLeaveAndBenefits());
        $this->assertFalse((new EmploymentDetail(['employment_status' => null]))->hasLeaveAndBenefits());
    }

    public function test_employee_delegates_to_its_employment_record()
    {
        $entitled = new Employee();
        $entitled->setRelation('employmentDetail', new EmploymentDetail(['employment_status' => 'Permanent']));
        $this->assertTrue($entitled->hasLeaveAndBenefits());

        $jobOrder = new Employee();
        $jobOrder->setRelation('employmentDetail', new EmploymentDetail(['employment_status' => EmploymentDetail::JOB_ORDER]));
        $this->assertFalse($jobOrder->hasLeaveAndBenefits());

        // No employment record at all: no entitlement to point at.
        $bare = new Employee();
        $bare->setRelation('employmentDetail', null);
        $this->assertFalse($bare->hasLeaveAndBenefits());
    }

    // ── The gate ──────────────────────────────────────────────────────────

    private function probe(?User $user, string $path = '/employee/leave'): int
    {
        $request = Request::create($path, 'GET');
        $request->setUserResolver(fn () => $user);

        try {
            (new EnsureLeaveAndBenefitsEligible())->handle($request, fn () => new Response(self::REACHED_ROUTE));

            return 200;
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }
    }

    private function user(?string $status, bool $withEmployee = true, bool $withDetail = true): User
    {
        $user = new User(['email' => 'juan.dela.cruz@pagsanjan.gov.ph']);

        if (! $withEmployee) {
            $user->setRelation('employee', null);

            return $user;
        }

        $employee = new Employee(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);
        $employee->id = 41;
        $employee->setRelation(
            'employmentDetail',
            $withDetail ? new EmploymentDetail(['employment_status' => $status]) : null,
        );

        $user->setRelation('employee', $employee);

        return $user;
    }

    public function test_a_job_order_is_refused_the_leave_and_benefits_page()
    {
        $this->assertSame(403, $this->probe($this->user(EmploymentDetail::JOB_ORDER)));
    }

    public function test_an_entitled_employee_reaches_the_page()
    {
        $this->assertSame(200, $this->probe($this->user('Permanent')));
        $this->assertSame(200, $this->probe($this->user('Casual')));
    }

    public function test_an_employee_with_no_employment_record_is_refused()
    {
        $this->assertSame(403, $this->probe($this->user(null, withDetail: false)));
        $this->assertSame(403, $this->probe($this->user(null, withEmployee: false)));
    }

    public function test_guest_defers_to_auth_middleware()
    {
        // The middleware must not answer a guest: the route's `auth` middleware
        // owns the login redirect, and a 403 here would replace it.
        $this->assertSame(200, $this->probe(null));
    }

    // ── The wiring ────────────────────────────────────────────────────────

    public function test_the_entitlement_routes_carry_the_gate()
    {
        foreach (self::GATED_ROUTES as $name) {
            $route = $this->route($name);
            $this->assertContains(
                'leave.eligible',
                $route->gatherMiddleware(),
                "Route [{$name}] does not carry the leave-and-benefits gate.",
            );
        }
    }

    public function test_the_rest_of_the_employee_area_is_left_open()
    {
        foreach (self::UNGATED_ROUTES as $name) {
            $route = $this->route($name);
            $this->assertNotContains(
                'leave.eligible',
                $route->gatherMiddleware(),
                "Route [{$name}] is not part of the leave-and-benefits entitlement and must not be gated.",
            );
        }
    }

    public function test_the_alias_resolves_to_the_middleware()
    {
        // The routes name the alias, so a route can be gated and the gate still
        // never run if the alias is missing or points somewhere else.
        $this->assertSame(
            EnsureLeaveAndBenefitsEligible::class,
            app('router')->getMiddleware()['leave.eligible'] ?? null,
        );
    }

    private function route(string $name): RoutingRoute
    {
        $route = Route::getRoutes()->getByName($name);
        $this->assertNotNull($route, "Route [{$name}] is not registered.");

        return $route;
    }
}
