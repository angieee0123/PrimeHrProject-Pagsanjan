<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypesConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            // Vacation Leave (VL)
            [
                'leave_code' => 'VL',
                'leave_name' => 'Vacation Leave',
                'is_accrued' => true,
                'annual_limit' => 15.00,
                'is_cumulative' => true,
                'requires_6_months' => true,
                'is_monetizable' => true,
                'requires_attachment' => false,
                'attachment_info' => 'Earned at 1.25 days per month. Can be monetized up to 10 days per year.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Sick Leave (SL)
            [
                'leave_code' => 'SL',
                'leave_name' => 'Sick Leave',
                'is_accrued' => true,
                'annual_limit' => 15.00,
                'is_cumulative' => true,
                'requires_6_months' => false,
                'is_monetizable' => true,
                'requires_attachment' => true,
                'attachment_info' => 'Medical certificate required if more than 2 consecutive days. Can be monetized upon retirement.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Maternity Leave (ML)
            [
                'leave_code' => 'ML',
                'leave_name' => 'Maternity Leave',
                'is_accrued' => false,
                'annual_limit' => 105.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'RA 11210 - 105 days paid leave with optional 30-day extension. Medical certificate, pregnancy test, and birth certificate required. 60 days for miscarriage.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Maternity Leave Extension (MLE)
            [
                'leave_code' => 'MLE',
                'leave_name' => 'Maternity Leave Extension',
                'is_accrued' => false,
                'annual_limit' => 30.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'Optional 30-day extension of maternity leave (RA 11210). Must be filed after initial 105 days.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Paternity Leave (PL)
            [
                'leave_code' => 'PL',
                'leave_name' => 'Paternity Leave',
                'is_accrued' => false,
                'annual_limit' => 7.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'Birth certificate or medical certificate of spouse required. Must be filed within 60 days from childbirth.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Special Privilege Leave (SPL)
            [
                'leave_code' => 'SPL',
                'leave_name' => 'Special Privilege Leave',
                'is_accrued' => false,
                'annual_limit' => 3.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => false,
                'attachment_info' => 'For all employees who have rendered at least one year of service. 3 days granted annually.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Solo Parent Leave (SOPL)
            [
                'leave_code' => 'SOPL',
                'leave_name' => 'Solo Parent Leave',
                'is_accrued' => false,
                'annual_limit' => 7.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'Valid Solo Parent ID from DSWD required (RA 8972). Granted annually.',
                'document_path' => null,
                'is_active' => true,
            ],
            // VAWC Leave
            [
                'leave_code' => 'VAWC',
                'leave_name' => 'VAWC Leave',
                'is_accrued' => false,
                'annual_limit' => 10.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'Violence Against Women and Children (RA 9262). Barangay certificate, police report, or protection order required.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Special Leave Benefits for Women (SLBW)
            [
                'leave_code' => 'SLBW',
                'leave_name' => 'Special Leave Benefits for Women',
                'is_accrued' => false,
                'annual_limit' => 60.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'For gynecological surgeries (RA 9710). Medical certificate and surgical documents required.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Study Leave (STL)
            [
                'leave_code' => 'STL',
                'leave_name' => 'Study Leave',
                'is_accrued' => false,
                'annual_limit' => 180.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'Maximum 180 days (6 months). Requires approval from head of agency. Certificate of enrollment and course outline required.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Rehabilitation Leave (RL)
            [
                'leave_code' => 'RL',
                'leave_name' => 'Rehabilitation Leave',
                'is_accrued' => false,
                'annual_limit' => 0.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'For employees recovering from illness/injury. Medical certificate from attending physician required.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Special Emergency Leave (SEL)
            [
                'leave_code' => 'SEL',
                'leave_name' => 'Special Emergency Leave',
                'is_accrued' => false,
                'annual_limit' => 0.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'For calamities, disasters, or emergency situations. Supporting documents required (e.g., barangay certificate).',
                'document_path' => null,
                'is_active' => true,
            ],
            // Adoption Leave (AL)
            [
                'leave_code' => 'AL',
                'leave_name' => 'Adoption Leave',
                'is_accrued' => false,
                'annual_limit' => 60.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'For employees who legally adopt a child below 7 years old. 60 days leave. Adoption decree or certificate required.',
                'document_path' => null,
                'is_active' => true,
            ],

            // Magna Carta Leave (MCL)
            [
                'leave_code' => 'MCL',
                'leave_name' => 'Magna Carta Leave for Women',
                'is_accrued' => false,
                'annual_limit' => 60.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'For female employees under RA 9710. 60 days (2 months) with full pay for gynecological surgeries. Medical certificate required.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Terminal Leave (TL)
            [
                'leave_code' => 'TL',
                'leave_name' => 'Terminal Leave',
                'is_accrued' => false,
                'annual_limit' => 0.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => true,
                'requires_attachment' => false,
                'attachment_info' => 'Monetization of accumulated leave credits upon retirement, resignation, or separation.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Monetization of Leave Credits (MLC)
            [
                'leave_code' => 'MLC',
                'leave_name' => 'Monetization of Leave Credits',
                'is_accrued' => false,
                'annual_limit' => 10.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => true,
                'requires_attachment' => false,
                'attachment_info' => 'Maximum 10 days of VL credits can be monetized annually.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Forced Leave (FL)
            [
                'leave_code' => 'FL',
                'leave_name' => 'Forced Leave',
                'is_accrued' => false,
                'annual_limit' => 5.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => false,
                'attachment_info' => 'Mandatory 5 consecutive days leave for officials/employees with sensitive positions.',
                'document_path' => null,
                'is_active' => true,
            ],

            // Bereavement Leave (BL)
            [
                'leave_code' => 'BL',
                'leave_name' => 'Bereavement Leave',
                'is_accrued' => false,
                'annual_limit' => 3.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => true,
                'attachment_info' => 'For death of immediate family member. Death certificate required.',
                'document_path' => null,
                'is_active' => true,
            ],
            // Wellness Leave (WL)
            [
                'leave_code' => 'WL',
                'leave_name' => 'Wellness Leave',
                'is_accrued' => false,
                'annual_limit' => 5.00,
                'is_cumulative' => false,
                'requires_6_months' => false,
                'is_monetizable' => false,
                'requires_attachment' => false,
                'attachment_info' => 'For health and wellness activities. May vary per agency policy.',
                'document_path' => null,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types_config')->insert(array_merge($leaveType, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
