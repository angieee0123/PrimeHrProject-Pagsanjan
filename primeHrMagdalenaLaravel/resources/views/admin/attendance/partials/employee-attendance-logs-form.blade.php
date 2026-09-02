{{-- The office's "Time Master · Employee Attendance Logs" sheet — the printed
     Daily Time Record — on A4 portrait (595.28 x 841.89 pt).

     THIS IS A TRACING, NOT A REDESIGN. Every number below was measured off the
     office's own template (a 1055 x 1491 export, whose 0.7076 aspect is A4 to
     within half a point) and converted at 0.5644 pt per pixel:

       page frame        inset 6pt, 1pt rule
       title box         24.3, 75.1   555.6 x 46.3   1.7pt #13438C
       ID / Dept chips   29.3 / 252.3, 133.2         61.5 / 59.8 x 19.2
       Name chip         29.3, 164.8
       column box        24.3, 195.3  555.6 x 33.3
       column chips      36.7 122.5 198.1 274.3 351.1 427.3 504.0 at 203.7
       ruled lines       27.1, 550.8 wide, 250.0 down to 716.2
       signature rules   83.5 (120.2 wide) and 364.6 (143.9 wide) at 774.5

     Do not "tidy" a number here. Change one only if the office's template
     changes — and if it changes shape, this file plus config/dtr_form.php are
     the whole of what has to be rewritten.

     Everything is positioned in points against the sheet, which is what makes
     dompdf's page 2 identical to its page 1.

     Values come from DtrFormDataService. The static wording comes from
     config/dtr_form.php. Nothing on this sheet is invented here. --}}
@php
    /* The ruled band, measured: the first rule at 250.0pt, the last at 716.2pt.
       The pitch follows the configured row count rather than being a literal,
       so a form re-cut with more or fewer lines still fills the same band
       instead of running off the sheet. At the template's 24 rows this is
       exactly the measured 20.27pt. */
    $RULE_FIRST = 250.0;
    $RULE_LAST  = 716.2;

    $perPage = max(1, (int) $rowsPerPage);
    $pitch   = $perPage > 1 ? ($RULE_LAST - $RULE_FIRST) / ($perPage - 1) : ($RULE_LAST - $RULE_FIRST);

    /* Writing sits *above* a ruled line, the way it does on the paper form.
       The text box ends half a point short of the rule, which leaves the
       baseline clear of it — end the box *on* the rule and the stroke runs
       through the glyphs' feet and reads as a strike-through. */
    $CELL_LIFT = 0.5;
    $cellH  = min(16.5, $pitch - 3.0);
    $cellFs = max(6.0, min(9.0, $cellH - 7.5));
    $markFs = max(5.5, $cellFs - 0.5);

    /* Column boxes are centred on the header chips' own centres — 70.55,
       151.55, 227.15, 303.65, 380.45, 456.65 and 534.2pt — at a width that
       clears its neighbours. */
    $COL_W = 74.0;
    $COLS  = [
        'date'   => 33.55,
        'am_in'  => 114.55,
        'am_out' => 190.15,
        'pm_in'  => 266.65,
        'pm_out' => 343.45,
        'ot_in'  => 419.65,
        'ot_out' => 497.20,
    ];

    /* A day covered by approved leave or an approved travel order has no
       punches to print. Its marker spans the four am/pm columns — from the
       "am IN" box's left edge to the "pm OUT" box's right edge. */
    $MARK_LEFT  = $COLS['am_in'];
    $MARK_WIDTH = ($COLS['pm_out'] + $COL_W) - $COLS['am_in'];

    /* A value too long for the space steps down rather than running over the
       frame; dompdf will not clip it for us. Same idea as the Travel Order's
       TimesText, at the one size this sheet needs it. */
    $fit = function (string $value, float $width, float $size): float {
        $length = mb_strlen($value);

        if ($length === 0) {
            return $size;
        }

        // Helvetica-Bold averages ~0.55em over mixed-case text.
        $needed = $length * 0.55 * $size;

        return $needed <= $width ? $size : max(6.5, round($width / ($length * 0.55), 2));
    };

    /* The sheet has two signature rules. A third configured officer has
       nowhere to sign, so the form prints the two it can rather than
       overprinting them. */
    $signatories = array_slice(array_values($signatories), 0, 2);
