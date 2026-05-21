<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CscTimeConversionService as CSC;

class ExactConversionTest extends TestCase
{
    /**
     * Test exact days to minutes conversion
     * NO ROUNDING - Must be exact
     */
    public function test_exact_days_to_minutes_conversion()
    {
        // Test cases: [days, expected_minutes]
        $testCases = [
            // Standard conversions
            [1.0, 480],      // 1 day = 8 hours = 480 minutes
            [0.5, 240],      // Half day = 4 hours = 240 minutes
            [0.25, 120],     // Quarter day = 2 hours = 120 minutes
            [0.125, 60],     // 1 hour = 60 minutes ✅ YOUR CASE
            [0.0625, 30],    // 30 minutes
            
            // Edge cases
            [0.020833, 9],   // 10 minutes (0.020833 * 480 = 9.99984 → 9)
            [0.002083, 0],   // 1 minute (0.002083 * 480 = 0.99984 → 0)
            [2.0, 960],      // 2 days = 16 hours = 960 minutes
            [0.375, 180],    // 3 hours = 180 minutes ✅ YOUR CASE
            
            // Precise CSC values
            [0.125000, 60],  // Exactly 1 hour
            [0.250000, 120], // Exactly 2 hours
            [0.375000, 180], // Exactly 3 hours
        ];

        foreach ($testCases as [$days, $expectedMinutes]) {
            $actualMinutes = CSC::convertDaysToMinutes($days);
            
            $this->assertEquals(
                $expectedMinutes,
                $actualMinutes,
                "Failed: {$days} days should be exactly {$expectedMinutes} minutes, got {$actualMinutes}"
            );
        }
    }

    /**
     * Test exact minutes to days conversion
     * NO ROUNDING - Must be exact division
     */
    public function test_exact_minutes_to_days_conversion()
    {
        // Test cases: [minutes, expected_days]
        $testCases = [
            [480, 1.0],           // 480 minutes = 1 day
            [240, 0.5],           // 240 minutes = 0.5 day
            [120, 0.25],          // 120 minutes = 0.25 day
            [60, 0.125],          // 60 minutes = 0.125 day ✅ YOUR CASE
            [30, 0.0625],         // 30 minutes = 0.0625 day
            [180, 0.375],         // 180 minutes = 0.375 day ✅ YOUR CASE
            [1, 0.002083333333],  // 1 minute = 0.002083... day
        ];

        foreach ($testCases as [$minutes, $expectedDays]) {
            $actualDays = CSC::convertMinutesToDays($minutes);
            
            $this->assertEquals(
                $expectedDays,
                $actualDays,
                "Failed: {$minutes} minutes should be exactly {$expectedDays} days, got {$actualDays}",
                0.000001  // Allow tiny floating point difference
            );
        }
    }

    /**
     * Test round-trip conversion (days → minutes → days)
     * Must return to original value
     */
    public function test_round_trip_days_minutes_days()
    {
        $testDays = [
            1.0,
            0.5,
            0.25,
            0.125,   // ✅ YOUR CASE
            0.0625,
            0.375,   // ✅ YOUR CASE
        ];

        foreach ($testDays as $originalDays) {
            $minutes = CSC::convertDaysToMinutes($originalDays);
            $backToDays = CSC::convertMinutesToDays($minutes);
            
            $this->assertEquals(
                $originalDays,
                $backToDays,
                "Round trip failed: {$originalDays} days → {$minutes} min → {$backToDays} days",
                0.000001
            );
        }
    }

    /**
     * Test your specific scenario: 3 hours late, VL=0.125, SL=0.125
     */
    public function test_your_specific_scenario()
    {
        // Setup
        $lateMinutes = 180;  // 3 hours late
        $vlDays = 0.125;     // 1 hour VL
        $slDays = 0.125;     // 1 hour SL
        
        // Convert VL and SL to minutes
        $vlMinutes = CSC::convertDaysToMinutes($vlDays);
        $slMinutes = CSC::convertDaysToMinutes($slDays);
        
        // Verify exact conversions
        $this->assertEquals(60, $vlMinutes, "VL: 0.125 days must be exactly 60 minutes");
        $this->assertEquals(60, $slMinutes, "SL: 0.125 days must be exactly 60 minutes");
        
        // Calculate LWOP
        $totalCoveredMinutes = $vlMinutes + $slMinutes;
        $lwopMinutes = $lateMinutes - $totalCoveredMinutes;
        
        // Verify exact calculations
        $this->assertEquals(120, $totalCoveredMinutes, "Total covered must be exactly 120 minutes");
        $this->assertEquals(60, $lwopMinutes, "LWOP must be exactly 60 minutes");
        
        // Verify accredited hours
        $initialAccredited = 480 - $lateMinutes;  // 300 minutes (5 hours)
        $finalAccredited = $initialAccredited + $totalCoveredMinutes;  // 420 minutes (7 hours)
        
        $this->assertEquals(300, $initialAccredited, "Initial accredited must be 300 minutes");
        $this->assertEquals(420, $finalAccredited, "Final accredited must be exactly 420 minutes (7 hours)");
        
        // Convert to hours for display
        $accreditedHours = $finalAccredited / 60;
        $this->assertEquals(7.0, $accreditedHours, "Must show exactly 7 hours");
    }

    /**
     * Test that 0.125 never rounds up or down
     */
    public function test_0_125_never_rounds()
    {
        $days = 0.125;
        $minutes = CSC::convertDaysToMinutes($days);
        
        // Must be EXACTLY 60, not 59, not 61
        $this->assertSame(60, $minutes, "0.125 days must be EXACTLY 60 minutes (no rounding)");
        
        // Verify it's not 59 (rounded down)
        $this->assertNotEquals(59, $minutes, "Must not round down to 59");
        
        // Verify it's not 61 (rounded up)
        $this->assertNotEquals(61, $minutes, "Must not round up to 61");
    }

