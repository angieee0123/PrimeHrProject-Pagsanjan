<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceTodayToNextWeekSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = Carbon::today();
        $endDate   = Carbon::today()->addDays(7);

        $employees = DB::table('employees')->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found.');
            return;
        }

        $this->command->info("Generating attendance from {$startDate->toDateString()} to {$endDate->toDateString()} for {$employees->count()} employees.");

        $records = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->dayOfWeek === Carbon::SUNDAY) continue;

            $dateStr = $date->toDateString();

            foreach ($employees as $employee) {
                $exists = DB::table('attendance')
                    ->where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->exists();

                if ($exists) continue;

                $roll = rand(1, 100);

                if ($date->dayOfWeek === Carbon::SATURDAY && $roll > 50) continue;

                if ($roll <= 5) continue; // 5% absent

                if ($roll <= 12) {
                    $amIn = $this->randomTime('08:16:00', '09:30:00'); // late
                } elseif ($roll <= 19) {
                    $amIn = $this->randomTime('06:30:00', '07:29:00'); // early bird
                } else {
                    $amIn = $this->randomTime('07:30:00', '08:10:00'); // normal
                }

                $records[] = [
                    'employee_id'     => $employee->id,
                    'date'            => $dateStr,
                    'am_in'           => $amIn,
                    'am_out'          => '12:00:00',
                    'pm_in'           => '13:00:00',
                    'pm_out'          => $this->randomTime('17:00:00', '17:30:00'),
                    'ot_in'           => null,
                    'ot_out'          => null,
                    'attendance_type' => 'REGULAR',
                    'remarks'         => null,
                ];
            }
        }

        if (empty($records)) {
            $this->command->info('All attendance records already exist for this period.');
            return;
        }

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('attendance')->insert($chunk);
        }

        $this->command->info('Inserted ' . count($records) . ' attendance records.');
    }

    private function randomTime(string $start, string $end): string
    {
        return date('H:i:s', rand(strtotime($start), strtotime($end)));
    }
}
