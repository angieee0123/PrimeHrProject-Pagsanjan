<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\EmployeeSupportingDocument;
use App\Models\EmploymentDetail;
use App\Models\GovernmentId;
use App\Models\Schedule;
use App\Models\User;
use Database\Seeders\Concerns\SeedsPagsanjanRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The thirty personnel taken from `docs/excels/HR-PAYROLL-PAGSANJAN.xlsx`, with
 * everything an employee record is made of: the person, their employment
 * detail, both addresses, their contacts, a work schedule, the 201 file, the
 * five government IDs and a signed-in account.
 *
 * Five of the thirty are Job Orders, taken from the workbook's `JO#*` payroll
 * sheets rather than the plantilla RECAP — they hold an "Admin Aide I"
 * designation paid at a daily rate.
 *
 * Every account is addressed at `maildrop.cc`, a public throwaway inbox: the
 * address is deliberately not deliverable to a real person, and the shared
 * password is public for the same reason. Re-running replaces this roster's own
 * rows and touches nothing else.
 */
class PagsanjanEmployeeSeeder extends Seeder
{
    use SeedsPagsanjanRoster;

    /** Barangays of Pagsanjan, Laguna — the municipality this HRIS serves. */
    private const BARANGAYS = [
        'Barangay I (Poblacion)', 'Barangay II (Poblacion)', 'Barangay III (Poblacion)',
        'Buboy', 'Cabanbanan', 'Duhat', 'Lambac', 'Layugan', 'Maulawin',
        'Pinagsanjan', 'Sabang', 'Sampaloc', 'San Isidro', 'Santa Cruz',
    ];

    private const STREETS = [
        'Rizal Street', 'Mabini Street', 'Burgos Street', 'Bonifacio Street',
        'Del Pilar Street', 'J.P. Rizal Street', 'Gomez Street', 'Luna Street',
    ];

    /** Offices whose work is outdoors, so the owner is exempt from the flags. */
    private const FIELD_OFFICES = ['GSO', 'GSO-SL', 'MDRRM'];

    public function run(): void
    {
        $this->seedRandomness();

        $now = now();
        $created = 0;

        $numbers = array_map(fn (int $index) => $this->employeeNumberFor($index), array_keys(self::ROSTER));
        $emails = array_map(
            fn (int $index) => $this->emailFor($index, self::ROSTER[$index]),
            array_keys(self::ROSTER)
        );

        // Idempotence, in its own committed step. Re-seeding replaces this
        // roster's own rows, and every child row (address, contact, IDs, 201
        // file, schedule, account) goes with the employee by cascade.
        //
        // The delete cannot share a transaction with the inserts: MySQL checks
        // `employees_email_unique` as rows are written, so at the moment the
        // second run re-inserts Febralyn Queliste her uncommitted predecessor is
        // still there and the insert is rejected as a duplicate.
        //
        // It prunes by email as well as by number. The email is the roster's
        // real key — a person's row must not be duplicated because a later edit
        // to this list moved somebody from index 27 to index 5 — and pruning by
        // number alone leaves the skipped index behind as a second Febralyn.
        DB::transaction(function () use ($numbers, $emails) {
            $ids = Employee::whereIn('employee_id', $numbers)
                ->orWhereIn('email', $emails)
                ->pluck('id');

            $userIds = User::whereIn('employee_id', $ids)->pluck('id');

            $this->releaseForeignKeyDependents($ids, $userIds);

            // Employees first, accounts second. Nothing in `users` cascades
            // into `employees`, while `employees` cascades into the whole
            // personnel file — addresses, government IDs, the 201 file, the
            // register, the leave ledger, payslips — so removing the person is
            // what clears the file.
            Employee::whereIn('id', $ids)->delete();
            User::whereIn('id', $userIds)->delete();
        });

        DB::transaction(function () use ($now, &$created) {
            foreach (self::ROSTER as $index => $person) {
                $number = $this->employeeNumberFor($index);

                $employee = $this->createEmployee($index, $person, $number, $now);
                $this->createEmploymentDetail($index, $person, $employee);

                $this->createAddresses($index, $employee, $now);
                $this->createContacts($index, $employee, $now);
                $this->createSchedule($index, $person, $employee, $now);
                $this->createSupportingDocuments($index, $person, $employee, $now);
                $this->createGovernmentIds($index, $person, $employee, $now);

                $this->createUser($index, $person, $employee);

                $created++;
            }
        });

        $this->command->info(
            'Pagsanjan roster: ' . count(self::ROSTER) . ' employees ('
            . count(array_filter(self::ROSTER, fn ($p) => ! empty($p['job_order'])))
            . ' Job Order) with accounts, schedules, 201 files and government IDs.'
        );
    }

