<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Services\CsvReportWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * "Export Report" on the Training Verification page.
 *
 * The file used to be a bare header row and a grid of values, ignoring every
 * filter on screen: a reviewer who had narrowed the queue to the pending
 * Technical submissions got the whole table back, with no title, no
 * municipality and nothing saying what it covered. It now carries the same
 * `CsvReportWriter` letterhead as the Personnel, Departments, Attendance and
 * Leave & Benefits exports, and the status chips, position filter and search
 * box on the page reach it as query params.
 *
 * The file is a verification report, not a copy of the table: it carries the
 * whole CSC PDS Section IV row plus the trail the table has no column for —
 * who verified it, when, and the reason a rejected submission was sent back.
 *
 * **Hours claimed and hours credited are separate columns.** A rejected
 * submission credits 0 to the employee's PDS however many hours it declared,
 * which is what the table's muted pill means and what
 * `EmployeeTrainingController` sums. One "Hours" column would make a rejected
 * submission read as credited in a spreadsheet, on the one report whose whole
 * subject is which hours count.
 */
class TrainingExportController extends Controller
{
    /** The status values the queue's filter chips offer. */
    private const STATUSES = ['pending', 'verified', 'rejected'];

    public function export(Request $request)
    {
        $status = strtolower(trim((string) $request->get('status')));
        $positionType = trim((string) $request->get('position_type'));
        $search = trim((string) $request->get('search'));

        // "all" is the chip's own word for no filter; anything unrecognised is
        // treated the same way rather than silently returning nothing.
        if (!in_array($status, self::STATUSES, true)) {
            $status = '';
        }

        try {
            $trainings = Training::with([
                    'employee.employmentDetail.departmentRelation',
                    'verifiedBy',
                ])
                // The queue's own order: what still needs a decision first,
                // then what was sent back, then what is settled.
                ->orderByRaw("FIELD(status, 'pending', 'rejected', 'verified')")
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function (Training $t) use ($status, $positionType, $search) {
                    if ($status !== '' && $t->status !== $status) {
                        return false;
                    }

                    if ($positionType !== '' && $t->position_type !== $positionType) {
                        return false;
                    }

                    if ($search !== '') {
                        // The same five fields the page's search box reads off
                        // each row, so a narrowed queue and its export match.
                        $employee = $t->employee;
                        $haystack = mb_strtolower(implode(' ', [
                            trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                            $employee->employee_id ?? '',
                            $employee->employmentDetail->departmentRelation->name ?? '',
                            $t->title ?? '',
                            $t->ref_doc_no ?? '',
                        ]));
                        if (!str_contains($haystack, mb_strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();

            $fileName = 'Training_Verification_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $trainings, $status, $positionType, $search
            ) {
                $csv->letterhead(
                    'Training Verification Report',
                    'Human Resource Management Office · PRIME HRIS',
                    'CSC PDS Section IV — Learning & Development · Submissions on file as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Status:'           => $status !== '' ? ucfirst($status) : 'All Status',
                    'Type of Position:' => $positionType !== '' ? $positionType : 'All Position Types',
                    'Search Term:'      => $search !== '' ? $search : 'None',
                ], $trainings->count());

                $csv->columns([
                    'No.', 'Employee ID', 'Employee Name', 'Department / Office',
                    'Title of Seminar / Conference / Training Program',
                    'Date From', 'Date To', 'No. of Days',
                    'Hours Claimed', 'Hours Credited', 'Type of Position',
                    'Conducted / Sponsored By', 'Certificate No.', 'Reference Document No.',
                    'Certificate on File', 'Status',
                    'Date Submitted', 'Verified By', 'Date Verified', 'Reason for Rejection',
                ]);

                foreach ($trainings as $index => $t) {
                    $employee = $t->employee;

                    $csv->row([
                        $index + 1,
                        $employee->employee_id ?? '',
                        trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                        $employee->employmentDetail->departmentRelation->name ?? 'Unassigned',
                        $t->title,
                        $t->date_from ? $t->date_from->format('M d, Y') : '',
                        $t->date_to ? $t->date_to->format('M d, Y') : '',
                        $this->dayCount($t),
                        $t->hours,
                        $this->creditedHours($t),
                        $t->position_type,
                        $t->conducted_by,
                        $t->cert_no ?? '',
                        $t->ref_doc_no ?? '',
                        $t->certificate_path ? 'Yes' : 'No',
                        ucfirst($t->status),
                        $t->created_at ? $t->created_at->format('M d, Y') : '',
                        $this->verifierName($t),
                        $t->verified_at ? $t->verified_at->format('M d, Y') : '',
                        $t->rejected_reason ?? '',
                    ]);
                }

                if ($trainings->isEmpty()) {
                    $csv->emptyNotice('No training submissions matched the filters above.');
                }

                $verified = $trainings->where('status', 'verified');

                $csv->summary('Summary', [
                    'Total Submissions:'      => $trainings->count(),
                    'Pending Review:'         => $trainings->where('status', 'pending')->count(),
                    'Verified:'               => $verified->count(),
                    'Rejected:'               => $trainings->where('status', 'rejected')->count(),
                    'Total Hours Claimed:'    => $this->hours($trainings->sum('hours')),
                    'Total Hours Credited:'   => $this->hours($verified->sum('hours')),
                    'With Certificate:'       => $trainings->filter(fn (Training $t) => (bool) $t->certificate_path)->count(),
                    'Without Certificate:'    => $trainings->filter(fn (Training $t) => !$t->certificate_path)->count(),
                ]);

                $csv->summary('Breakdown by Type of Position', $this->countsBy(
                    $trainings,
                    fn (Training $t) => $t->position_type ?: 'Unspecified'
                ));

                // The same three buckets the employee's own Training page shows
                // its L&D breakdown in, resolved by Training::ldCategory().
                $csv->summary('Breakdown by L&D Category', $this->countsBy(
                    $trainings,
                    fn (Training $t) => ucfirst($t->ldCategory())
                ));

                $csv->summary('Breakdown by Department / Office', $this->countsBy(
                    $trainings,
                    fn (Training $t) => $t->employee->employmentDetail->departmentRelation->name ?? 'Unassigned'
                ));

                $csv->summary('Credited Hours per Department / Office',
                    $verified->groupBy(fn (Training $t) => $t->employee->employmentDetail->departmentRelation->name ?? 'Unassigned')
                        ->map(fn (Collection $rows) => $rows->sum('hours'))
                        ->sortDesc()
                        ->mapWithKeys(fn ($sum, $label) => [$label . ':' => $this->hours($sum)])
                        ->all()
                );

                $csv->notes([
                    'Hours Credited counts verified submissions only — a rejected submission credits 0 hours to CSC PDS Section IV however many it declared.',
                    'A submission with no certificate on file was credited, or is awaiting a decision, with no document to verify the hours against.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.training')->with('error', 'Export failed: ' . $e->getMessage());
        }
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

    /** Inclusive of both ends, matching the table: a one-day seminar is 1. */
    private function dayCount(Training $training): string
    {
        if (!$training->date_from || !$training->date_to) {
            return '';
        }

        return (string) ((int) abs($training->date_from->diffInDays($training->date_to)) + 1);
    }

    /** What this submission actually adds to the employee's PDS. */
    private function creditedHours(Training $training): string
    {
        return $training->status === 'verified' ? (string) $training->hours : '0';
    }

    /** Trailing zeros dropped: "16" rather than "16.00", "7.5" kept. */
    private function hours($total): string
    {
        return rtrim(rtrim(number_format((float) $total, 2, '.', ''), '0'), '.') ?: '0';
    }

    /** Whoever stamped the decision, named the way HR would recognise them. */
    private function verifierName(Training $training): string
    {
        $user = $training->verifiedBy;

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

        return trim((string) $user->name);
    }
}
