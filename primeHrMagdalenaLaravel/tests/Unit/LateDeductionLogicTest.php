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

    /**
     * Mirrors LateDeductionService::processLateDeduction()
     * Returns updated log state + remaining leave credits.
     */
    private function runLateDeduction(int $lateMinutes, float $vlCredits, float $slCredits): array
    {
        $lateDays            = CSC::convertMinutesToDays($lateMinutes);
        $remainingLateDays   = $lateDays;
        $totalCoveredMinutes = 0;
        $leaveTypes          = [];

        if ($remainingLateDays > 0 && $vlCredits > 0) {
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

        $lateLwop           = max(0, $lateMinutes - $totalCoveredMinutes);
        $initialAccredited  = 480 - $lateMinutes;   // what computeAccreditedHours produced
        $logAccredited      = $lateLwop <= 0 ? 480 : min(480, $initialAccredited + $totalCoveredMinutes);

        return [
            'log_accredited'  => $logAccredited,
            'log_lwop'        => $lateLwop,
            'covered_minutes' => $totalCoveredMinutes,
            'lwop_minutes'    => $lateLwop,
            'fully_covered'   => $lateLwop <= 0,
            'leave_types'     => $leaveTypes,
            'vl_remaining'    => $vlCredits,
            'sl_remaining'    => $slCredits,
        ];
    }

    /**
     * Mirrors UndertimeDeductionService::processUndertimeDeduction()
     * Takes the log state left by runLateDeduction() as input.
     */
    private function runUndertimeDeduction(
        int   $undertimeMinutes,
        float $vlCredits,
        float $slCredits,
        int   $logAccredited,   // total_accredited_minutes after late service
        int   $logLwop          // lwop_minutes after late service
    ): array {
        $undertimeDays          = CSC::convertMinutesToDays($undertimeMinutes);
        $remainingUndertimeDays = $undertimeDays;
        $totalCoveredMinutes    = 0;
        $leaveTypes             = [];

        if ($remainingUndertimeDays > 0 && $vlCredits > 0) {
            $deduct = min($vlCredits, $remainingUndertimeDays);
            $remainingUndertimeDays -= $deduct;
            $totalCoveredMinutes    += (int)($deduct * 480);
            $leaveTypes[]            = 'VL';
            $vlCredits              -= $deduct;
        }
        if ($remainingUndertimeDays > 0 && $slCredits > 0) {
            $deduct = min($slCredits, $remainingUndertimeDays);
            $remainingUndertimeDays -= $deduct;
            $totalCoveredMinutes    += (int)($deduct * 480);
            $leaveTypes[]            = 'SL';
            $slCredits              -= $deduct;
        }

        $undertimeLwop = max(0, $undertimeMinutes - $totalCoveredMinutes);
        // Preserve late LWOP: only reduce it by what undertime leave actually covered
        $lateLwop      = max(0, $logLwop - $totalCoveredMinutes);
        $newLwop       = $lateLwop + $undertimeLwop;

        // Subtract uncovered undertime from current accredited (key fix)
        $newAccredited = max(0, $logAccredited - $undertimeLwop);

        return [
            'final_accredited_min' => $newAccredited,
            'final_accredited_hrs' => $newAccredited / 60,
            'final_lwop'           => $newLwop,
            'covered_minutes'      => $totalCoveredMinutes,
            'leave_types'          => $leaveTypes,
            'vl_remaining'         => $vlCredits,
            'sl_remaining'         => $slCredits,
        ];
    }

    /** Convenience: run both services in sequence (late first, then undertime) */
    private function runDeduction(int $lateMinutes, float $vlCredits, float $slCredits): array
    {
        $late = $this->runLateDeduction($lateMinutes, $vlCredits, $slCredits);
        return array_merge($late, [
            'final_accredited_min' => $late['log_accredited'],
            'final_accredited_hrs' => $late['log_accredited'] / 60,
            'final_lwop'           => $late['log_lwop'],
        ]);
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

    // ─── COMBINED late + undertime on the same day ──────────────────────────

    /**
     * 30 min late + 30 min undertime, VL=0.0625 (30 min only)
     * Late fully covered by VL, undertime has no leave left → 30 min LWOP, 7.5 hrs accredited
     */
    public function test_late_covered_undertime_lwop()
    {
        $late = $this->runLateDeduction(30, 0.0625, 0.0);
        $this->assertEquals(480, $late['log_accredited'], 'Late fully covered → 480 accredited');
        $this->assertEquals(0,   $late['log_lwop'],       'No late LWOP');
        $this->assertEquals(0.0, $late['vl_remaining'],   'VL exhausted');

        $ut = $this->runUndertimeDeduction(30, $late['vl_remaining'], $late['sl_remaining'], $late['log_accredited'], $late['log_lwop']);
        $this->assertEquals(450, $ut['final_accredited_min'], '7.5 hrs: 480 - 30 uncovered undertime');
        $this->assertEquals(7.5, $ut['final_accredited_hrs']);
        $this->assertEquals(30,  $ut['final_lwop'],           '30 min LWOP from undertime');
    }

    /**
     * 30 min late + 30 min undertime, VL=1.0 (plenty)
     * Both fully covered → 480 accredited, 0 LWOP
     */
    public function test_late_and_undertime_both_fully_covered()
    {
        $late = $this->runLateDeduction(30, 1.0, 0.0);
        $this->assertEquals(480, $late['log_accredited']);
        $this->assertEquals(0,   $late['log_lwop']);

        $ut = $this->runUndertimeDeduction(30, $late['vl_remaining'], $late['sl_remaining'], $late['log_accredited'], $late['log_lwop']);
        $this->assertEquals(480, $ut['final_accredited_min'], 'Full 8 hrs');
        $this->assertEquals(8.0, $ut['final_accredited_hrs']);
        $this->assertEquals(0,   $ut['final_lwop'],           'No LWOP');
    }

    /**
     * 60 min late + 60 min undertime, no leave at all
     * Both become LWOP → 360 accredited (6 hrs), 120 min LWOP
     */
    public function test_late_and_undertime_no_leave_all_lwop()
    {
        $late = $this->runLateDeduction(60, 0.0, 0.0);
        $this->assertEquals(420, $late['log_accredited'], '480-60=420 accredited after late');
        $this->assertEquals(60,  $late['log_lwop']);

        $ut = $this->runUndertimeDeduction(60, 0.0, 0.0, $late['log_accredited'], $late['log_lwop']);
        $this->assertEquals(360, $ut['final_accredited_min'], '6 hrs: 420 - 60 undertime LWOP');
        $this->assertEquals(6.0, $ut['final_accredited_hrs']);
        $this->assertEquals(120, $ut['final_lwop'],           '60 late LWOP + 60 undertime LWOP');
    }

    /**
     * 60 min late + 60 min undertime, VL=0.125 (covers late only), SL=0.125 (covers undertime)
     * Both fully covered via different leave types → 480 accredited, 0 LWOP
     */
    public function test_late_covered_by_vl_undertime_covered_by_sl()
    {
        $late = $this->runLateDeduction(60, 0.125, 0.125);
        $this->assertEquals(480,   $late['log_accredited']);
        $this->assertEquals(0,     $late['log_lwop']);
        $this->assertEquals(0.0,   $late['vl_remaining'], 'VL used for late');
        $this->assertEquals(0.125, $late['sl_remaining'], 'SL untouched');

        $ut = $this->runUndertimeDeduction(60, $late['vl_remaining'], $late['sl_remaining'], $late['log_accredited'], $late['log_lwop']);
        $this->assertEquals(480, $ut['final_accredited_min'], 'Full 8 hrs');
        $this->assertEquals(0,   $ut['final_lwop'],           'No LWOP');
        $this->assertContains('SL', $ut['leave_types'],       'SL used for undertime');
    }

    /**
     * 120 min late + 60 min undertime, VL=0.125 (60 min), SL=0.125 (60 min)
     * Late: VL covers 60, SL covers 60 → late fully covered
     * Undertime: no leave left → 60 min LWOP, 420 accredited (7 hrs)
     */
    public function test_late_fully_covered_undertime_all_lwop()
    {
        $late = $this->runLateDeduction(120, 0.125, 0.125);
        $this->assertEquals(480, $late['log_accredited'], 'Late fully covered');
        $this->assertEquals(0,   $late['log_lwop']);
        $this->assertEquals(0.0, $late['vl_remaining']);
        $this->assertEquals(0.0, $late['sl_remaining']);

        $ut = $this->runUndertimeDeduction(60, $late['vl_remaining'], $late['sl_remaining'], $late['log_accredited'], $late['log_lwop']);
        $this->assertEquals(420, $ut['final_accredited_min'], '7 hrs: 480 - 60 undertime LWOP');
        $this->assertEquals(7.0, $ut['final_accredited_hrs']);
        $this->assertEquals(60,  $ut['final_lwop'],           '60 min undertime LWOP');
    }
}
