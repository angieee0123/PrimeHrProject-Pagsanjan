@extends('layouts.app')

@section('content')
@include('admin.topbar.attendanceTopbar')
@php
$avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

$startDateDisplay = request('start_date', now()->startOfMonth()->format('Y-m-d'));
$endDateDisplay = request('end_date', now()->endOfMonth()->format('Y-m-d'));
$periodDisplay = date('M d, Y', strtotime($startDateDisplay)) . ' - ' . date('M d, Y', strtotime($endDateDisplay));
@endphp

<div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">DTR Submitted</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $completeCount }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">{{ $incompleteCount }} incomplete</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Present</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPresent }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">{{ $periodDisplay }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Absences</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalAbsent }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Across all personnel</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Overtime Hours</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalOT }} hrs</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">{{ $totalLate }} late arrivals</p>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
    <button class="tab-btn active" onclick="switchTab('summary')">Attendance Summary</button>
    <button class="tab-btn" onclick="switchTab('detailed')">Detailed Time Record</button>
</div>

@include('admin.attendance.partials.attendance-summary-tab')

@include('admin.attendance.partials.detailed-time-record-tab')

@include('admin.attendance.modals.dtrDetailModal')
@include('admin.attendance.modals.detailedDtrModal')
@include('admin.attendance.modals.editDtrModal')
@include('admin.attendance.modals.correctAttendanceModal')
@include('admin.attendance.modals.successModal')

@push('styles')
@vite('resources/css/adminAttendance.css')
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.periodDisplay = '{{ $periodDisplay }}';
    window.periodDisplayFile = '{{ str_replace([' ', ',', '-'], '_', $periodDisplay) }}';

    // Tab switching functionality
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById(tabName + '-tab').style.display = 'block';
    }

    // Check URL parameter and switch to correct tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        
        if (activeTab === 'detailed') {
            // Switch to detailed tab
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
            
            document.querySelectorAll('.tab-btn')[1].classList.add('active');
            document.getElementById('detailed-tab').style.display = 'block';
        }
    });

    // Search functionality
    function searchAttendance(query) {
        const searchTerm = query.toLowerCase().trim();
        const tbody = document.querySelector('.payroll-table tbody');
        if (!tbody) return;

        if (!window.allAttendanceRows || window.allAttendanceRows.length === 0) {
            window.allAttendanceRows = Array.from(tbody.querySelectorAll('tr'));
        }

        const filtered = window.allAttendanceRows.filter(row => {
            const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
            const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
            const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
            return searchTerm === '' || name.includes(searchTerm) || id.includes(searchTerm) || dept.includes(searchTerm);
        });

        tbody.innerHTML = '';
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: #6b6a8a;">No records found matching your search.</td></tr>';
        } else {
            filtered.forEach(row => tbody.appendChild(row.cloneNode(true)));
        }
    }
</script>
@vite('resources/js/adminAttendance.js')
@endpush
@endsection

