{{-- The office's Monetization sheet — the one-page computation of Total Leave
     Benefits — on the template's own paper: 8.5 x 14 in (612 x 1008 pt).

     THIS IS A TRACING, NOT A REDESIGN. Every number below was measured off the
     office's own template (a 2550 x 4200 export at 300 dpi, i.e. 8.5 x 14 in)
     and converted at 0.24 pt per pixel:

       margin            left rule at 71pt (1 inch), as the template is typed
       heading           Province 79.1, Municipality 99.8   centred
       title             137.1                              centred
       identity rows     169.8 / 186.5 / 203.1   captions 71, values 211
       credits caption   234.4
       credit rows       250.5 / 266.6   label 110, colon 225, value 237
       total credits     282.7                              value column 232
       "Computation:"    315.5
       formula line      347.8
       legend rows       379.0 / 395.6 / 411.8 / 427.9   symbol 71, "=" 102,
                                                         wording 145
       working lines     459.1 / 491.4                     "=" column 102
       total             525.7                             "=" column 102
       "Prepared by:"    574.6
       preparer name     640.1
       preparer title    655.2

     Do not "tidy" a number here. Change one only if the office's template
     changes — and if it changes shape, this file plus config/monetization_form.php
     are the whole of what has to be rewritten.

     Everything is positioned in points against the sheet, which is what makes
     dompdf lay it out identically whatever the values are.

     The sheet is set in Times, which is what the template is typed in and what
     dompdf resolves to a core font — no font file has to ship for the printed
     copy to look like the office's own. It is also why the salary carries a
     plain "P": the core Times face has no peso glyph, and a missing one prints
     as a hollow box on a document somebody signs.

     Values come from MonetizationFormDataService. The static wording comes
     from config/monetization_form.php. No figure is computed here. --}}
@php
    /* Measured centres, converted to a box top: each line is placed by its
       centre and given its own leading, so a caption and its value sit on the
       same optical line however long either one is. */
    $BODY_FS = 14.0;   $BODY_LH = 16.6;
    $HEAD_FS = 15.0;   $HEAD_LH = 17.3;
    $BIG_FS  = 16.0;   $BIG_LH  = 18.4;

    $top = fn (float $centre, float $lh) => $centre - $lh / 2;

    /* Columns, left to right across the sheet. */
    $LEFT          = 71.0;   // the typed left margin — 1 inch
    $VALUE_X       = 211.0;  // Name / Position / Salary values
    $CREDIT_LABEL  = 110.0;  // the indented "Vacation Leave" / "Sick Leave"
    $CREDIT_COLON  = 225.0;
    $CREDIT_VALUE  = 237.0;
    $TOTAL_CREDITS = 232.0;  // the summed line, set under the credit values
    $LEGEND_EQ     = 102.0;  // the "=" column of the formula key
    $LEGEND_TEXT   = 145.0;  // the wording against each symbol
    $WORKING_X     = 102.0;  // the "= 12,087.00 x 75 days x ..." lines

    $RIGHT_EDGE = 612.0 - $LEFT;   // where a value box may run to

    /* A plantilla title like "Administrative Officer V (Human Resource
       Management Officer III)" is half again as long as the template's
       "Driver II", and on a sheet whose rows are placed by measurement a
       second line is a collision, not a wrap: the position would print
       through the salary under it.

       So an over-long value is set down a point at a time until it fits the
       column, to a floor of 9pt — small, but legible, and still the same
       typeface on the same line the template puts it on. Anything past the
       floor wraps, which the identity block below survives because it is laid
       out as a table: a wrapped row pushes the next one down rather than
       printing over it.

       0.5em per character is Times bold's average advance, near enough to
       choose a size by; the floor is what stops the estimate mattering. */
    $fit = function (string $text, float $available, float $base) {
        $length = max(1, mb_strlen(trim($text)));
        $needed = $available / (0.5 * $length);

        return max(9.0, min($base, $needed));
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $formTitle }} · {{ $requestNumber }}</title>
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", Times, serif;
            color: #000;
        }

        /* One sheet, addressed in points from its own top-left corner. */
        .sheet {
            position: relative;
            width: 612pt;
            height: 1008pt;
        }

        .at { position: absolute; }
        .b  { font-weight: bold; }
        .c  { text-align: center; }
        .u  { text-decoration: underline; }
    </style>
