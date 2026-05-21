@extends('layouts.app')

@push('styles')
@vite('resources/css/adminPerformance.css')
@endpush

@section('content')

@php
$performance = collect([
    ['id' => 'PGS-0041', 'name' => 'Maria B. Santos',       'position' => 'Administrative Officer IV',  'dept' => 'Office of the Mayor',            'period' => 'Jan-Jun 2025', 'rating' => 4.8,  'status' => 'Completed', 'evaluator' => 'Mayor Office',        'dueDate' => 'Jun 30, 2025'],
    ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz',     'position' => 'Municipal Engineer II',       'dept' => 'Office of the Mun. Engineer',    'period' => 'Jan-Jun 2025', 'rating' => 4.5,  'status' => 'Completed', 'evaluator' => 'Municipal Engineer',  'dueDate' => 'Jun 30, 2025'],
    ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes',          'position' => 'Nurse II',                    'dept' => 'Municipal Health Office',         'period' => 'Jan-Jun 2025', 'rating' => 4.9,  'status' => 'Completed', 'evaluator' => 'Health Officer',      'dueDate' => 'Jun 30, 2025'],
    ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza',     'position' => 'Municipal Treasurer III',     'dept' => 'Office of the Mun. Treasurer',   'period' => 'Jan-Jun 2025', 'rating' => 4.6,  'status' => 'Completed', 'evaluator' => 'Municipal Treasurer', 'dueDate' => 'Jun 30, 2025'],
    ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez',         'position' => 'Social Welfare Officer II',   'dept' => 'MSWD – Pagsanjan',               'period' => 'Jan-Jun 2025', 'rating' => null, 'status' => 'Pending',   'evaluator' => 'MSWD Head',           'dueDate' => 'Jun 30, 2025'],
    ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores',     'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar',      'period' => 'Jan-Jun 2025', 'rating' => null, 'status' => 'Pending',   'evaluator' => 'Civil Registrar',     'dueDate' => 'Jun 30, 2025'],
]);

$avatarColors = ['#0b044d','#8e1e18','#1a0f6e','#5a0f0b','#2d1a8e','#6b3fa0'];

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

$departments      = ['All Departments','Office of the Mayor','Office of the Mun. Engineer','Municipal Health Office','MSWD – Pagsanjan','Office of the Mun. Treasurer'];
$totalEvaluations = $performance->count();
$completedCount   = $performance->where('status','Completed')->count();
$pendingCount     = $performance->where('status','Pending')->count();
$avgRating        = $performance->whereNotNull('rating')->avg('rating') ?? 0;
@endphp

@include('admin.topbar.performanceTopbar')
@include('admin.notification.adminNotification')

{{-- Stats --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Evaluations</p>
            <div class="stat-icon-wrap pf-icon-primary">
                <svg width="18" height="18" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalEvaluations }}</h2>
        <div class="stat-footer"><span class="stat-dot pf-dot-primary"></span><p class="stat-sub">All employees</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Completed</p>
            <div class="stat-icon-wrap pf-icon-success">
                <svg width="18" height="18" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $completedCount }}</h2>
        <div class="stat-footer"><span class="stat-dot pf-dot-success"></span><p class="stat-sub">Finished evaluations</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending</p>
            <div class="stat-icon-wrap pf-icon-warning">
                <svg width="18" height="18" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingCount }}</h2>
        <div class="stat-footer"><span class="stat-dot pf-dot-warning"></span><p class="stat-sub">Awaiting evaluation</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Average Rating</p>
            <div class="stat-icon-wrap pf-icon-purple">
                <svg width="18" height="18" fill="none" stroke="#6b3fa0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ number_format($avgRating, 1) }}</h2>
        <div class="stat-footer"><span class="stat-dot pf-dot-purple"></span><p class="stat-sub">Out of 5.0</p></div>
    </div>
</div>

