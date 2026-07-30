<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiAccessPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * AiAccessPolicy is the only thing standing between an employee asking the
 * assistant a question and the assistant handing back the whole payroll. Every
 * retrieval service delegates here, so these assertions cover all of them.
 */
class AiAccessPolicyTest extends TestCase
{
    private AiAccessPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new AiAccessPolicy();
    }

    private function user(array $roles, ?int $employeeId = null, string $status = 'Active'): User
    {
        $user = new User();
        $user->roles = $roles;
        $user->status = $status;
        $user->employee_id = $employeeId;

        return $user;
    }

    #[Test]
    public function admin_hr_and_mayor_get_organisation_wide_access(): void
    {
        $this->assertTrue($this->policy->hasOrgWideAccess($this->user(['admin'])));
        $this->assertTrue($this->policy->hasOrgWideAccess($this->user(['hr'])));
        $this->assertTrue($this->policy->hasOrgWideAccess($this->user(['mayor'])));
        $this->assertTrue($this->policy->hasOrgWideAccess($this->user(['employee', 'hr'])));
    }

    #[Test]
    public function a_plain_employee_does_not(): void
    {
        $this->assertFalse($this->policy->hasOrgWideAccess($this->user(['employee'], 5)));
    }

    /**
     * There is no "manager" role in this system. If one is ever added it must
     * be granted explicitly rather than inherited by accident.
     */
    #[Test]
    public function unknown_roles_confer_nothing(): void
    {
        $this->assertFalse($this->policy->hasOrgWideAccess($this->user(['manager'])));
        $this->assertFalse($this->policy->hasOrgWideAccess($this->user(['supervisor', 'lead'])));
    }

    #[Test]
    public function an_employee_may_only_reach_their_own_record(): void
    {
        $employee = $this->user(['employee'], 5);

        $this->assertTrue($this->policy->canAccessEmployee($employee, 5));
        $this->assertFalse($this->policy->canAccessEmployee($employee, 6));
        $this->assertFalse($this->policy->canAccessEmployee($employee, null));
    }

    #[Test]
    public function an_admin_may_reach_any_record(): void
    {
        $admin = $this->user(['admin'], 1);

        $this->assertTrue($this->policy->canAccessEmployee($admin, 999));
    }

    /**
     * A user with no linked employee row has no "own" data either — the safe
     * outcome is nothing, not everything.
     */
    #[Test]
    public function an_employee_with_no_linked_record_sees_nothing(): void
    {
        $orphan = $this->user(['employee'], null);

        $this->assertFalse($this->policy->canAccessEmployee($orphan, 1));
        $this->assertNull($this->policy->ownEmployeeId($orphan));
    }

    #[Test]
    public function generated_sql_is_limited_to_organisation_wide_roles(): void
    {
        $this->assertTrue($this->policy->canRunGeneratedSql($this->user(['hr'])));
        $this->assertFalse($this->policy->canRunGeneratedSql($this->user(['employee'], 5)));
    }

    #[Test]
    public function inactive_accounts_cannot_use_the_assistant(): void
    {
        $this->assertFalse($this->policy->canUseAssistant($this->user(['admin'], 1, 'Inactive')));
        $this->assertTrue($this->policy->canUseAssistant($this->user(['admin'], 1, 'Active')));
    }

    #[Test]
    public function an_account_with_no_roles_cannot_use_the_assistant(): void
    {
        $this->assertFalse($this->policy->canUseAssistant($this->user([], 1)));
    }

    #[Test]
    public function scoping_a_query_adds_a_self_filter_for_employees(): void
    {
        $employee = $this->user(['employee'], 7);

        $query = \App\Models\Employee::query();
        $this->policy->scopeEmployeeQuery($query, $employee);

        $this->assertStringContainsString('where "id" = ?', $query->toSql());
        $this->assertSame([7], $query->getBindings());
    }

    #[Test]
    public function scoping_a_query_leaves_it_untouched_for_admins(): void
    {
        $query = \App\Models\Employee::query();
        $this->policy->scopeEmployeeQuery($query, $this->user(['admin'], 1));

        $this->assertStringNotContainsString('where', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * An orphaned account must produce an impossible predicate, not an
     * unfiltered query.
     */
    #[Test]
    public function scoping_a_query_for_an_orphan_returns_no_rows(): void
    {
        $query = \App\Models\Employee::query();
        $this->policy->scopeEmployeeQuery($query, $this->user(['employee'], null));

        $this->assertStringContainsString('1 = 0', $query->toSql());
    }
}
