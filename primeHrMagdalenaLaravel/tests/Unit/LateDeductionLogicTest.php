<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CscTimeConversionService as CSC;

/**
 * Tests the late-to-leave-deduction logic in LateDeductionService
 * without hitting the database. Mirrors the exact math used in
 * processLateDeduction() and processUndertimeDeduction().
 */
class LateDeductionLogicTest extends TestCase
{
    // ─── helpers that mirror the service logic ───────────────────────────────

    private function runDeduction(int $lateMinutes, float $vlCredits, float $slCredits): array
    {
        $lateDays          = CSC::convertMinutesToDays($lateMinutes);
        $remainingLateDays = $lateDays;
        $totalCoveredMinutes = 0;
        $leaveTypes        = [];

        if ($vlCredits > 0) {
            $deduct = min($vlCredits, $remainingLateDays);
            $remainingLateDays   -= $deduct;
            $totalCoveredMinutes += (int)($deduct * 480);
            $leaveTypes[]         = 'VL';
            $vlCredits           -= $deduct;
        }

        if ($remainingLateDays > 0 && $slCredits > 0) {
            $deduct = min($slCredits, $remainingLateDays);
            $remainingLateDays   -= $deduct;
            $totalCoveredMinutes += (int)($deduct * 480);
            $leaveTypes[]         = 'SL';
            $slCredits           -= $deduct;
        }

        $lwopMinutes        = $lateMinutes - $totalCoveredMinutes;
        $initialAccredited  = 480 - $lateMinutes;
        $fullyCovered       = $lwopMinutes <= 0;

        $finalAccredited = $fullyCovered
            ? 480
            : min(480, $initialAccredited + $totalCoveredMinutes);

        return [
            'late_minutes'         => $lateMinutes,
            'late_days'            => $lateDays,
            'covered_minutes'      => $totalCoveredMinutes,
            'lwop_minutes'         => max(0, $lwopMinutes),
            'fully_covered'        => $fullyCovered,
            'final_accredited_min' => $finalAccredited,
            'final_accredited_hrs' => $finalAccredited / 60,
            'leave_types'          => $leaveTypes,
            'vl_remaining'         => $vlCredits,
            'sl_remaining'         => $slCredits,
        ];
    }

    // ─── tests ───────────────────────────────────────────────────────────────

    /** Employee is 30 min late, has 1 day VL → fully covered, no LWOP */
    public function test_30min_late_fully_covered_by_vl()
    {
        $r = $this->runDeduction(30, 1.0, 0.0);

        $this->assertEquals(0,   $r['lwop_minutes'],         'No LWOP expected');
        $this->assertTrue($r['fully_covered'],               'Should be fully covered');
        $this->assertEquals(480, $r['final_accredited_min'], 'Full 8 hrs credited');
        $this->assertEquals(8.0, $r['final_accredited_hrs'], '8.0 hours');
        $this->assertContains('VL', $r['leave_types']);
    }

    /** Employee is 60 min (1 hr) late, has 0.125 VL (exactly 1 hr) → fully covered */
    public function test_60min_late_exactly_covered_by_vl()
    {
        $r = $this->runDeduction(60, 0.125, 0.0);

        $this->assertEquals(0,   $r['lwop_minutes'],         'No LWOP');
        $this->assertTrue($r['fully_covered']);
        $this->assertEquals(480, $r['final_accredited_min']);
        $this->assertEquals(8.0, $r['final_accredited_hrs']);
    }

    /** Employee is 180 min (3 hrs) late, VL=0.125 (1 hr), SL=0.125 (1 hr) → 60 min LWOP */
    public function test_180min_late_partial_coverage_vl_and_sl()
    {
        $r = $this->runDeduction(180, 0.125, 0.125);

        $this->assertEquals(120, $r['covered_minutes'],      '120 min covered (60 VL + 60 SL)');
        $this->assertEquals(60,  $r['lwop_minutes'],         '60 min LWOP remains');
        $this->assertFalse($r['fully_covered']);
        $this->assertEquals(420, $r['final_accredited_min'], '7 hrs accredited');
        $this->assertEquals(7.0, $r['final_accredited_hrs']);
        $this->assertContains('VL', $r['leave_types']);
        $this->assertContains('SL', $r['leave_types']);
    }

    /** Employee is 480 min (full day) late, no leave credits → full LWOP */
    public function test_480min_late_no_leave_full_lwop()
    {
        $r = $this->runDeduction(480, 0.0, 0.0);

        $this->assertEquals(480, $r['lwop_minutes'],         'Full day LWOP');
        $this->assertFalse($r['fully_covered']);
        $this->assertEquals(0,   $r['final_accredited_min'], '0 min accredited');
        $this->assertEmpty($r['leave_types']);
    }

    /** Employee is 480 min late, has 1 full day VL → fully covered, 0 LWOP */
    public function test_480min_late_fully_covered_by_1day_vl()
    {
        $r = $this->runDeduction(480, 1.0, 0.0);

        $this->assertEquals(0,   $r['lwop_minutes']);
        $this->assertTrue($r['fully_covered']);
        $this->assertEquals(480, $r['final_accredited_min']);
        $this->assertEquals(0.0, $r['vl_remaining'],         'VL fully consumed');
    }

    /** VL is exhausted first, then SL covers the rest */
    public function test_vl_exhausted_sl_covers_remainder()
    {
        // 240 min late (0.5 day), VL=0.25 (2 hrs), SL=0.5 (4 hrs)
        $r = $this->runDeduction(240, 0.25, 0.5);

        $this->assertEquals(0,   $r['lwop_minutes'],         'Fully covered by VL+SL');
        $this->assertTrue($r['fully_covered']);
        $this->assertEquals(480, $r['final_accredited_min']);
        $this->assertEquals(0.0, $r['vl_remaining'],         'VL fully used');
        $this->assertContains('VL', $r['leave_types']);
        $this->assertContains('SL', $r['leave_types']);
    }

    /** No leave credits at all → LWOP equals late minutes */
    public function test_no_leave_credits_all_lwop()
    {
        $r = $this->runDeduction(120, 0.0, 0.0);

        $this->assertEquals(120, $r['lwop_minutes']);
        $this->assertFalse($r['fully_covered']);
        $this->assertEmpty($r['leave_types']);
    }

    /** CSC conversion: 1 minute = 1/480 day, covered minutes must be integer */
    public function test_covered_minutes_is_always_integer()
    {
        foreach ([15, 30, 45, 60, 90, 120, 180, 240, 300, 360, 420, 480] as $late) {
            $r = $this->runDeduction($late, 1.0, 1.0);
            $this->assertIsInt($r['covered_minutes'], "covered_minutes must be int for {$late} min late");
            $this->assertIsInt($r['lwop_minutes'],    "lwop_minutes must be int for {$late} min late");
        }
    }

    /** Late minutes that don't divide evenly into 480 still produce correct LWOP */
    public function test_odd_late_minutes_lwop_accuracy()
    {
        // 100 min late, VL=0.125 (60 min) → covered=60, lwop=40
        $r = $this->runDeduction(100, 0.125, 0.0);

        $this->assertEquals(60, $r['covered_minutes']);
        $this->assertEquals(40, $r['lwop_minutes']);
        $this->assertEquals(440, $r['final_accredited_min']); // 480-100+60
    }

    /** Existing test: the 1-minute precision issue in convertMinutesToDays */
    public function test_1_minute_precision_known_issue()
    {
        // 1/480 = 0.002083333... — PHP float, delta comparison needed
        $days = CSC::convertMinutesToDays(1);
        $this->assertEqualsWithDelta(1 / 480, $days, 0.000001);
    }
}
