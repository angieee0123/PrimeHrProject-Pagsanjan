@extends('layouts.app')

@section('content')
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
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Deduction Types</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['total_types'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">{{ $stats['mandatory_count'] }} mandatory, {{ $stats['loan_count'] }} loans</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active Loans</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['active_loans'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">{{ $stats['active_loans'] > 0 ? 'Ongoing loans' : 'No active loans' }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Outstanding</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text>
                </svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">₱{{ number_format($stats['total_outstanding'], 2) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Loan balances</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Transactions</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['transactions_this_month'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This month</p>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
    <button class="tab-btn active" onclick="switchTab('deduction-types')">Deduction Types</button>
    <button class="tab-btn" onclick="switchTab('employee-deductions')">Employee Deductions</button>
    <button class="tab-btn" onclick="switchTab('loans')">Loans</button>
    <button class="tab-btn" onclick="switchTab('schedules')">Schedules</button>
    <button class="tab-btn" onclick="switchTab('loan-types')">Loan Types</button>
    <button class="tab-btn" onclick="switchTab('transactions')">Transactions</button>
</div>

@include('admin.deductions.partials.deduction-types')

@include('admin.deductions.partials.employee-deductions')

@include('admin.deductions.partials.loans')

@include('admin.deductions.partials.schedules')

@include('admin.deductions.partials.loan-types')

@include('admin.deductions.partials.transactions')

<style>
.modal-overlay.active { display: flex !important; align-items: center; justify-content: center; }
</style>

@push('scripts')
<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById(tabName + '-tab').style.display = 'block';
    }
</script>
@endpush
@endsection