{{-- Table --}}
<div class="table-section">
    <div class="table-header">
        <div>
            <p class="table-title">Performance Evaluations</p>
            <p class="table-sub">Municipal Government of Pagsanjan · <span id="showing-count">{{ $totalEvaluations }}</span> of {{ $totalEvaluations }} evaluations</p>
        </div>
        <div class="table-actions">
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
                    $color       = $avatarColors[$index % count($avatarColors)];
                    $statusClass = $perf['status'] === 'Completed' ? 'processed' : 'pending';
                @endphp
                <tr data-dept="{{ $perf['dept'] }}" data-status="{{ $perf['status'] }}">
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background:{{ $color }}">{{ getInitials($perf['name']) }}</div>
                            <div>
                                <p class="emp-name">{{ $perf['name'] }}</p>
                                <p class="emp-id">{{ $perf['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="position-cell">{{ $perf['position'] }}</span></td>
                    <td><span class="dept-tag">{{ $perf['dept'] }}</span></td>
                    <td class="pf-period-cell">{{ $perf['period'] }}</td>
                    <td class="pf-evaluator-cell">{{ $perf['evaluator'] }}</td>
                    <td>
                        @if($perf['rating'])
                        <div class="pf-rating-wrap">
                            <div class="pf-stars">
                                @for($i = 1; $i <= 5; $i++)
                                <svg width="14" height="14" viewBox="0 0 24 24"
                                     fill="{{ $i <= round($perf['rating']) ? '#6b3fa0' : '#e4e3f0' }}"
                                     stroke="{{ $i <= round($perf['rating']) ? '#6b3fa0' : '#e4e3f0' }}"
                                     stroke-width="1">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                @endfor
                            </div>
                            <span class="pf-rating-value">{{ $perf['rating'] }}</span>
                        </div>
                        @else
                        <span class="pf-not-rated">Not rated</span>
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
        <div class="empty-state" id="empty-state" style="display:none">
            <p class="empty-sub">No evaluations found</p>
        </div>
    </div>

    <div class="table-footer">
        <span>Showing <strong id="visible-count">{{ $totalEvaluations }}</strong> of <strong>{{ $totalEvaluations }}</strong> evaluations</span>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal-overlay" id="view-modal" style="display:none">
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
            <div class="pf-modal-emp-row">
                <div class="emp-avatar emp-avatar-lg" id="modal-avatar">MS</div>
                <div>
                    <p class="pf-modal-emp-id" id="modal-emp-id">PGS-0041</p>
                    <span class="badge-status processed" id="modal-status-badge">Completed</span>
                </div>
            </div>
            <div class="modal-row"><span>Employee</span><strong id="modal-name"></strong></div>
            <div class="modal-row"><span>Position</span><strong id="modal-position"></strong></div>
            <div class="modal-row"><span>Department</span><strong id="modal-dept"></strong></div>
            <div class="modal-row"><span>Evaluation Period</span><strong id="modal-period"></strong></div>
            <div class="modal-row"><span>Evaluator</span><strong id="modal-evaluator"></strong></div>
            <div class="modal-row"><span>Due Date</span><strong id="modal-dueDate"></strong></div>
            <div class="modal-row"><span>Overall Rating</span><strong id="modal-rating"></strong></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="modal-btn-primary" id="modal-action-btn">View Full Report</button>
        </div>
    </div>
</div>

{{-- Evaluate Modal --}}
<div class="modal-overlay" id="evaluate-modal" style="display:none">
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
            <form id="eval-form">
                <div class="form-grid">
                    <div class="form-field form-full">
                        <label>Overall Rating (1.0 – 5.0)</label>
                        <input type="number" id="eval-rating" step="0.1" min="1" max="5" value="4.0">
                    </div>
                    <div class="form-field form-full">
                        <label>Performance Comments</label>
                        <textarea id="eval-comments" placeholder="Enter performance comments..." rows="4"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('evaluate-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="submitEvaluation()">Submit Evaluation</button>
        </div>
    </div>
</div>

<script>
const performanceData = @json($performance);
const avatarColors    = @json($avatarColors);
let currentEvalId     = null;

function getInitials(name) {
    return name.split(' ').filter(n => /^[A-Z]/.test(n)).map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

function viewPerformance(perfId) {
    const perf = performanceData.find(p => p.id === perfId);
    if (!perf) return;
    const idx   = performanceData.findIndex(p => p.id === perfId);
    const color = avatarColors[idx % avatarColors.length];

    document.getElementById('modal-perf-id').textContent       = 'PERFORMANCE EVALUATION · ' + perf.id;
    document.getElementById('modal-perf-name').textContent     = perf.name;
    document.getElementById('modal-perf-position').textContent = perf.position + ' · ' + perf.dept;
    document.getElementById('modal-avatar').style.background   = color;
    document.getElementById('modal-avatar').textContent        = getInitials(perf.name);
    document.getElementById('modal-emp-id').textContent        = perf.id;

    const badge = document.getElementById('modal-status-badge');
    badge.textContent = perf.status;
    badge.className   = 'badge-status ' + (perf.status === 'Completed' ? 'processed' : 'pending');

    document.getElementById('modal-name').textContent      = perf.name;
    document.getElementById('modal-position').textContent  = perf.position;
    document.getElementById('modal-dept').textContent      = perf.dept;
    document.getElementById('modal-period').textContent    = perf.period;
    document.getElementById('modal-evaluator').textContent = perf.evaluator;
    document.getElementById('modal-dueDate').textContent   = perf.dueDate;
    document.getElementById('modal-rating').textContent    = perf.rating ? perf.rating + ' / 5.0' : 'Not yet rated';
    document.getElementById('modal-action-btn').textContent = perf.status === 'Completed' ? 'View Full Report' : 'Start Evaluation';

    document.getElementById('view-modal').style.display = 'flex';
}

function showEvaluateModal(perfId) {
    const perf = performanceData.find(p => p.id === perfId);
    if (!perf) return;
    currentEvalId = perfId;
    document.getElementById('eval-name').textContent     = perf.name;
    document.getElementById('eval-position').textContent = perf.position;
    document.getElementById('eval-rating').value         = '4.0';
    document.getElementById('eval-comments').value       = '';
    document.getElementById('evaluate-modal').style.display = 'flex';
}

function submitEvaluation() {
    const rating = parseFloat(document.getElementById('eval-rating').value);
    if (rating < 1 || rating > 5) { alert('Please enter a rating between 1.0 and 5.0'); return; }

    const rows = document.querySelectorAll('#performance-table-body tr');
    rows.forEach(row => {
        const empName = row.querySelector('.emp-name').textContent;
        const emp = performanceData.find(e => e.name === empName);
        if (emp && emp.id === currentEvalId) {
            emp.rating = rating; emp.status = 'Completed';
            row.dataset.status = 'Completed';
            row.querySelector('td:nth-child(7) .badge-status').textContent = 'Completed';
            row.querySelector('td:nth-child(7) .badge-status').className   = 'badge-status processed';

            let stars = '<div class="pf-rating-wrap"><div class="pf-stars">';
            for (let i = 1; i <= 5; i++) {
                const c = i <= Math.round(rating) ? '#6b3fa0' : '#e4e3f0';
                stars += `<svg width="14" height="14" viewBox="0 0 24 24" fill="${c}" stroke="${c}" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
            }
            stars += `</div><span class="pf-rating-value">${rating}</span></div>`;
            row.querySelector('td:nth-child(6)').innerHTML = stars;
            row.querySelector('td:last-child .row-actions').innerHTML =
                `<button class="btn-view" onclick="viewPerformance('${emp.id}')">View</button>`;
        }
    });
    closeModal('evaluate-modal');
    filterPerformance();
    alert('Evaluation submitted successfully!');
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }

document.getElementById('dept-filter').addEventListener('change', filterPerformance);
document.getElementById('status-filter').addEventListener('change', filterPerformance);
document.getElementById('search-input').addEventListener('input', filterPerformance);

function filterPerformance() {
    const dept   = document.getElementById('dept-filter').value;
    const status = document.getElementById('status-filter').value;
    const q      = document.getElementById('search-input').value.toLowerCase();
    const rows   = document.querySelectorAll('#performance-table-body tr');
    let visible  = 0;

    rows.forEach(row => {
        const match = (dept === 'All Departments' || row.dataset.dept === dept)
            && (status === 'All' || row.dataset.status === status)
            && (!q || row.querySelector('.emp-name').textContent.toLowerCase().includes(q)
                   || row.querySelector('.emp-id').textContent.toLowerCase().includes(q));
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    document.getElementById('showing-count').textContent = visible;
    document.getElementById('visible-count').textContent = visible;
    document.getElementById('empty-state').style.display = visible === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal('view-modal'); closeModal('evaluate-modal'); }
});
</script>
@endsection
