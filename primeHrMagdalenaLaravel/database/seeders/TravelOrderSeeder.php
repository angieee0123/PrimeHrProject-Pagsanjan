<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TravelOrder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TravelOrderSeeder extends Seeder
{
    // Realistic travel order data per employee
    private array $travelData = [
        ['destination' => 'Quezon City, Metro Manila',   'purpose' => 'Attend Regional HR Summit and capacity building seminar.',          'transport' => 'Bus',        'budget' => 3500.00, 'days' => 2],
        ['destination' => 'Legazpi City, Albay',         'purpose' => 'Participate in Provincial Finance Officers\' Conference.',           'transport' => 'Van',        'budget' => 2800.00, 'days' => 2],
        ['destination' => 'Naga City, Camarines Sur',    'purpose' => 'Attend Civil Service Commission training on PRIME-HRM.',            'transport' => 'Bus',        'budget' => 1500.00, 'days' => 1],
        ['destination' => 'Daet, Camarines Norte',       'purpose' => 'Coordination meeting with Provincial Health Office.',               'transport' => 'Motorcycle', 'budget' => 800.00,  'days' => 1],
        ['destination' => 'Sorsogon City, Sorsogon',     'purpose' => 'Attend Regional Engineering and Infrastructure Planning Workshop.', 'transport' => 'Van',        'budget' => 2200.00, 'days' => 2],
        ['destination' => 'Iriga City, Camarines Sur',   'purpose' => 'Conduct field inspection of municipal road projects.',              'transport' => 'Motorcycle', 'budget' => 600.00,  'days' => 1],
        ['destination' => 'Manila, Metro Manila',        'purpose' => 'Attend DILG National Conference on Local Governance.',              'transport' => 'Bus',        'budget' => 4500.00, 'days' => 3],
        ['destination' => 'Pili, Camarines Sur',         'purpose' => 'Submit quarterly reports to the Provincial Government.',            'transport' => 'Van',        'budget' => 700.00,  'days' => 1],
        ['destination' => 'Ligao City, Albay',           'purpose' => 'Attend inter-LGU coordination meeting on solid waste management.',  'transport' => 'Bus',        'budget' => 1200.00, 'days' => 1],
        ['destination' => 'Tabaco City, Albay',          'purpose' => 'Participate in Regional Social Welfare and Development Forum.',     'transport' => 'Van',        'budget' => 1800.00, 'days' => 2],
        ['destination' => 'Masbate City, Masbate',       'purpose' => 'Attend Provincial Budget Hearing and fiscal planning session.',     'transport' => 'Bus',        'budget' => 3200.00, 'days' => 2],
        ['destination' => 'Virac, Catanduanes',          'purpose' => 'Conduct community health outreach and medical mission.',            'transport' => 'Van',        'budget' => 2600.00, 'days' => 2],
    ];

    public function run(): void
    {
        $employees = DB::table('employees')
            ->join('users', 'users.employee_id', '=', 'employees.id')
            ->select('employees.id as emp_id', 'employees.first_name', 'employees.last_name', 'users.id as user_id')
            ->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found.');
            return;
        }

        // Stagger travel dates: each employee gets a unique weekday starting next Monday
        $baseDate  = Carbon::today()->next(Carbon::MONDAY);
        $inserted  = 0;
        $skipped   = 0;

        foreach ($employees as $index => $emp) {
            $data = $this->travelData[$index % count($this->travelData)];

            // Stagger: each employee starts 1 weekday apart to avoid same-day congestion
            $travelDate = $this->addWeekdays($baseDate->copy(), $index);
            $returnDate = $this->addWeekdays($travelDate->copy(), $data['days'] - 1);
            $duration   = $travelDate->diffInDays($returnDate) + 1;

            // Check overlap
            $overlap = TravelOrder::where('employee_id', $emp->emp_id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('travel_date', '<=', $returnDate->toDateString())
                ->where('return_date', '>=', $travelDate->toDateString())
                ->exists();

            if ($overlap) {
                $this->command->line("  Skipping {$emp->first_name} {$emp->last_name}: overlapping travel order exists.");
                $skipped++;
                continue;
            }

            try {
                $order = TravelOrder::create([
                    'employee_id'        => $emp->emp_id,
                    'destination'        => $data['destination'],
                    'purpose'            => $data['purpose'],
                    'travel_date'        => $travelDate->toDateString(),
                    'return_date'        => $returnDate->toDateString(),
                    'duration'           => $duration,
                    'transportation_mode'=> $data['transport'],
                    'estimated_budget'   => $data['budget'],
                    'attachment'         => null,
                    'status'             => 'pending',
                    'filed_by'           => $emp->user_id,
                ]);

                $this->command->info("  Filed: {$order->order_number} | {$emp->first_name} {$emp->last_name} | {$data['destination']} | {$travelDate->toDateString()} – {$returnDate->toDateString()} ({$duration}d)");
                $inserted++;

            } catch (\Exception $e) {
                $this->command->error("  Failed {$emp->first_name} {$emp->last_name}: " . $e->getMessage());
                $skipped++;
            }
        }

        $this->command->info("Done. Filed: {$inserted} | Skipped: {$skipped}");
    }

    private function addWeekdays(Carbon $date, int $days): Carbon
    {
        $added = 0;
        while ($added < $days) {
            $date->addDay();
            if (!$date->isWeekend()) $added++;
        }
        return $days === 0 ? $date : $date;
    }
}
