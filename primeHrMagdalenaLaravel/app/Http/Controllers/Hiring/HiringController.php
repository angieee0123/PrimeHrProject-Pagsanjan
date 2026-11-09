<?php

namespace App\Http\Controllers\Hiring;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\RecruitmentAudit;
use Illuminate\Http\Request;

/**
 * Shared rules for the Recruitment pages.
 *
 * These pages now read and write this application's own database. The one
 * permission the module adds is about *which HRIS role may write* — every path
 * is already inside `admin/`, which `EnsureRoleForArea` closes to everyone but
 * admin and HR.
 *
 * The write rule mirrors what the old hiring service called HR_ADMIN: creating
 * or moving a vacancy and reading the audit trail are `admin` only, while
 * screening work — uploading resumes, accept/disapprove — is open to HR staff.
 */
abstract class HiringController extends Controller
{
    /**
     * Refuse a write for anyone who is not an administrator.
     *
     * A hidden button is tidiness, not a permission; every write endpoint calls
     * this itself.
     */
    protected function requireManager(Request $request): void
    {
        abort_unless(
            (bool) $request->user()?->hasRole('admin'),
            403,
            'Only an administrator can change a vacancy or read the recruitment audit trail.'
        );
    }

    /**
     * Record an action against the officer who took it.
     *
     * The recruitment audit trail is the security record for this module, so it
     * is written after the action succeeds — a move that was refused changed
     * nothing and its refusal is already flashed to the officer.
     */
    protected function audit(Request $request, string $action, ?string $entity = null, ?string $entityId = null): void
    {
        RecruitmentAudit::record($request->user(), $action, $entity, $entityId);
    }
}
