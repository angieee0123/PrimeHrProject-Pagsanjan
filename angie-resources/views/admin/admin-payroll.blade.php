@extends('layouts.app')

@section('title', 'Payroll Management · PRIME HRIS')

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
            <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
            <h2>Payroll Management</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Semi-Monthly Payroll Processing</p>
        </div>
    </div>
    <div class="banner-right">
        <span class="banner-badge">
            <span class="banner-badge-dot"></span>
            2 Pending
        </span>
        <span class="banner-badge outline">June 2025</span>
    </div>
</div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Gross Payroll</p>
                <div class="stat-icon-wrap" style="background: #f0effe">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M8 10h8M8 14h8"/></svg>
                </div>
            </div>
            <p class="stat-value" style="font-size:20px">₱127,485.50</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background: #0b044d"></span>
                <p class="stat-sub">June 16-30, 2025</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Total Net Pay</p>
                <div class="stat-icon-wrap" style="background: #e8f9ef">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
            </div>
            <p class="stat-value" style="font-size:20px">₱109,467.50</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background: #22c55e"></span>
                <p class="stat-sub">After deductions</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Total Deductions</p>
                <div class="stat-icon-wrap" style="background: #fdf0ef">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
            </div>
            <p class="stat-value" style="font-size:20px">₱18,018.00</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background: #8e1e18"></span>
                <p class="stat-sub">GSIS, PhilHealth etc</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">Pending Records</p>
                <div class="stat-icon-wrap" style="background: #fefce8">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#a16207" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <p class="stat-value">2</p>
            <div class="stat-footer">
                <span class="stat-dot" style="background: #f59e0b"></span>
                <p class="stat-sub">6 processed</p>
            </div>
        </div>
        </div>

        {{-- Payroll Table Section --}}
        <div class="table-section">
        <div class="table-header">
            <div>
                <p class="table-title">Payroll Register — June 16-30, 2025</p>
                <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: Jun 30, 2025 · 8 of 8 records</p>
            </div>
            <div class="table-actions">
                <select class="filter-select" id="semiFilter">
                    <option>1st (1-15)</option>
                    <option selected>2nd (16-30)</option>
                </select>
                <select class="filter-select" id="monthFilter">
                    <option>January</option><option>February</option><option>March</option>
                    <option>April</option><option>May</option>
                    <option selected>June</option>
                    <option>July</option><option>August</option><option>September</option>
                    <option>October</option><option>November</option><option>December</option>
                </select>
                <select class="filter-select" id="yearFilter">
                    <option>2025</option><option>2024</option><option>2023</option>
                </select>
                <select class="filter-select" id="deptFilter">
                    <option>All Departments</option>
                    <option>Office of the Mayor</option>
                    <option>Office of the Vice Mayor</option>
                    <option>Sangguniang Bayan</option>
                    <option>Office of the Mun. Treasurer</option>
                    <option>Municipal Assessor's Office</option>
                    <option>Municipal Civil Registrar</option>
                    <option>Municipal Health Office</option>
                    <option>MSWD - Pagsanjan</option>
                    <option>Municipal Planning & Dev't Office</option>
                    <option>Office of the Mun. Engineer</option>
                    <option>Office of the Mun. Agriculturist</option>
                    <option>Municipal Environment & Natural Resources</option>
                    <option>Municipal Business & Dev't Office</option>
                    <option>Human Resource Management Office</option>
                    <option>Municipal Disaster Risk Reduction & Mgmt</option>
                    <option>Office of the Mun. Budget</option>
                    <option>Municipal Circuit Trial Court</option>
                </select>
                <select class="filter-select" id="statusFilter">
                    <option value="All">All Status</option>
                    <option>Processed</option>
                    <option selected>Pending</option>
                    <option>On Hold</option>
                </select>
                <button class="btn-export" onclick="exportPayroll()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </button>
                <button class="modal-btn-primary" onclick="showRunPayrollConfirm()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Run Payroll
                </button>
            </div>
        </div>

        <div class="payroll-summary-bar" style="margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 16px; padding: 12px 16px; background: #f7f6ff; border-radius: 8px; border: 1px solid #eceaf8;">
            <div class="psummary-item" style="display: flex; flex-direction: column;">
                <span style="font-size: 11px; color: #6b6a8a;">Gross Total</span>
                <strong style="font-size: 14px; color: #0b044d;">₱127,485.50</strong>
            </div>
            <div class="psummary-divider" style="width: 1px; height: 32px; background: #e5e4f0;"></div>
            <div class="psummary-item" style="display: flex; flex-direction: column;">
                <span style="font-size: 11px; color: #6b6a8a;">Total Deductions</span>
                <strong class="deduction" style="font-size: 14px; color: #8e1e18;">₱18,018.00</strong>
            </div>
            <div class="psummary-divider" style="width: 1px; height: 32px; background: #e5e4f0;"></div>
            <div class="psummary-item" style="display: flex; flex-direction: column;">
                <span style="font-size: 11px; color: #6b6a8a;">Total Net Pay</span>
                <strong class="net-pay" style="font-size: 14px; color: #15803d;">₱109,467.50</strong>
            </div>
            <div class="psummary-divider" style="width: 1px; height: 32px; background: #e5e4f0;"></div>
            <div class="psummary-item" style="display: flex; flex-direction: column;">
                <span style="font-size: 11px; color: #6b6a8a;">Pay Date</span>
                <strong style="font-size: 14px; color: #0b044d;">Jun 30, 2025</strong>
            </div>
            <div class="psummary-divider" style="width: 1px; height: 32px; background: #e5e4f0;"></div>
            <div class="psummary-item" style="display: flex; flex-direction: column;">
                <span style="font-size: 11px; color: #6b6a8a;">Records</span>
                <strong style="font-size: 14px; color: #0b044d;">8</strong>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Basic Pay</th>
                        <th>GSIS</th>
                        <th>PhilHealth</th>
                        <th>Pag-IBIG</th>
                        <th>Tax</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="payrollTableBody">
                    <tr data-id="PGS-0041">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #0b044d;">MB</div>
                                <div>
                                    <p class="emp-name">Maria B. Santos</p>
                                    <p class="emp-id">PGS-0041</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Office of the Mayor</span></td>
                        <td class="pay-cell">₱21,079.50</td>
                        <td class="deduction">₱1,897.00</td>
                        <td class="deduction">₱525.00</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,743.50</td>
                        <td class="net-pay">₱16,864.00</td>
                        <td><span class="badge-status processed">Processed</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0041')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0041')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0082">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #8e1e18;">JC</div>
                                <div>
                                    <p class="emp-name">Juan P. dela Cruz</p>
                                    <p class="emp-id">PGS-0082</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Office of the Mun. Engineer</span></td>
                        <td class="pay-cell">₱19,042.50</td>
                        <td class="deduction">₱1,714.00</td>
                        <td class="deduction">₱475.00</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,569.50</td>
                        <td class="net-pay">₱15,234.00</td>
                        <td><span class="badge-status processed">Processed</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0082')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0082')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0115">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #1a0f6e;">AR</div>
                                <div>
                                    <p class="emp-name">Ana R. Reyes</p>
                                    <p class="emp-id">PGS-0115</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Municipal Health Office</span></td>
                        <td class="pay-cell">₱16,921.50</td>
                        <td class="deduction">₱1,523.00</td>
                        <td class="deduction">₱425.00</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,386.00</td>
                        <td class="net-pay">₱13,537.50</td>
                        <td><span class="badge-status pending">Pending</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0115')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0115')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0203">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #5a0f0b;">CM</div>
                                <div>
                                    <p class="emp-name">Carlos M. Mendoza</p>
                                    <p class="emp-id">PGS-0203</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Office of the Mun. Treasurer</span></td>
                        <td class="pay-cell">₱23,627.50</td>
                        <td class="deduction">₱2,126.50</td>
                        <td class="deduction">₱575.00</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,974.00</td>
                        <td class="net-pay">₱18,902.00</td>
                        <td><span class="badge-status processed">Processed</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0203')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0203')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0267">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #2d1a8e;">LG</div>
                                <div>
                                    <p class="emp-name">Liza G. Gomez</p>
                                    <p class="emp-id">PGS-0267</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">MSWD - Pagsanjan</span></td>
                        <td class="pay-cell">₱17,548.50</td>
                        <td class="deduction">₱1,579.50</td>
                        <td class="deduction">₱437.50</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,442.50</td>
                        <td class="net-pay">₱14,039.00</td>
                        <td><span class="badge-status on-hold">On Hold</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0267')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0267')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0310">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #6b3fa0;">RF</div>
                                <div>
                                    <p class="emp-name">Roberto T. Flores</p>
                                    <p class="emp-id">PGS-0310</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Municipal Civil Registrar</span></td>
                        <td class="pay-cell">₱15,265.50</td>
                        <td class="deduction">₱1,374.00</td>
                        <td class="deduction">₱387.50</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,241.50</td>
                        <td class="net-pay">₱12,212.50</td>
                        <td><span class="badge-status processed">Processed</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0310')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0310')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0342">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #0b044d;">GV</div>
                                <div>
                                    <p class="emp-name">Grace A. Villanueva</p>
                                    <p class="emp-id">PGS-0342</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Office of the Mun. Budget</span></td>
                        <td class="pay-cell">₱14,500.00</td>
                        <td class="deduction">₱1,305.00</td>
                        <td class="deduction">₱362.50</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱1,100.00</td>
                        <td class="net-pay">₱11,682.50</td>
                        <td><span class="badge-status pending">Pending</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0342')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0342')">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="PGS-0358">
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: #8e1e18;">RC</div>
                                <div>
                                    <p class="emp-name">Ramon D. Cruz</p>
                                    <p class="emp-id">PGS-0358</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="dept-tag">Office of the Mun. Agriculturist</span></td>
                        <td class="pay-cell">₱13,500.00</td>
                        <td class="deduction">₱1,215.00</td>
                        <td class="deduction">₱337.50</td>
                        <td class="deduction">₱50.00</td>
                        <td class="deduction">₱990.00</td>
                        <td class="net-pay">₱10,907.50</td>
                        <td><span class="badge-status processed">Processed</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-view" onclick="showPayslip('PGS-0358')">Payslip</button>
                                <button class="btn-edit" onclick="showEditPayroll('PGS-0358')">Edit</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <p>Showing <strong>8</strong> of <strong>8</strong> records</p>
            <div class="pagination">
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
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

