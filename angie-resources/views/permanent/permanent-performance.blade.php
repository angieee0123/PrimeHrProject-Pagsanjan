@extends('layouts.app')

@section('title', 'Performance · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<style>
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 14px; padding: 18px; border: 1.5px solid #e5e4f0; }
        .stat-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .stat-label { font-size: 12px; color: #9999bb; font-weight: 600; }
        .stat-icon-wrap { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .stat-value { font-size: 22px; font-weight: 800; color: #0b044d; margin: 0 0 6px; }
        .stat-footer { display: flex; align-items: center; gap: 6px; }
        .stat-dot { width: 6px; height: 6px; border-radius: 50%; }
        .stat-sub { font-size: 11px; color: #9999bb; margin: 0; }
        
        .table-section { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; margin-bottom: 20px; overflow: hidden; }
        .table-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #e5e4f0; }
        .table-title { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0 0 2px; }
        .table-sub { font-size: 12px; color: #9999bb; margin: 0; }
        .table-actions { display: flex; gap: 10px; }
        .btn-export { padding: 8px 14px; border-radius: 8px; border: none; background: linear-gradient(135deg,#0b044d,#1a0f6e); font-size: 12px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .table-wrapper { overflow-x: auto; }
        .payroll-table { width: 100%; border-collapse: collapse; }
        .payroll-table th { text-align: left; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #9999bb; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e4f0; }
        .payroll-table td { padding: 14px 16px; font-size: 13px; color: #0b044d; border-bottom: 1px solid #f4f3ff; }
        .badge-status { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; display: inline-block; }
        .badge-status.on-hold { background: #f0effe; color: #0b044d; border: 1px solid #e5e4f0; }
        .badge-status.processed { background: #e8f9ef; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-emptype { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 20px; background: #f0effe; color: #0b044d; }
        .dept-tag { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 20px; background: #f0effe; color: #0b044d; }
        .btn-view { padding: 7px 14px; border-radius: 8px; border: 1.5px solid #e5e4f0; background: #fff; font-size: 12px; font-weight: 600; color: #6b6a8a; cursor: pointer; }
        .btn-view:hover { border-color: #0b044d; color: #0b044d; }
        .btn-edit { padding: 7px 14px; border-radius: 8px; border: none; background: linear-gradient(135deg,#0b044d,#1a0f6e); font-size: 12px; font-weight: 600; color: #fff; cursor: pointer; }
        .table-footer { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e5e4f0; background: #faf9ff; }
        .table-footer p { font-size: 12px; color: #6b6a8a; margin: 0; }
        
        .training-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; padding: 16px 20px; }
        .training-card { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; padding: 20px; padding-top: 48px; position: relative; }
        .card-header { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
        .card-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
        .card-header > div { flex: 1; min-width: 0; }
        .card-title { font-size: 14px; font-weight: 700; color: #0b044d; margin: 0; line-height: 1.3; word-wrap: break-word; }
        .card-sub { font-size: 11px; color: #9999bb; margin: 4px 0 0; }
        .card-desc { font-size: 12.5px; color: #6b6a8a; line-height: 1.6; margin-bottom: 12px; }
        .card-footer { border-top: 1px solid #f0effe; padding-top: 12px; display: flex; align-items: center; gap: 8px; }
        
        .goals-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; padding: 16px 20px; }
        .goal-card { background: #fff; border-radius: 14px; border: 1.5px solid #e5e4f0; padding: 20px; cursor: pointer; }
        .goal-card:hover { border-color: #0b044d; }
        .goal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .goal-header > div { flex: 1; }
        .goal-id { font-size: 10px; color: #9999bb; font-weight: 600; margin-bottom: 4px; display: block; }
        .goal-title { font-size: 14px; font-weight: 700; color: #0b044d; margin-bottom: 4px; }
        .goal-category { font-size: 12px; color: #6b6a8a; }
        .progress-bar { margin-bottom: 12px; }
        .progress-label { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 10px; }
        .progress-label span:first-child { color: #9999bb; font-weight: 600; }
        .progress-fill { height: 6px; background: #f0effe; border-radius: 99px; overflow: hidden; }
        .goal-footer { border-top: 1px solid #f0effe; padding-top: 12px; }
        .goal-footer p:first-child { font-size: 10px; color: #9999bb; margin-bottom: 2px; }
        .goal-footer p:last-child { font-size: 12px; font-weight: 600; color: #0b044d; }
        
        .chart-container { display: flex; align-items: flex-end; gap: 16px; height: 200px; padding: 28px 32px; }
        .chart-bar { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .chart-bar-fill { width: 100%; display: flex; flex-direction: column; justify-content: flex-end; height: 100%; }
        .chart-bar-content { width: 100%; border-radius: 8px 8px 0 0; display: flex; align-items: flex-start; justify-content: center; padding-top: 8px; min-height: 40px; }
        .chart-bar-content span { font-size: 13px; font-weight: 700; color: #fff; }
        .chart-bar-label { text-align: center; }
        .chart-bar-label p { font-size: 11px; color: #6b6a8a; font-weight: 600; white-space: nowrap; }
        
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(11, 4, 77, 0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; opacity: 0; visibility: hidden; transition: all 0.2s; }
        .modal-overlay.show { opacity: 1; visibility: visible; }
        .modal-box { background: #fff; border-radius: 16px; width: 90%; max-width: 520px; max-height: 90vh; overflow: hidden; transform: scale(0.95); transition: transform 0.2s; }
        .modal-overlay.show .modal-box { transform: scale(1); }
        .modal-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px; border-bottom: 1px solid #e5e4f0; }
        .pmodal-hero { display: flex; align-items: center; gap: 14px; }
        .pmodal-hero-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .modal-eyebrow { font-size: 10px; font-weight: 700; color: #9999bb; letter-spacing: 1px; text-transform: uppercase; }
        .modal-title { font-size: 18px; font-weight: 700; color: #0b044d; margin: 4px 0; }
        .modal-sub { font-size: 12px; color: #6b6a8a; margin: 0; }
        .modal-close { background: none; border: none; cursor: pointer; color: #9999bb; padding: 4px; }
        .modal-body { padding: 0 24px 20px; }
        .pmodal-badges { display: flex; gap: 6px; margin-top: 8px; }
        .rating-box { background: #f7f6ff; border-radius: 12px; padding: 18px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px; }
        .rating-box-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .rating-box-icon span { font-size: 20px; font-weight: 800; color: #fff; }
        .rating-box p:first-child { font-size: 11px; color: #9999bb; margin-bottom: 4px; font-weight: 600; letter-spacing: 0.8px; }
        .rating-box p:last-child { font-size: 16px; font-weight: 800; color: #0b044d; }
        .modal-section-label { font-size: 10.5px; font-weight: 700; color: #aaa8cc; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
        .modal-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f4f3ff; font-size: 13px; }
        .modal-row span:first-child { color: #6b6a8a; }
        .modal-row strong { color: #0b044d; }
        .strengths { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .strength-tag { font-size: 10px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
        .modal-progress { margin-bottom: 20px; }
        .modal-progress-label { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .modal-progress-label span:first-child { font-size: 11px; color: #9999bb; font-weight: 600; letter-spacing: 0.8px; }
        .modal-progress-label span:last-child { font-size: 11px; font-weight: 700; }
        .modal-footer { display: flex; justify-content: space-between; padding: 16px 24px; border-top: 1px solid #e5e4f0; }
        .modal-btn-ghost { padding: 10px 20px; border-radius: 9px; border: 1.5px solid #e5e4f0; background: #fff; font-size: 13px; font-weight: 600; color: #6b6a8a; cursor: pointer; }
        .modal-btn-primary { padding: 10px 20px; border-radius: 9px; border: none; background: linear-gradient(135deg,#0b044d,#1a0f6e); font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .row-actions { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
        .btn-download { padding:7px 14px; border-radius:8px; border:none; background:linear-gradient(135deg,#0b044d,#1a0f6e); font-size:12px; font-weight:600; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
        .btn-download:hover { opacity:0.9; }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card { padding: 14px; }
            .stat-value { font-size: 18px; }
            .stat-label { font-size: 11px; }
            .stat-icon-wrap { width: 32px; height: 32px; }
            .stat-icon-wrap svg { width: 15px; height: 15px; }
            
            .training-cards { grid-template-columns: 1fr; gap: 12px; padding: 12px 16px; }
            .training-card { padding: 16px; padding-top: 44px; }
            
            .goals-grid { grid-template-columns: 1fr; gap: 12px; padding: 12px 16px; }
            .goal-card { padding: 16px; }
            
            .chart-container { padding: 20px 16px; height: 180px; gap: 12px; }
            .chart-bar-content span { font-size: 12px; }
            .chart-bar-label p { font-size: 10px; }
            
            .table-header { flex-direction: column; align-items: flex-start; gap: 12px; padding: 14px 16px; }
            .table-actions { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
            .btn-export { font-size: 11px; padding: 7px 12px; }
            
            .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .payroll-table { min-width: 700px; }
            .payroll-table th { font-size: 10px; padding: 10px 12px; }
            .payroll-table td { font-size: 12px; padding: 12px; }
            
            .modal-box { max-width: 95%; margin: 0 10px; }
            .modal-header { padding: 16px 18px; }
            .pmodal-hero { gap: 10px; }
            .pmodal-hero-icon { width: 44px; height: 44px; }
            .modal-title { font-size: 16px; }
            .modal-body { padding: 0 18px 16px; }
            .modal-footer { padding: 12px 18px; flex-wrap: wrap; }
            .modal-btn-ghost, .modal-btn-primary { flex: 1; min-width: 120px; justify-content: center; }
        }
        
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; gap: 10px; }
            .stat-card { padding: 12px; }
            .stat-value { font-size: 16px; }
            .stat-label { font-size: 10px; }
            
            .welcome-banner { flex-direction: column; align-items: flex-start; gap: 12px; }
            .banner-right { width: 100%; flex-wrap: wrap; gap: 8px; }
            
            .chart-container { flex-wrap: wrap; height: auto; }
            .chart-bar { min-width: calc(50% - 8px); height: 150px; }
            
            .card-title { font-size: 13px; }
            .card-desc { font-size: 12px; }
            .goal-title { font-size: 13px; }
            
            .table-title { font-size: 13px; }
            .table-sub { font-size: 11px; }
            
            .btn-view, .btn-edit { font-size: 11px; padding: 6px 10px; }
            .row-actions { flex-direction:column; gap:4px; }
            .btn-view, .btn-download { width:100%; justify-content:center; font-size:11px; padding:6px 10px; }
            .modal-btn-ghost, .modal-btn-primary { width: 100%; }
        }
</style>
@endpush

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('permanent.permanent-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('permanent.permanent-notification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div>
                    <h2>Performance Overview</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Latest Rating</p>
                        <div class="stat-icon-wrap" style="background:#0b044d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                    </div>
                    <h2 class="stat-value">4.8</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#0b044d"></span>
                        <p class="stat-sub">Jan-Jun 2025</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Average Rating</p>
                        <div class="stat-icon-wrap" style="background:#15803d15"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 18"/></svg></div>
                    </div>
                    <h2 class="stat-value">4.7</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#15803d"></span>
                        <p class="stat-sub">All evaluations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Total Evaluations</p>
                        <div class="stat-icon-wrap" style="background:#d9bb0015"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
                    </div>
                    <h2 class="stat-value">4</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#d9bb00"></span>
                        <p class="stat-sub">Completed reviews</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Goals Achieved</p>
                        <div class="stat-icon-wrap" style="background:#8e1e1815"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></div>
                    </div>
                    <h2 class="stat-value">1</h2>
                    <div class="stat-footer">
                        <span class="stat-dot" style="background:#8e1e18"></span>
                        <p class="stat-sub">4 total goals</p>
                    </div>
                </div>
            </div>
            
            <section class="table-section" style="margin-bottom:24px;">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Training Impact on Performance</h3>
                        <p class="table-sub">How completed training programs have influenced your performance metrics</p>
                    </div>
                </div>
                <div class="training-cards">
                    <div class="training-card">
                        <span class="badge-status on-hold" style="position:absolute;top:16px;right:16px;">Completed</span>
                        <div class="card-header">
                            <div class="card-icon" style="background:linear-gradient(135deg, #15803d, #166534);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                            <div>
                                <h4 class="card-title">Customer Service Excellence</h4>
                                <p class="card-sub">Completed: May 20, 2025</p>
                            </div>
                        </div>
                        <p class="card-desc">Improved team collaboration score by 25%</p>
                        <div class="card-footer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span style="font-size:13px;font-weight:700;color:#0b044d;">4.9</span>
                            <span style="font-size:11px;color:#9999bb;">Training Rating</span>
                        </div>
                    </div>
                    <div class="training-card">
                        <span class="badge-status on-hold" style="position:absolute;top:16px;right:16px;">Completed</span>
                        <div class="card-header">
                            <div class="card-icon" style="background:linear-gradient(135deg, #15803d, #166534);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                            <div>
                                <h4 class="card-title">Digital Literacy Training</h4>
                                <p class="card-sub">Completed: Apr 15, 2025</p>
                            </div>
                        </div>
                        <p class="card-desc">Enhanced technical proficiency and workflow efficiency</p>
                        <div class="card-footer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span style="font-size:13px;font-weight:700;color:#0b044d;">4.7</span>
                            <span style="font-size:11px;color:#9999bb;">Training Rating</span>
                        </div>
                    </div>
                    <div class="training-card">
                        <span class="badge-status processed" style="position:absolute;top:16px;right:16px;">In Progress</span>
                        <div class="card-header">
                            <div class="card-icon" style="background:linear-gradient(135deg, #d9bb00, #fbbf24);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                            <div>
                                <h4 class="card-title">Leadership Development Program</h4>
                                <p class="card-sub">Completed: In Progress</p>
                            </div>
                        </div>
                        <p class="card-desc">Developing management and decision-making skills</p>
                    </div>
                </div>
            </section>
            
            <section class="table-section" style="margin-bottom:24px;">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Performance Trend</h3>
                        <p class="table-sub">Your rating history over time</p>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content" style="background:linear-gradient(180deg, #15803d, #15803d99);height:90%;">
                                <span>4.5</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jul-Dec 2023</p>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content" style="background:linear-gradient(180deg, #15803d, #15803d99);height:92%;">
                                <span>4.6</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jan-Jun 2024</p>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content" style="background:linear-gradient(180deg, #15803d, #15803d99);height:94%;">
                                <span>4.7</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jul-Dec 2024</p>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content" style="background:linear-gradient(180deg, #15803d, #15803d99);height:96%;">
                                <span>4.8</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jan-Jun 2025</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <section class="table-section" style="margin-bottom:24px;">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Performance Goals</h3>
                        <p class="table-sub">Track your progress towards set objectives linked to training programs</p>
                    </div>
                </div>
                <div class="goals-grid">
                    <div class="goal-card" onclick="openGoal('Complete Advanced Leadership Training', 'Professional Development', 65, 'Jul 15, 2025', 'In Progress')">
                        <div class="goal-header">
                            <div>
                                <span class="goal-id">GOAL-001</span>
                                <h4 class="goal-title">Complete Advanced Leadership Training</h4>
                                <p class="goal-category">Professional Development</p>
                            </div>
                            <span class="badge-status processed">In Progress</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-label"><span>PROGRESS</span><span style="color:#d9bb00">65%</span></div>
                            <div class="progress-fill"><div style="height:100%;width:65%;background:linear-gradient(90deg, #d9bb00, #fbbf24);border-radius:99px;"></div></div>
                        </div>
                        <div class="goal-footer">
                            <p>Target Date</p>
                            <p>Jul 15, 2025</p>
                        </div>
                    </div>
                    <div class="goal-card" onclick="openGoal('Improve Team Collaboration Metrics', 'Teamwork', 100, 'Jun 30, 2025', 'Achieved')">
                        <div class="goal-header">
                            <div>
                                <span class="goal-id">GOAL-002</span>
                                <h4 class="goal-title">Improve Team Collaboration Metrics</h4>
                                <p class="goal-category">Teamwork</p>
                            </div>
                            <span class="badge-status on-hold">Achieved</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-label"><span>PROGRESS</span><span style="color:#15803d">100%</span></div>
                            <div class="progress-fill"><div style="height:100%;width:100%;background:linear-gradient(90deg, #15803d, #166534);border-radius:99px;"></div></div>
                        </div>
                        <div class="goal-footer">
                            <p>Target Date</p>
                            <p>Jun 30, 2025</p>
                        </div>
                    </div>
                    <div class="goal-card" onclick="openGoal('Reduce Processing Time by 20%', 'Efficiency', 45, 'Dec 31, 2025', 'In Progress')">
                        <div class="goal-header">
                            <div>
                                <span class="goal-id">GOAL-003</span>
                                <h4 class="goal-title">Reduce Processing Time by 20%</h4>
                                <p class="goal-category">Efficiency</p>
                            </div>
                            <span class="badge-status processed">In Progress</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-label"><span>PROGRESS</span><span style="color:#d9bb00">45%</span></div>
                            <div class="progress-fill"><div style="height:100%;width:45%;background:linear-gradient(90deg, #d9bb00, #fbbf24);border-radius:99px;"></div></div>
                        </div>
                        <div class="goal-footer">
                            <p>Target Date</p>
                            <p>Dec 31, 2025</p>
                        </div>
                    </div>
                    <div class="goal-card" onclick="openGoal('Complete Safety Certification', 'Compliance', 30, 'Aug 30, 2025', 'In Progress')">
                        <div class="goal-header">
                            <div>
                                <span class="goal-id">GOAL-004</span>
                                <h4 class="goal-title">Complete Safety Certification</h4>
                                <p class="goal-category">Compliance</p>
                            </div>
                            <span class="badge-status processed">In Progress</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-label"><span>PROGRESS</span><span style="color:#d9bb00">30%</span></div>
                            <div class="progress-fill"><div style="height:100%;width:30%;background:linear-gradient(90deg, #d9bb00, #fbbf24);border-radius:99px;"></div></div>
                        </div>
                        <div class="goal-footer">
                            <p>Target Date</p>
                            <p>Aug 30, 2025</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Evaluation History</h3>
                        <p class="table-sub">Your complete performance evaluation records</p>
                    </div>
                    <div class="table-actions">
                        <button class="btn-export">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Export All
                        </button>
                    </div>
                </div>
                
                <div class="table-wrapper">
                    <table class="payroll-table">
                        <thead>
                            <tr>
                                <th>Evaluation ID</th>
                                <th>Period</th>
                                <th>Evaluator</th>
                                <th>Completed Date</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">EVAL-2025-01</td>
                                <td style="font-size:12.5px;color:#0b044d;font-weight:600;">Jan-Jun 2025</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Jun 28, 2025</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="font-size:14px;font-weight:700;color:#15803d;">4.8</span>
                                        <span style="font-size:11px;color:#9999bb;">/ 5.0</span>
                                    </div>
                                </td>
                                <td><span class="badge-status on-hold">Completed</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-view" onclick="openEvaluation('EVAL-2025-01', 'Jan-Jun 2025', 4.8, 'Jun 28, 2025', 'Mayor Office', 'Excellent performance and leadership skills demonstrated. Shows strong commitment to professional development and training completion.', ['Leadership', 'Communication', 'Problem Solving', 'Training Completion'], ['Time Management'])">View</button>
                                        <button class="btn-download">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">EVAL-2024-02</td>
                                <td style="font-size:12.5px;color:#0b044d;font-weight:600;">Jul-Dec 2024</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Dec 20, 2024</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="font-size:14px;font-weight:700;color:#15803d;">4.7</span>
                                        <span style="font-size:11px;color:#9999bb;">/ 5.0</span>
                                    </div>
                                </td>
                                <td><span class="badge-status on-hold">Completed</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-view" onclick="openEvaluation('EVAL-2024-02', 'Jul-Dec 2024', 4.7, 'Dec 20, 2024', 'Mayor Office', 'Consistently high performance with excellent patient care and teamwork.', ['Teamwork', 'Patient Care', 'Punctuality'], ['Documentation'])">View</button>
                                        <button class="btn-download">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">EVAL-2024-01</td>
                                <td style="font-size:12.5px;color:#0b044d;font-weight:600;">Jan-Jun 2024</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Jun 25, 2024</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="font-size:14px;font-weight:700;color:#15803d;">4.6</span>
                                        <span style="font-size:11px;color:#9999bb;">/ 5.0</span>
                                    </div>
                                </td>
                                <td><span class="badge-status on-hold">Completed</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-view" onclick="openEvaluation('EVAL-2024-01', 'Jan-Jun 2024', 4.6, 'Jun 25, 2024', 'Mayor Office', 'Good performance with notable improvement in clinical skills and communication.', ['Clinical Skills', 'Communication', 'Initiative'], ['Time Management'])">View</button>
                                        <button class="btn-download">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">EVAL-2023-02</td>
                                <td style="font-size:12.5px;color:#0b044d;font-weight:600;">Jul-Dec 2023</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Dec 18, 2023</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="font-size:14px;font-weight:700;color:#15803d;">4.5</span>
                                        <span style="font-size:11px;color:#9999bb;">/ 5.0</span>
                                    </div>
                                </td>
                                <td><span class="badge-status on-hold">Completed</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-view" onclick="openEvaluation('EVAL-2023-02', 'Jul-Dec 2023', 4.5, 'Dec 18, 2023', 'Mayor Office', 'Solid performance with consistent dedication to duties and patient welfare.', ['Dedication', 'Patient Care', 'Reliability'], ['Leadership Skills'])">View</button>
                                        <button class="btn-download">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-footer">
                    <p>Showing <strong>4</strong> evaluation records</p>
                </div>
            </section>
    </main>

</div>

@include('permanent.permanent-chatbot')

{{-- Evaluation Modal --}}
<div class="modal-overlay" id="evalModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="pmodal-hero">
                    <div class="pmodal-hero-icon" id="evalIcon" style="background:linear-gradient(135deg, #0b044d, #1a0f6e);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div>
                        <span class="modal-eyebrow" id="evalId">PERFORMANCE EVALUATION</span>
                        <h3 class="modal-title">Evaluation Report</h3>
                        <p class="modal-sub" id="evalSub">Period · Completed on Date</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" id="evalBody">
            </div>
            <div class="modal-footer">
                <button class="modal-btn-ghost" onclick="closeModal()">Close</button>
                <button class="modal-btn-primary">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download Report
                </button>
            </div>
        </div>
    </div>
    
    <div class="modal-overlay" id="goalModal">
        <div class="modal-box">
            <div class="modal-header">
                <div>
                    <span class="modal-eyebrow" id="goalId">PERFORMANCE GOAL</span>
                    <h3 class="modal-title" id="goalTitle">Goal Title</h3>
                    <p class="modal-sub" id="goalSub">Category · Target</p>
                </div>
                <button class="modal-close" onclick="closeGoalModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" id="goalBody">
            </div>
            <div class="modal-footer" id="goalFooter">
                <button class="modal-btn-ghost" onclick="closeGoalModal()">Close</button>
                <button class="modal-btn-primary" id="goalAction">Update Progress</button>
            </div>
        </div>
    </div>
    
    <script>
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('toggle-btn');
    const logoText     = document.getElementById('logo-text');
    const navLabel     = document.getElementById('nav-label');
    const userInfo     = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn    = document.getElementById('mobile-menu-btn');
    const overlay      = document.getElementById('mobile-overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = collapsed ? '›' : '‹';
            if (logoText) logoText.style.display  = collapsed ? 'none' : '';
            if (navLabel) navLabel.style.display  = collapsed ? 'none' : '';
            if (userInfo) userInfo.style.display  = collapsed ? 'none' : '';
            if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
            document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
                el.style.display = collapsed ? 'none' : '';
            });
        });
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    function openEvaluation(id, period, rating, completedDate, evaluator, feedback, strengths, improvements) {
            const ratingColor = rating >= 4.5 ? '#15803d' : rating >= 4.0 ? '#d9bb00' : '#8e1e18';
            document.getElementById('evalId').textContent = 'PERFORMANCE EVALUATION · ' + id;
            document.getElementById('evalSub').textContent = period + ' · Completed on ' + completedDate;
            document.getElementById('evalIcon').style.background = 'linear-gradient(135deg, ' + ratingColor + ', ' + ratingColor + '99)';
            
            let strengthsHtml = strengths.map(s => '<span class="strength-tag" style="background:#15803d15;color:#15803d;">' + s + '</span>').join('');
            let improvementsHtml = improvements.map(i => '<span class="strength-tag" style="background:#d9bb0015;color:#d9bb00;">' + i + '</span>').join('');
            
            document.getElementById('evalBody').innerHTML = '<div class="rating-box"><div class="rating-box-icon" style="background:linear-gradient(135deg,' + ratingColor + ',' + ratingColor + '99);"><span>' + rating + '</span></div><div><p>OVERALL RATING</p><p>' + rating + ' out of 5.0</p></div></div><div class="modal-section-label">EVALUATION DETAILS</div><div class="modal-row"><span>Evaluation Period</span><strong>' + period + '</strong></div><div class="modal-row"><span>Evaluator</span><strong>' + evaluator + '</strong></div><div class="modal-row"><span>Completed Date</span><strong>' + completedDate + '</strong></div><div class="modal-section-label" style="margin-top:20px;">FEEDBACK</div><p style="font-size:13px;color:#6b6a8a;line-height:1.6;margin-bottom:16px;">' + feedback + '</p><div class="modal-section-label">STRENGTHS</div><div class="strengths">' + strengthsHtml + '</div><div class="modal-section-label">AREAS FOR IMPROVEMENT</div><div class="strengths">' + improvementsHtml + '</div>';
            
            document.getElementById('evalModal').classList.add('show');
    }
    
    function closeModal() {
            document.getElementById('evalModal').classList.remove('show');
    }
    
    function openGoal(title, category, progress, target, status) {
            const statusColor = status === 'Achieved' ? '#15803d' : status === 'In Progress' ? '#d9bb00' : '#8e1e18';
            document.getElementById('goalTitle').textContent = title;
            document.getElementById('goalSub').textContent = category + ' · Target: ' + target;
            document.getElementById('goalBody').innerHTML = '<div class="modal-progress"><div class="modal-progress-label"><span>PROGRESS</span><span style="color:' + statusColor + '">' + progress + '%</span></div><div style="height:8px;background:#f0effe;border-radius:99px;"><div style="height:100%;width:' + progress + '%;background:linear-gradient(90deg,' + statusColor + ',' + statusColor + '99);border-radius:99px;"></div></div></div><div class="modal-section-label">GOAL DETAILS</div><div class="modal-row"><span>Category</span><strong>' + category + '</strong></div><div class="modal-row"><span>Target Date</span><strong>' + target + '</strong></div><div class="modal-row"><span>Status</span><span class="badge-status ' + (status === 'Achieved' ? 'on-hold' : 'processed') + '">' + status + '</span></div>';
            
            document.getElementById('goalFooter').style.display = status !== 'Achieved' ? 'flex' : 'flex';
            document.getElementById('goalAction').style.display = status !== 'Achieved' ? 'block' : 'none';
            document.getElementById('goalModal').classList.add('show');
    }
    
    function closeGoalModal() {
            document.getElementById('goalModal').classList.remove('show');
    }
    
    document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeGoalModal();
            }
        });
</script>
@endsection