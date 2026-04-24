<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PRIME HRIS - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body>
    <div class="app-layout">
        @include('admin.sidebar.adminSidebar')
        <main class="main-content">
            @include('admin.topbar.adminTopbar')
            @yield('content')
        </main>
        @include('admin.chatbot.adminChatbot')
        @include('admin.themeSettings.adminThemeSettings')
    </div>
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
