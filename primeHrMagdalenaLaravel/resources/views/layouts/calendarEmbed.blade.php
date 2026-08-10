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
           It fills the iframe height exactly and never scrolls: the calendar grid below
           flexes so the whole month is always visible. */
        html, body { height: 100%; margin: 0; padding: 0; background: transparent; overflow: hidden; }
        body { font-family: 'Poppins', sans-serif; -webkit-font-smoothing: antialiased; }
        .lc-embed-wrap {
            height: 100%;
            box-sizing: border-box;
            padding: 18px 22px 20px;
            display: flex;
            flex-direction: column;
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
