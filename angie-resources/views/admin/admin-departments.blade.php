@extends('layouts.app')

@section('title', 'Departments · PRIME HRIS')

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

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>
                </div>
                <div>
                    <h2>Departments & Offices</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Municipal Government of Pagsanjan</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    17 Offices
                </span>
                <span class="banner-badge outline">311 Personnel</span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Departments</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">17</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All offices</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Personnel</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <p class="stat-value">311</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">Across all offices</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Active Offices</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">17</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">Operational units</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Largest Office</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                </div>
                <p class="stat-value">42</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Office of the Mayor</p>
                </div>
            </div>

        </div>

        {{-- Departments Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Departments & Offices</p>
                    <p class="table-sub">Municipal Government of Pagsanjan · Province of Laguna · 17 offices</p>
                </div>
                <div class="table-actions">
                    <button class="btn-export" onclick="exportDepartments()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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
                    <tbody id="deptTableBody">
                        <tr data-code="OM">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#0b044d">OM</div>
                                    <div>
                                        <p class="emp-name">Office of the Mayor</p>
                                        <p class="emp-id">OM</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">OM</span></td>
                            <td><span class="position-cell">Hon. Mayor</span></td>
                            <td><span class="pay-cell">42</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('OM')">View</button></td>
                        </tr>
                        <tr data-code="OVM">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#8e1e18">OV</div>
                                    <div>
                                        <p class="emp-name">Office of the Vice Mayor</p>
                                        <p class="emp-id">OVM</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">OVM</span></td>
                            <td><span class="position-cell">Hon. Vice Mayor</span></td>
                            <td><span class="pay-cell">18</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('OVM')">View</button></td>
                        </tr>
                        <tr data-code="SB">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#1a0f6e">SB</div>
                                    <div>
                                        <p class="emp-name">Sangguniang Bayan</p>
                                        <p class="emp-id">SB</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">SB</span></td>
                            <td><span class="position-cell">SB Secretary</span></td>
                            <td><span class="pay-cell">24</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('SB')">View</button></td>
                        </tr>
                        <tr data-code="MTO">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#5a0f0b">MT</div>
                                    <div>
                                        <p class="emp-name">Office of the Municipal Treasurer</p>
                                        <p class="emp-id">MTO</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MTO</span></td>
                            <td><span class="position-cell">Municipal Treasurer</span></td>
                            <td><span class="pay-cell">31</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MTO')">View</button></td>
                        </tr>
                        <tr data-code="MAO">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#2d1a8e">MA</div>
                                    <div>
                                        <p class="emp-name">Municipal Assessor's Office</p>
                                        <p class="emp-id">MAO</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MAO</span></td>
                            <td><span class="position-cell">Municipal Assessor</span></td>
                            <td><span class="pay-cell">14</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MAO')">View</button></td>
                        </tr>
                        <tr data-code="MCR">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#6b3fa0">MC</div>
                                    <div>
                                        <p class="emp-name">Municipal Civil Registrar</p>
                                        <p class="emp-id">MCR</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MCR</span></td>
                            <td><span class="position-cell">Civil Registrar</span></td>
                            <td><span class="pay-cell">12</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MCR')">View</button></td>
                        </tr>
                        <tr data-code="MHO">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#0b044d">MH</div>
                                    <div>
                                        <p class="emp-name">Municipal Health Office</p>
                                        <p class="emp-id">MHO</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MHO</span></td>
                            <td><span class="position-cell">Municipal Health Officer</span></td>
                            <td><span class="pay-cell">38</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MHO')">View</button></td>
                        </tr>
                        <tr data-code="MSWD">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#3b1a6e">MW</div>
                                    <div>
                                        <p class="emp-name">MSWD - Pagsanjan</p>
                                        <p class="emp-id">MSWD</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MSWD</span></td>
                            <td><span class="position-cell">MSWD Officer</span></td>
                            <td><span class="pay-cell">27</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MSWD')">View</button></td>
                        </tr>
                        <tr data-code="MPDO">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#6b0f0b">MP</div>
                                    <div>
                                        <p class="emp-name">Municipal Planning & Dev't Office</p>
                                        <p class="emp-id">MPDO</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MPDO</span></td>
                            <td><span class="position-cell">MPDO Officer</span></td>
                            <td><span class="pay-cell">16</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MPDO')">View</button></td>
                        </tr>
                        <tr data-code="MEO">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background:#0b044d">ME</div>
                                    <div>
                                        <p class="emp-name">Office of the Mun. Engineer</p>
                                        <p class="emp-id">MEO</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-tag">MEO</span></td>
                            <td><span class="position-cell">Municipal Engineer</span></td>
                            <td><span class="pay-cell">22</span></td>
                            <td><span class="badge-status processed">Active</span></td>
                            <td><button class="btn-view" onclick="showDeptDetail('MEO')">View</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>1–10</strong> of <strong>17</strong> offices</span>
                <div class="pagination">
                    <button class="page-btn">‹</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">›</button>
                </div>
            </div>
        </div>

    </main>

    @include('admin.admin-chatbot')

</div>

<div class="modal-overlay" id="deptModal" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="emp-avatar xl" style="background: #0b044d; font-size: 13;" id="deptAvatar">OM</div>
                <div>
                    <span class="modal-eyebrow" id="deptEyebrow">DEPARTMENT DETAIL · OM</span>
                    <h3 class="modal-title" id="deptName">Office of the Mayor</h3>
                    <p class="modal-sub">Municipal Government of Pagsanjan</p>
                    <div class="pmodal-badges">
                        <span class="badge-status processed" id="deptStatus">Active</span>
                        <span class="badge-emptype" id="deptPersonnel">42 Personnel</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('deptModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2px;">Office Code</p>
                        <p style="font-size: 15px; font-weight: 800; color: #0b044d;" id="deptCodeDisplay">OM</p>
                    </div>
                </div>
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2px;">Total Personnel</p>
                        <p style="font-size: 15px; font-weight: 800; color: #15803d;" id="deptPersonnelDisplay">42</p>
                    </div>
                </div>
            </div>
            <div class="modal-section-label">OFFICE INFORMATION</div>
            <div class="modal-row"><span>Department Head</span><strong id="deptHeadDisplay">Hon. Mayor</strong></div>
            <div class="modal-row"><span>Status</span><span class="badge-status processed" id="deptStatusDisplay">Active</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('deptModal')">Close</button>
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

const departments = [
    { code: 'OM', name: 'Office of the Mayor', head: 'Hon. Mayor', personnel: 42, status: 'Active', color: '#0b044d' },
    { code: 'OVM', name: 'Office of the Vice Mayor', head: 'Hon. Vice Mayor', personnel: 18, status: 'Active', color: '#8e1e18' },
    { code: 'SB', name: 'Sangguniang Bayan', head: 'SB Secretary', personnel: 24, status: 'Active', color: '#1a0f6e' },
    { code: 'MTO', name: 'Office of the Municipal Treasurer', head: 'Municipal Treasurer', personnel: 31, status: 'Active', color: '#5a0f0b' },
    { code: 'MAO', name: "Municipal Assessor's Office", head: 'Municipal Assessor', personnel: 14, status: 'Active', color: '#2d1a8e' },
    { code: 'MCR', name: 'Municipal Civil Registrar', head: 'Civil Registrar', personnel: 12, status: 'Active', color: '#6b3fa0' },
    { code: 'MHO', name: 'Municipal Health Office', head: 'Municipal Health Officer', personnel: 38, status: 'Active', color: '#0b044d' },
    { code: 'MSWD', name: 'MSWD - Pagsanjan', head: 'MSWD Officer', personnel: 27, status: 'Active', color: '#3b1a6e' },
    { code: 'MPDO', name: "Municipal Planning & Dev't Office", head: 'MPDO Officer', personnel: 16, status: 'Active', color: '#6b0f0b' },
    { code: 'MEO', name: 'Office of the Mun. Engineer', head: 'Municipal Engineer', personnel: 22, status: 'Active', color: '#0b044d' },
    { code: 'MAGO', name: 'Office of the Mun. Agriculturist', head: 'Municipal Agriculturist', personnel: 19, status: 'Active', color: '#8e1e18' },
    { code: 'MENRO', name: 'Municipal Environment & Natural Resources', head: 'MENRO Officer', personnel: 11, status: 'Active', color: '#15803d' },
    { code: 'MBDO', name: "Municipal Business & Dev't Office", head: 'MBDO Officer', personnel: 9, status: 'Active', color: '#6b3fa0' },
    { code: 'HRMO', name: 'Human Resource Management Office', head: 'HRMO Officer', personnel: 8, status: 'Active', color: '#0b044d' },
    { code: 'MDRRMO', name: 'Municipal Disaster Risk Reduction & Mgmt', head: 'MDRRMO Officer', personnel: 15, status: 'Active', color: '#d9bb00' },
    { code: 'MBO', name: 'Office of the Mun. Budget', head: 'Municipal Budget Officer', personnel: 7, status: 'Active', color: '#1a0f6e' },
    { code: 'MCTC', name: 'Municipal Circuit Trial Court', head: 'Presiding Judge', personnel: 6, status: 'Active', color: '#5a0f0b' },
];

function showModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function showDeptDetail(code) {
    const dept = departments.find(d => d.code === code);
    if (!dept) return;
    document.getElementById('deptAvatar').textContent = dept.code.slice(0, 2);
    document.getElementById('deptAvatar').style.background = dept.color;
    document.getElementById('deptEyebrow').textContent = 'DEPARTMENT DETAIL · ' + dept.code;
    document.getElementById('deptName').textContent = dept.name;
    document.getElementById('deptPersonnel').textContent = dept.personnel + ' Personnel';
    document.getElementById('deptCodeDisplay').textContent = dept.code;
    document.getElementById('deptPersonnelDisplay').textContent = dept.personnel;
    document.getElementById('deptHeadDisplay').textContent = dept.head;
    document.getElementById('deptStatusDisplay').textContent = dept.status;
    showModal('deptModal');
}

function exportDepartments() { alert('Export functionality would generate Excel/CSV file.'); }

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none'); }
});

document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) { modal.style.display = 'none'; }
    });
});
</script>
@endsection