{{-- CS Form No. 6, Revised 2020 — Application for Leave (official layout) --}}
@php
  $app = $application;
  $loc = $app->leave_location;
  $sick = $app->sick_leave_type;
  $study = $app->study_leave_purpose;
  $chk = fn($on) => $on ? 'on' : '';
@endphp
<style>
  .cs-form {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5pt;
    color: #000;
    line-height: 1.3;
    background: #fff;
  }
  .cs-form table { width: 100%; border-collapse: collapse; }
  .cs-form td, .cs-form th { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
  .cs-form .nb td, .cs-form .nb th { border: none; }
  .cs-form .lbl { font-weight: bold; white-space: nowrap; }
  .cs-form .val { border-bottom: 1px solid #000; min-height: 14px; display: inline-block; min-width: 40px; padding: 0 2px; }
  .cs-form .val-block { border-bottom: 1px solid #000; min-height: 14px; display: block; padding: 1px 2px; }
  .cs-form .cb {
    display: inline-block; width: 10px; height: 10px; border: 1px solid #000;
    text-align: center; line-height: 10px; font-size: 8px; font-weight: bold;
    margin-right: 2px; vertical-align: middle;
  }
  .cs-form .cb.on::after { content: '✓'; }
  .cs-form .sec-hdr { background: #e8e8e8; font-weight: bold; font-size: 8.5pt; }
  .cs-form .lt-row { font-size: 7.5pt; padding: 2px 4px; line-height: 1.35; }
  .cs-form .lt-row td { border: none; padding: 1px 3px; vertical-align: top; }
  .cs-form .credits td, .cs-form .credits th { text-align: center; font-size: 8pt; padding: 2px 4px; }
  .cs-form .credits th { background: #f0f0f0; }
  .cs-form .stamp {
    border: 1px solid #000; width: 95px; height: 50px; font-size: 6.5pt;
    text-align: center; padding: 3px; line-height: 1.2;
  }
  .cs-form .sig { border-top: 1px solid #000; margin-top: 36px; padding-top: 2px; text-align: center; font-size: 7.5pt; font-weight: bold; }
  .cs-form .detail-line { font-size: 7.5pt; margin: 2px 0; }
  @media print { .cs-form { padding: 0; } }
</style>

<div class="cs-form">

  {{-- HEADER --}}
  <table class="nb" style="margin-bottom: 4px;">
    <tr>
      <td style="width: 100px; vertical-align: top;">
        <div class="stamp">Stamp of Date<br>of Receipt<br><strong style="font-size: 7pt;">{{ $receiptDate }}</strong></div>
      </td>
      <td style="text-align: center; vertical-align: top;">
        <div style="font-size: 8pt; font-weight: bold; letter-spacing: 0.5px;">APPLICATION FOR LEAVE</div>
        <div style="font-size: 8.5pt; margin-top: 2px;">Republic of the Philippines</div>
        <div style="font-size: 9pt; font-weight: bold;">{{ $agencyName }}</div>
        <div style="font-size: 8pt;">{{ $agencyAddress }}</div>
        <div style="font-size: 9pt; font-weight: bold; margin-top: 3px;">Civil Service Form No. 6</div>
        <div style="font-size: 8.5pt; font-weight: bold;">Revised 2020</div>
      </td>
      <td style="width: 100px; vertical-align: top; text-align: right;">
        @if($logoBase64)
          <img src="{{ $logoBase64 }}" alt="" style="height: 42px;">
        @endif
      </td>
    </tr>
  </table>

  {{-- ITEMS 1–5 --}}
  <table style="margin-bottom: 3px;">
    <tr>
      <td style="width: 20%;"><span class="lbl">1. OFFICE/DEPARTMENT</span></td>
      <td colspan="3"><span class="val-block">{{ $department }}</span></td>
    </tr>
    <tr>
      <td><span class="lbl">2. NAME:</span></td>
      <td style="width: 27%;">(Last) <span class="val-block">{{ strtoupper($employee->last_name) }}</span></td>
      <td style="width: 27%;">(First) <span class="val-block">{{ strtoupper($employee->first_name) }}</span></td>
      <td style="width: 26%;">(Middle) <span class="val-block">{{ strtoupper($employee->middle_name ?? '') }}</span></td>
    </tr>
    <tr>
      <td><span class="lbl">3. DATE OF FILING</span></td>
      <td><span class="val-block">{{ $app->created_at->format('F j, Y') }}</span></td>
      <td><span class="lbl">4. POSITION</span></td>
      <td><span class="val-block">{{ $designation }}</span></td>
    </tr>
    <tr>
      <td><span class="lbl">5. SALARY</span></td>
      <td colspan="3"><span class="val-block">{{ $salary }}</span></td>
    </tr>
  </table>

  {{-- SECTION 6 --}}
  <table style="margin-bottom: 3px;">
    <tr>
      <td colspan="2" class="sec-hdr">6. DETAILS OF APPLICATION</td>
    </tr>
    <tr>
      <td style="width: 50%; vertical-align: top; border-right: 1px solid #000;">
        <div class="lbl" style="margin-bottom: 3px;">6.A TYPE OF LEAVE TO BE AVAILED OF</div>
        <table class="lt-row" style="width: 100%;">
          @foreach($officialLeaveTypes as $code => $label)
            <tr>
              <td style="width: 14px; padding-top: 3px;">
                <span class="cb {{ $selectedLeaveCode === $code ? 'on' : '' }}"></span>
              </td>
              <td>{{ $label }}</td>
            </tr>
          @endforeach
          <tr>
            <td style="padding-top: 3px;"><span class="cb {{ $isOtherLeave ? 'on' : '' }}"></span></td>
            <td>
              Others: <span class="val" style="width: 55%;">{{ $otherLeaveLabel }}</span>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 50%; vertical-align: top;">
        <div class="lbl" style="margin-bottom: 3px;">6.B DETAILS OF LEAVE</div>

        <div class="detail-line">
          <strong>In case of Vacation/Special Privilege Leave:</strong><br>
          <span class="cb {{ $chk(in_array($selectedLeaveCode, ['VL','SPL']) && $loc === 'ph') }}"></span> Within the Philippines
          <span class="cb {{ $chk(in_array($selectedLeaveCode, ['VL','SPL']) && $loc === 'abroad') }}" style="margin-left: 8px;"></span> Abroad (Specify)
          <span class="val" style="width: 38%;">{{ $loc === 'abroad' ? $app->leave_location_specify : '' }}</span>
        </div>

        <div class="detail-line" style="margin-top: 4px;">
          <strong>In case of Sick Leave:</strong><br>
          <span class="cb {{ $chk($selectedLeaveCode === 'SL' && $sick === 'in_hospital') }}"></span> In Hospital (Specify Illness)
          <span class="val" style="width: 50%;">{{ $sick === 'in_hospital' ? $app->illness_specify : '' }}</span><br>
          <span class="cb {{ $chk($selectedLeaveCode === 'SL' && $sick === 'out_patient') }}"></span> Out Patient (Specify Illness)
          <span class="val" style="width: 48%;">{{ $sick === 'out_patient' ? $app->illness_specify : '' }}</span>
        </div>

        <div class="detail-line" style="margin-top: 4px;">
          <strong>In case of Special Leave Benefits for Women:</strong><br>
          (Specify Illness) <span class="val" style="width: 55%;">{{ in_array($selectedLeaveCode, ['SLBW','MCL']) ? $app->illness_specify : '' }}</span>
        </div>

        <div class="detail-line" style="margin-top: 4px;">
          <strong>In case of Study Leave:</strong><br>
          <span class="cb {{ $chk($selectedLeaveCode === 'STL' && $study === 'masters') }}"></span> Completion of Master's Degree
          <span class="cb {{ $chk($selectedLeaveCode === 'STL' && $study === 'bar_review') }}" style="margin-left: 6px;"></span> BAR/Board Examination Review<br>
          Other purpose: <span class="val" style="width: 45%;">{{ ($selectedLeaveCode === 'STL' && $study === 'other') ? $app->reason : '' }}</span>
        </div>

        <div class="detail-line" style="margin-top: 4px;">
          <strong>Other purpose:</strong>
          <span class="val-block" style="margin-top: 2px;">{{ $app->reason }}</span>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <span class="lbl">6.C NUMBER OF WORKING DAYS APPLIED FOR</span><br>
        <span class="val-block" style="margin-top: 3px; width: 80px;">{{ number_format($app->number_of_days, 0) == number_format($app->number_of_days, 1) ? number_format($app->number_of_days, 0) : number_format($app->number_of_days, 1) }}</span>
      </td>
      <td>
        <span class="lbl">6.D COMMUTATION</span><br>
        <div style="margin-top: 3px;">
          <span class="cb {{ $chk(!$app->commutation_requested) }}"></span> Not Requested &nbsp;&nbsp;
          <span class="cb {{ $chk($app->commutation_requested) }}"></span> Requested
        </div>
        <div style="margin-top: 5px;">
          <span class="lbl">INCLUSIVE DATES</span><br>
          <span class="val-block" style="margin-top: 2px;">{{ $inclusiveDates }}</span>
        </div>
      </td>
    </tr>
  </table>

  {{-- SECTION 7 --}}
  <table style="margin-bottom: 3px;">
    <tr>
      <td colspan="2" class="sec-hdr">7. DETAILS OF ACTION ON APPLICATION</td>
    </tr>
    <tr>
      <td style="width: 50%; vertical-align: top; border-right: 1px solid #000;">
        <div class="lbl">7.A CERTIFICATION OF LEAVE CREDITS</div>
        <div style="margin: 3px 0; font-size: 8pt;">As of <span class="val" style="width: 80px;">{{ $creditsAsOf }}</span></div>
        <table class="credits" style="margin-top: 2px;">
          <tr>
            <th style="width: 34%;"></th>
            <th>Vacation Leave</th>
            <th>Sick Leave</th>
          </tr>
          <tr>
            <td style="text-align: left; font-weight: bold;">Total Earned</td>
            <td>{{ $vlCredits['total_earned'] }}</td>
            <td>{{ $slCredits['total_earned'] }}</td>
          </tr>
          <tr>
            <td style="text-align: left; font-weight: bold;">Less this application</td>
            <td>{{ $vlCredits['less'] }}</td>
            <td>{{ $slCredits['less'] }}</td>
          </tr>
          <tr>
            <td style="text-align: left; font-weight: bold;">Balance</td>
            <td>{{ $vlCredits['balance'] }}</td>
            <td>{{ $slCredits['balance'] }}</td>
          </tr>
        </table>
      </td>
      <td style="width: 50%; vertical-align: top;">
        <div class="lbl">7.B RECOMMENDATION</div>
        <div style="margin-top: 6px;">
          <span class="cb {{ $chk($isApproved) }}"></span> For approval<br>
          <span class="cb {{ $chk($isRejected) }}" style="margin-top: 4px;"></span> For disapproval due to
          <span class="val" style="width: 50%;">{{ $isRejected ? $app->approver_remarks : '' }}</span>
        </div>
        @if($approverName)
          <div style="margin-top: 20px; font-size: 7.5pt; text-align: center;">{{ $approverName }}</div>
          <div style="font-size: 7pt; text-align: center;">(Authorized Officer)</div>
        @endif
      </td>
    </tr>
    <tr>
      <td>
        <div class="lbl">7.C APPROVED FOR:</div>
        <div style="margin-top: 4px; font-size: 8pt;">
          <span class="val" style="width: 35px;">{{ $daysWithPay !== '' ? number_format((float)$daysWithPay, 0) == number_format((float)$daysWithPay, 1) ? number_format((float)$daysWithPay, 0) : number_format((float)$daysWithPay, 1) : '' }}</span> days with pay<br>
          <span class="val" style="width: 35px; margin-top: 3px;">{{ $daysWithoutPay !== '' && (float)$daysWithoutPay > 0 ? number_format((float)$daysWithoutPay, 1) : '' }}</span> days without pay<br>
          <span class="val" style="width: 35px; margin-top: 3px;"></span> others (Specify)
          <span class="val" style="width: 45%;">{{ $approvedOtherSpecify }}</span>
        </div>
      </td>
      <td>
        <div class="lbl">7.D DISAPPROVED DUE TO:</div>
        <span class="val-block" style="margin-top: 4px; min-height: 30px;">{{ $isRejected ? $app->approver_remarks : '' }}</span>
      </td>
    </tr>
  </table>

  {{-- SIGNATURES --}}
  <table class="nb" style="margin-top: 10px;">
    <tr>
      <td style="width: 50%; padding-right: 20px;">
        <div class="sig">(Signature of Applicant)</div>
      </td>
      <td style="width: 50%; padding-left: 20px;">
        <div class="sig">(Authorized Official)</div>
        @if($approverName && $isApproved)
          <div style="text-align: center; font-size: 7.5pt; margin-top: -30px;">{{ $approverName }}</div>
        @endif
      </td>
    </tr>
  </table>

  <div style="font-size: 6.5pt; color: #666; text-align: center; margin-top: 6px;">
    Ref: {{ $app->application_number }} · Generated {{ $generatedDate }}
  </div>
</div>
