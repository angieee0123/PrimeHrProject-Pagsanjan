<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PRIME HRIS - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/admin/admin.css', 'resources/css/admin/adminDashboard.css', 'resources/css/admin/adminAttendance.css', 'resources/css/admin/adminRecruitment.css', 'resources/css/admin/adminTraining.css', 'resources/css/admin/adminPerformance.css', 'resources/css/admin/adminDepartment.css', 'resources/css/admin/employeeWizard.css', 'resources/css/admin/adminChatbot.css', 'resources/css/admin/adminPayroll.css', 'resources/css/topbarTheme.css'])
    @stack('styles')
    @php
        $__s = \App\Models\SystemAiSetting::current();
        $activeTheme = $__s->theme ?? 'default';
    @endphp
    <style>{!! \App\Services\SystemTheme::toCss($activeTheme, $__s->custom_theme_primary, $__s->theme_secondary, $__s->theme_accent, $__s->theme_muted) !!}</style>
</head>
<body>
    <div class="app-layout">
        {{-- Mobile Menu Button --}}
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        {{-- Mobile Overlay --}}
        <div class="mobile-overlay" id="mobile-overlay"></div>

        @include('admin.sidebar.adminSidebar')
        <main class="main-content">

            @yield('content')
        </main>
        {{-- The floating widget's FAB sits fixed bottom-right, on top of the
             full-page assistant's own send button — and showing both on the
             same page is redundant anyway, so it's skipped here. --}}
        @unless(Route::currentRouteName() === 'admin.ai-assistant')
        @include('admin.chatbot.adminChatbot')
        @endunless
        @include('admin.leaveCalendar.leaveCalendarFab')
        @include('admin.themeSettings.adminThemeSettings')
    </div>
    
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
