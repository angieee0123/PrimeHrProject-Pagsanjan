<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\DeductionType;
use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The reference tables first, then the thirty-personnel dataset built from
     * `docs/excels/HR-PAYROLL-PAGSANJAN.xlsx`.
     *
     * **The order of the `Pagsanjan*` seeders is load-bearing** and is not the
     * order the files were written in:
     *
     * 1. `PagsanjanEmployeeSeeder` — the people themselves. Every later seeder
     *    resolves its subjects through the roster trait, so nothing else can run
     *    before it.
     * 2. `PagsanjanLeaveBalanceSeeder` — the credits a leave application spends.
     * 3. `PagsanjanLeaveApplicationSeeder` — approved leave, filed before the
     *    register is built so the attendance seeder can mark those days LEAVE.
     * 4. `PagsanjanTravelOrderSeeder` — likewise: approved orders are what the
     *    attendance seeder marks TRAVEL_ORDER.
     * 5. `PagsanjanAttendanceSeeder` — the register, which reads (3) and (4) to
     *    know which days another workflow already owns. Put it first and the
     *    approved leave and travel days come back as absences.
     * 6. `PagsanjanPayrollSeeder` — payslips, aggregated from the daily salary
     *    computations (5) writes.
     * 7. `PagsanjanDeductionSeeder`, `PagsanjanTrainingSeeder`,
     *    `PagsanjanAttendanceExemptionSeeder` — independent of the payroll chain.
     * 8. `PagsanjanPassSlipSeeder`, `PagsanjanMonetizationSeeder` — last, because
     *    monetization debits the `VL` balance that (3) has already moved, and it
     *    must read the settled figure rather than one about to change.
     */
    public function run(): void
    {
        if ($this->referenceDataIsMissing()) {
            $this->call([
                DepartmentSeeder::class,
                DesignationSeeder::class,
                AdminUserSeeder::class,
                LeaveTypesConfigSeeder::class,
                DeductionTypesSeeder::class,
                LeaveAccrualRatesSeeder::class,
            ]);
        } elseif ($this->command) {
            $this->command->line('  Reference data already present — seeding the personnel dataset on top of it.');
        }

        if ($this->shouldSeedDemonstrationData()) {
            $this->call([
                PagsanjanEmployeeSeeder::class,
                PagsanjanLeaveBalanceSeeder::class,
                PagsanjanLeaveApplicationSeeder::class,
                PagsanjanTravelOrderSeeder::class,
                PagsanjanAttendanceSeeder::class,
                PagsanjanPayrollSeeder::class,
                PagsanjanDeductionSeeder::class,
                PagsanjanTrainingSeeder::class,
                PagsanjanAttendanceExemptionSeeder::class,
                PagsanjanPassSlipSeeder::class,
                PagsanjanMonetizationSeeder::class,
            ]);
        }
    }

    /**
     * Whether the reference tables still need to be seeded.
     *
     * The four reference seeders are plain `insert()` calls with no lookup
     * first, so on a database that already holds them they die on the unique
     * index (`Duplicate entry 'MO' for key 'departments_code_unique'`) rather
     * than reporting anything useful. That made `db:seed` a once-per-database
     * command: there was no way to fill in the personnel dataset afterwards
     * without a `migrate:fresh`, which drops whatever work had accumulated.
     *
     * Guarding on the data rather than on `migrate:fresh` is what makes the
     * command re-runnable — and it is deliberately a *presence* check, not a
     * count: an installation that has added its own offices must not have the
     * shipped set inserted underneath them, and one that has emptied a table can
     * get it back.
     */
    private function referenceDataIsMissing(): bool
    {
        foreach ([
            [Department::class, 'departments'],
            [Designation::class, 'designations'],
            [LeaveType::class, 'leave_types_config'],
            [DeductionType::class, 'deduction_types'],
        ] as [$model, $table]) {
            if (! Schema::hasTable($table) || $model::query()->doesntExist()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the thirty-personnel dataset is wanted.
     *
     * It is opt-in rather than automatic, and the reason is not tidiness:
     * `PagsanjanEmployeeSeeder` mints thirty employee records, thirty accounts
     * and roughly five thousand attendance rows, each with its accredited-hours
     * log and daily peso computation. A developer running
     * `migrate:fresh --seed` to get a working login should not silently end up
     * with a populated HRIS, and a deployment that seeders the reference tables
     * must not publish a roster of people who do not work there.
     *
     * So `php artisan db:seed` alone leaves a clean install, and
     * `SEED_DEMO_DATA=true php artisan migrate:fresh --seed` (or
     * `php artisan db:seed --class=PagsanjanEmployeeSeeder` and the rest of the
     * chain) fills it.
     */
    private function shouldSeedDemonstrationData(): bool
    {
        return filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN);
    }
}
