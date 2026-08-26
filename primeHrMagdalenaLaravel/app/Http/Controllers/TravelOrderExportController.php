<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;
use App\Services\CsvReportWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * "Export" on the Admin → Travel Order toolbar.
 *
 * The button rendered, styled and clickable with no handler on it at all —
 * the same state the Attendance toolbar's Export was in. It sits above three
 * tabs, so it exports the tab you are looking at: a Pending file has no
 * approver to name and a Disapproved one exists to carry the reason, and one
 * shared endpoint would either print empty approval columns on pending orders
 * or drop the reason from the only report whose subject is why a request was
 * refused.
 *
 * Every export re-runs the query server-side. All three tabs paginate and all
 * four toolbar filters (plus the topbar search) are applied in the browser, so
 * scraping the rendered table would hand back the ten rows on page one with
 * the filters silently baked in and nothing in the file saying so.
 *
 * The letterhead — republic line, municipality, address, title, parameters,
 * generated-by and the RA 10173 notice — comes from `CsvReportWriter`, which
 * reads the office identity from `SiteContentService` rather than restating it.
 */
class TravelOrderExportController extends Controller
{
    /** `travel_orders.status` values, spelled the way the tabs spell them. */
    private const STATUS_LABELS = [
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'rejected'  => 'Disapproved',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Every travel order on file, whatever its status — the register.
     *
     * The three tab exports answer "what is in front of me"; this one answers
     * "what has this office recorded". It carries a Status column and the
     * approval columns filled only where they apply, which is the trade the
     * per-tab files exist to avoid — worth it once, in the file whose subject
     * is the whole record, and wrong as the only file on offer.
     */
    public function all(Request $request)
    {
        $orders = $this->orders(null, $request);

        return CsvReportWriter::download($this->fileName('Travel_Orders_All_Records'), function (CsvReportWriter $csv) use ($orders, $request) {
            $csv->letterhead(
                'Travel Orders — Complete Register',
                'Human Resource Management Office · PRIME HRIS',
                'All official travel requests on file as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'All Statuses', $orders->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Status', 'Filed By', 'Acted On By', 'Date Acted On', 'Reason for Disapproval',
            ]));

            foreach ($orders as $index => $order) {
                $actedAt = $this->actedAt($order);

                $csv->row(array_merge($this->baseRow($order, $index), [
                    $this->statusLabel($order),
                    $this->userName($order->filer),
                    // Blank on a pending order rather than filled with a dash:
                    // nobody has acted on it, and an empty cell under "Acted On
                    // By" is the honest way to say so.
                    $order->status === 'pending' ? '' : $this->userName($this->actor($order)),
                    CsvReportWriter::dateTime($actedAt, ''),
                    $order->status === 'rejected'
                        ? ($order->disapproval_reason ?: ($order->remarks ?: 'No reason recorded'))
                        : '',
                ]));
            }

            $this->writeTail($csv, $orders, 'No travel orders matched the filters above.', [
                'Acted On By and Date Acted On are blank for a pending order — no decision has been made on it yet.',
                'Reason for Disapproval is filled only on disapproved orders.',
            ], includeStatusBreakdown: true);
        });
    }

    /**
     * Pending Travel Orders — what still needs a decision.
     *
     * Carries "Days Pending" and who filed it, which is what an officer
     * clearing a backlog is reading the file for, and no approval columns:
     * nobody has acted on these yet.
     */
    public function pending(Request $request)
    {
        $orders = $this->orders('pending', $request);

        return CsvReportWriter::download($this->fileName('Travel_Orders_Pending'), function (CsvReportWriter $csv) use ($orders, $request) {
            $csv->letterhead(
                'Travel Orders — Pending Approval',
                'Human Resource Management Office · PRIME HRIS',
                'Official travel requests awaiting action as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'Pending Approval', $orders->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Filed By', 'Days Pending',
            ]));

            foreach ($orders as $index => $order) {
                $csv->row(array_merge($this->baseRow($order, $index), [
                    $this->userName($order->filer),
                    $order->created_at ? (int) $order->created_at->startOfDay()->diffInDays(now()->startOfDay()) : '',
                ]));
            }

            $this->writeTail($csv, $orders, 'No pending travel orders matched the filters above.', [
                'Days Pending counts from the date the request was filed to the date this report was generated.',
            ]);
        });
    }

    /**
     * Approved Travel Orders — the authority to travel that was actually granted.
     */
    public function approved(Request $request)
    {
        $orders = $this->orders('approved', $request);

        return CsvReportWriter::download($this->fileName('Travel_Orders_Approved'), function (CsvReportWriter $csv) use ($orders, $request) {
            $csv->letterhead(
                'Travel Orders — Approved',
                'Human Resource Management Office · PRIME HRIS',
                'Authorised official travel as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'Approved', $orders->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Filed By', 'Approved By', 'Date Approved',
            ]));

            foreach ($orders as $index => $order) {
                $csv->row(array_merge($this->baseRow($order, $index), [
                    $this->userName($order->filer),
                    $this->userName($order->approver),
                    CsvReportWriter::dateTime($order->approved_at, ''),
                ]));
            }

            $this->writeTail($csv, $orders, 'No approved travel orders matched the filters above.', [
                'An approved travel order marks its travel dates TRAVEL_ORDER on the affected Daily Time Records.',
                'Estimated Budget is the amount stated on the request — not a disbursed or liquidated amount.',
            ]);
        });
    }

    /**
     * Disapproved Travel Orders — refused requests and the reason for each.
     *
     * The reason is the whole point of this file, so it is the last column and
     * it is printed in full, unlike the on-screen table which truncates it.
     */
    public function disapproved(Request $request)
    {
        $orders = $this->orders('rejected', $request);

        return CsvReportWriter::download($this->fileName('Travel_Orders_Disapproved'), function (CsvReportWriter $csv) use ($orders, $request) {
            $csv->letterhead(
                'Travel Orders — Disapproved',
                'Human Resource Management Office · PRIME HRIS',
                'Refused official travel requests as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'Disapproved', $orders->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Filed By', 'Disapproved By', 'Date Disapproved', 'Reason for Disapproval',
            ]));

            foreach ($orders as $index => $order) {
                $actedAt = $this->actedAt($order);

                $csv->row(array_merge($this->baseRow($order, $index), [
                    $this->userName($order->filer),
                    $this->userName($this->actor($order)),
                    CsvReportWriter::dateTime($actedAt, ''),
                    $order->disapproval_reason ?: ($order->remarks ?: 'No reason recorded'),
                ]));
            }

            $this->writeTail($csv, $orders, 'No disapproved travel orders matched the filters above.', [
                'A disapproved travel order leaves the Daily Time Record untouched — the travel dates are not credited.',
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    //  Query
    // ─────────────────────────────────────────────────────────────────

    /**
     * The travel orders the toolbar's filters select, in one status or in all.
     *
     * The four toolbar controls and the topbar search box are matched here the
     * same way the page matches them in the browser, so a narrowed screen and
     * its export cover the same rows. A null `$status` is the complete
     * register — every status, for `all()`.
     */
    private function orders(?string $status, Request $request): Collection
    {
        $department = $this->filter($request, 'department');
        $mode       = $this->filter($request, 'mode');
        $dateFrom   = $this->filter($request, 'date_from');
        $dateTo     = $this->filter($request, 'date_to');
        $search     = mb_strtolower($this->filter($request, 'search'));

        return TravelOrder::with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation',
                'approver.employee',
                'disapprover.employee',
                'filer.employee',
                'companions.employee',
            ])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy('travel_date', 'desc')
            ->get()
            ->filter(function (TravelOrder $order) use ($department, $mode, $dateFrom, $dateTo, $search) {
                if ($department !== '' && $this->department($order) !== $department) {
                    return false;
                }

                if ($mode !== '' && (string) $order->transportation_mode !== $mode) {
                    return false;
                }

                // The screen filters on travel_date — the date its rows show.
                $travelDate = $order->travel_date?->format('Y-m-d');

                if ($dateFrom !== '' && (!$travelDate || $travelDate < $dateFrom)) {
                    return false;
                }

                if ($dateTo !== '' && (!$travelDate || $travelDate > $dateTo)) {
                    return false;
                }

                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', [
                        $order->order_number,
                        $this->employeeName($order),
                        $order->employee->employee_id ?? '',
                        $this->department($order),
                        $order->destination,
                        $order->purpose,
                    ]));

                    if (!str_contains($haystack, $search)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }

    // ─────────────────────────────────────────────────────────────────
    //  The shared parts of the document
    // ─────────────────────────────────────────────────────────────────

    /**
     * The parameter block: every filter, printed whether it was set or not.
     *
     * A blank cell cannot be told apart from "this covers everything", so an
     * untouched filter is spelled out as "All Departments" rather than left
     * empty — the rule `CsvReportWriter` exists to keep.
     */
    private function writeParameters(CsvReportWriter $csv, Request $request, string $status, int $count): void
    {
        $csv->parameters([
            'Travel Period:'          => $this->describeRange($this->filter($request, 'date_from'), $this->filter($request, 'date_to')),
            'Department / Office:'    => $this->filter($request, 'department') ?: 'All Departments',
            'Mode of Transportation:' => $this->filter($request, 'mode') ?: 'All Modes',
            'Search Term:'            => $this->filter($request, 'search') ?: 'None',
            'Status:'                 => $status,
        ], $count);
    }

    /** The columns every travel order report carries, whatever its status. */
    private function baseColumns(): array
    {
        return [
            'No.', 'Travel Order No.', 'Date Filed',
            'Employee ID', 'Employee Name', 'Position', 'Department / Office',
            'Destination', 'Purpose of Travel',
            'Travel Date', 'Return Date', 'Duration (days)',
            'Mode of Transportation', 'Estimated Budget (PHP)',
            'Companions', 'Travelling Party Size', 'Attachment on File',
        ];
    }

    /** @return array<int,mixed> the same fields, in the same order */
    private function baseRow(TravelOrder $order, int $index): array
    {
        $detail = $order->employee->employmentDetail ?? null;

        return [
            $index + 1,
            $order->order_number ?: '—',
            CsvReportWriter::date($order->created_at, ''),
            $order->employee->employee_id ?? '',
            $this->employeeName($order),
            $detail->designationRelation->title ?? 'Unassigned',
            $this->department($order) ?: 'Unassigned',
            $order->destination,
            $order->purpose,
            CsvReportWriter::date($order->travel_date, ''),
            CsvReportWriter::date($order->return_date, ''),
            $order->duration,
            $order->transportation_mode ?: 'Unspecified',
            // Left unformatted so a spreadsheet can total the column; the
            // currency is named in the header rather than pasted on every cell.
            $order->estimated_budget !== null ? number_format((float) $order->estimated_budget, 2, '.', '') : '',
            $this->companionNames($order),
            1 + $order->companions->count(),
            $order->attachment ? 'Yes' : 'No',
        ];
    }

    /**
     * The empty notice, the totals, the breakdowns and the closing notes —
     * identical across the three tabs, so they are written once.
     */
    private function writeTail(
        CsvReportWriter $csv,
        Collection $orders,
        string $emptyMessage,
        array $notes,
        bool $includeStatusBreakdown = false
    ): void {
        if ($orders->isEmpty()) {
            $csv->emptyNotice($emptyMessage);
        }

        $csv->summary('Summary', [
            'Total Travel Orders:'           => $orders->count(),
            'Employees Covered:'             => $orders->pluck('employee_id')->unique()->count(),
            'Total Travel Days:'             => $orders->sum('duration'),
            'Travel Orders with Companions:' => $orders->filter(fn (TravelOrder $o) => $o->companions->count() > 0)->count(),
            'Total Personnel Travelling:'    => $orders->sum(fn (TravelOrder $o) => 1 + $o->companions->count()),
            'Total Estimated Budget (PHP):'  => number_format((float) $orders->sum('estimated_budget'), 2),
            'With Attachment on File:'       => $orders->filter(fn (TravelOrder $o) => (bool) $o->attachment)->count(),
        ]);

        // Only the complete register mixes statuses. On a single-status file
        // this block would restate the total record count as a breakdown.
        if ($includeStatusBreakdown) {
            $csv->summary('Breakdown by Status', $this->countsBy(
                $orders,
                fn (TravelOrder $o) => $this->statusLabel($o)
            ));
        }

        $csv->summary('Breakdown by Department / Office', $this->countsBy(
            $orders,
            fn (TravelOrder $o) => $this->department($o) ?: 'Unassigned'
        ));

        $csv->summary('Breakdown by Mode of Transportation', $this->countsBy(
            $orders,
            fn (TravelOrder $o) => $o->transportation_mode ?: 'Unspecified'
        ));

        $csv->summary(
            'Estimated Budget per Department / Office',
            $orders->groupBy(fn (TravelOrder $o) => $this->department($o) ?: 'Unassigned')
                ->map(fn (Collection $rows) => (float) $rows->sum('estimated_budget'))
                ->sortDesc()
                ->mapWithKeys(fn ($sum, $label) => [$label . ':' => number_format($sum, 2)])
                ->all()
        );

        $csv->notes(array_merge([
            'Duration is the number of days stated on the travel order, inclusive of the travel and return dates.',
            'Travelling Party Size counts the requesting employee plus every companion listed on the order.',
        ], $notes));
    }

    // ─────────────────────────────────────────────────────────────────
    //  Small readers
    // ─────────────────────────────────────────────────────────────────

    /** Filenames lead with the report so a folder of exports sorts by kind. */
    private function fileName(string $report): string
    {
        return $report . '_' . now()->format('M_d_Y') . '.csv';
    }

    /**
     * A query param, trimmed, with the selects' own word for "no filter"
     * treated as absent — the toolbar sends `all`, and a report reading
     * "Department: all" is worse than one reading "All Departments".
     */
    private function filter(Request $request, string $key): string
    {
        $value = trim((string) $request->get($key, ''));

        return strtolower($value) === 'all' ? '' : $value;
    }

    /** The status, spelled the way the tab that holds it is spelled. */
    private function statusLabel(TravelOrder $order): string
    {
        return self::STATUS_LABELS[$order->status] ?? ucfirst((string) $order->status);
    }

    /**
     * Whoever last acted on the order.
     *
     * The decision stamps `approved_by` whichever way it went, so a refusal is
     * usually recorded there too; `disapproved_by` is only filled by orders
     * refused through another path. Reading both is what keeps a real refusal
     * from printing with no actor beside it.
     */
    private function actor(TravelOrder $order)
    {
        return $order->status === 'rejected'
            ? ($order->disapprover ?: $order->approver)
            : $order->approver;
    }

    private function actedAt(TravelOrder $order): ?Carbon
    {
        if ($order->status === 'pending') {
            return null;
        }

        return $order->status === 'rejected'
            ? ($order->disapproved_at ?: $order->approved_at)
            : $order->approved_at;
    }

    private function department(TravelOrder $order): string
    {
        return (string) ($order->employee->employmentDetail->departmentRelation->name ?? '');
    }

    private function employeeName(TravelOrder $order): string
    {
        $employee = $order->employee;

        if (!$employee) {
            return 'Unknown employee';
        }

        return trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
            $employee->suffix,
        ])));
    }

    /** Every companion on the order, named, so the file stands on its own. */
    private function companionNames(TravelOrder $order): string
    {
        return $order->companions
            ->map(function ($companion) {
                $employee = $companion->employee;
                $name = $employee
                    ? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                    : 'Unknown employee';

                // A companion who declined still belongs in the record; their
                // response is what says whether they actually travelled.
                return $name . ' (' . ucfirst((string) $companion->status) . ')';
            })
            ->implode('; ');
    }

    /** Whoever acted, named the way HR would recognise them. */
    private function userName($user): string
    {
        if (!$user) {
            return '';
        }

        $employee = $user->employee ?? null;

        if ($employee) {
            $name = trim($employee->first_name . ' ' . $employee->last_name);
            if ($name !== '') {
                return $name;
            }
        }

        // `users` carries no display name of its own beyond the username.
        return trim((string) ($user->username ?? '')) ?: 'System';
    }

    /**
     * label => count, largest first.
     *
     * @return array<string,int>
     */
    private function countsBy(Collection $items, callable $key): array
    {
        return $items->groupBy($key)
            ->map->count()
            ->sortDesc()
            ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
            ->all();
    }

    private function describeRange(string $from, string $to): string
    {
        if ($from === '' && $to === '') {
            return 'All dates';
        }
        if ($from !== '' && $to !== '') {
            return Carbon::parse($from)->format('F d, Y') . ' to ' . Carbon::parse($to)->format('F d, Y');
        }
        if ($from !== '') {
            return 'From ' . Carbon::parse($from)->format('F d, Y');
        }

        return 'Up to ' . Carbon::parse($to)->format('F d, Y');
    }
}