<style>
.modal-overlay { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,4,77,0.55);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;visibility:hidden;transition:all 0.2s;padding:20px; }
.modal-overlay.show { opacity:1;visibility:visible; }
.modal-box { background:#fff;border-radius:16px;width:100%;max-width:480px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);transform:translateY(16px);transition:transform 0.2s;max-height:90vh;overflow:hidden;display:flex;flex-direction:column; }
.modal-overlay.show .modal-box { transform:translateY(0); }
.modal-box.modal-sm { max-width:400px; }
.modal-header { display:flex;justify-content:space-between;align-items:flex-start;padding:24px 24px 0;flex-shrink:0; }
.pmodal-hero { display:flex;gap:14px;align-items:center; }
.pmodal-hero-icon { width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.modal-eyebrow { font-size:10.5px;color:#9999bb;font-weight:700;letter-spacing:1px;display:block; }
.modal-title { font-size:18px;font-weight:700;color:#0b044d;margin:4px 0 2px; }
.modal-sub { font-size:13px;color:#6b6a8a;margin:0; }
.modal-close { background:none;border:none;cursor:pointer;padding:4px;color:#9999bb;flex-shrink:0; }
.modal-close:hover { color:#0b044d; }
.modal-body { padding:20px 24px;overflow-y:auto; }
.modal-section-label { font-size:10.5px;font-weight:700;color:#9999bb;letter-spacing:1px;margin-bottom:8px;display:block; }
.modal-row { display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f4f3ff;font-size:13px; }
.modal-row span:first-child { color:#6b6a8a; }
.modal-row strong { color:#0b044d;font-weight:600; }
.modal-row.total { border-top:1.5px solid #e5e4f0;border-bottom:none;margin-top:4px; }
.modal-row.total span { font-weight:700;color:#0b044d; }
.modal-deduct { color:#8e1e18;font-weight:600; }
.modal-net-row { display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#f0fdf4;border-radius:10px;margin-top:14px;border:1.5px solid #bbf7d0; }
.modal-net-row span { font-size:11px;font-weight:700;color:#15803d;letter-spacing:1px; }
.modal-net-row strong { font-size:20px;font-weight:800;color:#15803d; }
.modal-footer { display:flex;justify-content:flex-end;gap:10px;padding:16px 24px 24px;border-top:1px solid #e5e4f0;flex-shrink:0; }
.modal-btn-ghost { padding:9px 18px;border-radius:9px;border:1.5px solid #dddcf0;background:#fff;font-size:13px;font-weight:600;color:#6b6a8a;cursor:pointer; }
.modal-btn-ghost:hover { border-color:#0b044d;color:#0b044d; }
.modal-btn-primary { padding:9px 18px;border-radius:9px;border:none;background:linear-gradient(135deg,#0b044d,#1a0f6e);color:#fff;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
.pmodal-badges { display:flex;gap:6px;margin-top:8px; }
</style>

<div class="modal-overlay" id="payslipModal" onclick="closeModal('payslipModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon" id="payslipAvatar" style="background:linear-gradient(135deg,#0b044d,#1a0f6e);font-size:15px;font-weight:800;color:#fff;">MB</div>
                <div>
                    <span class="modal-eyebrow">PAYSLIP · JUNE 16-30, 2025</span>
                    <h3 class="modal-title" id="payslipName">Maria B. Santos</h3>
                    <p class="modal-sub" id="payslipDetails">Administrative Officer IV · Office of the Mayor</p>
                    <div class="pmodal-badges">
                        <span class="badge-status" id="payslipStatus">Processed</span>
                        <span class="badge-status on-hold" id="payslipId">PGS-0041</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('payslipModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-section-label">PAY PERIOD</div>
            <div class="modal-row"><span>Period</span><strong>June 16-30, 2025</strong></div>
            <div class="modal-row"><span>Pay Date</span><strong>Jun 30, 2025</strong></div>
            <div class="modal-section-label" style="margin-top: 16px;">EARNINGS</div>
            <div class="modal-row"><span>Basic Semi-Monthly Pay</span><strong id="payslipBasic">₱21,079.50</strong></div>
            <div class="modal-section-label" style="margin-top: 16px;">DEDUCTIONS</div>
            <div class="modal-row"><span>GSIS Premium</span><span class="modal-deduct" id="payslipGsis">₱1,897.00</span></div>
            <div class="modal-row"><span>PhilHealth</span><span class="modal-deduct" id="payslipPhilhealth">₱525.00</span></div>
            <div class="modal-row"><span>Pag-IBIG</span><span class="modal-deduct" id="payslipPagibig">₱50.00</span></div>
            <div class="modal-row"><span>Withholding Tax</span><span class="modal-deduct" id="payslipTax">₱1,743.50</span></div>
            <div class="modal-row total"><span>Total Deductions</span><span class="modal-deduct" id="payslipTotalDed">₱4,215.50</span></div>
            <div class="modal-net-row">
                <span>NET PAY</span>
                <strong id="payslipNet">₱16,864.00</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('payslipModal')">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Payslip
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="runPayrollModal" onclick="closeModal('runPayrollModal')">
    <div class="modal-box modal-sm" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">PAYROLL PROCESSING</span>
                <h3 class="modal-title">Process June 16-30, 2025 Payroll?</h3>
            </div>
            <button class="modal-close" onclick="closeModal('runPayrollModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-confirm-info" style="margin-bottom: 16px;">
                <div class="modal-row"><span>Pay Period</span><strong>June 16-30, 2025</strong></div>
                <div class="modal-row"><span>Total Personnel</span><strong>8</strong></div>
                <div class="modal-row"><span>Gross Payroll</span><strong>₱127,485.50</strong></div>
                <div class="modal-row"><span>Pay Date</span><strong>Jun 30, 2025</strong></div>
            </div>
            <p style="font-size: 13px; color: #8e1e18; background: #8e1e1818; padding: 10px 12px; border-radius: 6px;">⚠ This will finalize payroll for all listed employees. Ensure all DTR and leave records are updated before proceeding.</p>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('runPayrollModal')">Cancel</button>
            <button class="modal-btn-primary" onclick="confirmRunPayroll()">Confirm & Process</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="successModal" onclick="closeModal('successModal')">
    <div class="modal-box modal-sm" onclick="event.stopPropagation()">
        <div class="modal-body" style="text-align:center;padding-top:28px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#15803d18;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-title" style="margin-bottom:8px;">Payroll Processed!</h3>
            <p style="font-size:13.5px;color:#6b6a8a;margin-bottom:16px;">June 16-30, 2025 payroll for <strong>8 employees</strong> has been successfully processed.</p>
            <div style="text-align:left;">
                <div class="modal-row"><span>Reference No.</span><strong>PAY-2025-06-002</strong></div>
                <div class="modal-row"><span>Pay Date</span><strong>Jun 30, 2025</strong></div>
                <div class="modal-row"><span>Processed by</span><strong>Admin User</strong></div>
                <div class="modal-row"><span>Date &amp; Time</span><strong>Jun 25, 2025 · 10:42 AM</strong></div>
            </div>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="modal-btn-primary" style="width:100%;justify-content:center;" onclick="closeModal('successModal')">Done</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="editPayrollModal" onclick="closeModal('editPayrollModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">EDIT PAYROLL RECORD</span>
                <h3 class="modal-title" id="editName">Maria B. Santos</h3>
                <p class="modal-sub" id="editId">PGS-0041</p>
            </div>
            <button class="modal-close" onclick="closeModal('editPayrollModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 500; color: #6b6a8a; margin-bottom: 4px;">Basic Semi-Monthly Pay (₱)</label>
                    <input type="number" id="editBasic" value="21079.50" style="width: 100%; padding: 8px 12px; border: 1px solid #e5e4f0; border-radius: 6px; font-size: 14px;" />
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 500; color: #6b6a8a; margin-bottom: 4px;">GSIS (₱)</label>
                    <input type="number" id="editGsis" value="1897" style="width: 100%; padding: 8px 12px; border: 1px solid #e5e4f0; border-radius: 6px; font-size: 14px;" />
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 500; color: #6b6a8a; margin-bottom: 4px;">PhilHealth (₱)</label>
                    <input type="number" id="editPhilhealth" value="525" style="width: 100%; padding: 8px 12px; border: 1px solid #e5e4f0; border-radius: 6px; font-size: 14px;" />
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 500; color: #6b6a8a; margin-bottom: 4px;">Pag-IBIG (₱)</label>
                    <input type="number" id="editPagibig" value="50" style="width: 100%; padding: 8px 12px; border: 1px solid #e5e4f0; border-radius: 6px; font-size: 14px;" />
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 500; color: #6b6a8a; margin-bottom: 4px;">Withholding Tax (₱)</label>
                    <input type="number" id="editTax" value="1743.50" style="width: 100%; padding: 8px 12px; border: 1px solid #e5e4f0; border-radius: 6px; font-size: 14px;" />
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 500; color: #6b6a8a; margin-bottom: 4px;">Status</label>
                    <select id="editStatus" style="width: 100%; padding: 8px 12px; border: 1px solid #e5e4f0; border-radius: 6px; font-size: 14px; background: white;">
                        <option>Processed</option><option>Pending</option><option>On Hold</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 16px; display: flex; justify-content: space-between; padding: 12px 16px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                <span>NET PAY PREVIEW</span>
                <strong id="editNetPreview" style="color: #15803d;">₱16,864.00</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('editPayrollModal')">Cancel</button>
            <button class="modal-btn-primary" onclick="savePayrollEdit()">Save Changes</button>
        </div>
    </div>
</div>

<script>
const payrollData = [
    { id: 'PGS-0041', name: 'Maria B. Santos', position: 'Administrative Officer IV', dept: 'Office of the Mayor', basic: 21079.50, gsis: 1897, philhealth: 525, pagibig: 50, tax: 1743.50, status: 'Processed', color: '#0b044d', initials: 'MB' },
    { id: 'PGS-0082', name: 'Juan P. dela Cruz', position: 'Municipal Engineer II', dept: 'Office of the Mun. Engineer', basic: 19042.50, gsis: 1714, philhealth: 475, pagibig: 50, tax: 1569.50, status: 'Processed', color: '#8e1e18', initials: 'JC' },
    { id: 'PGS-0115', name: 'Ana R. Reyes', position: 'Nurse II', dept: 'Municipal Health Office', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386, status: 'Pending', color: '#1a0f6e', initials: 'AR' },
    { id: 'PGS-0203', name: 'Carlos M. Mendoza', position: 'Municipal Treasurer III', dept: 'Office of the Mun. Treasurer', basic: 23627.50, gsis: 2126.50, philhealth: 575, pagibig: 50, tax: 1974, status: 'Processed', color: '#5a0f0b', initials: 'CM' },
    { id: 'PGS-0267', name: 'Liza G. Gomez', position: 'Social Welfare Officer II', dept: 'MSWD - Pagsanjan', basic: 17548.50, gsis: 1579.50, philhealth: 437.50, pagibig: 50, tax: 1442.50, status: 'On Hold', color: '#2d1a8e', initials: 'LG' },
    { id: 'PGS-0310', name: 'Roberto T. Flores', position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar', basic: 15265.50, gsis: 1374, philhealth: 387.50, pagibig: 50, tax: 1241.50, status: 'Processed', color: '#6b3fa0', initials: 'RF' },
    { id: 'PGS-0342', name: 'Grace A. Villanueva', position: 'Budget Officer II', dept: 'Office of the Mun. Budget', basic: 14500, gsis: 1305, philhealth: 362.50, pagibig: 50, tax: 1100, status: 'Pending', color: '#0b044d', initials: 'GV' },
    { id: 'PGS-0358', name: 'Ramon D. Cruz', position: 'Agriculturist I', dept: 'Office of the Mun. Agriculturist', basic: 13500, gsis: 1215, philhealth: 337.50, pagibig: 50, tax: 990, status: 'Processed', color: '#8e1e18', initials: 'RC' },
];

const peso = n => '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });

function showModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function showPayslip(id) {
    const emp = payrollData.find(e => e.id === id);
    if (!emp) return;
    const deductions = emp.gsis + emp.philhealth + emp.pagibig + emp.tax;
    const net = emp.basic - deductions;
    const statusClass = emp.status === 'Processed' ? 'processed' : emp.status === 'Pending' ? 'pending' : 'on-hold';
    document.getElementById('payslipName').textContent = emp.name;
    document.getElementById('payslipDetails').textContent = emp.position + ' · ' + emp.dept;
    const avatar = document.getElementById('payslipAvatar');
    avatar.textContent = emp.initials;
    avatar.style.background = 'linear-gradient(135deg,' + emp.color + ',' + emp.color + 'cc)';
    document.getElementById('payslipId').textContent = emp.id;
    document.getElementById('payslipStatus').className = 'badge-status ' + statusClass;
    document.getElementById('payslipStatus').textContent = emp.status;
    document.getElementById('payslipBasic').textContent = peso(emp.basic);
    document.getElementById('payslipGsis').textContent = peso(emp.gsis);
    document.getElementById('payslipPhilhealth').textContent = peso(emp.philhealth);
    document.getElementById('payslipPagibig').textContent = peso(emp.pagibig);
    document.getElementById('payslipTax').textContent = peso(emp.tax);
    document.getElementById('payslipTotalDed').textContent = peso(deductions);
    document.getElementById('payslipNet').textContent = peso(net);
    showModal('payslipModal');
}

function showRunPayrollConfirm() { showModal('runPayrollModal'); }

function confirmRunPayroll() {
    payrollData.forEach(e => { if (e.status === 'Pending') e.status = 'Processed'; });
    closeModal('runPayrollModal');
    showModal('successModal');
    updateTable();
}

function showEditPayroll(id) {
    const emp = payrollData.find(e => e.id === id);
    if (!emp) return;
    document.getElementById('editName').textContent = emp.name;
    document.getElementById('editId').textContent = emp.id;
    document.getElementById('editBasic').value = emp.basic;
    document.getElementById('editGsis').value = emp.gsis;
    document.getElementById('editPhilhealth').value = emp.philhealth;
    document.getElementById('editPagibig').value = emp.pagibig;
    document.getElementById('editTax').value = emp.tax;
    document.getElementById('editStatus').value = emp.status;
    updateNetPreview();
    showModal('editPayrollModal');
}

function updateNetPreview() {
    const basic = parseFloat(document.getElementById('editBasic').value) || 0;
    const gsis = parseFloat(document.getElementById('editGsis').value) || 0;
    const philhealth = parseFloat(document.getElementById('editPhilhealth').value) || 0;
    const pagibig = parseFloat(document.getElementById('editPagibig').value) || 0;
    const tax = parseFloat(document.getElementById('editTax').value) || 0;
    const net = basic - gsis - philhealth - pagibig - tax;
    document.getElementById('editNetPreview').textContent = peso(net);
}

['editBasic', 'editGsis', 'editPhilhealth', 'editPagibig', 'editTax'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateNetPreview);
});

function savePayrollEdit() {
    const id = document.getElementById('editId').textContent;
    const emp = payrollData.find(e => e.id === id);
    if (emp) {
        emp.basic = parseFloat(document.getElementById('editBasic').value);
        emp.gsis = parseFloat(document.getElementById('editGsis').value);
        emp.philhealth = parseFloat(document.getElementById('editPhilhealth').value);
        emp.pagibig = parseFloat(document.getElementById('editPagibig').value);
        emp.tax = parseFloat(document.getElementById('editTax').value);
        emp.status = document.getElementById('editStatus').value;
    }
    closeModal('editPayrollModal');
    updateTable();
}

function updateTable() {
    const tbody = document.getElementById('payrollTableBody');
    tbody.innerHTML = payrollData.map(emp => {
        const deductions = emp.gsis + emp.philhealth + emp.pagibig + emp.tax;
        const net = emp.basic - deductions;
        const statusClass = emp.status === 'Processed' ? 'processed' : emp.status === 'Pending' ? 'pending' : 'on-hold';
        return `<tr data-id="${emp.id}">
            <td><div class="emp-cell"><div class="emp-avatar" style="background: ${emp.color};">${emp.initials}</div><div><p class="emp-name">${emp.name}</p><p class="emp-id">${emp.id}</p></div></div></td>
            <td><span class="dept-tag">${emp.dept}</span></td>
            <td class="pay-cell">${peso(emp.basic)}</td>
            <td class="deduction">${peso(emp.gsis)}</td>
            <td class="deduction">${peso(emp.philhealth)}</td>
            <td class="deduction">${peso(emp.pagibig)}</td>
            <td class="deduction">${peso(emp.tax)}</td>
            <td class="net-pay">${peso(net)}</td>
            <td><span class="badge-status ${statusClass}">${emp.status}</span></td>
            <td><div class="row-actions"><button class="btn-view" onclick="showPayslip('${emp.id}')">Payslip</button><button class="btn-edit" onclick="showEditPayroll('${emp.id}')">Edit</button></div></td>
        </tr>`;
    }).join('');
}

function exportPayroll() { alert('Export functionality would generate Excel/CSV file.'); }

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    }
});


</script>
@endsection