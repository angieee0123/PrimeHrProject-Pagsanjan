<?php

namespace Database\Seeders\Concerns;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmploymentDetail;

/**
 * The thirty personnel seeded from `docs/excels/HR-PAYROLL-PAGSANJAN.xlsx`,
 * and the lookups every seeder that touches them needs.
 *
 * The roster lives here rather than in `PagsanjanEmployeeSeeder` because ten
 * other seeders key off the same thirty people - attendance, payslips, leave,
 * deductions. A second copy of the list is a second answer to "which employee
 * is 7", and the two would drift the first time somebody edited one of them.
 *
 * Names, designations, offices and salary rates are transcribed from the
 * workbook's RECAP sheet; the five Job Order personnel and their daily rate come
 * from its `JO#*` payroll sheets, where every job order is carried as
 * "Admin Aide I" at a daily rate (PHP 344.63 to PHP 430.82) instead of a monthly one.
 */
trait SeedsPagsanjanRoster
{
    /**
     * Employee numbers are minted into a reserved 9xxx range.
     *
     * `Employee::generateEmployeeId()` hands out `EMP-<year>-0001` upward, so a
     * registration made after this seeder runs must not collide with anything it
     * wrote. Keeping the block at 9001 is what guarantees that.
     */
    public const ROSTER_ID_BASE = 9001;

    /** Every seeded account shares one password; maildrop.cc inboxes are public. */
    public const ROSTER_PASSWORD = 'password';

    /** Job orders in the workbook are paid per day, not per month. */
    public const JOB_ORDER_DAILY_RATE = 344.63;

    /** Repeating decimals for a job order's monthly-equivalent rate are not money. */
    public const JOB_ORDER_MONTHLY_RATE = 7581.86;

