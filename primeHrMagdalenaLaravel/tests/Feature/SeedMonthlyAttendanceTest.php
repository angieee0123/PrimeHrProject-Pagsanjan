<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsAttendanceSchema;
use Tests\TestCase;

/**
 * The September seed command.
 *
 * Three properties have to hold, and these cover all three:
 *
 *  1. gap-filling only — a touched row or a day owned by another workflow
 *     (approved leave, travel, holiday) is never overwritten;
 *  2. accreditation matches the app's own math — punches go through
 *     AttendanceComputationService, so seeded days agree with the DTR;
 *  3. determinism — the same employee/day always paints the same day, so a
 *     rerun changes nothing and the repair pass converges.
 */
class SeedMonthlyAttendanceTest extends TestCase
{
    use BuildsAttendanceSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttendanceSchema();

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('leave_code', 10)->nullable();
            $table->string('application_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('leave_types_config', function (Blueprint $table) {
            $table->id();
            $table->string('leave_code');
            $table->string('leave_name');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('travel_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('status')->default('pending');
        });

        DB::table('employees')->insert([
            ['id' => 1, 'employee_id' => 'EMP-001', 'first_name' => 'Juan', 'last_name' => 'Santos'],
            ['id' => 2, 'employee_id' => 'EMP-002', 'first_name' => 'Ana', 'last_name' => 'Reyes'],
        ]);

        DB::table('leave_types_config')->insert([
            'id' => 1, 'leave_code' => 'VL', 'leave_name' => 'Vacation Leave', 'is_active' => true,
        ]);

        // Employee 1's approved leave straddles a corrupt pre-existing row
        // (REGULAR, no punches — the pre-fix shape) and a day with no row.
        DB::table('attendance')->insert([
            'employee_id' => 1, 'date' => '2026-09-02', 'attendance_type' => 'REGULAR',
        ]);

        DB::table('leave_applications')->insert([
            'employee_id' => 1, 'leave_code' => 'VL', 'application_number' => 'LA-2026-0006',
            'start_date' => '2026-09-02', 'end_date' => '2026-09-03', 'status' => 'approved',
        ]);
    }

    private function runSeed(): void
    {
        $this->artisan('attendance:seed-month', [
            'month' => '2026-09', '--to' => '2026-09-04', '--repair-mislabeled' => true,
        ])->assertSuccessful();
    }

    #[Test]
    public function weekdays_fill_with_punches_while_leave_days_become_leave(): void
    {
        $this->runSeed();

        // Employee 2: four clean weekdays, all punched.
        $this->assertSame(4, Attendance::where('employee_id', 2)->count());
        $this->assertSame(0, Attendance::where('employee_id', 2)->whereNull('am_in')->count());

        // Employee 1: Sep 2 repaired from REGULAR to LEAVE, Sep 3 backfilled
        // as LEAVE; Sep 1 and 4 punched regular days.
        $sep2 = Attendance::where('employee_id', 1)->whereDate('date', '2026-09-02')->first();
        $sep3 = Attendance::where('employee_id', 1)->whereDate('date', '2026-09-03')->first();

        $this->assertSame('LEAVE', $sep2->attendance_type);
        $this->assertSame('LEAVE', $sep3->attendance_type);
        $this->assertStringContainsString('LA-2026-0006', (string) $sep2->remarks);

        foreach (['2026-09-01', '2026-09-04'] as $date) {
            $row = Attendance::where('employee_id', 1)->whereDate('date', $date)->first();
            $this->assertNotNull($row);
            $this->assertSame('REGULAR', $row->attendance_type);
            $this->assertNotNull($row->am_in);
        }
    }

    #[Test]
    public function a_rerun_changes_nothing(): void
    {
        $this->runSeed();
        $count = Attendance::count();

        $this->runSeed();

        $this->assertSame($count, Attendance::count());
    }
}

