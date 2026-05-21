<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PRIME HRIS - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/css/adminDashboard.css', 'resources/css/adminAttendance.css', 'resources/css/adminRecruitment.css', 'resources/css/adminTraining.css', 'resources/css/adminPerformance.css', 'resources/css/adminDepartment.css', 'resources/css/employeeWizard.css', 'resources/css/adminChatbot.css', 'resources/css/adminPayroll.css'])
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
    
    <!-- Performance Measurement Widget -->
    <div id="performanceMetrics" style="position: fixed; bottom: 20px; right: 20px; background: #0b044d; color: white; padding: 12px 16px; border-radius: 8px; font-size: 12px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 200px;">
        <div style="font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <span>⚡</span>
            <span>Page Performance</span>
            <button onclick="document.getElementById('performanceMetrics').style.display='none'" style="margin-left: auto; background: none; border: none; color: white; cursor: pointer; font-size: 16px; line-height: 1;" title="Hide">&times;</button>
        </div>
        <div style="font-size: 11px; opacity: 0.9;">Backend: <strong id="backendTime">...</strong></div>
        <div style="font-size: 11px; opacity: 0.9;">Frontend: <strong id="frontendTime">...</strong></div>
        <div style="font-size: 11px; margin-top: 4px; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.2);">Total: <strong id="totalTime" style="font-size: 13px;">...</strong></div>
    </div>
    
    <script>
    // Global Performance Measurement
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (window.performance && window.performance.timing) {
                const perfData = window.performance.timing;
                const navigationStart = perfData.navigationStart;
                const responseEnd = perfData.responseEnd;
                const loadEventEnd = perfData.loadEventEnd;
                
                // Only calculate if we have valid data
                if (navigationStart > 0 && responseEnd > 0 && loadEventEnd > 0) {
                    const backendTime = responseEnd - navigationStart;
                    const frontendTime = loadEventEnd - responseEnd;
                    const totalTime = loadEventEnd - navigationStart;
                    
                    document.getElementById('backendTime').textContent = (backendTime / 1000).toFixed(2) + 's';
                    document.getElementById('frontendTime').textContent = (frontendTime / 1000).toFixed(2) + 's';
                    document.getElementById('totalTime').textContent = (totalTime / 1000).toFixed(2) + 's';
                    
                    // Color code based on performance
                    const totalEl = document.getElementById('totalTime');
                    if (totalTime > 3000) {
                        totalEl.style.color = '#ff6b6b'; // Red - slow
                    } else if (totalTime > 1500) {
                        totalEl.style.color = '#ffd93d'; // Yellow - moderate
                    } else {
                        totalEl.style.color = '#6bcf7f'; // Green - fast
                    }
                    
                    // Log to console
                    console.log('=== PAGE PERFORMANCE ===');
                    console.log('Page:', window.location.pathname);
                    console.log('Backend:', (backendTime / 1000).toFixed(2) + 's');
                    console.log('Frontend:', (frontendTime / 1000).toFixed(2) + 's');
                    console.log('Total:', (totalTime / 1000).toFixed(2) + 's');
                } else {
                    document.getElementById('backendTime').textContent = 'N/A';
                    document.getElementById('frontendTime').textContent = 'N/A';
                    document.getElementById('totalTime').textContent = 'N/A';
                }
            }
        }, 100);
    });
    </script>
    
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
