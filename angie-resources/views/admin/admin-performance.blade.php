@extends('layouts.app')

@section('title', 'Performance Management · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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

    @include('admin.admin-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('admin.admin-notification')

        @php
        $performance = collect([
            ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'period' => 'Jan-Jun 2025', 'rating' => 4.8, 'status' => 'Completed', 'evaluator' => 'Mayor Office', 'dueDate' => 'Jun 30, 2025'],
            ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'period' => 'Jan-Jun 2025', 'rating' => 4.5, 'status' => 'Completed', 'evaluator' => 'Municipal Engineer', 'dueDate' => 'Jun 30, 2025'],
            ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'period' => 'Jan-Jun 2025', 'rating' => 4.9, 'status' => 'Completed', 'evaluator' => 'Health Officer', 'dueDate' => 'Jun 30, 2025'],
            ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'period' => 'Jan-Jun 2025', 'rating' => 4.6, 'status' => 'Completed', 'evaluator' => 'Municipal Treasurer', 'dueDate' => 'Jun 30, 2025'],
            ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'period' => 'Jan-Jun 2025', 'rating' => null, 'status' => 'Pending', 'evaluator' => 'MSWD Head', 'dueDate' => 'Jun 30, 2025'],
            ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'period' => 'Jan-Jun 2025', 'rating' => null, 'status' => 'Pending', 'evaluator' => 'Civil Registrar', 'dueDate' => 'Jun 30, 2025'],
        ]);
        $avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
        if (!function_exists('getInitials')) {
            function getInitials($name) {
                $parts = explode(' ', $name);
                $initials = '';
                foreach ($parts as $part) {
                    if (preg_match('/^[A-Z]/', $part)) $initials .= $part[0];
                }
                return strtoupper(substr($initials, 0, 2));
            }
        }
        $departments = ['All Departments', 'Office of the Mayor', 'Office of the Mun. Engineer', 'Municipal Health Office', 'MSWD – Pagsanjan', 'Office of the Mun. Treasurer'];
        $totalEvaluations = $performance->count();
        $completedCount   = $performance->where('status', 'Completed')->count();
        $pendingCount     = $performance->where('status', 'Pending')->count();
        $avgRating        = $performance->whereNotNull('rating')->avg('rating') ?? 0;
        @endphp

        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div>
                    <h2>Performance Management</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Employee Evaluations</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    {{ $pendingCount }} Pending
                </span>
                <span class="banner-badge outline">FY {{ now()->year }}</span>
            </div>
        </div>

        <div class="stats-grid stats-grid-4" style="margin-bottom: 24px;">
            <div class="stat-card" style="--accent-color: #0b044d">
                <div class="stat-top">
                    <p class="stat-label">Total Evaluations</p>
                    <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalEvaluations }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All employees</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #15803d">
                <div class="stat-top">
                    <p class="stat-label">Completed</p>
                    <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $completedCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#15803d"></span>
                    <p class="stat-sub">Finished evaluations</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #d9bb00">
                <div class="stat-top">
                    <p class="stat-label">Pending</p>
                    <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $pendingCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#d9bb00"></span>
                    <p class="stat-sub">Awaiting evaluation</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #6b3fa0">
                <div class="stat-top">
                    <p class="stat-label">Average Rating</p>
                    <div class="stat-icon-wrap" style="background: rgba(107, 63, 160, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#6b3fa0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ number_format($avgRating, 1) }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#6b3fa0"></span>
                    <p class="stat-sub">Out of 5.0</p>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Performance Evaluations</p>
                    <p class="table-sub">Municipal Government of Pagsanjan · <span id="showing-count">{{ $totalEvaluations }}</span> of {{ $totalEvaluations }} evaluations</p>
                </div>
                <div class="table-actions">
                    <div class="search-wrap">
                        <svg width="13" height="13" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Search evaluations..." class="search-input">
                    </div>
                    <select class="filter-select" id="dept-filter">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="status-filter">
                        <option value="All">All Status</option>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
                    </select>
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Period</th>
                            <th>Evaluator</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="performance-table-body">
                        @foreach($performance as $index => $perf)
                        @php
                        $colorIndex = $index % count($avatarColors);
                        $statusClass = $perf['status'] === 'Completed' ? 'processed' : 'pending';
                        @endphp
                        <tr data-dept="{{ $perf['dept'] }}" data-status="{{ $perf['status'] }}">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background: {{ $avatarColors[$colorIndex] }}">{{ getInitials($perf['name']) }}</div>
                                    <div>
                                        <p class="emp-name">{{ $perf['name'] }}</p>
                                        <p class="emp-id">{{ $perf['id'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="position-cell">{{ $perf['position'] }}</span></td>
                            <td><span class="dept-tag">{{ $perf['dept'] }}</span></td>
                            <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $perf['period'] }}</td>
                            <td style="font-size: 12.5px; color: #6b6a8a;">{{ $perf['evaluator'] }}</td>
                            <td>
                                @if($perf['rating'])
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="display: flex; gap: 2px;">
                                        @for($i = 1; $i <= 5; $i++)
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= round($perf['rating']) ? '#6b3fa0' : '#e4e3f0' }}" stroke="{{ $i <= round($perf['rating']) ? '#6b3fa0' : '#e4e3f0' }}" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @endfor
                                    </div>
                                    <span style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $perf['rating'] }}</span>
                                </div>
                                @else
                                <span style="font-size: 12.5px; color: #9999bb;">Not rated</span>
                                @endif
                            </td>
                            <td><span class="badge-status {{ $statusClass }}">{{ $perf['status'] }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-view" onclick="viewPerformance('{{ $perf['id'] }}')">View</button>
                                    @if($perf['status'] === 'Pending')
                                    <button class="btn-evaluate" onclick="showEvaluateModal('{{ $perf['id'] }}')">Evaluate</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="empty-state" id="empty-state" style="display: none; text-align: center; padding: 40px 20px;">
                    <p style="font-size: 13px; color: #9999bb; margin: 0;">No evaluations found</p>
                </div>
            </div>

            <div class="table-footer">
                <p>Showing <strong id="visible-count">{{ $totalEvaluations }}</strong> of <strong>{{ $totalEvaluations }}</strong> evaluations</p>
                <div class="pagination">
                    <button class="page-btn active">1</button>
                    <button class="page-btn">›</button>
                </div>
            </div>
        </div>

    </main>

    @include('admin.admin-chatbot')

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

    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        logoText.style.display  = collapsed ? 'none' : '';
        navLabel.style.display  = collapsed ? 'none' : '';
        userInfo.style.display  = collapsed ? 'none' : '';
        sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });

    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
