@extends('layouts.permanent')

@section('title', 'Performance · PRIME HRIS')

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

    @include('permanent.sidebar.permanentSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-performance">

        @include('permanent.notification.permanentNotification')

        @include('permanent.topbar.performanceTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4 performance-stats-grid">
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Latest Rating</p>
                        <div class="stat-icon-wrap performance-icon performance-icon-primary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                    </div>
                    <h2 class="stat-value">4.8</h2>
                    <div class="stat-footer">
                        <span class="stat-dot stat-dot-primary"></span>
                        <p class="stat-sub">Jan-Jun 2025</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Average Rating</p>
                        <div class="stat-icon-wrap performance-icon performance-icon-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 18"/></svg></div>
                    </div>
                    <h2 class="stat-value">4.7</h2>
                    <div class="stat-footer">
                        <span class="stat-dot stat-dot-success"></span>
                        <p class="stat-sub">All evaluations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Total Evaluations</p>
                        <div class="stat-icon-wrap performance-icon performance-icon-warning"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
                    </div>
                    <h2 class="stat-value">4</h2>
                    <div class="stat-footer">
                        <span class="stat-dot stat-dot-amber"></span>
                        <p class="stat-sub">Completed reviews</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <p class="stat-label">Goals Achieved</p>
                        <div class="stat-icon-wrap performance-icon performance-icon-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></div>
                    </div>
                    <h2 class="stat-value">1</h2>
                    <div class="stat-footer">
                        <span class="stat-dot stat-dot-danger"></span>
                        <p class="stat-sub">4 total goals</p>
                    </div>
                </div>
            </div>
            
            <section class="table-section performance-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Training Impact on Performance</h3>
                        <p class="table-sub">How completed training programs have influenced your performance metrics</p>
                    </div>
                </div>
                <div class="training-cards">
                    <div class="training-card">
                        <span class="badge-status on-hold performance-card-badge">Completed</span>
                        <div class="card-header">
                            <div class="card-icon performance-card-icon-success"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                            <div>
                                <h4 class="card-title">Customer Service Excellence</h4>
                                <p class="card-sub">Completed: May 20, 2025</p>
                            </div>
                        </div>
                        <p class="card-desc">Improved team collaboration score by 25%</p>
                        <div class="card-footer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span class="performance-rating-value">4.9</span>
                            <span class="performance-rating-label">Training Rating</span>
                        </div>
                    </div>
                    <div class="training-card">
                        <span class="badge-status on-hold performance-card-badge">Completed</span>
                        <div class="card-header">
                            <div class="card-icon performance-card-icon-success"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                            <div>
                                <h4 class="card-title">Digital Literacy Training</h4>
                                <p class="card-sub">Completed: Apr 15, 2025</p>
                            </div>
                        </div>
                        <p class="card-desc">Enhanced technical proficiency and workflow efficiency</p>
                        <div class="card-footer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span class="performance-rating-value">4.7</span>
                            <span class="performance-rating-label">Training Rating</span>
                        </div>
                    </div>
                    <div class="training-card">
                        <span class="badge-status processed performance-card-badge">In Progress</span>
                        <div class="card-header">
                            <div class="card-icon performance-card-icon-warning"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                            <div>
                                <h4 class="card-title">Leadership Development Program</h4>
                                <p class="card-sub">Completed: In Progress</p>
                            </div>
                        </div>
                        <p class="card-desc">Developing management and decision-making skills</p>
                    </div>
                </div>
            </section>
            
            <section class="table-section performance-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Performance Trend</h3>
                        <p class="table-sub">Your rating history over time</p>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content performance-chart-bar h-90">
                                <span>4.5</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jul-Dec 2023</p>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content performance-chart-bar h-92">
                                <span>4.6</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jan-Jun 2024</p>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content performance-chart-bar h-94">
                                <span>4.7</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jul-Dec 2024</p>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-bar-fill">
                            <div class="chart-bar-content performance-chart-bar h-96">
                                <span>4.8</span>
                            </div>
                        </div>
                        <div class="chart-bar-label">
                            <p>Jan-Jun 2025</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <section class="table-section performance-section">
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
                            <div class="progress-label"><span>PROGRESS</span><span class="performance-progress-text-warning">65%</span></div>
                            <div class="progress-fill"><div class="performance-progress-fill performance-progress-warning w-65"></div></div>
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
                            <div class="progress-label"><span>PROGRESS</span><span class="performance-progress-text-success">100%</span></div>
                            <div class="progress-fill"><div class="performance-progress-fill performance-progress-success w-100"></div></div>
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
                            <div class="progress-label"><span>PROGRESS</span><span class="performance-progress-text-warning">45%</span></div>
                            <div class="progress-fill"><div class="performance-progress-fill performance-progress-warning w-45"></div></div>
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
                            <div class="progress-label"><span>PROGRESS</span><span class="performance-progress-text-warning">30%</span></div>
                            <div class="progress-fill"><div class="performance-progress-fill performance-progress-warning w-30"></div></div>
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
                    <table class="payroll-table performance-eval-table">
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
                                <td class="performance-table-id">EVAL-2025-01</td>
                                <td class="performance-table-period">Jan-Jun 2025</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td class="performance-table-date">Jun 28, 2025</td>
                                <td>
                                    <div class="performance-rating-wrap">
                                        <span class="performance-rating-main">4.8</span>
                                        <span class="performance-rating-scale">/ 5.0</span>
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
                                <td class="performance-table-id">EVAL-2024-02</td>
                                <td class="performance-table-period">Jul-Dec 2024</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td class="performance-table-date">Dec 20, 2024</td>
                                <td>
                                    <div class="performance-rating-wrap">
                                        <span class="performance-rating-main">4.7</span>
                                        <span class="performance-rating-scale">/ 5.0</span>
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
                                <td class="performance-table-id">EVAL-2024-01</td>
                                <td class="performance-table-period">Jan-Jun 2024</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td class="performance-table-date">Jun 25, 2024</td>
                                <td>
                                    <div class="performance-rating-wrap">
                                        <span class="performance-rating-main">4.6</span>
                                        <span class="performance-rating-scale">/ 5.0</span>
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
                                <td class="performance-table-id">EVAL-2023-02</td>
                                <td class="performance-table-period">Jul-Dec 2023</td>
                                <td><span class="dept-tag">Mayor Office</span></td>
                                <td class="performance-table-date">Dec 18, 2023</td>
                                <td>
                                    <div class="performance-rating-wrap">
                                        <span class="performance-rating-main">4.5</span>
                                        <span class="performance-rating-scale">/ 5.0</span>
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

@include('permanent.chatbot.permanentChatbot')

{{-- Evaluation Modal --}}
<div class="modal-overlay" id="evalModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="pmodal-hero">
                    <div class="pmodal-hero-icon performance-eval-icon" id="evalIcon">
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
            
            let strengthsHtml = strengths.map(s => '<span class="strength-tag performance-strength-tag">' + s + '</span>').join('');
            let improvementsHtml = improvements.map(i => '<span class="strength-tag performance-improvement-tag">' + i + '</span>').join('');
            
            document.getElementById('evalBody').innerHTML = '<div class="rating-box"><div class="rating-box-icon" style="background:linear-gradient(135deg,' + ratingColor + ',' + ratingColor + '99);"><span>' + rating + '</span></div><div><p>OVERALL RATING</p><p>' + rating + ' out of 5.0</p></div></div><div class="modal-section-label">EVALUATION DETAILS</div><div class="modal-row"><span>Evaluation Period</span><strong>' + period + '</strong></div><div class="modal-row"><span>Evaluator</span><strong>' + evaluator + '</strong></div><div class="modal-row"><span>Completed Date</span><strong>' + completedDate + '</strong></div><div class="modal-section-label performance-feedback-label">FEEDBACK</div><p class="performance-feedback-text">' + feedback + '</p><div class="modal-section-label">STRENGTHS</div><div class="strengths">' + strengthsHtml + '</div><div class="modal-section-label">AREAS FOR IMPROVEMENT</div><div class="strengths">' + improvementsHtml + '</div>';
            
            document.getElementById('evalModal').classList.add('show');
    }
    
    function closeModal() {
            document.getElementById('evalModal').classList.remove('show');
    }

    function filterPermanentPerformance(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.payroll-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        document.querySelectorAll('.goal-card').forEach(card => {
            card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }
    
    function openGoal(title, category, progress, target, status) {
            const statusColor = status === 'Achieved' ? '#15803d' : status === 'In Progress' ? '#d9bb00' : '#8e1e18';
            document.getElementById('goalTitle').textContent = title;
            document.getElementById('goalSub').textContent = category + ' · Target: ' + target;
            document.getElementById('goalBody').innerHTML = '<div class="modal-progress"><div class="modal-progress-label"><span>PROGRESS</span><span style="color:' + statusColor + '">' + progress + '%</span></div><div class="performance-modal-progress-track"><div style="height:100%;width:' + progress + '%;background:linear-gradient(90deg,' + statusColor + ',' + statusColor + '99);border-radius:99px;"></div></div></div><div class="modal-section-label">GOAL DETAILS</div><div class="modal-row"><span>Category</span><strong>' + category + '</strong></div><div class="modal-row"><span>Target Date</span><strong>' + target + '</strong></div><div class="modal-row"><span>Status</span><span class="badge-status ' + (status === 'Achieved' ? 'on-hold' : 'processed') + '">' + status + '</span></div>';
            
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