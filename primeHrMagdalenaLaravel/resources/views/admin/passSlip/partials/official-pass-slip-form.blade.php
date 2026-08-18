{{-- The HRMO's printed sheet: PASS SLIP above, CERTIFICATE OF APPEARANCE below,
     on one sheet of Philippine long bond (8.5 x 13 in = 612 x 936 pt).

     THIS IS A TRACING, NOT A REDESIGN. Every coordinate, font, weight and size
     below was read out of the office's own PASS SLIP (NEW).pdf — the two form
     boxes are at 30,17 and 30,483.6 at 555 x 451.9 / 555 x 439.7; the labels are
     Times Bold 14, and the ruled lines are runs of Times underscores at 12 or
     14. Nothing here is a designer's choice, so do not "tidy" a number: change
     it only if the source document changes.

     The one deliberate departure: both titles are set in Berlin Sans FB Demi
     18pt bold and centred on the box. The source has PASS SLIP at 16pt, but
     the office specified 18 for both.

     Everything is absolutely positioned in points, which is what makes the
     printed sheet identical in the browser and in dompdf, and is why no amount
     of entered text can push the certificate onto a second page.

     Values come from PassSlipFormDataService and are sized to their rule by
     TimesText::fit(), so a long entry steps down a half point at a time
     instead of running past the end of the line. --}}
@php
  use App\Support\TimesText;

  /* A baseline in the source document -> the CSS `top` of a line-height:1 box.
     0.7925 em is where the baseline sits in such a box for Times; the two
     display faces carry their own ratio. Verified by re-reading the generated
     PDF's text positions back against the source. */
  $t  = fn(float $base, float $size, float $asc = 0.7925) => round($base - $asc * $size, 2);

  /* A run of underscores -> a rule. Times puts the underscore 0.109 em below
     the baseline and draws it 0.049 em thick. */
  $ry = fn(float $base, float $size) => round($base + 0.109 * $size, 2);
  $rw = fn(float $size) => round(0.049 * $size, 2);

  /* Berlin Sans FB Demi. Its own hhea metrics put the baseline at 0.8599 em in
     a line-height:1 box; the renderer sits 0.045 em above that, the same
     offset measured for Times above. Cross-checks against the value this was
     first calibrated to empirically. */
  $ASC_BERLIN = 0.8139;
  $ASC_CALIBRI = 0.75;

  /* --- values, fitted to the rule each one sits on --- */
  $vDate = $date ?? '';
  $vEtd  = $timeOut ?? '';
  $vEta  = $timeIn ?? '';
  $vDest = $destination ?? '';

  // The purpose spans the form's two ruled lines (450pt then 504pt of usable rule).
  $purposeWrapped = TimesText::wrap($purposeText ?? '', [450, 504], 12);

  // Certificate rules are all 322pt wide.
  $coaRows = [
    ['NAME',        $employeeNameNatural ?? '', 182.35],
    ['OFFICE',      $department ?? '',          206.35],
    ['POSITION',    $designation ?? '',         230.55],
    ['APPEARED AT', $destination ?? '',         254.75],
    ['DATE',        $date ?? '',                278.97],
    ['PURPOSE',     $purposeText ?? '',         302.98],
  ];
