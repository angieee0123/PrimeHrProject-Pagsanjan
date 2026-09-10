<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- The kiosk's two POSTs are CSRF-exempt (see bootstrap/app.php), but the
         meta tag stays: it costs nothing, and it keeps this page working if the
         exemption is ever narrowed back. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Kiosk · HRIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @stack('styles')
    {{-- The active palette, injected exactly as the three signed-in layouts do
         it. The kiosk is anonymous but not unthemed: it stands in the same
         building, and a green-walled lobby with a navy kiosk in it looks like a
         different product. --}}
    <style>{!! \App\Services\SystemTheme::activeCss() !!}</style>
</head>
<body class="kiosk-body">
    @yield('content')

    @stack('scripts')
</body>
</html>
