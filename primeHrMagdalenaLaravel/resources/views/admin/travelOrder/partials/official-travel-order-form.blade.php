{{-- The municipality's Authority to Travel, on one sheet of Philippine long
     bond (8.5 x 13 in = 612 x 936 pt) — the same stock as the Pass Slip.

     THIS IS A TRACING, NOT A REDESIGN. The geometry was measured off the
     office's own Travel Order template and then cross-checked against the text
     itself: at Times New Roman 12pt over a 468pt column (1-inch margins) with a
     35pt first-line indent, both fixed paragraphs break on exactly the words
     the template breaks on — which is what fixes the body size, the column
     width and the margins beyond guesswork. Do not "tidy" a number here; change
     one only if the office's template changes.

     Everything is positioned in points against the sheet, which is what makes
     the printed page identical in the browser and in dompdf.

     The two fixed paragraphs are the template's own wording and are literals:
     they are the *form*, not data, and nothing in this system can supply them.

     Values come from TravelOrderFormDataService and are sized to the space they
     have by TimesText, so a long destination or a six-name party steps down
     rather than running off the sheet. --}}
@php
  use App\Support\TimesText;

  /* A baseline in the template -> the CSS `top` of a line-height:1 box.
     0.7925 em is where the baseline sits in such a box for Times. Same
     constant, and the same reasoning, as the Pass Slip form. */
  $t = fn(float $base, float $size) => round($base - 0.7925 * $size, 2);

  /* The same, for a *flowing* block whose lines are $lh apart: half the
     leading sits above the first line's ascender. */
  $tb = fn(float $base, float $size, float $lh) => round($base - (($lh - $size) / 2 + 0.7925 * $size), 2);

  $LH   = 14.4;   // the template's line spacing for Times 12
  $BODY = 12.0;   // the body size, fixed by the paragraph line breaks above

  /* ── Personnel block ───────────────────────────────────────────────────
     The template shows one traveller, with the block's own whitespace running
     down to the DESTINATION row. A party fills that whitespace first: the
     pitch only tightens once the rows would run past 468pt, so an order with
     up to eight travellers prints with the template's exact spacing and a
     larger party is compressed instead of overrunning the block below it. */
  $rows    = max(1, count($personnel));
  $pPitch  = $rows > 1 ? min(20.0, (468.0 - 320.6) / ($rows - 1)) : 20.0;
  $pFont   = max(7.5, min($BODY, $pPitch - 2));
  $pLast   = 320.6 + ($rows - 1) * $pPitch;

  /* ── Detail block ──────────────────────────────────────────────────────
     Label at the left margin, colon on the template's tab stop, value on the
     next one. A value too long for its 266pt column wraps onto as many as
     three lines and the row grows by what it used, so the rows below move
     down together instead of overprinting each other. */
  $detail = [];
  foreach ($detailRows as [$label, $value]) {
      [$size, $lines] = TimesText::fitLines($value, 266, 3, $BODY, 9.0);
      $used = count(array_filter($lines, fn ($line) => $line !== ''));
      $detail[] = [
          'label'  => $label,
          'lines'  => $lines,
          'size'   => $size,
          'height' => max(20.0, $used * $LH + 5.6),
      ];
  }

  /* ── Vertical assembly ─────────────────────────────────────────────────
     Every block below the personnel table sits at the template's own baseline
     when the content is the template's size, and is pushed down by exactly
     what the block above it overflowed by. The gaps are the template's:
     20pt from the last name to DESTINATION, 28pt to the entitlement
     paragraph, 40pt to "Recommending Approval:", 57.5pt from the recommending
     title to "Approved:". */
  $GAPS = [
      'detail'      => 20.0,   // last name        -> DESTINATION
      'entitlement' => 28.0,   // last detail row  -> the entitlement paragraph
      'recommend'   => 40.0,   // last of that     -> "Recommending Approval:"
      'approve'     => 57.5,   // recommending title -> "Approved:"
  ];

  /* The recommending name wraps over its 143pt rule the way the template's
     own does. The title follows the last line it actually used.

     Fitted to 135, not 143: TimesText measures the *roman* advance widths and
     this line is set bold, which runs a few per cent wider. The signature
     names are the only bold values on the sheet, so the allowance is made
     here rather than by widening the measurement table. */
  [$recSize, $recLines] = TimesText::fitLines($recommendingName, 135, 2, $BODY, 8.5);
  $recUsed = max(1, count(array_filter($recLines, fn ($line) => $line !== '')));

  /* Lay the sheet out for a given set of gaps. Taking them as an argument
     rather than closing over them is what lets the squeeze below re-run it:
     a `use` clause binds by value when the closure is *made*, so a second call
     would otherwise silently re-use the first set. */
  $layout = function (array $gaps) use ($pLast, $detail, $LH, $recUsed) {
      $cursor = max(400.0, $pLast + $gaps['detail']);
      $bases = [];
      $detailLast = $cursor;

      foreach ($detail as $row) {
          $bases[] = $cursor;
          $detailLast = $cursor;
          $cursor += $row['height'];
      }

      $entTop   = max(488.1, $detailLast + $gaps['entitlement']);
      $entLast  = $entTop + 2 * $LH;                      // the paragraph is three lines
      $recTop   = max(572.0, $entLast + $gaps['recommend']);
      $recRule  = $recTop + 30.0;
      $recName  = $recRule + 11.0;
      $recTitle = $recName + ($recUsed - 1) * $LH + 15.5;
      $appTop   = $recTitle + $gaps['approve'];

      return [
          'detailBases' => $bases,
          'entTop'   => $entTop,
          'recTop'   => $recTop,
          'recRule'  => $recRule,
          'recName'  => $recName,
          'recTitle' => $recTitle,
          'appTop'   => $appTop,
          'appRule'  => $appTop + 24.5,
          'appName'  => $appTop + 36.0,
          'appTitle' => $appTop + 49.4,
      ];
  };

  $L = $layout($GAPS);

  /* Squeeze the four gaps, never the text, if a long order would otherwise
     push the last signature line into the tourism footer. Each gap gives up
     the same share of the overflow down to a 12pt floor — the whole letter
     tightening a little reads as a full page, where one collapsed gap reads as
     a mistake. Past the floor there is nothing left to give: the sheet is a
     fixed height and the alternative is text off the edge of the form. */
  if ($L['appTitle'] > 812.0) {
      $over = $L['appTitle'] - 812.0;
      $L = $layout(array_map(fn ($gap) => max(12.0, $gap - $over / 4), $GAPS));
  }
