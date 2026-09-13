<?php

namespace Database\Seeders;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\LeaveTypesConfigSeeder;
use Database\Seeders\DeductionTypesSeeder;
use Database\Seeders\LeaveAccrualRatesSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            DesignationSeeder::class,
            AdminUserSeeder::class,
            LeaveTypesConfigSeeder::class,
            DeductionTypesSeeder::class,
            LeaveAccrualRatesSeeder::class,
        ]);
    }
}
