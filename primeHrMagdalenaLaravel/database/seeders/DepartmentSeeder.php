<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $departments = [
            ['code' => 'MO', 'name' => "Mayor's Office", 'head' => 'n/a', 'status' => 'Active', 'description' => null],
            ['code' => 'MAO', 'name' => "Municipal Assessor's Office", 'head' => 'n/a', 'status' => 'Active', 'description' => null],
            ['code' => 'HRMO', 'name' => 'Human Resources Management Office', 'head' => 'n/a', 'status' => 'Active', 'description' => null],
            ['code' => 'BAC', 'name' => 'Bids and Awards Committee Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Handles procurement processes, bidding, and awarding of government contracts.'],
            ['code' => 'ADMIN', 'name' => 'Admin Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Manages general administration, records, and internal office operations.'],
            ['code' => 'MPDC', 'name' => 'Municipal Planning and Development Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Oversees local planning, development programs, and land use plans.'],
            ['code' => 'MCR', 'name' => 'Municipal Civil Registry Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Maintains civil records such as birth, marriage, and death certificates.'],
            ['code' => 'GSO', 'name' => 'General Services Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Manages government assets, supplies, and maintenance services.'],
            ['code' => 'BO', 'name' => 'Budget Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Prepares and monitors the municipal budget and financial allocations.'],
            ['code' => 'AO', 'name' => 'Accounting Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Handles financial reporting, bookkeeping, and audits.'],
            ['code' => 'MTO', 'name' => "Municipal Treasurer's Office", 'head' => 'n/a', 'status' => 'Active', 'description' => 'Manages revenue collection, taxes, and municipal funds.'],
            ['code' => 'ASSESSOR', 'name' => "Assessor's Office", 'head' => 'n/a', 'status' => 'Active', 'description' => 'Determines property values for taxation purposes.'],
            ['code' => 'MHO', 'name' => 'Municipal Health Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Provides public health services and implements health programs.'],
            ['code' => 'LYSDO', 'name' => 'Local Youth and Sports Development Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Promotes youth development and sports activities.'],
            ['code' => 'GSO-SL', 'name' => 'GSO Streetlighting', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Handles installation and maintenance of streetlights.'],
            ['code' => 'MSWD', 'name' => 'Municipal Social Welfare and Development Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Provides social services and welfare programs.'],
            ['code' => 'AGRI', 'name' => 'Agriculture Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Supports farmers and agricultural development programs.'],
            ['code' => 'MENRO', 'name' => 'Municipal Environment and Natural Resources Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Manages environmental protection and natural resources.'],
            ['code' => 'ENG', 'name' => 'Engineering Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Handles infrastructure projects and public works.'],
            ['code' => 'TOURISM', 'name' => 'Tourism Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Promotes tourism and manages tourist-related programs.'],
            ['code' => 'MARKET-OM', 'name' => 'Market Office (OM)', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Manages public markets and vendor operations.'],
            ['code' => 'CEM-OM', 'name' => 'Cemetery Office (OM)', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Oversees cemetery operations and maintenance.'],
            ['code' => 'MDRRM', 'name' => 'Municipal Disaster Risk Reduction and Management Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Handles disaster preparedness, response, and mitigation.'],
            ['code' => 'VMO', 'name' => "Vice Mayor's Office", 'head' => 'n/a', 'status' => 'Active', 'description' => 'Supports legislative functions and assists the Vice Mayor.'],
            ['code' => 'SBO', 'name' => 'Sangguniang Bayan Office', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Legislative body responsible for local laws and ordinances.'],
            ['code' => 'SB-SEC', 'name' => 'SB Secretariat', 'head' => 'n/a', 'status' => 'Active', 'description' => 'Provides administrative support to the legislative council.'],
        ];

        DB::table('departments')->insert(
            collect($departments)->map(function ($item) use ($now) {
                return array_merge($item, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            })->toArray()
        );
    }
}