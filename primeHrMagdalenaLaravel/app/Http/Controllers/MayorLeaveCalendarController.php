<?php

namespace App\Http\Controllers;

/**
 * The mayor's Leave & Travel Calendar.
 *
 * Same read-only availability monitor the admin has, over the same query:
 * everything — the month/week/day framing, the filters, the stat strip, the
 * "+X more" counts — is inherited from {@see AdminLeaveCalendarController}
 * rather than restated here, so the two surfaces cannot disagree about who is
 * out on a given day. Only where the links point and which view draws them
 * differ, because /admin is closed to the mayor by EnsureRoleForArea.
 *
 * Nothing here narrows the data: the mayor already sees every leave
 * application and travel order in the municipality on their own pages, so a
 * calendar showing less would be a worse answer to the same question.
 */
class MayorLeaveCalendarController extends AdminLeaveCalendarController
{
    protected function routeName(): string
    {
        return 'mayor.leaveCalendar';
    }

    protected function viewName(): string
    {
        return 'mayor.leaveCalendar.leaveCalendar';
    }
}
