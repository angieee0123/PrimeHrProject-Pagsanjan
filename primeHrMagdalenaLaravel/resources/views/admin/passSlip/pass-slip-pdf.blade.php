<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Pass Slip — {{ $slipNumber }}</title>
  <style>
  /* Philippine long bond, 8.5 x 13 in — and no page margin, because the sheet
     itself is positioned at the source document's absolute page coordinates. */
  @page { size: 8.5in 13in; margin: 0; }

  @php
    // dompdf reads a font off disk; it cannot fetch an asset URL, and it
    // refuses any path outside its chroot. EmbeddableFont resolves both faces
    // to somewhere it will accept — see that class for why the office's
    // Windows fonts are cached rather than vendored. Each face is declared
    // only when a file was found, so a font installed nowhere degrades to the
    // CSS fallback instead of erroring dompdf.
    $display     = \App\Support\EmbeddableFont::path('BRLNSDB.TTF');
    $blackletter = \App\Support\EmbeddableFont::path('OLDENGL.TTF')
                ?? \App\Support\EmbeddableFont::path('UnifrakturMaguntia-Book.ttf');
  @endphp

  @if($blackletter)
  @font-face {
    font-family: 'PassSlipBlackletter';
    font-style: normal; font-weight: normal;
    src: url('{{ $blackletter }}') format('truetype');
  }
  @endif

  @if($display)
  @font-face {
    font-family: 'PassSlipDisplay';
    font-style: normal; font-weight: bold;
    src: url('{{ $display }}') format('truetype');
  }
  @endif

  body { margin: 0; padding: 0; }
  </style>
</head>
<body>
  @include('admin.passSlip.partials.official-pass-slip-form')
</body>
</html>