    /**
     * The roster: office code => [designation title, employment status, sex].
     *
     * `office` is the workbook's own office code, mapped to a `departments` row
     * by OFFICE_DEPARTMENTS below. `title` is the designation as the payroll
     * sheet names it; resolveDesignationId() finds the matching `designations`
     * row inside that department, because the titles were entered by hand and
     * "RCC II" / "RCCII" are the same job spelled two ways.
     *
     * The five Job Order personnel are marked `job_order => true` - they are the
     * ones carried on the `JO#*` sheets rather than the plantilla RECAP.
     */
    public const ROSTER = [
        // ---- Office of the Municipal Mayor ----
        [
            'first_name' => 'Mariliz Monina', 'last_name' => 'Trinidad', 'office' => 'MO',
            'title' => 'Executive Asst. II', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1978-04-11', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Aldrin', 'middle_name' => 'G.', 'last_name' => 'Macalalad', 'office' => 'MO',
            'title' => 'Private Sec. II', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1985-09-02', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Lorelie', 'middle_name' => 'L.', 'last_name' => 'Ambrosio', 'office' => 'MO',
            'title' => 'Admin. Aide IV', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1991-01-27', 'civil_status' => 'Single',
        ],

        // ---- Human Resources Management Office ----
        [
            'first_name' => 'May', 'middle_name' => 'A.', 'last_name' => 'Escote', 'office' => 'HRMO',
            'title' => 'Admin. Asst. IV', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1987-06-15', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Melarose', 'middle_name' => 'P.', 'last_name' => 'Nadera', 'office' => 'HRMO',
            'title' => 'Admin. Aide IV', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1993-11-08', 'civil_status' => 'Single',
        ],
        [
            'first_name' => 'Kevin Mar', 'middle_name' => 'Z.', 'last_name' => 'Moreno', 'office' => 'HRMO',
            'title' => 'Admin. Aide III', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1996-03-19', 'civil_status' => 'Single',
        ],

        // ---- Municipal Planning and Development Office ----
        [
            'first_name' => 'Prince Oliver', 'middle_name' => 'G.', 'last_name' => 'Fernandez', 'office' => 'MPDC',
            'title' => 'Data Encoder III', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1990-07-30', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Victor', 'middle_name' => 'D.', 'last_name' => 'Mirando', 'suffix' => 'Jr.', 'office' => 'MPDC',
            'title' => 'Admin. Aide IV', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1988-02-14', 'civil_status' => 'Married',
        ],

        // ---- Municipal Civil Registry Office ----
        [
            'first_name' => 'Dino Paolo', 'middle_name' => 'L.', 'last_name' => 'Guan', 'office' => 'MCR',
            'title' => 'Clerk IV', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1992-05-21', 'civil_status' => 'Single',
        ],

        // ---- General Services Office ----
        [
            'first_name' => 'Annallee', 'middle_name' => 'A.', 'last_name' => 'Aguilar', 'office' => 'GSO',
            'title' => 'Supply Officer I', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1986-12-03', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Francisco', 'middle_name' => 'L.', 'last_name' => 'Tobias', 'office' => 'GSO',
            'title' => 'Admin. Aide III', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1994-08-17', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Febralyn', 'middle_name' => 'R.', 'last_name' => 'Queliste', 'office' => 'GSO',
            'title' => 'Admin. Aide III', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1995-10-25', 'civil_status' => 'Single',
        ],

        // ---- Budget Office ----
        [
            'first_name' => 'Eduardo', 'last_name' => 'Cantillan', 'office' => 'BO',
            'title' => 'Data Encoder III', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1983-03-06', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Ruby', 'middle_name' => 'A.', 'last_name' => 'Dimalanta', 'office' => 'BO',
            'title' => 'Admin. Asst. II', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1989-09-12', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Nadine', 'last_name' => 'Bernadino', 'office' => 'BO',
            'title' => 'Admin. Aide IV', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1997-01-09', 'civil_status' => 'Single',
        ],

        // ---- Accounting Office ----
        [
            'first_name' => 'Ruby', 'middle_name' => 'F.', 'last_name' => 'Timoteo', 'office' => 'AO',
            'title' => 'Bookkeeper III', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1984-07-04', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Alma', 'last_name' => 'Estrella', 'office' => 'AO',
            'title' => 'Bookkeeper III', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1982-11-23', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Joana Patrizha', 'last_name' => 'Santiago', 'office' => 'AO',
            'title' => 'Admin. Aide IV', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1998-04-02', 'civil_status' => 'Single',
        ],

        // ---- Municipal Treasurers Office ----
        [
            'first_name' => 'Yolanda', 'middle_name' => 'T.', 'last_name' => 'Tan', 'office' => 'MTO',
            'title' => 'LRCO II', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1981-12-19', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Joal', 'middle_name' => 'C.', 'last_name' => 'Guan', 'office' => 'MTO',
            'title' => 'Cashier II', 'status' => 'Permanent', 'sex' => 'Male',
            'birth_date' => '1987-02-07', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Irene', 'middle_name' => 'A.', 'last_name' => 'Tamondong', 'office' => 'MTO',
            'title' => 'RCC II', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1990-10-13', 'civil_status' => 'Married',
        ],

        // ---- Assessors Office ----
        [
            'first_name' => 'Rosalie', 'last_name' => 'Castillo', 'office' => 'ASSESSOR',
            'title' => 'Assmt. Clerk II', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1985-01-30', 'civil_status' => 'Married',
        ],
        [
            'first_name' => 'Jessa', 'last_name' => 'Manalastas', 'office' => 'ASSESSOR',
            'title' => 'Tax Mapping Aide', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1994-12-01', 'civil_status' => 'Single',
        ],

        // ---- Municipal Health Office ----
        [
            'first_name' => 'Lei Arnie', 'middle_name' => 'L.', 'last_name' => 'Abrigo', 'office' => 'MHO',
            'title' => 'Clerk IV', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1992-09-26', 'civil_status' => 'Single',
        ],
        [
            'first_name' => 'Lyka', 'middle_name' => 'B.', 'last_name' => 'Nadera', 'office' => 'MHO',
            'title' => 'Admin. Aide III', 'status' => 'Permanent', 'sex' => 'Female',
            'birth_date' => '1998-08-08', 'civil_status' => 'Single',
        ],

        // ---- Job Order personnel (the workbook's `JO#*` payroll sheets) ----
        [
            'first_name' => 'Anselmo', 'middle_name' => 'D.', 'last_name' => 'Bautista', 'office' => 'GSO',
            'title' => 'Admin. Aide I', 'status' => 'Job Order', 'sex' => 'Male',
            'birth_date' => '1980-06-12', 'civil_status' => 'Married', 'job_order' => true,
        ],
        [
            'first_name' => 'Reynaldo', 'middle_name' => 'S.', 'last_name' => 'Magsino', 'office' => 'GSO-SL',
            'title' => 'Admin. Aide I', 'status' => 'Job Order', 'sex' => 'Male',
            'birth_date' => '1979-03-24', 'civil_status' => 'Married', 'job_order' => true,
        ],
        [
            'first_name' => 'Jimmy', 'middle_name' => 'P.', 'last_name' => 'Baldemor', 'office' => 'MDRRM',
            'title' => 'Admin. Aide I', 'status' => 'Job Order', 'sex' => 'Male',
            'birth_date' => '1983-10-09', 'civil_status' => 'Married', 'job_order' => true,
        ],
        [
            'first_name' => 'Teodoro', 'middle_name' => 'T.', 'last_name' => 'Ilagan', 'office' => 'GSO',
            'title' => 'Admin. Aide I', 'status' => 'Job Order', 'sex' => 'Male',
            'birth_date' => '1986-04-18', 'civil_status' => 'Married', 'job_order' => true,
        ],
        [
            'first_name' => 'Danilo', 'middle_name' => 'R.', 'last_name' => 'Panganiban', 'office' => 'AGRI',
            'title' => 'Admin. Aide I', 'status' => 'Job Order', 'sex' => 'Male',
            'birth_date' => '1988-12-27', 'civil_status' => 'Married', 'job_order' => true,
        ],
    ];