</script>

<div class="modal-overlay" id="view-modal" style="display: none;">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="modal-perf-id">PERFORMANCE EVALUATION · PGS-0041</span>
                <h3 class="modal-title" id="modal-perf-name">Maria B. Santos</h3>
                <p class="modal-sub" id="modal-perf-position">Administrative Officer IV · Office of the Mayor</p>
            </div>
            <button class="modal-close" onclick="closeModal('view-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row" style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding: 16px; background: #f7f6ff; border-radius: 12px;">
                <div class="emp-avatar lg" id="modal-avatar" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: #fff;">MS</div>
                <div>
                    <p class="modal-emp-id" id="modal-emp-id" style="font-size: 11px; color: #9999bb; margin: 0 0 4px;">PGS-0041</p>
                    <span class="badge-status" id="modal-status-badge">Completed</span>
                </div>
            </div>
            <div class="modal-row"><span>Employee</span><strong id="modal-name">Maria B. Santos</strong></div>
            <div class="modal-row"><span>Position</span><strong id="modal-position">Administrative Officer IV</strong></div>
            <div class="modal-row"><span>Department</span><strong id="modal-dept">Office of the Mayor</strong></div>
            <div class="modal-row"><span>Evaluation Period</span><strong id="modal-period">Jan-Jun 2025</strong></div>
            <div class="modal-row"><span>Evaluator</span><strong id="modal-evaluator">Mayor Office</strong></div>
            <div class="modal-row"><span>Due Date</span><strong id="modal-dueDate">Jun 30, 2025</strong></div>
            <div class="modal-row">
                <span>Overall Rating</span>
                <strong id="modal-rating">4.8 / 5.0</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="modal-btn-primary" id="modal-action-btn">View Full Report</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="evaluate-modal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">EVALUATE PERFORMANCE</span>
                <h3 class="modal-title" id="eval-name">Employee Name</h3>
                <p class="modal-sub" id="eval-position">Position</p>
            </div>
            <button class="modal-close" onclick="closeModal('evaluate-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-field form-full">
                    <label>Overall Rating (1.0 - 5.0)</label>
                    <input type="number" id="eval-rating" step="0.1" min="1" max="5" value="4.0" style="width: 100%; padding: 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 14px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none;">
                </div>
                <div class="form-field form-full">
                    <label>Performance Comments</label>
                    <textarea id="eval-comments" placeholder="Enter performance comments..." rows="4" style="width: 100%; padding: 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; resize: vertical;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('evaluate-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="submitEvaluation()">Submit Evaluation</button>
        </div>
    </div>