    /**
     * Clear the handful of rows that do NOT cascade when a person is removed.
     *
     * Almost the whole personnel file hangs off `employees` with `ON DELETE
     * CASCADE`, so deleting the employee clears it. Three tables do not, and
     * each is a real FK in the live schema rather than a hypothetical:
     *
     * - `employee_loans` and `payroll_deductions` reference `employees` with
     *   `NO ACTION`, so a loan row would refuse the delete outright;
     * - `trainings.verified_by` references `users` with `NO ACTION`, so an HR
     *   account that verified a training would refuse the account's delete;
     * - `leave_applications.filed_by` references `users` with **RESTRICT**, which
     *   is what actually broke re-running this seeder: the employee's leave
     *   applications block their own account from being removed. Removing the
     *   employee first is what clears those applications, since that FK is
     *   `CASCADE` from `employees`.
     * - `attendance_exemptions` keys on `reference_id` with **no foreign key at
     *   all** — it can point at an employee, a department or a designation — so
     *   nothing cascades and nothing refuses. It is cleared here explicitly:
     *   this seeder re-issues employee ids on every run, and a later seeder that
     *   cleans up by the id it wrote last time would leave a duplicate row
     *   behind for every run after that.
     *
     * The remaining tables that reference `users` cascade or null their column,
     * so they need no help. Everything deleted here belongs to the employees
     * being replaced — this is the demo roster clearing its own slice, never
     * another account's rows.
     */
    private function releaseForeignKeyDependents(Collection $employeeIds, Collection $userIds): void
    {
        if ($employeeIds->isNotEmpty()) {
            foreach (['employee_loans', 'payroll_deductions'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->whereIn('employee_id', $employeeIds)->delete();
                }
            }

            if (Schema::hasTable('attendance_exemptions')) {
                DB::table('attendance_exemptions')
                    ->where('exemption_type', 'employee')
                    ->whereIn('reference_id', $employeeIds)
                    ->delete();
            }
        }

        if ($userIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('trainings')) {
            DB::table('trainings')->whereIn('verified_by', $userIds)->delete();
        }

        foreach ([
            'ai_conversations',
            'chat_history',
            'notifications',
            'user_ai_settings',
            'user_notification_preferences',
            'user_settings',
            'user_theme_settings',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->whereIn('user_id', $userIds)->delete();
            }
        }
    }

    private function createEmployee(int $index, array $person, string $number, $now): Employee
    {
        $meta = $this->rosterMeta($index, $person);

        return Employee::create([
            'employee_id' => $number,
            'first_name' => $person['first_name'],
            'middle_name' => $person['middle_name'] ?? null,
            'last_name' => $person['last_name'],
            'suffix' => $person['suffix'] ?? null,
            'birth_date' => $person['birth_date'],
            'place_of_birth' => $meta['place_of_birth'],
            'sex' => $person['sex'],
            'civil_status' => $person['civil_status'],
            'citizenship' => $meta['citizenship'],
            'height' => $meta['height'],
            'weight' => $meta['weight'],
            'blood_type' => $meta['blood_type'],
            // Left null on purpose: `employees.photo` holds a public URL, and a
            // row pointing at a file that does not exist renders as a broken
            // image on the personnel page rather than as the placeholder avatar
            // a null gives.
            'photo' => null,
            'email' => $this->emailFor($index, $person),
            'created_at' => $now,
        ]);
    }

    private function createEmploymentDetail(int $index, array $person, Employee $employee): EmploymentDetail
    {
        $meta = $this->rosterMeta($index, $person);
        $departmentId = $this->departmentIdForOffice($person['office']);
        $designationId = $this->resolveDesignationId($departmentId, $person['title'], $person['office']);

        return EmploymentDetail::create([
            'employee_id' => $employee->id,
            'designation_id' => $designationId,
            'department_id' => $departmentId,
            'employment_status' => $person['status'],
            'appointment_date' => $meta['appointment_date'],
            'salary_grade' => $meta['salary_grade'],
            'step_increment' => $meta['step_increment'],
        ]);
    }

