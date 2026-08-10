<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PRIME HRIS - Mayor\'s View')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/admin/admin.css', 'resources/css/admin/adminDashboard.css', 'resources/css/topbarTheme.css', 'resources/css/mayor/mayor.css'])
    @stack('styles')
    {{-- The active palette. Organisation-wide: the theme lives on
         system_ai_settings and there is no per-user theme, so this is
         the same block for every viewer. Resolved in one place so the
         layouts cannot drift on how it is assembled. --}}
    <style>{!! \App\Services\SystemTheme::activeCss() !!}</style>
</head>
<body>
    <div class="app-layout">
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        <div class="mobile-overlay" id="mobile-overlay"></div>

        @include('mayor.sidebar.mayorSidebar')
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
