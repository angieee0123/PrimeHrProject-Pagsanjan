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

  /* Header */
  .ps-form .header-table { margin-bottom: 6px; }
  .ps-form .col-logo { width: 60px; text-align: center; }
  .ps-form .col-spacer { width: 60px; }
  .ps-form .header-cell { text-align: center; }
  .ps-form .header-country { font-size: 9pt; }
  .ps-form .header-agency-name { font-size: 10pt; font-weight: bold; }
  .ps-form .header-agency-address { font-size: 9pt; }
  .ps-form .header-title { font-size: 13pt; font-weight: bold; margin-top: 6px; letter-spacing: 1px; }
  .ps-form .logo-img { height: 48px; }

  /* Item 1 */
  .ps-form .mb-2 { margin-bottom: 2px; }
  .ps-form .col-18 { width: 18%; }
  .ps-form .col-32 { width: 32%; }

  /* Item 2 */
  .ps-form .mb-4 { margin-bottom: 4px; }
  .ps-form .col-10 { width: 10%; }
  .ps-form .col-55 { width: 55%; }
  .ps-form .col-10-right { width: 10%; text-align: right; }

  /* Item 3 */
  .ps-form .val-55 { width: 55%; }
  .ps-form .purpose-intro { margin-bottom: 2px; padding-left: 14px; }
  .ps-form .val-45 { width: 45%; }
  .ps-form .val-35 { width: 35%; }
  .ps-form .val-38 { width: 38%; }
  .ps-form .val-48 { width: 48%; }

  /* Items 4 & 5 */
  .ps-form .items-4-5-table { margin: 6px 0 2px; }
  .ps-form .col-50 { width: 50%; }
  .ps-form .val-90 { width: 90px; }

  /* Items 6 & 7 (signatures) */
  .ps-form .mt-4 { margin-top: 4px; }
  .ps-form .col-50-pr { width: 50%; padding-right: 16px; }
  .ps-form .col-50-pl { width: 50%; padding-left: 16px; }
  .ps-form .sig-caption { text-align: center; font-size: 7.5pt; }

  /* Item 8 */
  .ps-form .item8 { margin-top: 10px; font-size: 8.5pt; }
  .ps-form .approved-label { text-align: right; font-size: 8.5pt; margin-bottom: 2px; }

  .ps-form .rejected-box { margin-top: 10px; padding: 6px 8px; border: 1px solid #000; font-size: 8pt; }
  .ps-form .form-footer { font-size: 6.5pt; color: #666; text-align: center; margin-top: 8px; }
</style>

<div class="ps-form">

  {{-- HEADER --}}
  <table class="nb header-table">
    <tr>
      <td class="col-logo">
        @if($logoBase64)
          <img src="{{ $logoBase64 }}" alt="" class="logo-img">
        @endif
      </td>
      <td class="header-cell">
        <div class="header-country">Republic of the Philippines</div>
        <div class="header-agency-name">{{ $agencyName }}</div>
        <div class="header-agency-address">{{ $agencyAddress }}</div>
        <div class="header-title">PASS SLIP</div>
      </td>
      <td class="col-spacer"></td>
    </tr>
  </table>

  {{-- ITEM 1 --}}
  <table class="nb mb-2">
    <tr>
      <td class="col-18"><span class="lbl">1. ISSUED FOR:</span></td>
      <td class="col-32"><span class="cb {{ $chk($isOfficialActivity) }}"></span> OFFICIAL ACTIVITY</td>
      <td><span class="cb {{ $chk($isPersonalReason) }}"></span> PERSONAL REASONS</td>
    </tr>
  </table>

  {{-- ITEM 2 --}}
  <table class="nb mb-4">
    <tr>
      <td class="col-10"><span class="lbl">2. TO:</span></td>
      <td class="col-55"><span class="val-wide">{{ $employeeName }}</span></td>
      <td class="col-10-right"><span class="lbl">DATE:</span></td>
      <td><span class="val-wide">{{ $date }}</span></td>
    </tr>
  </table>

  {{-- ITEM 3 --}}
  <div class="mb-4">
    <span class="lbl">3.</span> You are hereby authorized to proceed to:
    <span class="val val-55">{{ $destination }}</span>
  </div>
  <div class="purpose-intro">for the purpose as indicated: (Check appropriate purpose)</div>

  <div class="purpose-row"><span class="lbl">A.</span></div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'coordinate_with') }}"></span> to coordinate with
    <span class="val val-45">{{ $purposeCategory === 'coordinate_with' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'meeting_conference') }}"></span> to attend meeting/conference
    <span class="val val-35">{{ $purposeCategory === 'meeting_conference' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'secure_documents') }}"></span> to secure documents &amp; others
    <span class="val val-38">{{ $purposeCategory === 'secure_documents' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'follow_up') }}"></span> to follow up
    <span class="val val-48">{{ $purposeCategory === 'follow_up' ? $purposeDetail : '' }}</span>
  </div>
  <div class="purpose-row"><span class="lbl">B.</span></div>
  <div class="purpose-row">
    <span class="cb {{ $chk($purposeCategory === 'personal_matter') }}"></span> to attend personal matter
    <span class="val val-45">{{ $purposeCategory === 'personal_matter' ? $purposeDetail : '' }}</span>
  </div>

  {{-- ITEMS 4 & 5 --}}
  <table class="nb items-4-5-table">
    <tr>
      <td class="col-50">
        <span class="lbl">4. Time of Departure from Office:</span>
        <span class="val val-90">{{ $timeOut }}</span>
        <span class="cb {{ $chk($timeOutPeriod === 'AM') }}"></span> AM
        <span class="cb {{ $chk($timeOutPeriod === 'PM') }}"></span> PM
      </td>
      <td>
        <span class="lbl">5. Time of Return:</span>
        <span class="val val-90">{{ $timeIn }}</span>
        <span class="cb {{ $chk($timeInPeriod === 'AM') }}"></span> AM
        <span class="cb {{ $chk($timeInPeriod === 'PM') }}"></span> PM
      </td>
    </tr>
  </table>

  {{-- ITEMS 6 & 7 (SIGNATURES) --}}
  <table class="nb mt-4">
    <tr>
      <td class="col-50-pr">
        <span class="lbl">6. Requested by:</span>
        <div class="sig"></div>
        <div class="sig-name">{{ $employeeName }}</div>
        <div class="sig-caption">Name and Signature of Employee</div>
      </td>
      <td class="col-50-pl">
        <span class="lbl">7. Recommending Approval:</span>
        <div class="sig"></div>
        @if($recommendedByName)
          <div class="sig-name">{{ $recommendedByName }}</div>
        @endif
        <div class="sig-caption">(Immediate Supervisor)</div>
      </td>
    </tr>
  </table>

  {{-- ITEM 8 --}}
  <div class="item8">
    <span class="lbl">8.</span> I Certify that the above mentioned personnel appeared on the date and purpose as indicated.
  </div>

  <table class="nb mt-4">
    <tr>
      <td class="col-50-pr">
        @if($hasReturned)
          <div class="sig"></div>
          <div class="sig-caption">Name and Signature</div>
        @endif
      </td>
      <td class="col-50-pl">
        <div class="approved-label">Approved:</div>
        <div class="sig"></div>
        @if($isApproved && $approverName)
          <div class="sig-name">{{ $approverName }}</div>
        @endif
        <div class="sig-caption">(Approving Officer)</div>
      </td>
    </tr>
  </table>

  @if($isRejected)
    <div class="rejected-box">
      <strong>Disapproved.</strong> {{ $remarks }}
    </div>
  @endif

  <div class="form-footer">
    Ref: {{ $slipNumber }} · Generated {{ $generatedDate }}
  </div>
</div>
