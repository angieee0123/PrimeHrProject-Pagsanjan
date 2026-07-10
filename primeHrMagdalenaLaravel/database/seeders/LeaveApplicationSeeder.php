<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Employees with available credits (latest year per leave_code)
        $balances = DB::table('leave_balances')
            ->where('available_credits', '>', 0)
            ->whereIn('id', function ($q) {
                $q->select(DB::raw('MAX(id)'))
                    ->from('leave_balances')
                    ->groupBy('employee_id', 'leave_code');
            })
            ->get();

        if ($balances->isEmpty()) {
            $this->command->warn('No employees with available leave credits found.');
            return;
        }

        $adminUserId = DB::table('users')->whereJsonContains('roles', 'admin')->value('id') ?? 1;
        $inserted    = 0;
        $skipped     = 0;

        foreach ($balances as $balance) {
            $leaveType = DB::table('leave_types_config')
                ->where('leave_code', $balance->leave_code)
                ->first();

            if (!$leaveType || !$leaveType->is_active) {
                $skipped++;
                continue;
            }

            $user = DB::table('users')->where('employee_id', $balance->employee_id)->first();
            if (!$user) {
                $skipped++;
                continue;
            }

            // Assign sensible leave days (1 day for most, capped at available)
            $daysToFile = min(1.0, (float) $balance->available_credits);

            // Pick a future weekday start date (next Monday from today)
            $startDate = Carbon::today()->next(Carbon::MONDAY);

            // Skip if leave type requires 6 months service and employee hasn't served 6 months
            if ($leaveType->requires_6_months) {
                $empDetail = DB::table('employment_details')
                    ->where('employee_id', $balance->employee_id)
                    ->first();
                $appointmentDate = $empDetail?->appointment_date
                    ? Carbon::parse($empDetail->appointment_date)
                    : null;

                if (!$appointmentDate || $appointmentDate->diffInMonths(Carbon::today()) < 6) {
                    $this->command->line("  Skipping emp {$balance->employee_id} ({$balance->leave_code}): does not meet 6-month service requirement.");
                    $skipped++;
                    continue;
                }
            }

            // Check for overlapping pending/approved leave on the same date
            $overlap = DB::table('leave_applications')
                ->where('employee_id', $balance->employee_id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_date', '<=', $startDate->toDateString())
                ->where('end_date', '>=', $startDate->toDateString())
                ->exists();

            if ($overlap) {
                $this->command->line("  Skipping emp {$balance->employee_id} ({$balance->leave_code}): overlapping leave exists.");
                $skipped++;
                continue;
            }

            $endDate = $startDate->copy();
            $year    = $startDate->year;

            // Generate application number
            $appNumber = $this->generateApplicationNumber();

            // Reason per leave type
            $reasons = [
                'VL'   => 'Personal vacation and rest.',
                'SL'   => 'Medical consultation and recovery.',
                'SLBW' => 'Special leave benefit for women as per RA 9710.',
                'PL'   => 'Paternity leave for newborn care.',
            ];
            $reason = $reasons[$balance->leave_code] ?? 'Filing leave using available credits.';

            // Extra fields per leave type
            $sickLeaveType = $balance->leave_code === 'SL' ? 'out_patient' : null;
            $leaveLocation = in_array($balance->leave_code, ['VL']) ? 'ph' : null;

            DB::beginTransaction();
            try {
                $appId = DB::table('leave_applications')->insertGetId([
                    'application_number'       => $appNumber,
                    'employee_id'              => $balance->employee_id,
                    'leave_code'               => $balance->leave_code,
                    'start_date'               => $startDate->toDateString(),
                    'end_date'                 => $endDate->toDateString(),
                    'number_of_days'           => $daysToFile,
                    'reason'                   => $reason,
                    'commutation_requested'    => 0,
                    'leave_location'           => $leaveLocation,
                    'leave_location_specify'   => null,
                    'sick_leave_type'          => $sickLeaveType,
                    'illness_specify'          => null,
                    'study_leave_purpose'      => null,
                    'status'                   => 'pending',
                    'attachment_path'          => null,
                    'filed_by'                 => $user->id,
                    'approved_by'              => null,
                    'approved_at'              => null,
                    'approver_remarks'         => null,
                    'approved_days_with_pay'   => null,
                    'approved_days_without_pay'=> null,
                    'approved_other_specify'   => null,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);

                // Update leave_balances: pending_credits +, available_credits -
                $balanceBefore = (float) $balance->available_credits;
                DB::table('leave_balances')
                    ->where('id', $balance->id)
                    ->update([
                        'pending_credits'   => DB::raw("pending_credits + {$daysToFile}"),
                        'available_credits' => DB::raw("available_credits - {$daysToFile}"),
                        'updated_at'        => now(),
                    ]);

                // Insert leave_transaction
                DB::table('leave_transactions')->insert([
                    'employee_id'      => $balance->employee_id,
                    'leave_code'       => $balance->leave_code,
                    'year'             => $balance->year,
                    'transaction_type' => 'pending',
                    'amount'           => -$daysToFile,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $balanceBefore - $daysToFile,
                    'reference_type'   => 'leave_application',
                    'reference_id'     => $appId,
                    'transaction_date' => now(),
                    'processed_by'     => $user->id,
                    'remarks'          => "Pending leave application {$appNumber}",
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                DB::commit();

                $emp = DB::table('employees')->find($balance->employee_id);
                $this->command->info("  Filed: {$appNumber} | {$emp->first_name} {$emp->last_name} | {$balance->leave_code} | {$daysToFile}d | {$startDate->toDateString()}");
                $inserted++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("  Failed emp {$balance->employee_id} ({$balance->leave_code}): " . $e->getMessage());
                $skipped++;
            }
        }

        $this->command->info("Done. Filed: {$inserted} | Skipped: {$skipped}");
    }

    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $last = DB::table('leave_applications')
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->value('application_number');

        $seq = $last ? intval(substr($last, -4)) + 1 : 1;
        return 'LA-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
