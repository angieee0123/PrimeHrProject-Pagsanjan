@extends('layouts.app')

@section('content')
@php
$avatarColors = ['#0b044d','#8e1e18','#1a0f6e','#5a0f0b','#2d1a8e','#0b044d','#3b1a6e','#6b0f0b'];

$departments = [
    ['code' => 'OM', 'name' => 'Office of the Mayor', 'head' => 'Hon. Mayor', 'personnel' => 42, 'status' => 'Active'],
    ['code' => 'OVM', 'name' => 'Office of the Vice Mayor', 'head' => 'Hon. Vice Mayor', 'personnel' => 18, 'status' => 'Active'],
    ['code' => 'SB', 'name' => 'Sangguniang Bayan', 'head' => 'SB Secretary', 'personnel' => 24, 'status' => 'Active'],
    ['code' => 'MTO', 'name' => 'Office of the Municipal Treasurer', 'head' => 'Municipal Treasurer', 'personnel' => 31, 'status' => 'Active'],
    ['code' => 'MAO', 'name' => "Municipal Assessor's Office", 'head' => 'Municipal Assessor', 'personnel' => 14, 'status' => 'Active'],
    ['code' => 'MCR', 'name' => 'Municipal Civil Registrar', 'head' => 'Civil Registrar', 'personnel' => 12, 'status' => 'Active'],
    ['code' => 'MHO', 'name' => 'Municipal Health Office', 'head' => 'Municipal Health Officer', 'personnel' => 38, 'status' => 'Active'],
    ['code' => 'MSWD', 'name' => 'MSWD – Pagsanjan', 'head' => 'MSWD Officer', 'personnel' => 27, 'status' => 'Active'],
    ['code' => 'MPDO', 'name' => "Municipal Planning & Dev't Office", 'head' => 'MPDO Officer', 'personnel' => 16, 'status' => 'Active'],
    ['code' => 'MEO', 'name' => 'Office of the Mun. Engineer', 'head' => 'Municipal Engineer', 'personnel' => 22, 'status' => 'Active'],
    ['code' => 'MAGO', 'name' => 'Office of the Mun. Agriculturist', 'head' => 'Municipal Agriculturist', 'personnel' => 19, 'status' => 'Active'],
    ['code' => 'MENRO', 'name' => 'Municipal Environment & Natural Resources', 'head' => 'MENRO Officer', 'personnel' => 11, 'status' => 'Active'],
    ['code' => 'MBDO', 'name' => "Municipal Business & Dev't Office", 'head' => 'MBDO Officer', 'personnel' => 9, 'status' => 'Active'],
    ['code' => 'HRMO', 'name' => 'Human Resource Management Office', 'head' => 'HRMO Officer', 'personnel' => 8, 'status' => 'Active'],
    ['code' => 'MDRRMO', 'name' => 'Municipal Disaster Risk Reduction & Mgmt', 'head' => 'MDRRMO Officer', 'personnel' => 15, 'status' => 'Active'],
    ['code' => 'MBO', 'name' => 'Office of the Mun. Budget', 'head' => 'Municipal Budget Officer', 'personnel' => 7, 'status' => 'Active'],
    ['code' => 'MCTC', 'name' => 'Municipal Circuit Trial Court', 'head' => 'Presiding Judge', 'personnel' => 6, 'status' => 'Active'],
];

$totalPersonnel = array_sum(array_column($departments, 'personnel'));
$largestDept = array_reduce($departments, fn($a, $b) => ($a['personnel'] ?? 0) > $b['personnel'] ? $a : $b, []);
$activeDepts = count(array_filter($departments, fn($d) => $d['status'] === 'Active'));
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Departments</p>
            <div class="stat-icon-wrap" style="background: #0b044d15; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ count($departments) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All offices</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background: #15803d15; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPersonnel }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">Across all offices</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active Offices</p>
            <div class="stat-icon-wrap" style="background: #d9bb0015; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $activeDepts }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Operational units</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Largest Office</p>
            <div class="stat-icon-wrap" style="background: #8e1e1815; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $largestDept['personnel'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">{{ $largestDept['code'] }}</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Departments & Offices</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Province of Laguna · {{ count($departments) }} offices</p>
        </div>
        <div class="table-actions">
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Department / Office</th>
                    <th>Code</th>
                    <th>Department Head</th>
                    <th>Personnel</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Table rows will be dynamically rendered by JavaScript -->
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong><span id="showing-start">1</span>-<span id="showing-end">10</span></strong> of <strong>{{ count($departments) }}</strong> offices</p>
        <div class="pagination">
            <button class="page-btn" id="prev-btn" onclick="changePage('prev')">‹</button>
            <button class="page-btn active" data-page="1" onclick="goToPage(1)">1</button>
            <button class="page-btn" data-page="2" onclick="goToPage(2)">2</button>
            <button class="page-btn" id="next-btn" onclick="changePage('next')">›</button>
        </div>
    </div>
</section>

