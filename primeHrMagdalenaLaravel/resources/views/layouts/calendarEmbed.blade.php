<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Leave & Travel Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Same core stylesheets the admin layout loads, so glass-shell / filter-card
         / btn-solid and the reused leave & travel modal styles all resolve. --}}
    @vite(['resources/css/app.css', 'resources/css/admin/admin.css', 'resources/css/admin/adminDashboard.css', 'resources/css/admin/adminLeaveAndBenefits.css', 'resources/css/travelOrder.css', 'resources/css/topbarTheme.css'])
    @stack('styles')
    {{-- The embed renders inside the admin modal, so it has to carry the same
         palette. It loads the themed stylesheets but is its own document, and
         a <style> block in the parent page does not reach into an iframe. --}}
    <style>{!! \App\Services\SystemTheme::activeCss() !!}</style>
    <style>
        /* The embed renders inside the modal iframe — flush, transparent, no page chrome.
           It fills the iframe height exactly and the calendar grid below flexes so the
           whole month is visible without scrolling.

           The grid's rows are `minmax(floor, auto)`: a row is as tall as the busiest
           date in it and shares out whatever height is left over. What stops that
           outgrowing the panel is shared/calendarFit.js, which measures this wrapper's
           grid and shows as many records per cell as every row can afford, naming the
           rest with a "+N" chip. So the month fits by *fitting*, not by being cropped.

           `overflow-y: auto` stays as the release valve for the two cases the fit pass
           cannot reach: JavaScript off, and a window so short that one record per cell
           still does not fit. A scroll is the honest failure there — a week row sliced
           in half at the bottom of the panel is not. It is a fallback, never the
           mechanism: if this is scrolling on an ordinary screen, the fit pass is what
           has gone wrong. */
        html, body { height: 100%; margin: 0; padding: 0; background: transparent; overflow: hidden; }
        body { font-family: 'Poppins', sans-serif; -webkit-font-smoothing: antialiased; }
        .lc-embed-wrap {
            height: 100%;
            box-sizing: border-box;
            padding: 18px 22px 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            /* Reserve the scrollbar's width whether or not it is showing. The
               fit pass measures this wrapper's grid, and without a stable
               gutter its own result changes the width it measured from — show
               everything to measure, a scrollbar appears, the grid narrows,
               the pass runs again on the narrower grid. A reserved gutter is
               what makes one pass settle. */
            scrollbar-gutter: stable;
            overscroll-behavior: contain;
        }
        @media (max-width: 720px) {
            .lc-embed-wrap { padding: 12px 13px 14px; }
        }
    </style>
</head>
<body>
    <div class="lc-embed-wrap">
        @yield('content')
    </div>
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
