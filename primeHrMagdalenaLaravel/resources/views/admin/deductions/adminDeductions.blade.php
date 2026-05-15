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
        <h2 class="stat-value">8</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">4 mandatory, 4 loans</p>
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
        <h2 class="stat-value">0</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">No active loans</p>
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
        <h2 class="stat-value" style="font-size: 18px;">₱0.00</h2>
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
        <h2 class="stat-value">0</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This month</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Management</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Manage employee deductions, contributions, and loans</p>
        </div>
    </div>

    <div style="display: flex; gap: 8px; margin: 0 24px 20px 24px; border-bottom: 2px solid #f0effe; padding-bottom: 0;">
        <button class="tab-btn active" data-tab="deduction-types">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="2"/>
                <line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
            Deduction Types
        </button>
        <button class="tab-btn" data-tab="employee-deductions">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            Employee Deductions
        </button>
        <button class="tab-btn" data-tab="loans">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
            </svg>
            Loans
        </button>
        <button class="tab-btn" data-tab="schedules">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Schedules
        </button>
        <button class="tab-btn" data-tab="transactions">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Transactions
        </button>
    </div>

    <div class="tab-content active" id="deduction-types">
        @include('admin.deductions.partials.deduction-types')
    </div>

    <div class="tab-content" id="employee-deductions">
        @include('admin.deductions.partials.employee-deductions')
    </div>

    <div class="tab-content" id="loans">
        @include('admin.deductions.partials.loans')
    </div>

    <div class="tab-content" id="schedules">
        @include('admin.deductions.partials.schedules')
    </div>

    <div class="tab-content" id="transactions">
        @include('admin.deductions.partials.transactions')
    </div>
</section>

<style>
.tab-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    background: transparent;
    color: #6b6a8a;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    font-family: 'Poppins', sans-serif;
}

.tab-btn:hover {
    color: #0b044d;
    background: #f7f6ff;
}

.tab-btn.active {
    color: #0b044d;
    border-bottom-color: #0b044d;
    background: #fff;
}

.tab-btn svg {
    flex-shrink: 0;
}

.tab-content {
    display: none;
    padding: 24px;
}

.tab-content.active {
    display: block;
}

@media (max-width: 768px) {
    .tab-btn {
        padding: 10px 14px;
        font-size: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
});
</script>
@endsection
