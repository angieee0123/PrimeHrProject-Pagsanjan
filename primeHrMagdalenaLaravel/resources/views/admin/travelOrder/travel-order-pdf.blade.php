<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Travel Order — {{ $orderNumber }}</title>
  <style>
  /* Philippine long bond, 8.5 x 13 in — and no page margin, because the sheet
     itself is positioned at the template's absolute page coordinates. */
  @page { size: 8.5in 13in; margin: 0; }

  @php
    // dompdf reads a font off disk; it cannot fetch an asset URL, and it
    // refuses any path outside its chroot. EmbeddableFont resolves the face to
    // somewhere it will accept — see that class for why the office's Windows
    // fonts are cached rather than vendored. Declared only when a file was
    // found, so a font installed nowhere degrades to the CSS fallback instead
    // of erroring dompdf.
    $blackletter = \App\Support\EmbeddableFont::path('OLDENGL.TTF')
                ?? \App\Support\EmbeddableFont::path('UnifrakturMaguntia-Book.ttf');
  @endphp

  @if($blackletter)
  @font-face {
    font-family: 'FormBlackletter';
    font-style: normal; font-weight: normal;
    src: url('{{ $blackletter }}') format('truetype');
  }
  @endif

  body { margin: 0; padding: 0; }
  </style>
</head>
<body>
  @include('admin.travelOrder.partials.official-travel-order-form')
</body>
</html>