@endphp
<style>
  .ps-sheet {
    position: relative;
    width: 612pt;
    height: 936pt;
    margin: 0;
    padding: 0;
    background: #fff;
    color: #000;
    font-family: 'Times New Roman', Times, serif;
  }

  /* The two form boxes, at their page coordinates.
     The box carries no border of its own: a border would inset the origin its
     absolutely-positioned children measure from, throwing every coordinate
     below off by its width. The frame is drawn as a child instead, offset by
     half a stroke so the 1.5pt line straddles the source rectangle exactly. */
  .ps-box { position: absolute; }
  .ps-box-slip { left: 30pt; top: 17pt;    width: 555pt; height: 451.9pt; }
  .ps-box-coa  { left: 30pt; top: 483.6pt; width: 555pt; height: 439.7pt; }

  .ps-frame {
    left: -0.75pt; top: -0.75pt;
    width: 556.5pt;
    border: 1.5pt solid #000;
  }
  .ps-frame-slip { height: 453.4pt; }
  .ps-frame-coa  { height: 441.2pt; }

  /* Every positioned item is measured from its box's inner top-left. */
  .ps-box > * { position: absolute; margin: 0; padding: 0; }

  /* Text: line-height 1 so the baseline lands where `top` puts it. */
  .x  { line-height: 1; white-space: nowrap; }
  .xc { line-height: 1; white-space: nowrap; text-align: center; }

  .tb { font-weight: bold; }
  .tr { font-weight: normal; }

  /* The two display lines: Berlin Sans FB Demi, 18pt, bold. The family is
     present on the office's own workstations; public/fonts/BRLNSDB.TTF is
     picked up by the PDF when it has been placed there (see the wrapper
     views). Falls back to a bold sans rather than silently to Times. */
  .disp {
    font-family: 'Berlin Sans FB Demi', 'Berlin Sans FB', 'PassSlipDisplay',
                 Arial, Helvetica, sans-serif;
    font-weight: bold;
    font-size: 18pt;
  }

  /* Both titles are centred on the form box. Centring them by rule rather than
     by a measured left edge keeps them centred even where the display face
     falls back and the string measures a different width. */
  .title { left: 0; width: 555pt; text-align: center; }

  /* A ruled line. Height 0 with a bottom border puts the stroke exactly at
     `top`, which is where the underscore's bar sits. */
  .r { height: 0; border-bottom-style: solid; border-bottom-color: #000; }

  .ps-img { display: block; }

  /* ---- Drawn letterhead, used only when the artwork is missing ---- */
  .lh-drawn-table { width: 100%; height: 80.5pt; border-collapse: collapse; }
  .lh-drawn-table td { padding: 0; vertical-align: middle; }
  .lh-seal { width: 62pt; text-align: left; }
  .lh-emblems { width: 108pt; text-align: right; }
  .lh-text { text-align: center; }
  .lh-seal-img { height: 56pt; }
  .lh-emblem-img { height: 52pt; margin-left: 5pt; }
  .lh-script {
    font-family: 'Old English Text MT', 'PassSlipBlackletter', 'Times New Roman', serif;
    line-height: 1.1;
  }
  .lh-republic { font-size: 9pt; }
  .lh-municipality { font-size: 12.5pt; }
  .lh-tagline { font-size: 9pt; }
  .lh-rule { border-top: 0.75pt solid #000; width: 62%; margin: 1.5pt auto; }
  .lh-office { font-size: 10.5pt; font-weight: bold; }
  .lh-tel { font-size: 6pt; font-weight: bold; }

  /* Disapproval note — system state the paper form has no box for. It sits in
     the slip box's own dead space below the signatures, so it cannot disturb
     anything above it. */
  .note {
    left: 16.62pt; top: 418pt; width: 521pt;
    border: 0.75pt solid #000;
    padding: 1.5pt 4pt;
    font-size: 7.5pt;
    line-height: 1.2;
    white-space: normal;
  }
</style>

<div class="ps-sheet">

  {{-- ═══════════════ PASS SLIP ═══════════════ --}}
  <div class="ps-box ps-box-slip">
    <div class="ps-frame ps-frame-slip"></div>

    {{-- Letterhead: masthead 459.8x80.5 at 26,10 and the mark 51.5x73 at 482.4,9.9 --}}
    @include('admin.passSlip.partials.form-letterhead', [
      'mhLeft' => 26, 'mhTop' => 10, 'mkLeft' => 482.4, 'mkTop' => 9.9,
    ])

    {{-- Control number, on the short rule top right --}}
    <div class="r" style="left:473.75pt; top:{{ $ry(108.22, 11) }}pt; width:61.35pt; border-bottom-width:{{ $rw(11) }}pt"></div>
    <div class="xc tr" style="left:473.75pt; top:{{ $t(108.22, 8, $ASC_CALIBRI) }}pt; width:61.35pt; font-size:8pt">{{ $slipNumber }}</div>

    <div class="xc disp title" style="top:{{ $t(126.22, 18, $ASC_BERLIN) }}pt">PASS SLIP</div>

    {{-- Row 1 — Date / ETD / ETA, all on baseline 180.85 --}}
    <div class="x tb" style="left:11.62pt;  top:{{ $t(180.85, 14) }}pt; font-size:14pt">Date:</div>
    <div class="x tb" style="left:208.67pt; top:{{ $t(180.85, 14) }}pt; font-size:14pt">ETD:</div>
    <div class="x tb" style="left:404.72pt; top:{{ $t(180.85, 14) }}pt; font-size:14pt">ETA:</div>

    <div class="r" style="left:42.96pt;  top:{{ $ry(180.85, 12) }}pt; width:162pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="r" style="left:245.88pt; top:{{ $ry(180.85, 12) }}pt; width:156pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="r" style="left:441.95pt; top:{{ $ry(180.85, 12) }}pt; width:102pt; border-bottom-width:{{ $rw(12) }}pt"></div>

    @php $s = TimesText::fit($vDate, 152, 12); @endphp
    <div class="x tr" style="left:47.96pt;  top:{{ $t(180.85, $s) }}pt; font-size:{{ $s }}pt">{{ $vDate }}</div>
    @php $s = TimesText::fit($vEtd, 146, 12); @endphp
    <div class="x tr" style="left:250.88pt; top:{{ $t(180.85, $s) }}pt; font-size:{{ $s }}pt">{{ $vEtd }}</div>
    @php $s = TimesText::fit($vEta, 92, 12); @endphp
    <div class="x tr" style="left:446.95pt; top:{{ $t(180.85, $s) }}pt; font-size:{{ $s }}pt">{{ $vEta }}</div>

    {{-- Row 2 — Destination, baseline 213.05 --}}
    <div class="x tb" style="left:15.23pt; top:{{ $t(213.05, 14) }}pt; font-size:14pt">Destination:</div>
    <div class="r" style="left:86.76pt; top:{{ $ry(213.05, 12) }}pt; width:450pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    @php $s = TimesText::fit($vDest, 440, 12); @endphp
    <div class="x tr" style="left:91.76pt; top:{{ $t(213.05, $s) }}pt; font-size:{{ $s }}pt">{{ $vDest }}</div>

    {{-- Row 3 — Purpose/s, baselines 245.25 and 275.47 --}}
    <div class="x tb" style="left:14.02pt; top:{{ $t(245.25, 14) }}pt; font-size:14pt">Purpose/s:</div>
    <div class="r" style="left:75.57pt; top:{{ $ry(245.25, 12) }}pt; width:456pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="r" style="left:8.02pt;  top:{{ $ry(275.47, 12) }}pt; width:510pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="x tr" style="left:78.57pt; top:{{ $t(245.25, 12) }}pt; font-size:12pt">{{ $purposeWrapped[0] }}</div>
    <div class="x tr" style="left:11.02pt; top:{{ $t(275.47, 12) }}pt; font-size:12pt">{{ $purposeWrapped[1] }}</div>

    {{-- Approval --}}
    <div class="x tb" style="left:188.08pt; top:{{ $t(330.67, 12) }}pt; font-size:12pt">Approved:</div>

    <div class="r" style="left:16.62pt;  top:{{ $ry(372.08, 12) }}pt; width:216pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="r" style="left:346.73pt; top:{{ $ry(372.08, 12) }}pt; width:192pt; border-bottom-width:{{ $rw(12) }}pt"></div>

    {{-- Signed names sit on their rules, on the rule's own baseline --}}
    @php $s = TimesText::fit($employeeNameNatural ?? '', 212, 12); @endphp
    <div class="xc tr" style="left:16.62pt;  top:{{ $t(372.08, $s) }}pt; width:216pt; font-size:{{ $s }}pt">{{ $employeeNameNatural }}</div>
    @php $s = TimesText::fit($departmentHeadName ?? '', 188, 12); @endphp
    <div class="xc tr" style="left:346.73pt; top:{{ $t(372.08, $s) }}pt; width:192pt; font-size:{{ $s }}pt">{{ $departmentHeadName }}</div>

    <div class="x tr" style="left:80.62pt;  top:{{ $t(392.70, 12) }}pt; font-size:12pt">Employee&rsquo;s Signature</div>
    <div class="x tr" style="left:86.02pt;  top:{{ $t(406.50, 12) }}pt; font-size:12pt">Over Printed Name</div>
    <div class="x tr" style="left:389.73pt; top:{{ $t(392.70, 12) }}pt; font-size:12pt">Department Head</div>

    @if($isRejected)
      <div class="note"><strong>DISAPPROVED.</strong> {{ $remarks }}</div>
    @endif
  </div>

  {{-- ═══════════ CERTIFICATE OF APPEARANCE ═══════════ --}}
  <div class="ps-box ps-box-coa">
    <div class="ps-frame ps-frame-coa"></div>

    @include('admin.passSlip.partials.form-letterhead', [
      'mhLeft' => 13.8, 'mhTop' => 4.6, 'mkLeft' => 473.9, 'mkTop' => 4.3,
    ])

    <div class="r" style="left:477.95pt; top:{{ $ry(89.73, 11) }}pt; width:61.35pt; border-bottom-width:{{ $rw(11) }}pt"></div>
    <div class="xc tr" style="left:477.95pt; top:{{ $t(89.73, 8, $ASC_CALIBRI) }}pt; width:61.35pt; font-size:8pt">{{ $slipNumber }}</div>

    <div class="xc disp title" style="top:{{ $t(104.13, 18, $ASC_BERLIN) }}pt">CERTIFICATE OF APPEARANCE</div>

    <div class="x tr" style="left:15.23pt; top:{{ $t(149.95, 14) }}pt; font-size:14pt">This is to certify that :</div>

    @foreach($coaRows as [$label, $value, $base])
      <div class="x tb" style="left:19.62pt;  top:{{ $t($base, 14) }}pt; font-size:14pt">{{ $label }}</div>
      <div class="x tb" style="left:127.65pt; top:{{ $t($base, 14) }}pt; font-size:14pt">:</div>
      <div class="r" style="left:156.04pt; top:{{ $ry($base, 14) }}pt; width:322pt; border-bottom-width:{{ $rw(14) }}pt"></div>
      {{-- A value too long for the rule drops to a second line in the gap
           above the next row, the way it would be written by hand. Rows are
           24pt apart, so the run-on sits clear of both rules. --}}
      @php [$s, $lines] = TimesText::fitLines($value, 316, 2, 12); @endphp
      <div class="x tr" style="left:159.04pt; top:{{ $t($base, $s) }}pt; font-size:{{ $s }}pt">{{ $lines[0] }}</div>
      @if($lines[1] !== '')
        <div class="x tr" style="left:159.04pt; top:{{ $t($base + $s + 1.5, $s) }}pt; font-size:{{ $s }}pt">{{ $lines[1] }}</div>
      @endif
    @endforeach

    {{-- "Issued this __ day of ____ __ for whatever legal purpose it may serve."
         Segment offsets are the source line's own, measured in Times 12. --}}
    <div class="x tr" style="left:37.62pt;  top:{{ $t(343.78, 12) }}pt; font-size:12pt">Issued this</div>
    <div class="x tr" style="left:133.62pt; top:{{ $t(343.78, 12) }}pt; font-size:12pt">day of</div>
    <div class="x tr" style="left:292.94pt; top:{{ $t(343.78, 12) }}pt; font-size:12pt">for whatever legal purpose it may serve.</div>

    <div class="r" style="left:91.62pt;  top:{{ $ry(343.78, 12) }}pt; width:42pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="r" style="left:169.94pt; top:{{ $ry(343.78, 12) }}pt; width:78pt; border-bottom-width:{{ $rw(12) }}pt"></div>
    <div class="r" style="left:250.94pt; top:{{ $ry(343.78, 12) }}pt; width:42pt; border-bottom-width:{{ $rw(12) }}pt"></div>

    <div class="xc tr" style="left:91.62pt;  top:{{ $t(343.78, 11) }}pt; width:42pt; font-size:11pt">{{ $issuedDay }}</div>
    <div class="xc tr" style="left:169.94pt; top:{{ $t(343.78, 11) }}pt; width:78pt; font-size:11pt">{{ $issuedMonth }}</div>
    <div class="xc tr" style="left:250.94pt; top:{{ $t(343.78, 11) }}pt; width:42pt; font-size:11pt">{{ $issuedYear }}</div>

    {{-- Deliberately unsigned: the head of the office *visited* certifies the
         appearance, and that office is not a record this system holds. --}}
    <div class="r" style="left:19.62pt; top:{{ $ry(390.00, 10) }}pt; width:160pt; border-bottom-width:{{ $rw(10) }}pt"></div>
    <div class="x tb" style="left:59.83pt; top:{{ $t(401.60, 10) }}pt; font-size:10pt">Head of the Office</div>
    <div class="x tr" style="left:45.62pt; top:{{ $t(411.20, 8) }}pt; font-size:8pt">(Signature over printed name)</div>
  </div>

</div>