    /**
     * A residential and a permanent address.
     *
     * Both are written because the registration form collects both and several
     * screens print the permanent one when it exists — leaving it null is what
     * makes those screens fall back to a blank block.
     */
    private function createAddresses(int $index, Employee $employee, $now): void
    {
        $barangay = self::BARANGAYS[$index % count(self::BARANGAYS)];
        $street = self::STREETS[($index * 3) % count(self::STREETS)];
        $houseNo = (string) (100 + ($index * 7) % 300);

        foreach (['residential', 'permanent'] as $offset => $type) {
            Address::create([
                'employee_id' => $employee->id,
                'type' => $type,
                // The permanent address is the family home; a few sit in a
                // different barangay of the same town, which is the ordinary case.
                'house_no' => $type === 'permanent' ? (string) (10 + ($index * 3) % 90) : $houseNo,
                'street' => $type === 'permanent'
                    ? self::STREETS[($index * 5 + $offset) % count(self::STREETS)]
                    : $street,
                'barangay' => $type === 'permanent'
                    ? self::BARANGAYS[($index + 4) % count(self::BARANGAYS)]
                    : $barangay,
                'city' => 'Pagsanjan',
                'province' => 'Laguna',
                'zip_code' => '4008',
            ]);
        }
    }

    /** A mobile, a landline where the household has one, and an emergency contact. */
    private function createContacts(int $index, Employee $employee, $now): void
    {
        $mobile = '09' . str_pad((string) (17 + $index), 2, '0', STR_PAD_LEFT)
            . str_pad((string) (1000000 + $index * 3571), 7, '0', STR_PAD_LEFT);

        Contact::create([
            'employee_id' => $employee->id,
            'type' => 'mobile',
            'number' => substr($mobile, 0, 11),
            'contact_person' => null,
        ]);

        // Every third employee keeps a landline; the rest are mobile-only, which
        // is the shape the registration form produces in practice.
        if ($index % 3 === 0) {
            Contact::create([
                'employee_id' => $employee->id,
                'type' => 'landline',
                'number' => '(049) ' . (5000 + $index) . '-' . str_pad((string) ($index * 13 % 10000), 4, '0', STR_PAD_LEFT),
                'contact_person' => null,
            ]);
        }

        Contact::create([
            'employee_id' => $employee->id,
            'type' => 'emergency',
            'number' => '09' . str_pad((string) (25 + $index), 2, '0', STR_PAD_LEFT)
                . str_pad((string) (2000000 + $index * 6421), 7, '0', STR_PAD_LEFT),
            'contact_person' => $this->emergencyContactName($index),
        ]);
    }

    /**
     * One work schedule covering the whole seeded year.
     *
     * Most of the municipality works 08:00–17:00. The offices whose staff are on
     * the road or on a shift start earlier, and that difference is the point:
     * lateness is measured against each person's own schedule, so a roster where
     * every row is identical would never exercise the rule. Job orders are
     * likewise on the standard day — the workbook pays them by the day worked.
     */
    private function createSchedule(int $index, array $person, Employee $employee, $now): void
    {
        $earlyOffice = in_array($person['office'], ['GSO', 'GSO-SL', 'MDRRM', 'MARKET-OM'], true);

        $amIn = $earlyOffice ? '07:00:00' : '08:00:00';
        $amOut = '12:00:00';
        $pmIn = '13:00:00';
        $pmOut = $earlyOffice ? '16:00:00' : '17:00:00';

        Schedule::create([
            'employee_id' => $employee->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'am_in' => $amIn,
            'am_out' => $amOut,
            'pm_in' => $pmIn,
            'pm_out' => $pmOut,
        ]);
    }

    /**
     * The 201 file.
     *
     * The paths are null — no uploads exist for a seeded employee, and a string
     * pointing at a missing file makes the personnel page offer a download that
     * 404s. The row itself is not optional: the wizard writes it on
     * registration and the 201-file card reads its presence.
     */
    private function createSupportingDocuments(int $index, array $person, Employee $employee, $now): void
    {
        EmployeeSupportingDocument::create([
            'employee_id' => $employee->id,
            'pds_file_path' => null,
            'appointment_form_file_path' => null,
            'position_description_file_path' => null,
            'medical_certificate_file_path' => null,
            'nbi_clearance_file_path' => null,
            'financial_clearance_file_path' => null,
            'neuro_exam_file_path' => null,
            'licenses_file_path' => null,
            'performance_eval_file_path' => null,
            'commendation_file_path' => null,
            'disciplinary_file_path' => null,
            'other_records_file_path' => null,
        ]);
    }

