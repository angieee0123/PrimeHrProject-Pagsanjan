<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PRIME HRIS - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
    @stack('styles')
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
        @include('admin.chatbot.adminChatbot')
        @include('admin.themeSettings.adminThemeSettings')
    </div>
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