    /**
     * Workbook office code => the `departments` row it belongs to.
     *
     * `code` is the department's own code, so the mapping survives a fresh
     * `DepartmentSeeder` run assigning different ids.
     */
    public const OFFICE_DEPARTMENTS = [
        'MO' => 'MO',
        'HRMO' => 'HRMO',
        'MPDC' => 'MPDC',
        'MCR' => 'MCR',
        'GSO' => 'GSO',
        'GSO-SL' => 'GSO-SL',
        'BO' => 'BO',
        'AO' => 'AO',
        'MTO' => 'MTO',
        'ASSESSOR' => 'ASSESSOR',
        'MHO' => 'MHO',
        'MDRRM' => 'MDRRM',
        'AGRI' => 'AGRI',
        'MSWD' => 'MSWD',
    ];

    /**
     * Employers' details that are the same for everybody in this dataset.
     *
     * The workbook's RECAP lists every plantilla position as appointed
     * "Mar. 2021", which is why the appointment dates cluster there; the job
     * orders take the first of the year their payroll sheet covers.
     */
    private function rosterMeta(int $index, array $person): array
    {
        return [
            'place_of_birth' => $person['place_of_birth'] ?? 'Pagsanjan, Laguna',
            'citizenship' => 'Filipino',
            'height' => 152 + (($index * 7) % 30),
            'weight' => 48 + (($index * 5) % 32),
            'blood_type' => ['A+', 'B+', 'O+', 'AB+', 'O-'][$index % 5],
            'appointment_date' => ($person['job_order'] ?? false) ? '2026-01-05' : '2021-03-01',
            'salary_grade' => ($person['job_order'] ?? false) ? null : (string) (1 + ($index % 24)),
            'step_increment' => ($person['job_order'] ?? false) ? null : (string) (1 + ($index % 8)),
        ];
    }

    /** The `departments` id for a workbook office code. */
    public function departmentIdForOffice(string $office): int
    {
        $code = self::OFFICE_DEPARTMENTS[$office] ?? $office;

        $id = Department::where('code', $code)->value('id');

        if (! $id) {
            throw new \RuntimeException("No department with code [{$code}] - run DepartmentSeeder first.");
        }

        return (int) $id;
    }

    /**
     * Payroll-sheet title => the plantilla title the `designations` table uses.
     *
     * The two lists were typed by different people for different purposes, so
     * some jobs are simply named differently in each: the RECAP calls the MPDC
     * encoder a "Data Encoder III" where the designation table calls the same
     * post a "Proj. Dev't. Officer III".
     *
     * This is a translation table and not a fuzzy match on purpose. A first
     * version fell back to "the roster title is contained in a designation of
     * this department", which paired "Data Encoder III" with "Admin. Aide III"
     * on the shared "III" and put the Budget Office's two clerks on the
     * Municipal Budget Officer's PHP 83,457 rate. A wrong rate is not a cosmetic
     * error here - it is the divisor behind every payslip in this dataset - so
     * an unmapped title now fails loudly instead.
     */
    public const DESIGNATION_ALIASES = [
        'Data Encoder III' => ['MPDC' => 'Proj. Dev\'t. Officer III', 'BO' => 'Admin Asst.V'],
        'Admin. Asst. II' => ['BO' => 'Budget Officer I'],
        'Admin. Asst. IV' => ['HRMO' => 'Admin. Aide IV'],
        'Admin. Aide IV' => ['BO' => 'Admin. Aide IV'],
        'Assmt. Clerk II' => ['ASSESSOR' => 'Assessment Clerk III'],
        'Tax Mapping Aide' => ['ASSESSOR' => 'Admin. Aide IV'],
        'RCC II' => ['MTO' => 'RCC II'],
        'LRCO II' => ['MTO' => 'LRCO III'],
        // A job order holds the plantilla post its payroll sheet is run against.
        // The workbook writes "Admin Aide I"; the designation table's nearest
        // row is "Admin. Aide III". Renaming the plantilla post to match a
        // 2017 sheet would move the job, not the pay - so the designation is
        // translated and `employment_status` carries the job-order fact.
        'Admin. Aide I' => ['*' => 'Admin. Aide III'],
    ];

