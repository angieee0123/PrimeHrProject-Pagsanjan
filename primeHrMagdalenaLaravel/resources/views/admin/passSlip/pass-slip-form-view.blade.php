<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pass Slip — {{ $slipNumber }}</title>
  <style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  {{-- Optional faces, served over HTTP. A workstation that already has
       Berlin Sans FB Demi or Old English Text MT installed uses those first
       (see the font stacks in the form partial). --}}
  @if(is_file(public_path('fonts/UnifrakturMaguntia-Book.ttf')))
  @font-face {
    font-family: 'PassSlipBlackletter';
    font-style: normal; font-weight: normal;
    src: url('{{ asset('fonts/UnifrakturMaguntia-Book.ttf') }}') format('truetype');
  }
  @endif
  @if(is_file(public_path('fonts/BRLNSDB.TTF')))
  @font-face {
    font-family: 'PassSlipDisplay';
    font-style: normal; font-weight: bold;
    src: url('{{ asset('fonts/BRLNSDB.TTF') }}') format('truetype');
  }
  @endif

  body {
    font-family: Arial, sans-serif;
    background: {{ request('embed') ? '#fff' : '#e8e8f0' }};
    padding: {{ request('embed') ? '8px' : '16px' }};
  }

  /* The sheet is a fixed 612 x 936 pt block; this just centres it and gives it
     a page edge on screen. It must not be scaled — the form is a tracing. */
  .form-page {
    width: 612pt;
    margin: 0 auto;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.12);
  }
  .toolbar {
    width: 612pt;
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
  }
  .btn-print { background: #6366f1; color: #fff; }
  .btn-download { background: #10b981; color: #fff; }

  @page { size: 8.5in 13in; margin: 0; }
  @media print {
    body { background: #fff; padding: 0; }
    .toolbar { display: none !important; }
    .form-page { box-shadow: none; margin: 0; }
  }
  </style>
</head>
<body>
  @unless(request('embed'))
  <div class="toolbar">
    <button class="btn-print" onclick="window.print()">Print Form</button>
    <button class="btn-download" onclick="window.location.href='{{ route('admin.passslip.download-form', $slip->id) }}'">Download PDF</button>
  </div>
  @endunless
  <div class="form-page">
    @include('admin.passSlip.partials.official-pass-slip-form')
  </div>
</body>
</html>