    /**
     * Test multiple 0.125 additions (like VL + SL)
     */
    public function test_multiple_0_125_additions()
    {
        $vlDays = 0.125;
        $slDays = 0.125;
        
        $vlMinutes = CSC::convertDaysToMinutes($vlDays);
        $slMinutes = CSC::convertDaysToMinutes($slDays);
        
        $totalMinutes = $vlMinutes + $slMinutes;
        
        // Must be EXACTLY 120 minutes (60 + 60)
        $this->assertEquals(120, $totalMinutes, "0.125 + 0.125 must be exactly 120 minutes");
        
        // Convert back to days
        $totalDays = CSC::convertMinutesToDays($totalMinutes);
        $this->assertEquals(0.25, $totalDays, "120 minutes must be exactly 0.25 days");
    }

    /**
     * Test that integer casting doesn't introduce errors
     */
    public function test_integer_casting_precision()
    {
        // These should all be exact (no decimal remainder)
        $exactCases = [
            [0.125, 60],   // 0.125 * 480 = 60.0 (exact)
            [0.25, 120],   // 0.25 * 480 = 120.0 (exact)
            [0.5, 240],    // 0.5 * 480 = 240.0 (exact)
            [0.375, 180],  // 0.375 * 480 = 180.0 (exact)
        ];

        foreach ($exactCases as [$days, $expectedMinutes]) {
            $calculation = $days * 480;
            $intCast = (int)$calculation;
            
            // Verify no decimal remainder
            $this->assertEquals(
                $calculation,
                (float)$intCast,
                "Calculation {$days} * 480 = {$calculation} should have no decimal remainder"
            );
            
            // Verify matches expected
            $this->assertEquals(
                $expectedMinutes,
                $intCast,
                "{$days} days should convert to exactly {$expectedMinutes} minutes"
            );
        }
    }

    /**
     * Test edge case: Very small values
     */
    public function test_very_small_values()
    {
        // 1 minute in days
        $oneMinuteDays = 1 / 480.0;  // 0.00208333...
        
        $minutes = CSC::convertDaysToMinutes($oneMinuteDays);
        
        // Should be 0 or 1, but consistent
        $this->assertIsInt($minutes, "Result must be integer");
        $this->assertGreaterThanOrEqual(0, $minutes, "Must not be negative");
    }

    /**
     * Test that no rounding functions are used
     */
    public function test_no_rounding_in_conversion()
    {
        // Test a value that would differ if rounded
        $days = 0.124999;  // Just under 0.125
        $minutes = CSC::convertDaysToMinutes($days);
        
        // With (int) cast: 0.124999 * 480 = 59.99952 → 59
        // With round(): 0.124999 * 480 = 59.99952 → 60
        // With floor(): 0.124999 * 480 = 59.99952 → 59
        // With ceil(): 0.124999 * 480 = 59.99952 → 60
        
        $expected = (int)(0.124999 * 480);  // Should be 59
        $this->assertEquals($expected, $minutes, "Must use direct int cast, not rounding");
    }

    /**
     * Test your complete cascade scenario
     */
    public function test_complete_cascade_scenario()
    {
        // Your scenario
        $lateMinutes = 180;      // 3 hours late
        $vlBalance = 0.125;      // 1 hour VL
        $slBalance = 0.125;      // 1 hour SL
        
        // Step 1: Convert late to days
        $lateDays = CSC::convertMinutesToDays($lateMinutes);
        $this->assertEquals(0.375, $lateDays, "180 minutes = 0.375 days");
        
        // Step 2: Deduct from VL
        $vlDeductDays = min($vlBalance, $lateDays);
        $this->assertEquals(0.125, $vlDeductDays, "VL deduct = 0.125 days");
        
        $vlDeductMinutes = CSC::convertDaysToMinutes($vlDeductDays);
        $this->assertEquals(60, $vlDeductMinutes, "VL deduct = 60 minutes");
        
        $remainingDays = $lateDays - $vlDeductDays;
        $this->assertEquals(0.25, $remainingDays, "Remaining = 0.25 days");
        
        // Step 3: Deduct from SL
        $slDeductDays = min($slBalance, $remainingDays);
        $this->assertEquals(0.125, $slDeductDays, "SL deduct = 0.125 days");
        
        $slDeductMinutes = CSC::convertDaysToMinutes($slDeductDays);
        $this->assertEquals(60, $slDeductMinutes, "SL deduct = 60 minutes");
        
        $remainingDays = $remainingDays - $slDeductDays;
        $this->assertEquals(0.125, $remainingDays, "Final remaining = 0.125 days");
        
        // Step 4: Calculate LWOP
        $totalCoveredMinutes = $vlDeductMinutes + $slDeductMinutes;
        $this->assertEquals(120, $totalCoveredMinutes, "Total covered = 120 minutes");
        
        $lwopMinutes = $lateMinutes - $totalCoveredMinutes;
        $this->assertEquals(60, $lwopMinutes, "LWOP = 60 minutes");
        
        // Step 5: Calculate final accredited hours
        $initialAccredited = 480 - $lateMinutes;
        $this->assertEquals(300, $initialAccredited, "Initial = 300 minutes");
        
        $finalAccredited = $initialAccredited + $totalCoveredMinutes;
        $this->assertEquals(420, $finalAccredited, "Final = 420 minutes (7 hours)");
        
        // Verify final hours
        $hours = $finalAccredited / 60;
        $this->assertEquals(7.0, $hours, "Must be exactly 7 hours");
    }
}
