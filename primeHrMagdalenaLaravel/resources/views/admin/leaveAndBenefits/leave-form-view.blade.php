<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CS Form No. 6 — {{ $application->application_number }}</title>
  <style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: Arial, sans-serif;
    background: {{ request('embed') ? '#fff' : '#e8e8f0' }};
    padding: {{ request('embed') ? '8px' : '16px' }};
  }
  .form-page {
    max-width: 8.5in;
    margin: 0 auto;
    background: #fff;
    padding: 0.5in;
    box-shadow: 0 2px 12px rgba(0,0,0,0.12);
  }
  .toolbar {
    max-width: 8.5in;
    margin: 0 auto 12px;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }
  .toolbar button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .btn-print { background: #6366f1; color: #fff; }
  .btn-download { background: #10b981; color: #fff; }
  @media print {
    body { background: #fff; padding: 0; }
    .toolbar { display: none !important; }
    .form-page { box-shadow: none; padding: 0.4in; max-width: 100%; }
  }
  </style>
</head>
<body>
  @unless(request('embed'))
  <div class="toolbar">
    <button class="btn-print" onclick="window.print()">Print Form</button>
    <button class="btn-download" onclick="window.location.href='{{ route('admin.leave.download-form', $application->id) }}'">Download PDF</button>
  </div>
  @endunless
  <div class="form-page">
    @include('admin.leaveAndBenefits.partials.cs-form-no-6')
  </div>
</body>
</html>
