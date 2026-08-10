<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Permanent Dashboard · PRIME HRIS')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/employee/employee.css', 'resources/css/topbarTheme.css'])
    @stack('styles')
    {{-- The active palette. Organisation-wide: the theme lives on
         system_ai_settings and there is no per-user theme, so this is
         the same block for every viewer. Resolved in one place so the
         layouts cannot drift on how it is assembled. --}}
    <style>{!! \App\Services\SystemTheme::activeCss() !!}</style>
</head>
<body>
    @yield('content')
    @auth
        @include('employee.leaveCalendar.leaveCalendarFab')
    @endauth
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