@endphp

<style>
  /* One sheet = one page. `dtr-last` drops the trailing break so the PDF does
     not end on a blank page. */
  .dtr-sheet {
    position: relative;
    width: 595.28pt;
    height: 841.89pt;
    margin: 0;
    padding: 0;
    background: #fff;
    color: #000;
    font-family: Helvetica, Arial, sans-serif;
    page-break-after: always;
  }
  .dtr-last { page-break-after: auto; }

  /* Every printed item is placed against the sheet. */
  .dtr-sheet > * { position: absolute; margin: 0; padding: 0; }

  /* The frame. The template draws it at the page edge; it is inset 6pt here
     because most printers cannot lay ink in the last 3-4mm, and a border that
     prints on one machine and vanishes on the next is worse than one moved by
     a sixteenth of an inch. Nothing inside it moved. */
  .dtr-frame {
    left: 6pt; top: 6pt;
    width: 581.28pt; height: 827.89pt;
    border: 1pt solid #0A0A0A;
  }

  /* ---- Wordmark ---- */
  .dtr-mark { left: 408.6pt; top: 23.7pt; }
  .dtr-mark img { display: block; }

  .dtr-mark-fb { left: 300pt; top: 21pt; width: 279.6pt; text-align: right; }
  .dtr-mark-name { font-size: 21pt; font-weight: bold; color: #C32A2F; line-height: 24pt; }
  .dtr-mark-tag  { font-size: 10pt; font-weight: bold; color: #111; line-height: 13pt; }

  /* ---- Title ---- */
  .dtr-title {
    left: 24.3pt; top: 75.1pt;
    width: 552.2pt; height: 42.9pt;
    border: 1.7pt solid #13438C;
    font-size: 21.5pt; font-weight: bold; color: #144D90;
    line-height: 42.9pt; text-align: center;
  }

  /* ---- Identity chips ----
     Outer size is the measured size: the 0.5pt outline is taken out of the
     box, not added to it. */
  .dtr-chip {
    height: 18.2pt;
    background: #1A539A;
    border: 0.5pt solid #123F73;
    color: #FFF;
    font-size: 11.5pt; font-weight: bold;
    line-height: 18.2pt; text-align: center;
    white-space: nowrap;
  }
  .dtr-chip-id   { left: 29.3pt;  top: 133.2pt; width: 60.5pt; }
  .dtr-chip-dept { left: 252.3pt; top: 133.2pt; width: 58.8pt; }
  .dtr-chip-name { left: 29.3pt;  top: 164.8pt; width: 60.5pt; }

  .dtr-val {
    height: 19.2pt;
    line-height: 19.2pt;
    font-weight: bold;
    color: #111;
    white-space: nowrap;
  }
  .dtr-val-id   { left: 98.8pt;  top: 133.2pt; width: 145.5pt; }
  .dtr-val-dept { left: 320.1pt; top: 133.2pt; width: 259.5pt; }
  .dtr-val-name { left: 98.8pt;  top: 164.8pt; width: 481.1pt; }

  /* ---- Column head ---- */
  .dtr-colbox {
    left: 24.3pt; top: 195.3pt;
    width: 552.2pt; height: 29.9pt;
    border: 1.7pt solid #13438C;
  }
  .dtr-col {
    top: 203.7pt; height: 15.9pt;
    background: #1A539A;
    border: 0.5pt solid #123F73;
    color: #FFF;
    font-size: 10.5pt; font-weight: bold;
    line-height: 15.9pt; text-align: center;
    white-space: nowrap;
  }
  .dtr-col-date  { left: 36.7pt;  width: 66.7pt; }
  .dtr-col-amin  { left: 122.5pt; width: 57.1pt; }
  .dtr-col-amout { left: 198.1pt; width: 57.1pt; }
  .dtr-col-pmin  { left: 274.3pt; width: 57.7pt; }
  .dtr-col-pmout { left: 351.1pt; width: 57.7pt; }
  .dtr-col-otin  { left: 427.3pt; width: 57.7pt; }
  .dtr-col-otout { left: 504.0pt; width: 59.4pt; }

  /* ---- The ruled band ----
     Height 0 with a bottom border puts the stroke exactly at `top`. */
  .dtr-rule {
    left: 27.1pt;
    width: 550.8pt; height: 0;
    border-bottom: 1pt solid #0A0A0A;
  }

  .dtr-cell {
    width: {{ $COL_W }}pt;
    height: {{ $cellH }}pt;
    line-height: {{ $cellH }}pt;
    font-size: {{ $cellFs }}pt;
    color: #111;
    text-align: center;
    white-space: nowrap;
  }

  .dtr-marker {
    height: {{ $cellH }}pt;
    line-height: {{ $cellH }}pt;
    font-size: {{ $markFs }}pt;
    font-style: italic;
    color: #1A539A;
    text-align: center;
    white-space: nowrap;
  }

  .dtr-folio {
    left: 27.1pt; top: 742pt;
    width: 550.8pt;
    font-size: 7.5pt;
    color: #6B7280;
    text-align: right;
  }

  /* ---- Signatories ---- */
  .dtr-sig-rule { top: 774.5pt; height: 0; border-bottom: 1pt solid #0A0A0A; }
  .dtr-sig-rule-l { left: 83.5pt;   width: 120.2pt; }
  .dtr-sig-rule-r { left: 364.6pt;  width: 143.9pt; }

  .dtr-sig-l { left: 43.6pt;   width: 200pt; }
  .dtr-sig-r { left: 336.55pt; width: 200pt; }

  .dtr-sig-name  { top: 777.5pt; height: 12pt; line-height: 12pt; font-size: 9pt; font-weight: bold; color: #111; text-align: center; }
  .dtr-sig-title { top: 789.5pt; height: 11pt; line-height: 11pt; font-size: 8pt; font-style: italic; color: #333; text-align: center; }
</style>

@foreach($pages as $pageIndex => $pageRows)
    @php $isLast = $pageIndex === count($pages) - 1; @endphp

    <div class="dtr-sheet{{ $isLast ? ' dtr-last' : '' }}">

        {{-- ── Page frame ── --}}
        <div class="dtr-frame"></div>

        {{-- ── Wordmark ──
             The office's own artwork when it is on disk; the wording from
             config drawn in its place when it is not, so the form still prints
             on an install that never copied the image across. --}}
        @if($wordmarkSrc)
            <div class="dtr-mark">
                <img src="{{ $wordmarkSrc }}"
                     style="width: {{ $wordmarkWidth }}pt; height: {{ $wordmarkHeight }}pt;">
            </div>
        @else
            <div class="dtr-mark-fb">
                <div class="dtr-mark-name">{{ $brand['name'] ?? '' }}</div>
                <div class="dtr-mark-tag">{{ $brand['tagline'] ?? '' }}</div>
            </div>
        @endif

        {{-- ── Title ── --}}
        <div class="dtr-title">{{ $title }}</div>

        {{-- ── Identity block ──
             Printed on every sheet: continuation pages get separated from
             their first page in a filing cabinet, and a loose page of times
             belonging to nobody is not a record. --}}
        <div class="dtr-chip dtr-chip-id">{{ $labels['id'] ?? '' }}</div>
        <div class="dtr-val dtr-val-id"
             style="font-size: {{ $fit($employee['id_no'], 141.5, 10.5) }}pt;">{{ $employee['id_no'] }}</div>

        <div class="dtr-chip dtr-chip-dept">{{ $labels['department'] ?? '' }}</div>
        <div class="dtr-val dtr-val-dept"
             style="font-size: {{ $fit($employee['department'], 255.5, 10.5) }}pt;">{{ $employee['department'] }}</div>

        <div class="dtr-chip dtr-chip-name">{{ $labels['name'] ?? '' }}</div>
        <div class="dtr-val dtr-val-name"
             style="font-size: {{ $fit($employee['name'], 477.0, 10.5) }}pt;">{{ $employee['name'] }}</div>

        {{-- ── Column head ── --}}
        <div class="dtr-colbox"></div>
        <div class="dtr-col dtr-col-date">{{ $columns['date'] ?? '' }}</div>
        <div class="dtr-col dtr-col-amin">{{ $columns['am_in'] ?? '' }}</div>
        <div class="dtr-col dtr-col-amout">{{ $columns['am_out'] ?? '' }}</div>
        <div class="dtr-col dtr-col-pmin">{{ $columns['pm_in'] ?? '' }}</div>
        <div class="dtr-col dtr-col-pmout">{{ $columns['pm_out'] ?? '' }}</div>
        <div class="dtr-col dtr-col-otin">{{ $columns['ot_in'] ?? '' }}</div>
        <div class="dtr-col dtr-col-otout">{{ $columns['ot_out'] ?? '' }}</div>

        {{-- ── Ruled lines ──
             Every line the template draws is drawn, whether or not a record
             landed on it: a short period prints the office's own part-filled
             sheet rather than a form that stops halfway down. --}}
        @for($i = 0; $i < $perPage; $i++)
            <div class="dtr-rule" style="top: {{ round($RULE_FIRST + $i * $pitch, 2) }}pt;"></div>
        @endfor

        {{-- ── The days ── --}}
        @foreach($pageRows as $i => $row)
            @php $top = round($RULE_FIRST + $i * $pitch - $cellH - $CELL_LIFT, 2); @endphp

            <div class="dtr-cell" style="left: {{ $COLS['date'] }}pt; top: {{ $top }}pt;">{{ $row['date'] }}</div>

            @if($row['marker'] !== '')
                <div class="dtr-marker"
                     style="left: {{ $MARK_LEFT }}pt; top: {{ $top }}pt; width: {{ $MARK_WIDTH }}pt;">{{ $row['marker'] }}</div>
            @else
                @foreach(['am_in', 'am_out', 'pm_in', 'pm_out'] as $slot)
                    @if($row[$slot] !== '')
                        <div class="dtr-cell" style="left: {{ $COLS[$slot] }}pt; top: {{ $top }}pt;">{{ $row[$slot] }}</div>
                    @endif
                @endforeach
            @endif

            @foreach(['ot_in', 'ot_out'] as $slot)
                @if($row[$slot] !== '')
                    <div class="dtr-cell" style="left: {{ $COLS[$slot] }}pt; top: {{ $top }}pt;">{{ $row[$slot] }}</div>
                @endif
            @endforeach
        @endforeach

        {{-- ── Sheet number ──
             Only when the period ran past one sheet. Pages of a DTR are filed
             loose and signed once; without this there is nothing on sheet 2
             saying it is sheet 2. --}}
        @if(count($pages) > 1)
            <div class="dtr-folio">Page {{ $pageIndex + 1 }} of {{ count($pages) }}</div>
        @endif

        {{-- ── Signatories ──
             On the final sheet only. They certify the record as a whole; one
             set per continuation page would be asking for the same document to
             be signed five times. --}}
        @if($isLast)
            @if(isset($signatories[0]))
                <div class="dtr-sig-rule dtr-sig-rule-l"></div>
                <div class="dtr-sig-name dtr-sig-l">{{ $signatories[0]['name'] ?? '' }}</div>
                <div class="dtr-sig-title dtr-sig-l">{{ $signatories[0]['title'] ?? '' }}</div>
            @endif

            @if(isset($signatories[1]))
                <div class="dtr-sig-rule dtr-sig-rule-r"></div>
                <div class="dtr-sig-name dtr-sig-r">{{ $signatories[1]['name'] ?? '' }}</div>
                <div class="dtr-sig-title dtr-sig-r">{{ $signatories[1]['title'] ?? '' }}</div>
            @endif
        @endif

    </div>
@endforeach
