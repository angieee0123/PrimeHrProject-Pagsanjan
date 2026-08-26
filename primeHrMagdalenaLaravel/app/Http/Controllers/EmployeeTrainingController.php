<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Training;
use App\Models\User;
use App\Services\CsvReportWriter;
use App\Services\NotificationService;
use Carbon\Carbon;

class EmployeeTrainingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return view('employee.training.employeeTraining', [
                'trainings'   => collect(),
                'stats'       => ['total_hours' => 0, 'verified' => 0, 'pending' => 0, 'rejected' => 0],
                'breakdown'   => ['leadership' => 0, 'technical' => 0, 'core' => 0],
                'goal_hours'  => 40,
                'fiscal_year' => date('Y'),
            ]);
        }

        // Load employee relationships for topbar
        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $trainings = Training::where('employee_id', $employee->id)
            ->orderBy('date_from', 'desc')
            ->get();

        $verified = $trainings->where('status', 'verified');
        $stats = [
            'total_hours' => $verified->sum('hours'),
            'verified'    => $verified->count(),
            'pending'     => $trainings->where('status', 'pending')->count(),
            'rejected'    => $trainings->where('status', 'rejected')->count(),
        ];

        $breakdown = ['leadership' => 0, 'technical' => 0, 'core' => 0];
        foreach ($verified as $training) {
            $cat = $training->ldCategory();
            $breakdown[$cat] = ($breakdown[$cat] ?? 0) + (int) $training->hours;
        }

        return view('employee.training.employeeTraining', compact('employee', 'trainings', 'stats', 'breakdown'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return back()->with('error', 'No employee record found.');
        }

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'conducted_by'  => 'required|string|max:255',
            'date_from'     => 'required|date',
            'date_to'       => 'required|date|after_or_equal:date_from',
            'hours'         => 'required|integer|min:1|max:999',
            'position_type' => 'required|in:Managerial,Supervisory,Technical,Clerical',
            'venue'         => 'nullable|string|max:255',
            'cert_no'       => 'nullable|string|max:100',
            'ref_doc_no'    => 'required|string|max:100',
            'certificate'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('certificate')->store('training_certificates', 'public');

        $training = Training::create([
            'employee_id'      => $employee->id,
            'title'            => $data['title'],
            'conducted_by'     => $data['conducted_by'],
            'date_from'        => $data['date_from'],
            'date_to'          => $data['date_to'],
            'hours'            => $data['hours'],
            'position_type'    => $data['position_type'],
            'venue'            => $data['venue'] ?? null,
            'cert_no'          => $data['cert_no'] ?? null,
            'ref_doc_no'       => $data['ref_doc_no'],
            'certificate_path' => $path,
            'status'           => 'pending',
        ]);

        NotificationService::trainingSubmitted($training);

        return redirect()->route('employee.training')
            ->with('success', 'Training record submitted for HR verification.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.training')->with('error', 'No employee record found.');
        }
        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($training->certificate_path) {
            Storage::disk('public')->delete($training->certificate_path);
        }
        $training->delete();

        return redirect()->route('employee.training')->with('success', 'Training record deleted.');
    }

    /**
     * "Export CSV" on the Training History toolbar.
     *
     * The button was a bare link taking no parameters, and the endpoint behind
     * it always returned every *verified* record. So the status chips, the
     * position filter and the search box narrowed the table on screen and the
     * download ignored all three: narrowing to the two rejected submissions
     * still downloaded the verified ones. The file also had no letterhead —
     * a bare header row and a grid of values, with nothing on it saying whose
     * record it was or which municipality issued it.
     *
     * It now applies the toolbar's filters server-side and wears the same
     * `CsvReportWriter` letterhead as every other export here.
     *
     * **Hours Claimed and Hours Credited stay separate columns.** A rejected
     * or still-pending submission credits 0 to CSC PDS Section IV however many
     * hours it declared — that is what the muted pill in the Hours column
     * means, and what the stat cards above the table sum. One "Hours" column
     * would make a rejected submission read as credited on the one report
     * whose whole subject is which hours count. It is also what lets this file
     * serve the PDS: the Hours Credited column is the PDS figure, whatever the
     * status filter was set to.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return redirect()->route('employee.training')->with('error', 'No employee record found.');
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $filters = $this->filters($request);

        try {
            $trainings = Training::where('employee_id', $employee->id)
                ->orderBy('date_from', 'desc')
                ->get()
                ->filter(fn (Training $t) => $this->matchesFilters($t, $filters))
                ->values();

            $fileName = 'Training_Record_' . $employee->employee_id . '_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $trainings, $employee, $filters
            ) {
                $csv->letterhead(
                    'Learning & Development Record',
                    'Human Resource Management Office · PRIME HRIS',
                    'CSC PDS Section IV — Training Programs, Seminars and Conferences Attended'
                );

                $csv->parameters([
                    'Employee:'            => trim($employee->first_name . ' ' . $employee->last_name),
                    'Employee ID:'         => $employee->employee_id,
                    'Position:'            => $employee->employmentDetail->designationRelation->title ?? 'Unassigned',
                    'Department / Office:' => $employee->employmentDetail->departmentRelation->name ?? 'Unassigned',
                    'Dates From:'          => $filters['date_from'] !== '' ? CsvReportWriter::longDate(Carbon::parse($filters['date_from'])) : 'All Dates',
                    'Dates To:'            => $filters['date_to'] !== '' ? CsvReportWriter::longDate(Carbon::parse($filters['date_to'])) : 'All Dates',
                    'Status:'              => $filters['status'] !== '' ? ucfirst($filters['status']) : 'All Status',
                    'Type of Position:'    => $filters['position_type'] !== '' ? $filters['position_type'] : 'All Position Types',
                    'Search Term:'         => $filters['search'] !== '' ? $filters['search'] : 'None',
                ], $trainings->count());

                $csv->columns([
                    'No.', 'Title of Seminar / Conference / Training Program',
                    'Date From', 'Date To', 'No. of Days',
                    'Hours Claimed', 'Hours Credited',
                    'Type of Position', 'L&D Category',
                    'Conducted / Sponsored By', 'Venue',
                    'Certificate No.', 'Reference Document No.', 'Certificate on File',
                    'Status', 'Date Submitted', 'Date Verified', 'Reason for Rejection',
                ]);

                foreach ($trainings as $index => $t) {
                    $csv->row([
                        $index + 1,
                        $t->title,
                        CsvReportWriter::date($t->date_from),
                        CsvReportWriter::date($t->date_to),
                        $this->dayCount($t),
                        (int) $t->hours,
                        $this->creditedHours($t),
                        $t->position_type ?: '—',
                        ucfirst($t->ldCategory()),
                        $t->conducted_by,
                        $t->venue ?: '—',
                        $t->cert_no ?: '—',
                        $t->ref_doc_no ?: '—',
                        $t->certificate_path ? 'Yes' : 'No',
                        ucfirst((string) $t->status),
                        CsvReportWriter::date($t->created_at),
                        CsvReportWriter::date($t->verified_at),
                        $t->rejected_reason ?: '—',
                    ]);
                }

                if ($trainings->isEmpty()) {
                    $csv->emptyNotice('No training records matched the filters above.');
                }

                $verified = $trainings->where('status', 'verified');

                // A totals line in the table's own columns as well as the
                // summary block, so the file can be checked against a printout
                // column by column.
                if ($trainings->isNotEmpty()) {
                    $csv->row([
                        '', 'TOTAL', '', '',
                        $trainings->sum(fn (Training $t) => (int) $this->dayCount($t)),
                        $trainings->sum('hours'),
                        $verified->sum('hours'),
                        '', '', '', '', '', '', '', '', '', '', '',
                    ]);
                }

                $csv->summary('Summary', [
                    'Records Covered:'      => $trainings->count(),
                    'Verified:'             => $verified->count(),
                    'Pending Verification:' => $trainings->where('status', 'pending')->count(),
                    'Rejected:'             => $trainings->where('status', 'rejected')->count(),
                    'Total Hours Claimed:'  => (int) $trainings->sum('hours'),
                    'Total Hours Credited:' => (int) $verified->sum('hours'),
                    'With Certificate:'     => $trainings->filter(fn (Training $t) => (bool) $t->certificate_path)->count(),
                ]);

                // The same three buckets the breakdown panel above the table
                // shows, resolved by Training::ldCategory() so the file and the
                // panel cannot disagree.
                $csv->summary('Credited Hours by L&D Category',
                    collect(['leadership', 'technical', 'core'])
                        ->mapWithKeys(fn (string $cat) => [
                            ucfirst($cat) . ':' => (int) $verified->filter(
                                fn (Training $t) => $t->ldCategory() === $cat
                            )->sum('hours'),
                        ])
                        ->all()
                );

                $csv->notes([
                    'Hours Credited counts verified records only — a rejected or still-pending submission credits 0 hours to CSC PDS Section IV however many it declared. The Hours Credited column is the figure the PDS takes.',
                    'No. of Days counts both ends: a one-day seminar is 1.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('employee.training')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /** The status values the filter chips offer, beside their "all". */
    private const STATUSES = ['pending', 'verified', 'rejected'];

    /**
     * The toolbar's state, with every key always present so the export's
     * parameter block can print "All Status" rather than leave a cell
     * unwritten — a reader has to be able to tell "this covers everything"
     * from "this did not get filled in".
     *
     * @return array{status: string, position_type: string, date_from: string, date_to: string, search: string}
     */
    private function filters(Request $request): array
    {
        $status = strtolower(trim((string) $request->get('status')));

        return [
            // "all" is the chip's own word for no filter; anything
            // unrecognised is treated the same way rather than silently
            // handing back an empty file.
            'status'        => in_array($status, self::STATUSES, true) ? $status : '',
            'position_type' => trim((string) $request->get('position_type')),
            'date_from'     => $this->dateOrEmpty($request->get('date_from')),
            'date_to'       => $this->dateOrEmpty($request->get('date_to')),
            'search'        => trim((string) $request->get('search')),
        ];
    }

    /**
     * The same four tests `filterPermanentTraining()` runs over each row, in
     * the same order — so a narrowed table and its export agree.
     */
    private function matchesFilters(Training $training, array $filters): bool
    {
        if ($filters['status'] !== '' && $training->status !== $filters['status']) {
            return false;
        }

        if ($filters['position_type'] !== '' && $training->position_type !== $filters['position_type']) {
            return false;
        }

        if (!$this->inDateRange($training, $filters['date_from'], $filters['date_to'])) {
            return false;
        }

        if ($filters['search'] !== '') {
            // The fields the search box reads off each rendered row.
            $haystack = mb_strtolower(implode(' ', array_filter([
                $training->title,
                $training->ref_doc_no,
                $training->conducted_by,
                $training->venue,
                $training->position_type,
                $training->status,
                $training->date_from?->format('M d, Y'),
                $training->date_to?->format('M d, Y'),
            ])));

            if (!str_contains($haystack, mb_strtolower($filters['search']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * A training belongs in the range if its inclusive dates *overlap* it.
     *
     * A three-day seminar running 30 July to 1 August belongs in an August
     * filter; testing only its start date would drop it under a parameter
     * block that says August is covered. Mirrors `trainingInDateRange()` in
     * `employeeTraining.js`.
     */
    private function inDateRange(Training $training, string $from, string $to): bool
    {
        $start = $training->date_from?->format('Y-m-d');
        $end   = $training->date_to?->format('Y-m-d') ?: $start;

        if ($from !== '' && $end !== null && $end < $from) {
            return false;
        }

        if ($to !== '' && $start !== null && $start > $to) {
            return false;
        }

        return true;
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
    private function creditedHours(Training $training): int
    {
        return $training->status === 'verified' ? (int) $training->hours : 0;
    }

    /** A date the toolbar sent, or '' — never a parse error thrown at a page. */
    private function dateOrEmpty($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function certificate($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            abort(403, 'No employee record found.');
        }
        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if (!$training->certificate_path || !Storage::disk('public')->exists($training->certificate_path)) {
            abort(404, 'Certificate not found.');
        }

        return response()->file(storage_path('app/public/' . $training->certificate_path));
    }
}
