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

    /**
     * Narration prompts address the caller. An employee reaching a shared
     * capability must not be written to as though they were HR staff looking
     * at someone else's file.
     */
    #[Test]
    public function the_audience_label_follows_the_callers_access(): void
    {
        $this->assertSame('an HR administrator', $this->policy->audienceLabel($this->user(['hr'], 1)));
        $this->assertSame(
            'an employee viewing their own records',
            $this->policy->audienceLabel($this->user(['employee'], 5))
        );
    }

    /**
     * The "what can you do?" list is derived from the same policy that scopes
     * the queries, so it cannot advertise a capability the caller would then
     * be refused.
     */
    #[Test]
    public function the_capability_list_never_promises_more_than_the_scope_allows(): void
    {
        $employee = $this->policy->describeCapabilities($this->user(['employee'], 5));
        $hr = $this->policy->describeCapabilities($this->user(['hr'], 1));

        $this->assertNotEmpty($employee);
        $this->assertCount(count($employee) + 6, $hr);

        // Nothing org-wide may appear in an employee's list.
        foreach ($employee as $item) {
            $this->assertStringNotContainsStringIgnoringCase('all employees', $item);
            $this->assertStringNotContainsStringIgnoringCase('organisation-wide', $item);
        }
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

    /**
     * Silent scoping is its own defect. An employee who asks "show me everyone
     * in the Mayor's Office" gets their own row back; without a notice the
     * narration reports "1 employee" as though that were the department's
     * headcount.
     */
    #[Test]
    public function a_self_scoped_caller_is_told_the_answer_was_narrowed(): void
    {
        $notice = $this->policy->scopeNotice($this->user(['employee'], 9));

        $this->assertNotNull($notice);
        $this->assertStringContainsString('your own records', $notice);
    }

    #[Test]
    public function an_org_wide_caller_gets_no_scope_notice(): void
    {
        $this->assertNull($this->policy->scopeNotice($this->user(['admin'], 3)));
        $this->assertNull($this->policy->scopeNotice($this->user(['hr'], null)));
        $this->assertNull($this->policy->scopeNotice($this->user(['mayor'], 7)));
    }

    #[Test]
    public function an_orphaned_account_is_told_why_it_sees_nothing(): void
    {
        $notice = $this->policy->scopeNotice($this->user(['employee'], null));

        $this->assertNotNull($notice);
        $this->assertStringContainsString('not linked to an employee record', $notice);
    }

    /**
     * The prompt-side half of the same fact. A narration prompt that omits it
     * lets the model state a self-scoped count as an organisation-wide one.
     */
    #[Test]
    public function the_narration_prompt_note_distinguishes_the_two_audiences(): void
    {
        $this->assertStringContainsString(
            'own',
            $this->policy->scopePromptNote($this->user(['employee'], 9))
        );
        $this->assertStringContainsString(
            'organisation-wide',
            $this->policy->scopePromptNote($this->user(['admin'], 3))
        );
    }
}