</div>

<style>
/* search-wrap — not in global CSS */
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-wrap svg {
    position: absolute;
    left: 10px;
    pointer-events: none;
}
.search-input {
    height: 34px;
    padding: 0 10px 0 30px;
    border: 1.5px solid #e4e3f0;
    border-radius: 8px;
    font-size: 12.5px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: #fafafe;
    outline: none;
    width: 180px;
    transition: border-color 0.2s;
}
.search-input:focus { border-color: #0b044d; }

/* modal-lg override */
.modal-lg { max-width: 560px; }

/* form fields — not in global CSS */
.form-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
.form-field { display: flex; flex-direction: column; gap: 5px; }
.form-full { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 600; color: #0b044d; }
.form-field input,
.form-field textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e4e3f0;
    border-radius: 9px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    outline: none;
    box-sizing: border-box;
    background: #fff;
    resize: vertical;
}
.form-field input:focus,
.form-field textarea:focus { border-color: #0b044d; }

/* emp-avatar size variant — not in global CSS */
.emp-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.emp-avatar.lg { width: 48px; height: 48px; border-radius: 12px; font-size: 16px; }

/* evaluate button — page-specific */
.btn-evaluate {
    padding: 5px 14px;
    border: 1.5px solid #0b044d;
    border-radius: 7px;
    background: #0b044d;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-evaluate:hover { background: #1a0f6e; border-color: #1a0f6e; }
</style>

<script>
const performanceData = @json($performance);
const avatarColors = @json($avatarColors);

let currentEvalId = null;

function getInitials(name) {
    const parts = name.split(' ').filter(n => /^[A-Z]/.test(n));
    return parts.map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

function viewPerformance(perfId) {
    const perf = performanceData.find(p => p.id === perfId);
    if (!perf) return;
    
    const idx = performanceData.findIndex(p => p.id === perfId);
    const color = avatarColors[idx % avatarColors.length];
    
    document.getElementById('modal-perf-id').textContent = 'PERFORMANCE EVALUATION · ' + perf.id;
    document.getElementById('modal-perf-name').textContent = perf.name;
    document.getElementById('modal-perf-position').textContent = perf.position + ' · ' + perf.dept;
    document.getElementById('modal-avatar').style.background = color;
    document.getElementById('modal-avatar').textContent = getInitials(perf.name);
    document.getElementById('modal-emp-id').textContent = perf.id;
    
    const statusBadge = document.getElementById('modal-status-badge');
    statusBadge.textContent = perf.status;
    statusBadge.className = 'badge-status ' + (perf.status === 'Completed' ? 'processed' : 'pending');
    
    document.getElementById('modal-name').textContent = perf.name;
    document.getElementById('modal-position').textContent = perf.position;
    document.getElementById('modal-dept').textContent = perf.dept;
    document.getElementById('modal-period').textContent = perf.period;
    document.getElementById('modal-evaluator').textContent = perf.evaluator;
    document.getElementById('modal-dueDate').textContent = perf.dueDate;
    document.getElementById('modal-rating').textContent = perf.rating ? perf.rating + ' / 5.0' : 'Not yet rated';
    
    document.getElementById('modal-action-btn').textContent = perf.status === 'Completed' ? 'View Full Report' : 'Start Evaluation';
    
    document.getElementById('view-modal').style.display = 'flex';
}

function showEvaluateModal(perfId) {
    const perf = performanceData.find(p => p.id === perfId);
    if (!perf) return;
    
    currentEvalId = perfId;
    document.getElementById('eval-name').textContent = perf.name;
    document.getElementById('eval-position').textContent = perf.position;
    document.getElementById('eval-rating').value = '4.0';
    document.getElementById('eval-comments').value = '';
    document.getElementById('evaluate-modal').style.display = 'flex';
}

function submitEvaluation() {
    const rating = parseFloat(document.getElementById('eval-rating').value);
    
    if (rating < 1 || rating > 5) {
        alert('Please enter a rating between 1.0 and 5.0');
        return;
    }
    
    const row = document.querySelector(`#performance-table-body tr[data-status][td:first-child div div:last-child p:first-child]`);
    const rows = document.querySelectorAll('#performance-table-body tr');
    rows.forEach(r => {
        const empName = r.querySelector('.emp-name').textContent;
        const emp = performanceData.find(e => e.name === empName);
        if (emp && emp.id === currentEvalId) {
            emp.rating = rating;
            emp.status = 'Completed';
            r.dataset.status = 'Completed';
            
            const statusCell = r.querySelector('td:nth-child(7) .badge-status');
            statusCell.textContent = 'Completed';
            statusCell.className = 'badge-status processed';
            
            const ratingCell = r.querySelector('td:nth-child(6)');
            let starsHtml = '<div style="display: flex; align-items: center; gap: 6px;"><div style="display: flex; gap: 2px;">';
            for (let i = 1; i <= 5; i++) {
                starsHtml += `<svg width="14" height="14" viewBox="0 0 24 24" fill="${i <= Math.round(rating) ? '#6b3fa0' : '#e4e3f0'}" stroke="${i <= Math.round(rating) ? '#6b3fa0' : '#e4e3f0'}" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
            }
            starsHtml += `</div><span style="font-size: 13px; color: #0b044d; font-weight: 600;">${rating}</span></div>`;
            ratingCell.innerHTML = starsHtml;
            
            const actionsCell = r.querySelector('td:last-child .row-actions');
            actionsCell.innerHTML = `
                <button class="btn-view" onclick="viewPerformance('${emp.id}')">View</button>
            `;
        }
    });
    
    closeModal('evaluate-modal');
    filterPerformance();
    alert('Evaluation submitted successfully!');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('dept-filter').addEventListener('change', filterPerformance);
document.getElementById('status-filter').addEventListener('change', filterPerformance);
document.getElementById('search-input').addEventListener('input', filterPerformance);

function filterPerformance() {
    const deptFilter = document.getElementById('dept-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('#performance-table-body tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const dept = row.dataset.dept;
        const status = row.dataset.status;
        const name = row.querySelector('.emp-name').textContent.toLowerCase();
        const empId = row.querySelector('.emp-id').textContent.toLowerCase();
        
        const matchDept = deptFilter === 'All Departments' || dept === deptFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || name.includes(searchQuery) || empId.includes(searchQuery);
        
        const isVisible = matchDept && matchStatus && matchSearch;
        row.style.display = isVisible ? 'table-row' : 'none';
        if (isVisible) visibleCount++;
    });
    
    document.getElementById('showing-count').textContent = visibleCount;
    document.getElementById('visible-count').textContent = visibleCount;
    document.getElementById('empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('view-modal').style.display = 'none';
        document.getElementById('evaluate-modal').style.display = 'none';
    }
});

</script>
@endsection