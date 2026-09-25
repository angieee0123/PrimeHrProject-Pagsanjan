<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Training;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Learning and development records for five of the Pagsanjan personnel.
 *
 * The subjects are the first five in roster order â€” three from the Office of the
 * Mayor and two from the Human Resources Management Office â€” and every title is
 * chosen for the office and designation that person actually holds, because the
 * training page is read as evidence of what somebody was sent to. A revenue
 * collection seminar on an HR clerk's file is not a neutral placeholder: it is a
 * claim about public money spent on a course nobody attended.
 *
 * Two properties of the data are load-bearing rather than cosmetic:
 *
 * - `Training::ldCategory()` derives the L&D grouping from keywords in the
 *   *title*, and it tests for leadership words before technical ones. A title
 *   containing "management" therefore groups as leadership however technical the
 *   subject is, which is why the titles below avoid that word where the intended
 *   bucket is technical â€” and why `guardCategory()` fails the run rather than
 *   letting a later title edit move a record between buckets unnoticed.
 * - `certificate_path` stays null on every row. No upload exists for a seeded
 *   employee, and a path to a file that is not there turns the personnel page's
 *   certificate link into a download that 404s.
 */
class PagsanjanTrainingSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** How many employees carry training records in this dataset. */
    private const SUBJECT_COUNT = 5;

    /**
     * The date every record below has to sit behind.
     *
     * The rest of the Pagsanjan dataset is stated as of this day, and a
     * certificate dated after it is a certificate the municipality cannot
     * already hold â€” verified records would have a verification date in the
     * future.
     */
    private const AS_OF = '2026-09-14';

    /**
     * A Monday, and the anchor every scheduled date is measured from.
     *
     * Working from a fixed Monday means the weekday rule (a training starts and
     * ends Monday to Friday, never on a weekend) is satisfied by construction
     * rather than by rejection sampling, so the same dates come out on every run
     * without a retry loop that could draw a different sequence.
     */
    private const FIRST_MONDAY = '2026-01-05';

    /** A five-day working week, so a span can never cross a weekend. */
    private const WORKING_DAYS = 5;

    /** Working hours in a training day. */
    private const HOURS_PER_DAY = 8;

    /** The first office reference number this seeder mints. */
    private const REFERENCE_START = 143;

    /** The account every verified record is attributed to. */
    private const ADMIN_FALLBACK_USER_ID = 1;

    /**
     * The training plan, in roster order: one list per subject, in date order.
     *
     * `week` is the Monday the record is scheduled in, counted from
     * `FIRST_MONDAY`; two weeks apart because a span is at most five days, which
     * staggers one person's records without ever overlapping them. `category` is
     * the bucket `Training::ldCategory()` has to return for the title â€” see
     * `guardCategory()`.
     *
     * `status` is fixed here rather than drawn: the brief asks for exactly one
     * `pending` record and exactly one `rejected` one, and the newest record on
     * the last subject's file is the honest place for the pending one â€” a
     * certificate filed in August that HR has not read yet.
     *
     * @var array<int, array<int, array{title: string, position: string, category: string, by: string, venue: string, ref: string, week: int, status: string}>>
     */
    private const PLAN = [
        // Executive Asst. II, Office of the Mayor â€” executive support work.
        [
            [
                'title' => 'Executive Leadership and Governance Seminar',
                'position' => 'Managerial', 'category' => 'leadership',
                'by' => 'Local Government Academy, DILG',
                'venue' => 'Development Academy of the Philippines, Pasig City',
                'ref' => 'LGA', 'week' => 2, 'status' => 'verified',
            ],
            [
                'title' => 'Strategic Planning Workshop on the Annual Investment Program',
                'position' => 'Supervisory', 'category' => 'leadership',
                'by' => 'DILG Regional Office IV-A',
                'venue' => 'Laguna Provincial Capitol, Santa Cruz',
                'ref' => 'DILG-RO4A', 'week' => 4, 'status' => 'verified',
            ],
            [
                'title' => 'Records and Document Filing System for Local Government Offices',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'National Archives of the Philippines',
                'venue' => 'Pagsanjan Municipal Hall',
                'ref' => 'NAP', 'week' => 6, 'status' => 'verified',
            ],
        ],

        // Private Sec. II, Office of the Mayor â€” correspondence and frontline work.
        [
            [
                'title' => 'Records Digitisation and Electronic Filing System Training',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'National Archives of the Philippines',
                'venue' => 'Pagsanjan Municipal Hall',
                'ref' => 'NAP', 'week' => 8, 'status' => 'verified',
            ],
            [
                'title' => "Frontline Service Excellence and Citizen's Charter Compliance Seminar",
                'position' => 'Clerical', 'category' => 'core',
                'by' => 'Civil Service Commission Regional Office IV-A',
                'venue' => 'CSC Regional Office IV-A, Lipa City',
                'ref' => 'CSC-RO4A', 'week' => 10, 'status' => 'verified',
            ],
        ],

        // Admin. Aide IV, Office of the Mayor â€” clerical and records support.
        [
            [
                'title' => 'Computer Literacy and Office Productivity Tools Training',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'Laguna State Polytechnic University',
                'venue' => 'Laguna State Polytechnic University, Santa Cruz',
                'ref' => 'LSPU', 'week' => 14, 'status' => 'verified',
            ],
            [
                'title' => 'Values Orientation and Public Service Ethics Seminar',
                'position' => 'Clerical', 'category' => 'core',
                'by' => 'Civil Service Commission Regional Office IV-A',
                'venue' => 'CSC Regional Office IV-A, Lipa City',
                'ref' => 'CSC-RO4A', 'week' => 16, 'status' => 'verified',
            ],
            [
                'title' => 'Data Entry and Records Accuracy Workshop',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'Provincial Government of Laguna',
                'venue' => 'Pagsanjan Municipal Hall',
                'ref' => 'PGL', 'week' => 18, 'status' => 'verified',
            ],
        ],

        // Admin. Aide IV, Human Resources Management Office â€” HR programme and CSC rules.
        [
            [
                'title' => 'PRIME-HRM Orientation and Reassessment Workshop',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'Civil Service Commission Regional Office IV-A',
                'venue' => 'CSC Regional Office IV-A, Lipa City',
                'ref' => 'CSC-RO4A', 'week' => 20, 'status' => 'verified',
            ],
            [
                'title' => 'Leave Administration and CSC Rules on Appointments Seminar',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'Civil Service Commission Regional Office IV-A',
                'venue' => 'Pagsanjan Municipal Hall',
                'ref' => 'CSC-RO4A', 'week' => 22, 'status' => 'verified',
            ],
            [
                'title' => 'Seminar on the Code of Conduct and Ethical Standards for Public Officials (R.A. 6713)',
                'position' => 'Clerical', 'category' => 'core',
                'by' => 'Civil Service Commission Regional Office IV-A',
                'venue' => 'CSC Regional Office IV-A, Lipa City',
                'ref' => 'CSC-RO4A', 'week' => 24, 'status' => 'rejected',
            ],
        ],

        // Admin. Aide IV, Human Resources Management Office â€” personnel records and benefits.
        [
            [
                'title' => 'Personnel Records and 201 File Documentation Training',
                'position' => 'Technical', 'category' => 'technical',
                'by' => 'Civil Service Commission Regional Office IV-A',
                'venue' => 'Pagsanjan Municipal Hall',
                'ref' => 'CSC-RO4A', 'week' => 26, 'status' => 'verified',
            ],
            [
                'title' => 'Employee Welfare and Benefits Administration Seminar',
                'position' => 'Clerical', 'category' => 'core',
                'by' => 'Government Service Insurance System, Laguna Branch',
                'venue' => 'Pagsanjan Municipal Hall',
                'ref' => 'GSIS-LB', 'week' => 28, 'status' => 'pending',
            ],
        ],
    ];

    /** Mints the office reference and certificate number of each row. */
    private int $sequence = self::REFERENCE_START;

    /** Resolved once: every verified record is signed off by the same account. */
    private ?int $adminUserId = null;

    public function run(): void
    {
        $this->seedRandomness();

        $roster = $this->rosterEmployees();

        if ($roster === []) {
            $this->command->warn('No Pagsanjan roster employees found â€” run PagsanjanEmployeeSeeder first.');

            return;
        }

        $subjects = $this->subjects();

        // Idempotence, committed ahead of the writes for the same reason
        // PagsanjanEmployeeSeeder commits its own: the replacements have to be
        // able to see the previous run's rows gone. It prunes the whole roster
        // rather than this run's five subjects, so a roster edit that moved
        // somebody out of the top five cannot leave orphaned training history
        // behind on a file no seeder owns any more.
        DB::transaction(function () use ($roster) {
            Training::whereIn('employee_id', array_keys($roster))->delete();
        });

        $records = 0;
        $employees = 0;
        $byStatus = ['verified' => 0, 'pending' => 0, 'rejected' => 0];

        DB::transaction(function () use ($subjects, &$records, &$employees, &$byStatus) {
            // `$subjects` is keyed by employee id, so the plan â€” which is written
            // in roster order â€” is indexed by a counter rather than by that key.
            $position = 0;

            foreach ($subjects as $employee) {
                foreach (self::PLAN[$position] as $planned) {
                    $training = $this->build($employee, $planned);

                    $this->guardCategory($training, $planned['category']);

                    $training->save();

                    $byStatus[$training->status]++;
                    $records++;
                }

                $employees++;
                $position++;
            }
        });

        $this->command->info(sprintf(
            'Pagsanjan trainings: %d records for %d employees (%d verified, %d pending, %d rejected).',
            $records,
            $employees,
            $byStatus['verified'],
            $byStatus['pending'],
            $byStatus['rejected']
        ));

        if ($employees < self::SUBJECT_COUNT) {
            $this->command->warn(sprintf(
                'Only %d roster employee(s) were available â€” expected %d.',
                $employees,
                self::SUBJECT_COUNT
            ));
        }
    }

    /**
     * The employees the records are written for: the first five in roster order.
     *
     * Job Orders are not filtered out here. They are people who attend
     * municipal trainings like anybody else, and the L&D page is a record of
     * attendance rather than of plantilla entitlements â€” the deduction seeder
     * skips them for the opposite reason.
     *
     * @return array<int, Employee> keyed by employees.id
     */
    private function subjects(): array
    {
        return array_slice($this->rosterEmployees(), 0, self::SUBJECT_COUNT, true);
    }

    /**
     * One training row, with its dates drawn inside the plan's week.
     *
     * The span is one to three working days and the start day is drawn from the
     * days left in that week, so the record always begins and ends Monday to
     * Friday however the draw falls. `hours` is the span Ã— eight rather than a
     * stored figure of its own: a three-day seminar that claims eight hours is a
     * contradiction the export prints verbatim.
     */
    private function build(Employee $employee, array $planned): Training
    {
        $span = $this->draw(1, 3);
        $monday = CarbonImmutable::parse(self::FIRST_MONDAY)->addWeeks($planned['week']);
        $from = $monday->addDays($this->draw(0, self::WORKING_DAYS - $span));
        $to = $from->addDays($span - 1);

        $verified = $planned['status'] === 'verified';
        [$reference, $certificate] = $this->nextReferenceNumbers($planned['ref']);

        return new Training([
            'employee_id' => $employee->id,
            'title' => $planned['title'],
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'hours' => $span * self::HOURS_PER_DAY,
            'position_type' => $planned['position'],
            'venue' => $planned['venue'],
            'cert_no' => $certificate,
            'conducted_by' => $planned['by'],
            'ref_doc_no' => $reference,
            // Null on purpose â€” see the class docblock.
            'certificate_path' => null,
            'status' => $planned['status'],
            // A verification date sits a few days after the seminar, which is
            // when the certificate reaches the HRMO. It is drawn from the same
            // seeded sequence as the dates, so a re-run verifies the same record
            // on the same day.
            'verified_at' => $verified ? $to->addDays($this->draw(3, 12))->setTime(9, 0) : null,
            'verified_by' => $verified ? $this->adminUserId() : null,
            'rejected_reason' => $planned['status'] === 'rejected'
                ? 'The certificate on file was issued to another participant; please resubmit the certificate bearing this employee\'s name.'
                : null,
        ]);
    }

    /**
     * Fail loudly when a title would group under the wrong L&D category.
     *
     * `Training::ldCategory()` reads the title, not the `position_type` column,
     * and it tests leadership keywords first â€” so a technical seminar titled
     * "... Management ..." silently reports as leadership in the breakdown. The
     * plan states the bucket each title is meant to land in, and this makes a
     * future title edit that changes the bucket a failed seed rather than a
     * quietly wrong chart.
     */
    private function guardCategory(Training $training, string $expected): void
    {
        $actual = $training->ldCategory();

        if ($actual !== $expected) {
            throw new \RuntimeException(sprintf(
                'Training [%s] groups as [%s] but the plan expects [%s] â€” reword the title so it lands in the ' .
                'intended bucket, or update the plan.',
                $training->title,
                $actual,
                $expected
            ));
        }
    }

    /**
     * The office reference and certificate numbers for one row.
     *
     * Minted from a counter rather than from the record's key, so the pair is
     * unique per row and shaped like the documents the offices file: a reference
     * naming the convening body and the year, and a certificate number that
     * matches it. `cert_no` is never null â€” a training page that cannot name the
     * certificate is a row the HRMO cannot check against its own file.
     *
     * @return array{0: string, 1: string}
     */
    private function nextReferenceNumbers(string $prefix): array
    {
        $sequence = $this->sequence++;

        return [
            sprintf('%s-2026-%04d', $prefix, $sequence),
            sprintf('CERT-2026-%04d', $sequence),
        ];
    }

    /** The administrator a verified record is attributed to, resolved once. */
    private function adminUserId(): int
    {
        return $this->adminUserId ??= (int) (User::whereJsonContains('roles', 'admin')->value('id')
            ?? self::ADMIN_FALLBACK_USER_ID);
    }
}