    /**
     * The `designations` row a roster entry is filed under.
     *
     * Exact title first, then the alias table, then a normalised comparison
     * inside the same department - which is what pairs the payroll sheet's
     * "RCCII" with the designation table's "RCC II". A department with several
     * rows of the same title (three "Admin. Aide III" in Accounting) is fine:
     * they are one job at slightly different steps, and the first row is as good
     * as the second.
     */
    private function resolveDesignationId(int $departmentId, string $title, string $officeCode): int
    {
        $exact = Designation::where('department_id', $departmentId)
            ->where('title', $title)
            ->value('id');

        if ($exact) {
            return (int) $exact;
        }

        $alias = self::DESIGNATION_ALIASES[$title][$officeCode]
            ?? self::DESIGNATION_ALIASES[$title]['*']
            ?? null;

        if ($alias !== null) {
            $aliased = Designation::where('department_id', $departmentId)
                ->where('title', $alias)
                ->value('id');

            if ($aliased) {
                return (int) $aliased;
            }
        }

        $normalise = fn (string $value): string => preg_replace('/[^a-z0-9]/', '', strtolower($value));
        $wanted = $normalise($title);

        $near = Designation::where('department_id', $departmentId)
            ->get(['id', 'title'])
            ->first(fn ($row) => $normalise($row->title) === $wanted);

        if ($near) {
            return (int) $near->id;
        }

        $department = Department::where('id', $departmentId)->value('name') ?? $departmentId;

        throw new \RuntimeException(
            "No designation [{$title}] in [{$department}] (office {$officeCode}) - add it to " .
            'PagsanjanEmployeeSeeder::DESIGNATION_ALIASES, or seed the designation first.'
        );
    }

    /**
     * The monthly rate a designation pays.
     *
     * A job order has no monthly rate on its designation row - the workbook pays
     * it a daily rate - so its monthly-equivalent is derived from that daily
     * rate rather than left at zero, which would make every payslip it appears on
     * read PHP 0.00.
     */
    private function monthlyRateFor(int $designationId): float
    {
        $rate = (float) (Designation::where('id', $designationId)->value('monthly_rate') ?? 0);

        return $rate > 0 ? $rate : self::JOB_ORDER_MONTHLY_RATE;
    }

    /**
     * The seeded employees, keyed by their minted employee number, in roster
     * order. Every other seeder reads its subjects through this so they cannot
     * disagree about who is in the dataset.
     *
     * @return array<int, Employee> keyed by employees.id
     */
    private function rosterEmployees(): array
    {
        $ids = [];

        foreach (array_keys(self::ROSTER) as $index) {
            $ids[] = $this->employeeNumberFor($index);
        }

        return Employee::whereIn('employee_id', $ids)
            ->get()
            ->sortBy(fn (Employee $employee) => array_search($employee->employee_id, $ids, true))
            ->keyBy('id')
            ->all();
    }

    /** The employee number at a roster index: EMP-<year>-9001, 9002, ... */
    private function employeeNumberFor(int $index): string
    {
        return Employee::employeeIdPrefix() . (self::ROSTER_ID_BASE + $index);
    }

    /**
     * Steady, repeatable pseudo-randomness.
     *
     * `rand()` would give a different attendance register, a different set of
     * absent days and a different net pay on every `db:seed`, so two people
     * comparing the same seeded record would be looking at two different
     * datasets. Seeding mt_rand with a fixed value makes the seed reproducible
     * while still looking irregular.
     */
    private function seedRandomness(): void
    {
        mt_srand(20260914);
    }

    /** Deterministic draw in [$min, $max]. */
    private function draw(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    /**
     * The employee's employment detail, resolved once - the rate every payroll
     * computation divides by is read from here.
     */
    private function employmentDetailFor(int $employeeId): ?EmploymentDetail
    {
        return EmploymentDetail::where('employee_id', $employeeId)->first();
    }
}