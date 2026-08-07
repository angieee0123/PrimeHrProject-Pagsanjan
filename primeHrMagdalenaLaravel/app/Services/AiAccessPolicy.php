<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * One place that decides what the AI Assistant is allowed to surface to a
 * given user. Every retrieval service funnels its queries through here so a
 * permission rule can never drift between features.
 *
 * Roles in this system (users.roles, a JSON array): employee, hr, admin, mayor.
 * There is no "manager" role — do not add checks for one.
 */
class AiAccessPolicy
{
    /** Roles that may see records belonging to other employees. */
    private const ORG_WIDE_ROLES = ['admin', 'hr', 'mayor'];

    /**
     * Whether this user may use the assistant at all. Employees may — they
     * just get a self-scoped view of it.
     */
    public function canUseAssistant(User $user): bool
    {
        return $user->isActive() && !empty($user->roles);
    }

    /**
     * Whether this user can see across the whole organisation, as opposed to
     * only their own employee record.
     */
    public function hasOrgWideAccess(User $user): bool
    {
        return $user->hasAnyRole(self::ORG_WIDE_ROLES);
    }

    /**
     * The employees.id this user is tied to, if any. Users are linked to an
     * employee row via users.employee_id.
     */
    public function ownEmployeeId(User $user): ?int
    {
        return $user->employee_id ? (int) $user->employee_id : null;
    }

    /**
     * Constrain a query over the `employees` table (aliased or not) to the
     * rows this user may see.
     */
    public function scopeEmployeeQuery(EloquentBuilder|QueryBuilder $query, User $user, string $idColumn = 'id'): EloquentBuilder|QueryBuilder
    {
        if ($this->hasOrgWideAccess($user)) {
            return $query;
        }

        $own = $this->ownEmployeeId($user);

        return $own === null
            ? $query->whereRaw('1 = 0')
            : $query->where($idColumn, $own);
    }

    /**
     * Constrain a query over any table carrying an `employee_id` foreign key
     * (documents, attendance, leave_applications, salary_computations, …).
     */
    public function scopeByEmployeeId(EloquentBuilder|QueryBuilder $query, User $user, string $column = 'employee_id'): EloquentBuilder|QueryBuilder
    {
        if ($this->hasOrgWideAccess($user)) {
            return $query;
        }

        $own = $this->ownEmployeeId($user);

        return $own === null
            ? $query->whereRaw('1 = 0')
            : $query->where($column, $own);
    }

    /**
     * Whether this user may read a record belonging to the given employee.
     */
    public function canAccessEmployee(User $user, ?int $employeeId): bool
    {
        if ($this->hasOrgWideAccess($user)) {
            return true;
        }

        $own = $this->ownEmployeeId($user);

        return $own !== null && $employeeId !== null && $own === $employeeId;
    }

    /**
     * Whether this user may run free-form generated SQL. Restricted to
     * org-wide roles: a self-scoped SQL sandbox cannot be enforced reliably
     * once arbitrary joins are in play.
     */
    public function canRunGeneratedSql(User $user): bool
    {
        return $this->hasOrgWideAccess($user);
    }

    /**
     * Who the assistant is talking to, for narration prompts.
     *
     * The same capability answers both audiences — an employee searching
     * employees gets their own record back, an HR officer gets the
     * organisation — so the prose has to know which it is. Prompts that
     * hard-code "an HR administrator" address an employee as though they were
     * staff looking at someone else's file.
     */
    public function audienceLabel(User $user): string
    {
        return $this->hasOrgWideAccess($user)
            ? 'an HR administrator'
            : 'an employee viewing their own records';
    }

    /**
     * A sentence telling the caller their answer was narrowed to their own
     * record, or null when nothing was narrowed.
     *
     * Scoping silently is worse than refusing. An employee who asks "show me
     * everyone in the Mayor's Office" gets their own row back, and a narration
     * that opens "1 employee in the Mayor's Office" states an org-wide fact
     * that is false — the filter, not the roster, produced that 1. The same
     * goes for an empty result: "no files matched" reads as "the file does not
     * exist" when it means "not yours to see". Every capability that scopes a
     * result rather than blocking it appends this.
     */
    public function scopeNotice(User $user): ?string
    {
        if ($this->hasOrgWideAccess($user)) {
            return null;
        }

        return $this->ownEmployeeId($user) === null
            ? 'Your account is not linked to an employee record, so no HR data is visible to it. Please ask HR to link your account.'
            : 'Note: this covers only your own records. Other employees\' records are not visible to your account.';
    }

    /**
     * The same fact as scopeNotice(), phrased for a narration prompt so the
     * model does not describe a self-scoped result as an organisation-wide one.
     */
    public function scopePromptNote(User $user): string
    {
        return $this->hasOrgWideAccess($user)
            ? 'This user has organisation-wide access, so the results cover the whole organisation.'
            : 'IMPORTANT: this user may only see their OWN employee record, so the results below were '
                . 'filtered to that one person. Never describe the count as an organisation-wide figure '
                . '(do not write "there is 1 employee in that department"); say the results are limited '
                . 'to their own record.';
    }

    /**
     * What this user may ask the assistant, in plain language. Drives the
     * "what can you do?" answer, so it stays in step with the scoping rules
     * above rather than being restated in a prompt that can drift from them.
     *
     * @return array<int, string>
     */
    public function describeCapabilities(User $user): array
    {
        $everyone = [
            'Your own leave balances, credits, and recent applications',
            'Your payslips, deductions, and net pay',
            'Your attendance and DTR records',
            'Your trainings and travel orders',
            'How to use PRIME HRIS — filing leave, travel orders, viewing your payslip',
            'HR policy questions — grace periods, late deductions, leave types',
        ];

        if (!$this->hasOrgWideAccess($user)) {
            return $everyone;
        }

        return array_merge($everyone, [
            'Organisation-wide questions — headcount, absences, pending approvals',
            'Employee lookups by name, department, position, or hire date',
            'Uploaded files and documents across all employees',
            'Generated reports and charts, exportable to PDF',
            'Ad-hoc questions answered from the database directly',
            'Drafting HR letters, checklists, and payroll previews',
        ]);
    }

    /**
     * Short description of the caller's scope, for prompt context and audit
     * lines.
     */
    public function describeScope(User $user): string
    {
        if ($this->hasOrgWideAccess($user)) {
            $roles = implode('/', array_intersect(self::ORG_WIDE_ROLES, $user->roles ?? []));

            return "organisation-wide access ({$roles})";
        }

        $own = $this->ownEmployeeId($user);

        return $own === null
            ? 'no employee record linked — no data visible'
            : "restricted to their own employee record (#{$own})";
    }
}
