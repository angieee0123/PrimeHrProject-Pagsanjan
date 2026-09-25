<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\User;
use Tests\TestCase;

/**
 * The entitlement gate on the real HTTP stack.
 *
 * `LeaveAndBenefitsAccessTest` (Unit) pins the rule, the middleware and the
 * route wiring route by route. This one answers the question the report was
 * actually about — can a Job Order still open the page? — by sending the
 * request through the whole web group, where the alias has to resolve, the
 * area and verification gates have to pass, and `leave.eligible` has to be the
 * one that turns them away.
 *
 * The refusal happens in middleware, before the controller, so nothing here
 * needs the leave, balance or monetization tables that `RefreshDatabase` cannot
 * build in this project. The permitted case (a Permanent employee reaching the
 * page) is the Unit test's, because *that* would run the controller.
 */
class LeaveAndBenefitsAccessTest extends TestCase
{
    /**
     * A signed-in Job Order employee.
     *
     * Everything the gates before ours read is set in memory rather than
     * queried: `status` for EnsureUserIsActive, `roles` for EnsureRoleForArea,
     * `email_verified_at` for EnsureEmailIsVerifiedForArea, and the two
     * relations the entitlement reads.
     */
    private function jobOrder(): User
    {
        $employee = new Employee();
        $employee->id = 7;
        $employee->setRelation('employmentDetail', new EmploymentDetail([
            'employment_status' => EmploymentDetail::JOB_ORDER,
        ]));

        $user = new User();
        $user->status = 'Active';
        $user->email = 'anselmo.bautista@pagsanjan.gov.ph';
        $user->email_verified_at = now();
        $user->roles = ['employee'];
        $user->setRelation('employee', $employee);

        return $user;
    }

    public function test_a_job_order_cannot_open_the_leave_and_benefits_page()
    {
        $this->actingAs($this->jobOrder())
            ->get('/employee/leave')
            ->assertForbidden();
    }

    public function test_a_job_order_cannot_file_leave_through_the_hidden_form()
    {
        // The page's File Leave modal POSTs here. Blocking the page while
        // leaving its endpoint open would close the door and leave the window.
        $this->actingAs($this->jobOrder())
            ->post('/leave/store', [])
            ->assertForbidden();
    }

    public function test_a_job_order_cannot_file_monetization_through_the_hidden_tab()
    {
        $this->actingAs($this->jobOrder())
            ->post('/employee/monetization', [])
            ->assertForbidden();
    }
}