<!-- Department Detail Modal -->
<div id="dept-modal" class="modal-overlay" style="display: none;" onclick="closeDeptModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="emp-avatar xl" id="modal-avatar" style="font-size: 13px;"></div>
                <div>
                    <span class="modal-eyebrow" id="modal-eyebrow"></span>
                    <h3 class="modal-title" id="modal-title"></h3>
                    <p class="modal-sub">Municipal Government of Pagsanjan</p>
                    <div class="pmodal-badges">
                        <span class="badge-status processed" id="modal-status"></span>
                        <span class="badge-emptype" id="modal-personnel"></span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeDeptModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2px;">Office Code</p>
                        <p style="font-size: 15px; font-weight: 800; color: #0b044d;" id="modal-code"></p>
                    </div>
                </div>
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2px;">Total Personnel</p>
                        <p style="font-size: 15px; font-weight: 800; color: #15803d;" id="modal-personnel-count"></p>
                    </div>
                </div>
            </div>
            <div class="modal-section-label">OFFICE INFORMATION</div>
            <div class="modal-row"><span>Department Head</span><strong id="modal-head"></strong></div>
            <div class="modal-row"><span>Status</span><span class="badge-status processed" id="modal-status-2"></span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeDeptModal()">Close</button>
        </div>
    </div>
</div>

<style>
.badge-emptype {
    font-size: 11px; color: #0b044d; background: #f0effe;
    padding: 3px 10px; border-radius: 20px; font-weight: 600;
    border: 1px solid #dddcf0;
}
.btn-edit {
    padding: 6px 16px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-edit:hover { background: #e8e7f5; }
.row-actions { display: flex; gap: 6px; }
.table-footer {
    padding: 16px 24px; border-top: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: center;
}
.table-footer p { font-size: 13px; color: #6b6a8a; }
.pagination { display: flex; gap: 6px; }
.page-btn {
    width: 32px; height: 32px; border: 1px solid #e8e7f5;
    border-radius: 6px; background: #fff; color: #6b6a8a;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.page-btn.active { background: #0b044d; color: #fff; border-color: #0b044d; }
.page-btn:hover { background: #f7f6ff; }
.pay-cell {
    font-size: 13px; color: #0b044d; font-weight: 600;
}
.pmodal-hero {
    display: flex; gap: 16px; align-items: flex-start;
}
.pmodal-badges {
    display: flex; gap: 6px; margin-top: 8px;
}
.emp-avatar.xl {
    width: 56px; height: 56px; font-size: 16px;
}
</style>

<script>
const departments = @json($departments);
const avatarColors = @json($avatarColors);
const itemsPerPage = 10;
let currentPage = 1;
const totalPages = Math.ceil(departments.length / itemsPerPage);

function renderTable() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, departments.length);
    const currentItems = departments.slice(startIndex, endIndex);
    
    const tbody = document.querySelector('.payroll-table tbody');
    tbody.innerHTML = '';
    
    currentItems.forEach((dept, i) => {
        const actualIndex = startIndex + i;
        const row = `
            <tr>
                <td>
                    <div class="emp-cell">
                        <div class="emp-avatar" style="background: ${avatarColors[actualIndex % avatarColors.length]}; font-size: 11px;">
                            ${dept.code.slice(0, 2)}
                        </div>
                        <p class="emp-name">${dept.name}</p>
                    </div>
                </td>
                <td><span class="dept-tag">${dept.code}</span></td>
                <td class="position-cell">${dept.head}</td>
                <td class="pay-cell">${dept.personnel}</td>
                <td><span class="badge-status processed">${dept.status}</span></td>
                <td>
                    <button class="btn-view" onclick="showDeptModal(${actualIndex})">View</button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Update showing text
    document.getElementById('showing-start').textContent = startIndex + 1;
    document.getElementById('showing-end').textContent = endIndex;
    
    // Update pagination buttons
    updatePaginationButtons();
}

function updatePaginationButtons() {
    const pageButtons = document.querySelectorAll('.page-btn[data-page]');
    pageButtons.forEach(btn => {
        const page = parseInt(btn.getAttribute('data-page'));
        if (page === currentPage) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Disable/enable prev/next buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    if (currentPage === 1) {
        prevBtn.style.opacity = '0.5';
        prevBtn.style.cursor = 'not-allowed';
        prevBtn.disabled = true;
    } else {
        prevBtn.style.opacity = '1';
        prevBtn.style.cursor = 'pointer';
        prevBtn.disabled = false;
    }
    
    if (currentPage === totalPages) {
        nextBtn.style.opacity = '0.5';
        nextBtn.style.cursor = 'not-allowed';
        nextBtn.disabled = true;
    } else {
        nextBtn.style.opacity = '1';
        nextBtn.style.cursor = 'pointer';
        nextBtn.disabled = false;
    }
}

function goToPage(page) {
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
    }
}

function changePage(direction) {
    if (direction === 'prev' && currentPage > 1) {
        currentPage--;
        renderTable();
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
        renderTable();
    }
}

function showDeptModal(index) {
    const dept = departments[index];
    const modal = document.getElementById('dept-modal');
    
    document.getElementById('modal-avatar').style.background = avatarColors[index % avatarColors.length];
    document.getElementById('modal-avatar').textContent = dept.code.slice(0, 2);
    document.getElementById('modal-eyebrow').textContent = 'DEPARTMENT DETAIL · ' + dept.code;
    document.getElementById('modal-title').textContent = dept.name;
    document.getElementById('modal-status').textContent = dept.status;
    document.getElementById('modal-personnel').textContent = dept.personnel + ' Personnel';
    document.getElementById('modal-code').textContent = dept.code;
    document.getElementById('modal-personnel-count').textContent = dept.personnel;
    document.getElementById('modal-head').textContent = dept.head;
    document.getElementById('modal-status-2').textContent = dept.status;
    
    modal.style.display = 'flex';
}

function closeDeptModal() {
    document.getElementById('dept-modal').style.display = 'none';
}

// Initialize table on page load
document.addEventListener('DOMContentLoaded', function() {
    renderTable();
});
</script>
@endsection