@endphp
<style>
  .to-sheet {
    position: relative;
    width: 612pt;
    height: 936pt;
    margin: 0;
    padding: 0;
    background: #fff;
    color: #000;
    font-family: 'Times New Roman', Times, serif;
  }

  /* Every printed item is placed against the sheet. */
  .to-sheet > * { position: absolute; margin: 0; padding: 0; }

  /* The seal sits behind everything: it is painted first and pinned to z-index
     0 so that even if a renderer ignores `opacity` the letter is still legible
     over it, rather than a form nobody can read. */
  .to-wm { left: 103pt; top: 260pt; width: 407pt; opacity: .08; z-index: 0; }
  .to-wm img { width: 407pt; display: block; }

  .to-ink { z-index: 1; }

  /* Text: line-height 1 so the baseline lands where `top` puts it. */
  .x  { line-height: 1; white-space: nowrap; }
  .xc { line-height: 1; white-space: nowrap; text-align: center; }

  .tb { font-weight: bold; }
  .ti { font-style: italic; }

  /* A ruled line. Height 0 with a bottom border puts the stroke exactly at
     `top`, which is where a typed underscore's bar sits. */
  .r { height: 0; border-bottom: 0.6pt solid #000; }

  /* A flowing paragraph — the two fixed passages, which have to wrap. */
  .p {
    font-size: 12pt;
    line-height: 14.4pt;
    text-align: left;
    white-space: normal;
  }

  /* ---- Letterhead ---- */
  .lh-seal { left: 71pt; top: 40pt; }
  .lh-seal img { width: 75pt; height: 75pt; display: block; }
  .lh-mark { left: 497pt; top: 40pt; }
  .lh-mark img { width: 51.5pt; height: 73pt; display: block; }
  .lh-script {
    font-family: 'Old English Text MT', 'FormBlackletter', 'Times New Roman', serif;
    left: 150pt;
    width: 330pt;
    text-align: center;
    line-height: 1;
    white-space: nowrap;
  }
  /* The flourish under the wording, drawn as a rule: the template's engraved
     ornament is artwork this project does not ship, and a rule of the same
     width reads as the same divider where a substituted dingbat would not. */
  .lh-rule { left: 215pt; top: 83pt; width: 200pt; }

  /* ---- Tourism footer ----
     The mark is bottom-anchored: its height follows the file's own aspect
     ratio (measured in the service), so a re-cut of the artwork grows upward
     into the margin instead of pushing the contact rule down the page. */
  .ft-logo { left: 69pt; }
  .ft-logo img { width: 146pt; display: block; }
  .ft-rule { top: 879pt; }
  .ft-text { top: {{ $t(874, 9) }}pt; font-size: 9pt; text-align: center; line-height: 1; }

  /* The template badges each contact detail with an icon. Those are artwork
     this project does not ship, so the glyphs stand in for them — both are in
     dompdf's bundled DejaVu Sans (verified against the embedded /W array), and
     a browser falls through to its own symbol face. Never a colour emoji: the
     form prints in black. */
  .ft-icon {
    font-family: 'DejaVu Sans', 'Segoe UI Symbol', 'Noto Sans Symbols', sans-serif;
    font-size: 8.5pt;
  }
</style>

<div class="to-sheet">

  {{-- The seal, watermarked behind the letter, as the template carries it. --}}
  @if($sealBase64)
    <div class="to-wm"><img src="{{ $sealBase64 }}" alt=""></div>
  @endif

  {{-- ═══════════════ Letterhead ═══════════════
       Drawn rather than printed from one image: the Travel Order's masthead
       carries no office line and no HRMO seal, so it cannot reuse the Pass
       Slip's masthead artwork. --}}
  @if($sealBase64)
    <div class="lh-seal to-ink"><img src="{{ $sealBase64 }}" alt=""></div>
  @endif

  <div class="lh-script to-ink" style="top:{{ $t(50.5, 10.5) }}pt; font-size:10.5pt">{{ $letterhead['republic'] }}</div>
  <div class="lh-script to-ink" style="top:{{ $t(63, 15) }}pt; font-size:15pt">{{ $letterhead['municipality'] }}</div>
  <div class="lh-script to-ink" style="top:{{ $t(74, 11) }}pt; font-size:11pt">{{ $letterhead['tagline'] }}</div>
  <div class="r lh-rule to-ink"></div>

  @if($letterhead['mark'])
    <div class="lh-mark to-ink"><img src="{{ $letterhead['mark'] }}" alt=""></div>
  @endif

  {{-- ═══════════════ Date ═══════════════
       The day the authority was granted, underlined as the template has it. --}}
  <div class="xc tb to-ink" style="left:72pt; top:{{ $t(154, 12) }}pt; width:468pt; font-size:12pt">
    <span style="text-decoration: underline">{{ $issuedDate }}</span>
  </div>

  {{-- ═══════════════ Opening paragraph ═══════════════ --}}
  <div class="p to-ink" style="left:72pt; top:{{ $tb(209, 12, $LH) }}pt; width:468pt; text-indent:35pt">
    In connection with the regular function of this office, the following personnel are hereby granted the authority to travel.
  </div>

  {{-- ═══════════════ Personnel ═══════════════ --}}
  <div class="xc tb to-ink" style="left:72pt;  top:{{ $t(279.5, 12) }}pt; width:180pt; font-size:12pt">Name</div>
  <div class="xc tb to-ink" style="left:300pt; top:{{ $t(279.5, 12) }}pt; width:240pt; font-size:12pt">Designation</div>

  @foreach($personnel as $i => $person)
    @php
      $base = 320.6 + $i * $pPitch;
      $nameSize = TimesText::fit($person['name'], 176, $pFont, 7.0);
      $desigSize = TimesText::fit($person['designation'], 236, $pFont, 7.0);
    @endphp
    <div class="xc to-ink" style="left:72pt;  top:{{ $t($base, $nameSize) }}pt;  width:180pt; font-size:{{ $nameSize }}pt">{{ $person['name'] }}</div>
    <div class="xc to-ink" style="left:300pt; top:{{ $t($base, $desigSize) }}pt; width:240pt; font-size:{{ $desigSize }}pt">{{ $person['designation'] }}</div>
  @endforeach

  {{-- ═══════════════ Trip details ═══════════════ --}}
  @foreach($detail as $i => $row)
    @php $base = $L['detailBases'][$i]; @endphp
    <div class="x tb to-ink"  style="left:72pt;  top:{{ $t($base, 12) }}pt; font-size:12pt">{{ $row['label'] }}</div>
    <div class="x tb to-ink"  style="left:225pt; top:{{ $t($base, 12) }}pt; font-size:12pt">:</div>
    @foreach($row['lines'] as $n => $line)
      @continue($line === '')
      <div class="x to-ink" style="left:274pt; top:{{ $t($base + $n * $LH, $row['size']) }}pt; font-size:{{ $row['size'] }}pt">{{ $line }}</div>
    @endforeach
  @endforeach

  {{-- ═══════════════ Entitlement ═══════════════ --}}
  <div class="p to-ink" style="left:72pt; top:{{ $tb($L['entTop'], 12, $LH) }}pt; width:468pt">
    The above-named personnel shall be entitled to the actual per diem and transportation allowance normally granted to government officials/employees on official travel, subject to usual accounting and auditing rules and regulations.
  </div>

  {{-- ═══════════════ Recommending Approval ═══════════════
       Signed by whoever approved the order in this system; the capacity under
       the rule is configuration, because it is an office and not the signer's
       plantilla designation. --}}
  <div class="x to-ink" style="left:360pt; top:{{ $t($L['recTop'], 12) }}pt; font-size:12pt">Recommending Approval:</div>
  <div class="r to-ink" style="left:388pt; top:{{ $L['recRule'] }}pt; width:152pt"></div>

  @foreach($recLines as $n => $line)
    @continue($line === '')
    <div class="x tb to-ink" style="left:397pt; top:{{ $t($L['recName'] + $n * $LH, $recSize) }}pt; font-size:{{ $recSize }}pt">{{ $line }}</div>
  @endforeach

  @php $recTitleSize = TimesText::fit($recommendingTitle, 143, 12, 8.0); @endphp
  <div class="xc ti to-ink" style="left:397pt; top:{{ $t($L['recTitle'], $recTitleSize) }}pt; width:143pt; font-size:{{ $recTitleSize }}pt">{{ $recommendingTitle }}</div>

  {{-- ═══════════════ Approved ═══════════════
       Deliberately unsigned in the system: nothing here records the Municipal
       Administrator's decision, so the rule is printed for a wet signature and
       the name below it comes from configuration. --}}
  <div class="x ti to-ink" style="left:72pt; top:{{ $t($L['appTop'], 12) }}pt; font-size:12pt">Approved:</div>
  <div class="r to-ink" style="left:72pt; top:{{ $L['appRule'] }}pt; width:218pt"></div>

  @php
    $appNameSize  = TimesText::fit($approvingName, 300, 12, 8.0);
    $appTitleSize = TimesText::fit($approvingTitle, 300, 12, 8.0);
  @endphp
  <div class="x tb to-ink" style="left:109pt; top:{{ $t($L['appName'], $appNameSize) }}pt; font-size:{{ $appNameSize }}pt">{{ $approvingName }}</div>
  <div class="x ti to-ink" style="left:111pt; top:{{ $t($L['appTitle'], $appTitleSize) }}pt; font-size:{{ $appTitleSize }}pt">{{ $approvingTitle }}</div>

  {{-- ═══════════════ Tourism footer ═══════════════
       The contact strip spans the width the mark leaves it; with the mark not
       on disk it spans the full column instead of leaving a gap where an
       image failed to load. --}}
  @php
    $ftLeft  = $footer['logo'] ? 229 : 72;
    $ftWidth = $footer['logo'] ? 311 : 468;

    /* Built here rather than inline, because the strip has to be one unbroken
       run of markup — a newline between the two details is whitespace the
       renderer may break the centred line on — and the separator has to be
       non-breaking spaces: HTML collapses a run of ordinary ones to a single
       space, which ran the telephone number straight into the address.
       Each detail is escaped; they come from configuration, not from a
       record, but nothing that reaches this sheet is trusted markup. */
    $ftParts = [];

    if ($footer['telephone']) {
        $ftParts[] = '<span class="ft-icon">&#9742;</span>&nbsp;' . e($footer['telephone']);
    }

    if ($footer['email']) {
        $ftParts[] = '<span class="ft-icon">&#9993;</span>&nbsp;' . e($footer['email']);
    }
  @endphp

  @if($footer['logo'])
    <div class="ft-logo to-ink" style="top:{{ round(885 - $footer['logoHeight'], 2) }}pt">
      <img src="{{ $footer['logo'] }}" alt="" style="height:{{ $footer['logoHeight'] }}pt">
    </div>
  @endif

  @if($ftParts)
    <div class="xc ft-text to-ink" style="left:{{ $ftLeft }}pt; width:{{ $ftWidth }}pt">{!! implode(str_repeat('&nbsp;', 5), $ftParts) !!}</div>
    <div class="r ft-rule to-ink" style="left:{{ $ftLeft }}pt; width:{{ $ftWidth }}pt"></div>
  @endif

</div>