    /**
     * The five IDs, in the shapes the agencies actually issue them.
     *
     * Numbers are deterministic rather than random so a re-seed reproduces the
     * same person; a driver's licence is only written for the staff whose work
     * involves one, which is what makes the "missing ID" card meaningful on the
     * personnel page.
     */
    private function createGovernmentIds(int $index, array $person, Employee $employee, $now): void
    {
        $needsLicense = in_array($person['office'], ['GSO', 'GSO-SL', 'MDRRM', 'AGRI'], true)
            || str_contains($person['title'], 'Driver');

        GovernmentId::create([
            'employee_id' => $employee->id,
            'gsis_no' => '2' . str_pad((string) (100000000 + $index * 137), 10, '0', STR_PAD_LEFT),
            'gsis_file_path' => null,
            'philhealth_no' => '11-' . str_pad((string) (200000000 + $index * 271), 9, '0', STR_PAD_LEFT) . '-' . ($index % 9 + 1),
            'philhealth_file_path' => null,
            'pagibig_no' => '1210-' . str_pad((string) (3000000 + $index * 419), 7, '0', STR_PAD_LEFT) . '-' . ($index % 9 + 1),
            'pagibig_file_path' => null,
            'tin_no' => str_pad((string) (400 + $index), 3, '0', STR_PAD_LEFT) . '-'
                . str_pad((string) (500 + $index * 3), 3, '0', STR_PAD_LEFT) . '-'
                . str_pad((string) (600 + $index * 7), 3, '0', STR_PAD_LEFT) . '-000',
            'tin_file_path' => null,
            'license_no' => $needsLicense
                ? 'N' . str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT) . '-'
                    . str_pad((string) (2026000 + $index * 53), 7, '0', STR_PAD_LEFT)
                : null,
            'license_file_path' => null,
        ]);
    }

    /**
     * The signed-in account.
     *
     * `employee_id` is what the rails, the AI assistant's self-service scope and
     * the notification audiences resolve the current user through — an account
     * without it signs in to a system that cannot find its own name.
     */
    private function createUser(int $index, array $person, Employee $employee): User
    {
        return User::create([
            'name' => $this->fullName($person),
            'username' => $this->usernameFor($index, $person),
            'email' => $this->emailFor($index, $person),
            'email_verified_at' => now(),
            'password' => self::ROSTER_PASSWORD,
            'roles' => ['employee'],
            'status' => 'Active',
            'employee_id' => $employee->id,
        ]);
    }

    /** "{first}.{last}@maildrop.cc" — the throwaway domain, one inbox per person. */
    private function emailFor(int $index, array $person): string
    {
        $handle = Str::slug($person['first_name'] . '.' . $person['last_name'], '.');
        $handle = str_replace('-', '', $handle);

        return $handle . '@maildrop.cc';
    }

    /** The username the sign-in form expects: the full name, unpunctuated. */
    private function usernameFor(int $index, array $person): string
    {
        return strtolower(
            preg_replace('/[^a-z]/i', '', $person['first_name'] . $person['last_name'])
        );
    }

    private function fullName(array $person): string
    {
        return trim(sprintf(
            '%s %s%s %s',
            $person['first_name'],
            $person['middle_name'] ?? '',
            ($person['middle_name'] ?? '') !== '' ? '.' : '',
            $person['last_name'] . (isset($person['suffix']) ? ' ' . $person['suffix'] : '')
        ));
    }

    /** An immediate family member, as the emergency-contact field expects. */
    private function emergencyContactName(int $index): string
    {
        $surnames = array_column(self::ROSTER, 'last_name');
        $given = ['Maria', 'Jose', 'Ana', 'Ramon', 'Luzviminda', 'Edgar', 'Cristina', 'Rogelio'];

        return $given[$index % count($given)] . ' ' . $surnames[($index + 5) % count($surnames)];
    }
}
