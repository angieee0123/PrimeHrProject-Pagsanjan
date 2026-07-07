{{-- Official Pass Slip form (replicates PASS-SLIP.pdf layout) --}}
@php
  $chk = fn($on) => $on ? 'on' : '';
@endphp
<style>
  .ps-form {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9pt;
    color: #000;
    line-height: 1.35;
    background: #fff;
    border: 1px solid #000;
    padding: 10px 14px;
  }
  .ps-form table { width: 100%; border-collapse: collapse; }
  .ps-form .nb td, .ps-form .nb th { border: none; padding: 2px; vertical-align: top; }
  .ps-form .lbl { font-weight: bold; }
  .ps-form .val { border-bottom: 1px solid #000; display: inline-block; min-height: 13px; min-width: 60px; padding: 0 3px; }
  .ps-form .val-wide { border-bottom: 1px solid #000; display: block; min-height: 13px; padding: 1px 3px; }
  .ps-form .cb {
    display: inline-block; width: 10px; height: 10px; border: 1px solid #000;
    text-align: center; line-height: 10px; font-size: 8px; font-weight: bold;
    margin-right: 3px; vertical-align: middle;
  }
  .ps-form .cb.on::after { content: 'X'; }
  .ps-form .purpose-row { padding: 1px 0 1px 18px; }
  .ps-form .sig { border-top: 1px solid #000; margin-top: 34px; padding-top: 2px; text-align: center; font-size: 8pt; }
  .ps-form .sig-name { text-align: center; font-size: 8.5pt; font-weight: bold; margin-top: -14px; }
  @media print { .ps-form { padding: 0; border: none; } }
</style>

<div class="ps-form">

  {{-- HEADER --}}
  <table class="nb" style="margin-bottom: 6px;">
    <tr>
      <td style="width: 60px; text-align: center;">
        @if($logoBase64)
          <img src="{{ $logoBase64 }}" alt="" style="height: 48px;">
        @endif
      </td>
      <td style="text-align: center;">
        <div style="font-size: 9pt;">Republic of the Philippines</div>
        <div style="font-size: 10pt; font-weight: bold;">{{ $agencyName }}</div>
        <div style="font-size: 9pt;">{{ $agencyAddress }}</div>
        <div style="font-size: 13pt; font-weight: bold; margin-top: 6px; letter-spacing: 1px;">PASS SLIP</div>
      </td>
      <td style="width: 60px;"></td>
    </tr>
  </table>

  {{-- ITEM 1 --}}
  <table class="nb" style="margin-bottom: 2px;">
    <tr>
      <td style="width: 18%;"><span class="lbl">1. ISSUED FOR:</span></td>
      <td style="width: 32%;"><span class="cb {{ $chk($isOfficialActivity) }}"></span> OFFICIAL ACTIVITY</td>
      <td><span class="cb {{ $chk($isPersonalReason) }}"></span> PERSONAL REASONS</td>
    </tr>
  </table>

  {{-- ITEM 2 --}}
  <table class="nb" style="margin-bottom: 4px;">
    <tr>
      <td style="width: 10%;"><span class="lbl">2. TO:</span></td>
      <td style="width: 55%;"><span class="val-wide">{{ $employeeName }}</span></td>
      <td style="width: 10%; text-align: right;"><span class="lbl">DATE:</span></td>
      <td><span class="val-wide">{{ $date }}</span></td>
    </tr>
  </table>

  {{-- ITEM 3 --}}
  <div style="margin-bottom: 4px;">
    <span class="lbl">3.</span> You are hereby authorized to proceed to:
    <span class="val" style="width: 55%;">{{ $destination }}</span>
  </div>
  <div style="margin-bottom: 2px; padding-left: 14px;">for the purpose as indicated: (Check appropriate purpose)</div>

  <div class="purpose-row"><span class="lbl">A.</span></div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'coordinate_with') }}"></span> to coordinate with
    <span class="val" style="width: 45%;">{{ $purposeCategory === 'coordinate_with' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'meeting_conference') }}"></span> to attend meeting/conference
    <span class="val" style="width: 35%;">{{ $purposeCategory === 'meeting_conference' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'secure_documents') }}"></span> to secure documents &amp; others
    <span class="val" style="width: 38%;">{{ $purposeCategory === 'secure_documents' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'follow_up') }}"></span> to follow up
    <span class="val" style="width: 48%;">{{ $purposeCategory === 'follow_up' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row"><span class="lbl">B.</span></div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'personal_matter') }}"></span> to attend personal matter
    <span class="val" style="width: 45%;">{{ $purposeCategory === 'personal_matter' ? $purposeDetail : '' }}</span>
  </div>

  {{-- ITEMS 4 & 5 --}}
  <table class="nb" style="margin: 6px 0 2px;">
    <tr>
      <td style="width: 50%;">
        <span class="lbl">4. Time of Departure from Office:</span>
        <span class="val" style="width: 90px;">{{ $timeOut }}</span>
        <span class="cb {{ $chk($timeOutPeriod === 'AM') }}"></span> AM
        <span class="cb {{ $chk($timeOutPeriod === 'PM') }}"></span> PM
      </td>
      <td>
        <span class="lbl">5. Time of Return:</span>
        <span class="val" style="width: 90px;">{{ $timeIn }}</span>
        <span class="cb {{ $chk($timeInPeriod === 'AM') }}"></span> AM
        <span class="cb {{ $chk($timeInPeriod === 'PM') }}"></span> PM
      </td>
    </tr>
  </table>

  {{-- ITEMS 6 & 7 (SIGNATURES) --}}
  <table class="nb" style="margin-top: 4px;">
    <tr>
      <td style="width: 50%; padding-right: 16px;">
        <span class="lbl">6. Requested by:</span>
        <div class="sig"></div>
        <div class="sig-name">{{ $employeeName }}</div>
        <div style="text-align: center; font-size: 7.5pt;">Name and Signature of Employee</div>
      </td>
      <td style="width: 50%; padding-left: 16px;">
        <span class="lbl">7. Recommending Approval:</span>
        <div class="sig"></div>
        @if($recommendedByName)
          <div class="sig-name">{{ $recommendedByName }}</div>
        @endif
        <div style="text-align: center; font-size: 7.5pt;">(Immediate Supervisor)</div>
      </td>
    </tr>
  </table>

  {{-- ITEM 8 --}}
  <div style="margin-top: 10px; font-size: 8.5pt;">
    <span class="lbl">8.</span> I Certify that the above mentioned personnel appeared on the date and purpose as indicated.
  </div>

  <table class="nb" style="margin-top: 4px;">
    <tr>
      <td style="width: 50%; padding-right: 16px;">
        @if($hasReturned)
          <div class="sig"></div>
          <div style="text-align: center; font-size: 7.5pt;">Name and Signature</div>
        @endif
      </td>
      <td style="width: 50%; padding-left: 16px;">
        <div style="text-align: right; font-size: 8.5pt; margin-bottom: 2px;">Approved:</div>
        <div class="sig"></div>
        @if($isApproved && $approverName)
          <div class="sig-name">{{ $approverName }}</div>
        @endif
        <div style="text-align: center; font-size: 7.5pt;">(Approving Officer)</div>
      </td>
    </tr>
  </table>

  @if($isRejected)
    <div style="margin-top: 10px; padding: 6px 8px; border: 1px solid #000; font-size: 8pt;">
      <strong>Disapproved.</strong> {{ $remarks }}
    </div>
  @endif

  <div style="font-size: 6.5pt; color: #666; text-align: center; margin-top: 8px;">
    Ref: {{ $slipNumber }} · Generated {{ $generatedDate }}
  </div>
</div>
