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
