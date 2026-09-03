<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * The one way a notification is written.
 *
 * Every module announces itself through a method on this class, and every one
 * of those methods goes through {@see deliver()}. That single funnel is what
 * gives the whole system four properties no per-module implementation could
 * keep on its own:
 *
 * - **A notification can never break the thing it announces.** deliver() catches
 *   everything and logs it. Approving a leave application must not fail because
 *   the bell could not be written — the approval is the record, the notification
 *   is a courtesy. (The call sites matter too: a deliver() inside a database
 *   transaction is still inside that transaction. They were moved after the
 *   commit for exactly this reason.)
 * - **A notification is idempotent.** Every writer passes a `dedupe_key` naming
 *   the event, unique per recipient, so a double-clicked Approve button or a
 *   retried request leaves one row, not two.
 * - **A notification reaches a bell that can open it.** `audience` decides which
 *   bell, and the link is always to a page that audience is allowed into — the
 *   mayor is never linked at /admin, an employee never at somebody else's file.
 * - **Recipients are resolved in one place.** approvers() and overseers() below
 *   are the only definitions of "who handles this" and "who watches this".
 */
class NotificationService
{
    /* ===================================================================== */
    /*  Core                                                                 */
    /* ===================================================================== */

    /**
     * Write one notification. Never throws.
     *
     * @param  User|int|null  $user       recipient
     * @param  array          $attributes type, audience, title, message, link,
     *                                    related_id, related_type, dedupe_key
     */
    public static function deliver($user, array $attributes): ?Notification
    {
        $userId = $user instanceof User ? $user->id : $user;

        if (! $userId) {
            return null;
        }

        try {
            // `is_read` is defaulted here as well as on the column: without it
            // the model handed back from create() has no value for it at all,
            // so `$notification->is_read` reads null and markAsUnread()'s
            // "already unread?" guard sees a state that is neither.
            $attributes = array_merge([
                'type'     => 'system',
                'audience' => 'employee',
                'link'     => null,
                'is_read'  => false,
                'read_at'  => null,
            ], $attributes);

            $attributes['user_id'] = $userId;

            $dedupeKey = $attributes['dedupe_key'] ?? null;

            // The unique index is (user_id, dedupe_key). firstOrCreate covers
            // the ordinary double-submit; the catch below covers two requests
            // racing each other into the same key, where both pass the SELECT.
            if ($dedupeKey && Notification::hasNotificationColumn('dedupe_key')) {
                try {
                    return Notification::firstOrCreate(
                        ['user_id' => $userId, 'dedupe_key' => $dedupeKey],
                        $attributes
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                    return Notification::where('user_id', $userId)
                        ->where('dedupe_key', $dedupeKey)
                        ->first();
                }
            }

            unset($attributes['dedupe_key']);

            return Notification::create($attributes);
        } catch (\Throwable $e) {
            // The workflow that called us has already succeeded (or is about
            // to). Record why the bell stayed quiet and let it continue.
            Log::error('Notification delivery failed', [
                'user_id'    => $userId,
                'type'       => $attributes['type'] ?? null,
                'dedupe_key' => $attributes['dedupe_key'] ?? null,
                'exception'  => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Deliver the same notification to several recipients.
     *
     * `$attributes` may be a callable taking the User, for the case where the
     * sentence differs per recipient ("your travel order" vs "a travel order
     * you are a companion on"). A recipient whose write fails does not stop
     * the ones after it.
     */
    public static function deliverMany(iterable $users, $attributes): void
    {
        foreach ($users as $user) {
            $payload = is_callable($attributes) ? $attributes($user) : $attributes;

            if ($payload === null) {
                continue;
            }

            self::deliver($user, $payload);
        }
    }

    /**
     * Who handles approval work: the admin and HR accounts.
     *
     * Inactive accounts are excluded — they cannot sign in, so a notification
     * for them is a row nobody will ever read — and so is the person who
     * performed the action. An HR officer filing a leave application on an
     * employee's behalf does not need "New Leave Request" for the request they
     * just typed; that is noise in the one list that is supposed to be a queue.
     *
     * @param  string|null  $preferenceKey  Settings → Notifications category to honour
     */
    public static function approvers(?string $preferenceKey = null): Collection
    {
        return self::recipients(['admin', 'hr'], $preferenceKey);
    }

    /**
     * Who watches without acting: the mayor's accounts.
     *
     * The mayor's area is read-only, so the mayor is told what was *decided*,
     * never what is queued — a pending item in the mayor's bell would imply an
     * action the mayor's screens do not offer.
     */
    public static function overseers(?string $preferenceKey = null): Collection
    {
        return self::recipients(['mayor'], $preferenceKey);
    }

    protected static function recipients(array $roles, ?string $preferenceKey): Collection
    {
        try {
            $actorId = Auth::id();

            return User::query()
                ->where(function ($q) use ($roles) {
                    foreach ($roles as $role) {
                        $q->orWhereJsonContains('roles', $role);
                    }
                })
                ->get()
                ->filter(fn (User $user) => $user->hasAnyRole($roles))
                ->filter(fn (User $user) => $user->isActive())
                ->filter(fn (User $user) => $user->id !== $actorId)
                ->filter(fn (User $user) => $preferenceKey === null || $user->wantsNotification($preferenceKey))
                ->values();
        } catch (\Throwable $e) {
            Log::error('Notification recipient lookup failed', [
                'roles'     => $roles,
                'exception' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * A named route, or null if this install does not have it.
     *
     * A missing route name throws, and an exception raised while building a
     * *link* would take down the approval the link points at. A notification
     * with no link still says what happened; it just does not navigate.
     */
    public static function link(string $name, array $parameters = []): ?string
    {
        try {
            return route($name, $parameters);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** "Juan Dela Cruz", or a stable stand-in when the row is incomplete. */
    public static function personName(?Employee $employee): string
    {
        if (! $employee) {
            return 'An employee';
        }

        $name = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));

        return $name !== '' ? $name : 'An employee';
    }

    /** The user account behind an employee row, if they have one. */
    protected static function userFor(?Employee $employee): ?User
    {
        return $employee?->user;
    }

    /* ===================================================================== */
    /*  Leave                                                                */
    /* ===================================================================== */

    /** Employee filed a leave application → the admin/HR queue. */
    public static function leaveRequestSubmitted($leaveApplication): void
    {
        $employee = $leaveApplication->employee ?? null;
        $name = self::personName($employee);
        $leaveName = $leaveApplication->leaveType->leave_name ?? 'leave';
        $period = self::dateRange($leaveApplication->start_date ?? null, $leaveApplication->end_date ?? null);
        $days = $leaveApplication->number_of_days;

        self::deliverMany(self::approvers('leave_requests'), [
            'type'         => 'leave_request',
            'audience'     => 'admin',
            'title'        => 'New Leave Request',
            'message'      => "{$name} submitted a {$leaveName} request for {$days} day(s){$period} and it is waiting for your review.",
            'link'         => self::link('admin.leave', ['highlight' => $leaveApplication->id]),
            'related_id'   => $leaveApplication->id,
            'related_type' => 'App\Models\LeaveApplication',
            'dedupe_key'   => "leave:{$leaveApplication->id}:submitted",
        ]);
    }

    /**
     * HR decided a leave application → the filer, and the mayor as oversight.
     *
     * The column stores 'rejected'; every screen in this system calls that
     * "Disapproved", and the employee is told what the screen says.
     */
    public static function leaveRequestStatusChanged($leaveApplication, $status): void
    {
        $employee = $leaveApplication->employee ?? null;
        $statusText = self::statusLabel($status);
        $leaveName = $leaveApplication->leaveType->leave_name ?? 'leave';
        $period = self::dateRange($leaveApplication->start_date ?? null, $leaveApplication->end_date ?? null);
        $reason = self::reasonClause($status, $leaveApplication->approver_remarks ?? null);

        self::deliver(self::userFor($employee), [
            'type'         => 'leave_request',
            'audience'     => 'employee',
            'title'        => "Leave Request {$statusText}",
            'message'      => "Your {$leaveName} request{$period} has been {$statusText}.{$reason}",
            'link'         => self::link('employee.leave', ['highlight' => $leaveApplication->id]),
            'related_id'   => $leaveApplication->id,
            'related_type' => 'App\Models\LeaveApplication',
            'dedupe_key'   => "leave:{$leaveApplication->id}:{$status}",
        ]);

        $name = self::personName($employee);

        self::deliverMany(self::overseers('leave_requests'), [
            'type'         => 'leave_request',
            'audience'     => 'mayor',
            'title'        => "Leave {$statusText}",
            'message'      => "{$name}'s {$leaveName} request{$period} was {$statusText} by HR.",
            'link'         => self::link('mayor.leave'),
            'related_id'   => $leaveApplication->id,
            'related_type' => 'App\Models\LeaveApplication',
            'dedupe_key'   => "leave:{$leaveApplication->id}:{$status}:oversight",
        ]);
    }

    /**
     * Employee withdrew a pending leave application → the admin/HR queue.
     *
     * The queue is a list of things to decide, so an item leaving it is news:
     * without this, HR opens a request that is no longer there to approve.
     */
    public static function leaveRequestCancelled($leaveApplication): void
    {
        $employee = $leaveApplication->employee ?? null;
        $name = self::personName($employee);
        $leaveName = $leaveApplication->leaveType->leave_name ?? 'leave';
        $period = self::dateRange($leaveApplication->start_date ?? null, $leaveApplication->end_date ?? null);

        self::deliverMany(self::approvers('leave_requests'), [
            'type'         => 'leave_request',
            'audience'     => 'admin',
            'title'        => 'Leave Request Cancelled',
            'message'      => "{$name} cancelled their {$leaveName} request{$period}. No decision is needed.",
            'link'         => self::link('admin.leave', ['highlight' => $leaveApplication->id]),
            'related_id'   => $leaveApplication->id,
            'related_type' => 'App\Models\LeaveApplication',
            'dedupe_key'   => "leave:{$leaveApplication->id}:cancelled",
        ]);
    }

    /* ===================================================================== */
    /*  Monetization                                                         */
    /*                                                                       */
    /*  These links carry `tab` but deliberately no `highlight`. Both leave   */
    /*  pages read `highlight` as a *leave application* id — the admin tab's  */
    /*  handler clicks `[data-leave-app-id="…"]` — so a monetization id in    */
    /*  that parameter would open whichever leave application happens to      */
    /*  share the number. The tab is the right landing place; the wrong       */
    /*  record opened automatically is worse than none.                      */
    /* ===================================================================== */

    /** Employee filed a monetization request → the admin/HR queue. */
    public static function monetizationSubmitted($monetization): void
    {
        $employee = $monetization->employee ?? null;
        $name = self::personName($employee);
        $days = (float) $monetization->vl_days + (float) $monetization->sl_days;
        $amount = number_format((float) $monetization->computed_amount, 2);

        self::deliverMany(self::approvers('employee_requests'), [
            'type'         => 'monetization',
            'audience'     => 'admin',
            'title'        => 'New Monetization Request',
            'message'      => "{$name} filed monetization request {$monetization->request_number} for "
                . rtrim(rtrim(number_format($days, 1), '0'), '.') . " day(s), computed at P{$amount}.",
            'link'         => self::link('admin.leave', ['tab' => 'monetization']),
            'related_id'   => $monetization->id,
            'related_type' => 'App\Models\MonetizationRequest',
            'dedupe_key'   => "monetization:{$monetization->id}:submitted",
        ]);
    }

    /** HR decided a monetization request → the filer. */
    public static function monetizationStatusChanged($monetization, string $status): void
    {
        $employee = $monetization->employee ?? null;
        $statusText = self::statusLabel($status);
        $amount = number_format((float) $monetization->computed_amount, 2);
        $reason = self::reasonClause($status, $monetization->approver_remarks ?? null);

        $message = $status === 'approved'
            ? "Your monetization request {$monetization->request_number} for P{$amount} has been Approved. The printable form is available from the request's detail view."
            : "Your monetization request {$monetization->request_number} for P{$amount} has been {$statusText}.{$reason}";

        self::deliver(self::userFor($employee), [
            'type'         => 'monetization',
            'audience'     => 'employee',
            'title'        => "Monetization Request {$statusText}",
            'message'      => $message,
            'link'         => self::link('employee.leave', ['tab' => 'monetization']),
            'related_id'   => $monetization->id,
            'related_type' => 'App\Models\MonetizationRequest',
            'dedupe_key'   => "monetization:{$monetization->id}:{$status}",
        ]);
    }

    /** Employee withdrew a pending monetization request → the admin/HR queue. */
    public static function monetizationCancelled($monetization): void
    {
        $name = self::personName($monetization->employee ?? null);

        self::deliverMany(self::approvers('employee_requests'), [
            'type'         => 'monetization',
            'audience'     => 'admin',
            'title'        => 'Monetization Request Cancelled',
            'message'      => "{$name} cancelled monetization request {$monetization->request_number}. No decision is needed.",
            'link'         => self::link('admin.leave', ['tab' => 'monetization']),
            'related_id'   => $monetization->id,
            'related_type' => 'App\Models\MonetizationRequest',
            'dedupe_key'   => "monetization:{$monetization->id}:cancelled",
        ]);
    }

    /* ===================================================================== */
    /*  Travel orders                                                        */
    /* ===================================================================== */

    /** Employee was named as a companion on somebody's travel order. */
    public static function travelOrderCompanionInvited($travelOrder, $companion): void
    {
        $companionEmployee = $companion->employee ?? null;
        $filerName = self::personName($travelOrder->employee ?? null);
        $dates = $travelOrder->formatted_dates;

        self::deliver(self::userFor($companionEmployee), [
            'type'         => 'travel_order',
            'audience'     => 'employee',
            'title'        => 'Travel Order Companion Request',
            'message'      => "{$filerName} included you as a companion on travel order {$travelOrder->order_number} to {$travelOrder->destination} ({$dates}). Please accept or reject the request.",
            'link'         => self::link('employee.travelorder', ['highlight' => $travelOrder->id]),
            'related_id'   => $travelOrder->id,
            'related_type' => 'App\Models\TravelOrder',
            'dedupe_key'   => "travel:{$travelOrder->id}:invited:{$companion->id}",
        ]);
    }

    /** A companion accepted or rejected → the filer. */
    public static function travelOrderCompanionResponded($travelOrder, $companion): void
    {
        $filer = $travelOrder->employee ?? null;
        $companionName = self::personName($companion->employee ?? null);
        $statusText = ucfirst((string) $companion->status);

        $message = "{$companionName} has {$companion->status} your companion request for travel order {$travelOrder->order_number}.";

        if ($travelOrder->allCompanionsResponded()) {
            $message .= ' All companions have responded — you can now forward it to HR for approval.';
        }

        self::deliver(self::userFor($filer), [
            'type'         => 'travel_order',
            'audience'     => 'employee',
            'title'        => "Companion Request {$statusText}",
            'message'      => $message,
            'link'         => self::link('employee.travelorder', ['highlight' => $travelOrder->id]),
            'related_id'   => $travelOrder->id,
            'related_type' => 'App\Models\TravelOrder',
            'dedupe_key'   => "travel:{$travelOrder->id}:companion:{$companion->id}:{$companion->status}",
        ]);
    }

    /** Travel order reached HR for approval → the admin/HR queue. */
    public static function travelOrderForwarded($travelOrder): void
    {
        $filerName = self::personName($travelOrder->employee ?? null);
        $companionCount = $travelOrder->companions()->where('status', 'accepted')->count();
        $companionText = $companionCount > 0 ? " with {$companionCount} companion(s)" : '';
        $dates = $travelOrder->formatted_dates;

        self::deliverMany(self::approvers('travel_orders'), [
            'type'         => 'travel_order',
            'audience'     => 'admin',
            'title'        => 'New Travel Order Request',
            'message'      => "{$filerName} submitted travel order {$travelOrder->order_number} to {$travelOrder->destination} ({$dates}){$companionText}.",
            'link'         => self::link('admin.travelorder', ['highlight' => $travelOrder->id]),
            'related_id'   => $travelOrder->id,
            'related_type' => 'App\Models\TravelOrder',
            'dedupe_key'   => "travel:{$travelOrder->id}:forwarded",
        ]);
    }

    /**
     * HR decided a travel order → the filer, every accepted companion, and the
     * mayor as oversight.
     *
     * The companions are notified because the decision is about days they will
     * or will not be out of the office; they filed nothing, so nothing else
     * would tell them.
     */
    public static function travelOrderStatusChanged($travelOrder, $status): void
    {
        $statusText = self::statusLabel($status);
        $reasonText = $travelOrder->remarks ?? $travelOrder->disapproval_reason;
        $reason = self::reasonClause($status, $reasonText);
        $dates = $travelOrder->formatted_dates;

        self::deliver(self::userFor($travelOrder->employee ?? null), [
            'type'         => 'travel_order',
            'audience'     => 'employee',
            'title'        => "Travel Order {$statusText}",
            'message'      => "Your travel order {$travelOrder->order_number} to {$travelOrder->destination} ({$dates}) has been {$statusText}.{$reason}",
            'link'         => self::link('employee.travelorder', ['highlight' => $travelOrder->id]),
            'related_id'   => $travelOrder->id,
            'related_type' => 'App\Models\TravelOrder',
            'dedupe_key'   => "travel:{$travelOrder->id}:{$status}",
        ]);

        $companions = $travelOrder->companions()->where('status', 'accepted')->with('employee.user')->get();

        foreach ($companions as $companion) {
            self::deliver(self::userFor($companion->employee ?? null), [
                'type'         => 'travel_order',
                'audience'     => 'employee',
                'title'        => "Travel Order {$statusText}",
                'message'      => "Travel order {$travelOrder->order_number} to {$travelOrder->destination} ({$dates}), where you are a companion, has been {$statusText}.{$reason}",
                'link'         => self::link('employee.travelorder', ['highlight' => $travelOrder->id]),
                'related_id'   => $travelOrder->id,
                'related_type' => 'App\Models\TravelOrder',
                'dedupe_key'   => "travel:{$travelOrder->id}:{$status}:companion:{$companion->id}",
            ]);
        }

        $filerName = self::personName($travelOrder->employee ?? null);

        self::deliverMany(self::overseers('travel_orders'), [
            'type'         => 'travel_order',
            'audience'     => 'mayor',
            'title'        => "Travel Order {$statusText}",
            'message'      => "{$filerName}'s travel order {$travelOrder->order_number} to {$travelOrder->destination} ({$dates}) was {$statusText} by HR.",
            'link'         => self::link('mayor.travelorder'),
            'related_id'   => $travelOrder->id,
            'related_type' => 'App\Models\TravelOrder',
            'dedupe_key'   => "travel:{$travelOrder->id}:{$status}:oversight",
        ]);
    }

    /* ===================================================================== */
    /*  Pass slips                                                           */
    /* ===================================================================== */

    /** Employee filed a pass slip → the admin/HR queue. */
    public static function passSlipSubmitted($passSlip): void
    {
        $employee = $passSlip->employee ?? null;

        if (! $employee) {
            return;
        }

        $name = self::personName($employee);
        $date = $passSlip->date ? $passSlip->date->format('M d, Y') : 'an unspecified date';

        self::deliverMany(self::approvers('employee_requests'), [
            'type'         => 'pass_slip',
            'audience'     => 'admin',
            'title'        => 'New Pass Slip Request',
            'message'      => "{$name} filed pass slip {$passSlip->slip_number} for {$date}: {$passSlip->reason}",
            'link'         => self::link('admin.passslip', ['highlight' => $passSlip->id]),
            'related_id'   => $passSlip->id,
            'related_type' => 'App\Models\PassSlip',
            'dedupe_key'   => "passslip:{$passSlip->id}:submitted",
        ]);
    }

    /**
     * HR decided a pass slip → the filer, and the mayor as oversight.
     *
     * A pass slip is time the employee intends to be out of the office, so the
     * decision is the half they act on — leaving it to be discovered by
     * reopening the page is how somebody leaves on a slip that was refused.
     */
    public static function passSlipStatusChanged($passSlip, $status): void
    {
        $employee = $passSlip->employee ?? null;
        $statusText = self::statusLabel($status);
        $date = $passSlip->date ? $passSlip->date->format('M d, Y') : 'an unspecified date';
        $reason = self::reasonClause($status, $passSlip->remarks ?? null);

        self::deliver(self::userFor($employee), [
            'type'         => 'pass_slip',
            'audience'     => 'employee',
            'title'        => "Pass Slip {$statusText}",
            'message'      => "Your pass slip {$passSlip->slip_number} for {$date} has been {$statusText}.{$reason}",
            'link'         => self::link('employee.passslip'),
            'related_id'   => $passSlip->id,
            'related_type' => 'App\Models\PassSlip',
            'dedupe_key'   => "passslip:{$passSlip->id}:{$status}",
        ]);

        $name = self::personName($employee);

        self::deliverMany(self::overseers('employee_requests'), [
            'type'         => 'pass_slip',
            'audience'     => 'mayor',
            'title'        => "Pass Slip {$statusText}",
            'message'      => "{$name}'s pass slip {$passSlip->slip_number} for {$date} was {$statusText} by HR.",
            'link'         => self::link('mayor.passslip'),
            'related_id'   => $passSlip->id,
            'related_type' => 'App\Models\PassSlip',
            'dedupe_key'   => "passslip:{$passSlip->id}:{$status}:oversight",
        ]);
    }

    /* ===================================================================== */
    /*  Training                                                             */
    /* ===================================================================== */

    public static function trainingSubmitted($training): void
    {
        $name = self::personName($training->employee ?? null);

        self::deliverMany(self::approvers('training_submissions'), [
            'type'         => 'training',
            'audience'     => 'admin',
            'title'        => 'New Training Submission',
            'message'      => "{$name} submitted a training record for verification: {$training->title}",
            'link'         => self::link('admin.training'),
            'related_id'   => $training->id,
            'related_type' => 'App\Models\Training',
            'dedupe_key'   => "training:{$training->id}:submitted",
        ]);
    }

    /**
     * HR verified or rejected a training record → the employee.
     *
     * The hours matter here, not just the verdict: a rejected submission
     * credits 0 to CSC PDS Section IV however many hours it declared, which is
     * the whole reason the employee needs telling.
     */
    public static function trainingVerified($training, $status): void
    {
        $statusText = $status === 'verified' ? 'Verified' : 'Rejected';

        $message = $status === 'verified'
            ? "Your training record '{$training->title}' has been Verified and now counts toward your PDS Section IV hours."
            : "Your training record '{$training->title}' was Rejected, so it credits no hours to your PDS Section IV.";

        self::deliver(self::userFor($training->employee ?? null), [
            'type'         => 'training',
            'audience'     => 'employee',
            'title'        => "Training {$statusText}",
            'message'      => $message,
            'link'         => self::link('employee.training'),
            'related_id'   => $training->id,
            'related_type' => 'App\Models\Training',
            'dedupe_key'   => "training:{$training->id}:{$status}",
        ]);
    }

    /* ===================================================================== */
    /*  Payroll and attendance                                               */
    /* ===================================================================== */

    /**
     * Payslips were generated for a period → each employee they cover.
     *
     * Keyed by the period, so re-running generation for the same fortnight
     * does not put the same sentence in everybody's bell a second time.
     */
    public static function payrollGenerated($startDate, $endDate, $employeeIds = []): void
    {
        $period = date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));
        $key = 'payroll:' . date('Y-m-d', strtotime($startDate)) . ':' . date('Y-m-d', strtotime($endDate));

        try {
            $users = User::query()
                ->when(
                    empty($employeeIds),
                    fn ($q) => $q->whereHas('employee'),
                    fn ($q) => $q->whereHas('employee', fn ($e) => $e->whereIn('id', $employeeIds))
                )
                ->get()
                ->filter(fn (User $user) => $user->isActive())
                ->filter(fn (User $user) => $user->wantsNotification('payslip_available'));
        } catch (\Throwable $e) {
            Log::error('Payroll notification recipient lookup failed', ['exception' => $e->getMessage()]);

            return;
        }

        self::deliverMany($users, [
            'type'       => 'payroll',
            'audience'   => 'employee',
            'title'      => 'Payslip Available',
            'message'    => "Your payslip for {$period} is now available.",
            'link'       => self::link('employee.payslip'),
            'dedupe_key' => $key,
        ]);
    }

    /**
     * HR corrected an attendance record → the employee whose day it is.
     *
     * The dedupe key carries the minute rather than only the row id: a second
     * correction weeks later is genuine news and must be announced, while a
     * double-submitted save within the same minute is not.
     */
    public static function attendanceCorrected($attendance): void
    {
        $employee = $attendance->employee ?? null;
        $date = date('M d, Y', strtotime($attendance->date));

        self::deliver(self::userFor($employee), [
            'type'         => 'attendance',
            'audience'     => 'employee',
            'title'        => 'Attendance Corrected',
            'message'      => "Your attendance record for {$date} has been corrected by HR. Check your Daily Time Record for the updated hours.",
            'link'         => self::link('employee.attendance'),
            'related_id'   => $attendance->id,
            'related_type' => 'App\Models\Attendance',
            'dedupe_key'   => "attendance:{$attendance->id}:corrected:" . now()->format('YmdHi'),
        ]);
    }

    /* ===================================================================== */
    /*  Account                                                              */
    /* ===================================================================== */

    /**
     * An account was created for an employee → that employee.
     *
     * Written when the account is made, so the notification is waiting the
     * first time they sign in. The credentials themselves go by email; this
     * only says the account exists and where to check its details.
     */
    public static function accountCreated(?User $user, ?Employee $employee = null): void
    {
        if (! $user) {
            return;
        }

        $employee = $employee ?? $user->employee;

        self::deliver($user, [
            'type'         => 'account',
            'audience'     => 'employee',
            'title'        => 'Welcome to PRIME HRIS',
            'message'      => 'Your employee account has been created by the HR office. Review your profile and tell HR about anything that needs correcting.',
            'link'         => self::link('employee.profile'),
            'related_id'   => $employee?->id,
            'related_type' => 'App\Models\Employee',
            'dedupe_key'   => "account:{$user->id}:created",
        ]);
    }

    /**
     * HR edited an employee's record → that employee.
     *
     * Personnel data is what payroll and leave are computed from, so a change
     * somebody else made to it is theirs to check. Keyed to the day: an admin
     * saving the same form twice while correcting a typo is one change.
     */
    public static function accountUpdated(?Employee $employee, string $summary = 'Your personnel record has been updated by the HR office.'): void
    {
        $user = self::userFor($employee);

        if (! $user || $user->id === Auth::id()) {
            return;
        }

        self::deliver($user, [
            'type'         => 'account',
            'audience'     => 'employee',
            'title'        => 'Personnel Record Updated',
            'message'      => $summary . ' Review your profile and tell HR about anything that looks wrong.',
            'link'         => self::link('employee.profile'),
            'related_id'   => $employee->id,
            'related_type' => 'App\Models\Employee',
            'dedupe_key'   => "account:{$employee->id}:updated:" . now()->format('Ymd'),
        ]);
    }

    /* ===================================================================== */
    /*  Employee requests                                                    */
    /* ===================================================================== */

    public static function payslipRequested($request): void
    {
        self::employeeRequestToAdmin($request, 'Payslip Request', 'requested a payslip');
    }

    public static function deductionInquiry($request): void
    {
        self::employeeRequestToAdmin($request, 'Deduction Inquiry', 'has a question about deductions');
    }

    public static function employeeRequestSubmitted($request): void
    {
        self::employeeRequestToAdmin($request, $request->request_type_name, 'submitted a request');
    }

    protected static function employeeRequestToAdmin($request, string $title, string $verb): void
    {
        $name = self::personName($request->employee ?? null);
        $detail = $request->description ?: $request->title;

        self::deliverMany(self::approvers('employee_requests'), [
            'type'         => 'request',
            'audience'     => 'admin',
            'title'        => $title,
            'message'      => "{$name} {$verb}: {$detail}",
            'link'         => self::link('admin.requests'),
            'related_id'   => $request->id,
            'related_type' => 'App\Models\EmployeeRequest',
            'dedupe_key'   => "request:{$request->id}:submitted",
        ]);
    }

    public static function requestStatusChanged($request, $status): void
    {
        $statusText = self::statusLabel($status);
        $message = "Your {$request->request_type_name} has been {$statusText}.";

        if ($request->admin_response) {
            $message .= " Response: {$request->admin_response}";
        }

        self::deliver(self::userFor($request->employee ?? null), [
            'type'         => 'request',
            'audience'     => 'employee',
            'title'        => "Request {$statusText}",
            'message'      => $message,
            'link'         => self::link('employee.requests'),
            'related_id'   => $request->id,
            'related_type' => 'App\Models\EmployeeRequest',
            'dedupe_key'   => "request:{$request->id}:{$status}",
        ]);
    }

    /* ===================================================================== */
    /*  Broadcast and bulk state                                             */
    /* ===================================================================== */

    /**
     * An announcement for everyone, or for one role.
     *
     * Audience follows the role asked for so the message lands in the bell that
     * role actually opens; with no role it is 'system', which every bell shows.
     */
    public static function systemNotification($title, $message, $role = null): void
    {
        try {
            $users = User::query()
                ->when($role, fn ($q) => $q->whereJsonContains('roles', $role))
                ->get()
                ->filter(fn (User $user) => $user->isActive());
        } catch (\Throwable $e) {
            Log::error('System notification recipient lookup failed', ['exception' => $e->getMessage()]);

            return;
        }

        $audience = match ($role) {
            'admin', 'hr' => 'admin',
            'mayor'       => 'mayor',
            'employee'    => 'employee',
            default       => 'system',
        };

        // One key for the whole announcement, so an accidentally repeated send
        // does not print it twice in anybody's list.
        $key = 'system:' . substr(sha1($title . '|' . $message . '|' . ($role ?? 'all')), 0, 32);

        self::deliverMany($users, [
            'type'       => 'system',
            'audience'   => $audience,
            'title'      => $title,
            'message'    => $message,
            'dedupe_key' => $key,
        ]);
    }

    /**
     * Mark all of one user's notifications read.
     *
     * The audience narrows *within* the caller's own rows, so clearing one
     * bell never clears another area's. Null keeps the clear-everything
     * behaviour, which is what an unrecognised value falls back to: silently
     * narrowing a write would leave a badge stuck at a count nothing resets.
     */
    public static function markAllAsRead($userId, $audience = null): int
    {
        $query = Notification::where('user_id', $userId)->unread();

        if (in_array($audience, ['admin', 'employee', 'mayor'], true)) {
            $query->forAudience($audience);
        }

        return $query->update(['is_read' => true, 'read_at' => now()]);
    }

    /* ===================================================================== */
    /*  Phrasing                                                             */
    /* ===================================================================== */

    /**
     * What the screens call a status.
     *
     * The columns store 'rejected'; every tab, badge and printed form in this
     * system says "Disapproved". Telling an employee their request was
     * "Rejected" sends them looking for a word that is on none of their pages.
     */
    protected static function statusLabel(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'approved'              => 'Approved',
            'rejected', 'disapproved' => 'Disapproved',
            'cancelled', 'canceled' => 'Cancelled',
            'pending'               => 'Pending',
            'completed'             => 'Completed',
            'processing'            => 'In Progress',
            default                 => ucfirst((string) $status),
        };
    }

    /** " Reason: …" — only on a refusal, and only when one was given. */
    protected static function reasonClause(?string $status, ?string $reason): string
    {
        $refused = in_array(strtolower((string) $status), ['rejected', 'disapproved'], true);

        return ($refused && $reason) ? " Reason: {$reason}" : '';
    }

    /**
     * " for Aug 12 – Aug 14, 2026", or an empty string.
     *
     * A notification that names the dates can be acted on from the bell; one
     * that says "your leave request" cannot be told apart from the other two
     * the employee has open.
     */
    protected static function dateRange($start, $end): string
    {
        if (! $start) {
            return '';
        }

        try {
            $from = \Carbon\Carbon::parse($start);
            $to   = $end ? \Carbon\Carbon::parse($end) : $from;

            if ($from->isSameDay($to)) {
                return ' for ' . $from->format('M d, Y');
            }

            return ' for ' . $from->format('M d') . ' – ' . $to->format('M d, Y');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