</head>
<body>
<div class="sheet">

    {{-- ── Heading ──────────────────────────────────────────────────── --}}
    <div class="at c" style="left: 0; top: {{ $top(79.1, $HEAD_LH) }}pt; width: 612pt; font-size: {{ $HEAD_FS }}pt; line-height: {{ $HEAD_LH }}pt;">
        {{ $heading['province'] ?? '' }}
    </div>
    <div class="at c b" style="left: 0; top: {{ $top(99.8, $HEAD_LH) }}pt; width: 612pt; font-size: {{ $HEAD_FS }}pt; line-height: {{ $HEAD_LH }}pt;">
        {{ $heading['municipality'] ?? '' }}
    </div>
    <div class="at c b" style="left: 0; top: {{ $top(137.1, $BIG_LH) }}pt; width: 612pt; font-size: {{ $BIG_FS }}pt; line-height: {{ $BIG_LH }}pt;">
        {{ $formTitle }}
    </div>

    {{-- ── Who the monetization belongs to ──────────────────────────────
         The employee on the request — never the account that printed it.

         Laid out as a table rather than three placed rows: the rows sit on
         their measured centres while nothing wraps, and a value long enough to
         wrap anyway pushes the row under it down instead of printing through
         it. --}}
    @php $VALUE_W = $RIGHT_EDGE - $VALUE_X; @endphp
    <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top(169.8, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $LEFT }}pt;">
        <table style="border-collapse: collapse; width: 100%;">
            @foreach ([
                [$labels['name']     ?? 'Name:',     $employeeName],
                [$labels['position'] ?? 'Position:', $position],
                [$labels['salary']   ?? 'Salary:',   $currencySymbol . ' ' . $salary],
            ] as [$caption, $value])
                <tr>
                    <td class="b" style="padding: 0; vertical-align: top; width: {{ $VALUE_X - $LEFT }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">{{ $caption }}</td>
                    <td class="b" style="padding: 0; vertical-align: top; font-size: {{ $fit($value, $VALUE_W, $BODY_FS) }}pt; line-height: {{ $BODY_LH }}pt;">{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- ── Leave credits as they stood when the request was filed ───────
         "as of" is the date the balances were read, not today: the approval
         has since taken the monetized days out of the live balance, so
         today's figure would contradict the computation below it. --}}
    <div class="at b" style="left: {{ $LEFT }}pt; top: {{ $top(234.4, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $LEFT }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $labels['credits_as_of'] ?? 'No. of Leave Credits as of' }} {{ $creditsAsOf }}
    </div>

    @foreach ([
        [250.5, $labels['vacation'] ?? 'Vacation Leave', $vacationCredits, false],
        [266.6, $labels['sick']     ?? 'Sick Leave',     $sickCredits,     true],
    ] as [$centre, $caption, $value, $ruled])
        <div class="at b" style="left: {{ $CREDIT_LABEL }}pt; top: {{ $top($centre, $BODY_LH) }}pt; width: {{ $CREDIT_COLON - $CREDIT_LABEL - 4 }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
            {{ $caption }}
        </div>
        <div class="at b" style="left: {{ $CREDIT_COLON }}pt; top: {{ $top($centre, $BODY_LH) }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">:</div>
        {{-- The sick-leave figure is ruled on the template: it is the second
             addend, and the rule under it is what marks the sum below. The
             rule runs under the figure only, not under the word "days". --}}
        <div class="at b" style="left: {{ $CREDIT_VALUE }}pt; top: {{ $top($centre, $BODY_LH) }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
            <span class="{{ $ruled ? 'u' : '' }}">{{ $value }}</span> days
        </div>
    @endforeach

    <div class="at b" style="left: {{ $TOTAL_CREDITS }}pt; top: {{ $top(282.7, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $TOTAL_CREDITS }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $totalCredits }} {{ $labels['total_credits'] ?? 'Total Earned Leave Credits' }}
    </div>

    {{-- ── Computation ──────────────────────────────────────────────── --}}
    <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top(315.5, $BODY_LH) }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $labels['computation'] ?? 'Computation:' }}
    </div>
    <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top(347.8, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $LEFT }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $labels['formula'] ?? 'Total Leave Benefits = S x D x CF' }}
    </div>

    {{-- The formula key. The symbols are fixed by the arithmetic in
         MonetizationRequest::computeAmount(); only the wording is configured. --}}
    @php
        $legendRows = [
            [379.0, 'S',   $legend['S']   ?? 'Monthly Salary'],
            [395.6, 'D',   $legend['D']   ?? 'Total No. of Leave Credits'],
            [411.8, 'CF',  $legend['CF']  ?? 'Constant Factor'],
            [427.9, 'TLB', $legend['TLB'] ?? 'S x D x CF'],
        ];
    @endphp
    @foreach ($legendRows as [$centre, $symbol, $wording])
        <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top($centre, $BODY_LH) }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">{{ $symbol }}</div>
        <div class="at" style="left: {{ $LEGEND_EQ }}pt; top: {{ $top($centre, $BODY_LH) }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">=</div>
        <div class="at" style="left: {{ $LEGEND_TEXT }}pt; top: {{ $top($centre, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $LEGEND_TEXT }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">{{ $wording }}</div>
    @endforeach

    {{-- The working, as the office's sheet shows it: S x D x CF, then the
         product of the first two against CF, then the stored total. --}}
    <div class="at" style="left: {{ $WORKING_X }}pt; top: {{ $top(459.1, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $WORKING_X }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        = {{ $salary }} x {{ $monetizedDays }} days x {{ $constantFactor }}
    </div>
    <div class="at" style="left: {{ $WORKING_X }}pt; top: {{ $top(491.4, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $WORKING_X }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        = {{ $salaryTimesDays }} x {{ $constantFactor }}
    </div>
    <div class="at b" style="left: {{ $WORKING_X }}pt; top: {{ $top(525.7, $BIG_LH) }}pt; width: {{ $RIGHT_EDGE - $WORKING_X }}pt; font-size: {{ $BIG_FS }}pt; line-height: {{ $BIG_LH }}pt;">
        = {{ $amount }} {{ $amountSuffix }}
    </div>

    {{-- ── Prepared by ──────────────────────────────────────────────────
         The account that produced this copy, with its own designation. The
         gap between the caption and the name is the template's space for a
         wet signature and is deliberately left empty. --}}
    <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top(574.6, $BODY_LH) }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $preparedBy['caption'] }}
    </div>
    <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top(640.1, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $LEFT }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $preparedBy['name'] }}
    </div>
    <div class="at" style="left: {{ $LEFT }}pt; top: {{ $top(655.2, $BODY_LH) }}pt; width: {{ $RIGHT_EDGE - $LEFT }}pt; font-size: {{ $BODY_FS }}pt; line-height: {{ $BODY_LH }}pt;">
        {{ $preparedBy['title'] }}
    </div>

</div>
</body>
</html>
