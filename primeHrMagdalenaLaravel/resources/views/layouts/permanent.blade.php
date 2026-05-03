<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Permanent Dashboard · PRIME HRIS')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/permanent.css'])
    @stack('styles')
</head>
<body>
    @yield('content')
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
