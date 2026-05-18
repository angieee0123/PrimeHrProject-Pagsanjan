@extends('layouts.permanent')

@section('title', 'Attendance · PRIME HRIS')

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
    <main class="main-content permanent-dashboard permanent-attendance">

        @include('permanent.notification.permanentNotification')

        @include('permanent.topbar.attendanceTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Days Present</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $present }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">{{ $late }} late arrival{{ $late != 1 ? 's' : '' }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Days Absent</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $absent }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">This month</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Overtime Hours</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $overtime }}h</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">{{ $onLeave }} leave day{{ $onLeave != 1 ? 's' : '' }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance Rate</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $rate }}%</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">{{ $workingDaysCount }} working days</p>
                </div>
            </div>

        </div>

        {{-- Summary Bar --}}
        <div class="attendance-summary-bar">
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Total Present</p>
                <p class="attendance-summary-value attendance-summary-value-success">{{ $present }}</p>
                <p class="attendance-summary-sub">days</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Total Absent</p>
                <p class="attendance-summary-value attendance-summary-value-danger">{{ $absent }}</p>
                <p class="attendance-summary-sub">days</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Late Arrivals</p>
                <p class="attendance-summary-value attendance-summary-value-warning">{{ $late }}</p>
                <p class="attendance-summary-sub">times</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Overtime</p>
                <p class="attendance-summary-value attendance-summary-value-primary">{{ $overtime }}</p>
                <p class="attendance-summary-sub">hrs</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Leave Days</p>
                <p class="attendance-summary-value attendance-summary-value-primary">{{ $onLeave }}</p>
                <p class="attendance-summary-sub">days</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
            <button class="tab-btn active" onclick="switchTab('summary')">Daily Time Record</button>
            <button class="tab-btn" onclick="switchTab('detailed')">Detailed Time Record</button>
        </div>

        {{-- Daily Time Record Tab --}}
        <div class="table-section" id="summary-tab">
            <div class="table-header">
                <div>
                    <p class="table-title">Daily Time Record</p>
                    <p class="table-sub">{{ $periodDisplay }} attendance records</p>
                </div>
                <div class="table-actions">
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" onclick="showDTRModal()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Summary
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table attendance-history-table detailed-dtr-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>AM In</th>
                            <th>AM Out</th>
                            <th>PM In</th>
                            <th>PM Out</th>
                            <th>OT In</th>
                            <th>OT Out</th>
                            <th>Late</th>
                            <th>Undertime</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="dtrTableBody">
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 40px; color: #9999bb;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin: 0 auto;">
                                    <circle cx="12" cy="12" r="10" opacity="0.25"/>
                                    <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/>
                                </svg>
                                <p style="margin-top: 12px;">Loading attendance records...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong id="recordRange">0</strong> of <strong id="recordCount">0</strong> record<span id="recordPlural">s</span></span>
                <div class="pagination" id="pagination" style="display: none;">
                    <button class="page-btn" id="prevBtn" onclick="changePage(-1)">‹</button>
                    <div id="pageNumbers"></div>
                    <button class="page-btn" id="nextBtn" onclick="changePage(1)">›</button>
                </div>
            </div>
        </div>

        {{-- Detailed Time Record Tab --}}
        <div class="table-section" id="detailed-tab" style="display: none;">
            <div class="table-header">
                <div>
                    <p class="table-title">Detailed Time Record</p>
                    <p class="table-sub">{{ $periodDisplay }} · Daily attendance logs with timestamps</p>
                </div>
                <div class="table-actions">
                    <input type="date" id="detailedStartDate" class="filter-select" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    <span style="font-size: 12px; color: #9999bb;">to</span>
                    <input type="date" id="detailedEndDate" class="filter-select" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                    <button class="btn-filter-main" onclick="filterDetailedRecords()">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filter
                    </button>
                    <button class="btn-filter-main" onclick="clearDetailedFilters()" style="background: #f7f6ff; color: #0b044d; border: 1px solid #e8e7f5;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Clear
                    </button>
                    <button class="btn-export" onclick="exportDetailedRecords()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table attendance-history-table detailed-dtr-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>AM In</th>
                            <th>AM Out</th>
                            <th>PM In</th>
                            <th>PM Out</th>
                            <th>OT In</th>
                            <th>OT Out</th>
                            <th>Late</th>
                            <th>Undertime</th>
                            <th>Total Hours</th>
                            <th>Accredited Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="detailedTableBody">
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 40px; color: #9999bb;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin: 0 auto;">
                                    <circle cx="12" cy="12" r="10" opacity="0.25"/>
                                    <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/>
                                </svg>
                                <p style="margin-top: 12px;">Loading detailed records...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong id="detailedRecordRange">0</strong> of <strong id="detailedRecordCount">0</strong> record<span id="detailedRecordPlural">s</span></span>
                <div class="pagination" id="detailedPagination" style="display: none;">
                    <button class="page-btn" id="detailedPrevBtn" onclick="changeDetailedPage(-1)">‹</button>
                    <div id="detailedPageNumbers"></div>
                    <button class="page-btn" id="detailedNextBtn" onclick="changeDetailedPage(1)">›</button>
                </div>
            </div>
        </div>

    </main>

</div>

{{-- DTR Modal --}}
<div class="modal-overlay" id="dtrModal" onclick="closeModal('dtrModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DAILY TIME RECORD · {{ strtoupper($periodDisplay) }}</span>
                <h3 class="modal-title">{{ $employee->first_name }} {{ $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '' }} {{ $employee->last_name }}</h3>
                <p class="modal-sub">{{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
            </div>
            <button class="modal-close" onclick="closeModal('dtrModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ $employee->employee_id }}</p>
                    <span class="badge-status {{ $absent == 0 && $late <= 2 ? 'processed' : 'pending' }}">{{ $absent == 0 && $late <= 2 ? 'Complete' : 'Incomplete' }}</span>
                </div>
            </div>
            <div class="modal-section-label">ATTENDANCE SUMMARY</div>
            <div class="modal-row"><span>Working Days</span><strong>{{ $workingDaysCount }} days</strong></div>
            <div class="modal-row"><span>Days Present</span><strong>{{ $present }} day{{ $present != 1 ? 's' : '' }}</strong></div>
            <div class="modal-row"><span>Days Absent</span><strong>{{ $absent }} day{{ $absent != 1 ? 's' : '' }}</strong></div>
            <div class="modal-row"><span>Late Arrivals</span><strong>{{ $late }} time{{ $late != 1 ? 's' : '' }}</strong></div>
            <div class="modal-row"><span>Leave Days</span><strong>{{ $onLeave }} day{{ $onLeave != 1 ? 's' : '' }}</strong></div>
            <div class="modal-section-label modal-section-deductions">OVERTIME</div>
            <div class="modal-row"><span>Total OT Hours</span><strong>{{ $overtime }} hrs</strong></div>
            <div class="modal-net-row">
                <span>ATTENDANCE RATE</span>
                <strong>{{ $rate }}%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('dtrModal')">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download DTR
            </button>
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

    function showDTRModal() {
        document.getElementById('dtrModal').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function filterPermanentAttendanceTable(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.payroll-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        }
    });

    // Tab switching functionality
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById(tabName + '-tab').style.display = 'block';
        
        if (tabName === 'detailed' && !window.detailedLoaded) {
            loadDetailedTable();
            window.detailedLoaded = true;
        }
    }

    // Load DTR on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadMainDTR();
    });

    let allRecords = [];
    let currentPage = 1;
    const recordsPerPage = 10;

    function loadMainDTR() {
        const startDate = '{{ now()->startOfMonth()->format('Y-m-d') }}';
        const endDate = '{{ now()->endOfMonth()->format('Y-m-d') }}';
        
        fetch(`{{ route('permanent.attendance.detailed') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                allRecords = data.records;
                currentPage = 1;
                displayMainDTR();
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('dtrTableBody').innerHTML = `
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 40px; color: #8e1e18;">
                            Failed to load attendance records. Please refresh the page.
                        </td>
                    </tr>
                `;
            });
    }

    function displayMainDTR() {
        const tbody = document.getElementById('dtrTableBody');
        tbody.innerHTML = '';
        
        if (allRecords.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" style="text-align: center; padding: 40px; color: #9999bb;">
                        No attendance records found for this period.
                    </td>
                </tr>
            `;
            document.getElementById('recordRange').textContent = '0';
            document.getElementById('recordCount').textContent = '0';
            document.getElementById('recordPlural').textContent = 's';
            document.getElementById('pagination').style.display = 'none';
            return;
        }
        
        // Calculate pagination
        const totalPages = Math.ceil(allRecords.length / recordsPerPage);
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, allRecords.length);
        const currentRecords = allRecords.slice(startIndex, endIndex);
        
        // Display records
        currentRecords.forEach(record => {
            const row = document.createElement('tr');
            
            const isOnLeave = record.is_on_leave;
            const isAbsent = !record.am_in && !record.pm_in && !isOnLeave;
            
            let statusBadge = '';
            if (isOnLeave) {
                statusBadge = '<span class="badge-status processed">On Leave</span>';
            } else if (isAbsent) {
                statusBadge = '<span class="badge-status on-hold">Absent</span>';
            } else if (record.late_minutes > 0) {
                statusBadge = '<span class="badge-status pending">Late</span>';
            } else if (record.accredited_minutes > 0) {
                statusBadge = '<span class="badge-status processed">Present</span>';
            } else {
                statusBadge = '<span class="badge-status on-hold">Incomplete</span>';
            }
            
            row.innerHTML = `
                <td class="table-cell-period">${record.date}</td>
                <td class="attendance-cell-day">${record.day}</td>
                <td class="${record.am_in ? 'attendance-cell-time' : 'attendance-cell-muted'}">${record.am_in || '-'}</td>
                <td class="${record.am_out ? 'attendance-cell-time' : 'attendance-cell-muted'}">${record.am_out || '-'}</td>
                <td class="${record.pm_in ? 'attendance-cell-time' : 'attendance-cell-muted'}">${record.pm_in || '-'}</td>
                <td class="${record.pm_out ? 'attendance-cell-time' : 'attendance-cell-muted'}">${record.pm_out || '-'}</td>
                <td class="${record.ot_in ? 'attendance-cell-time' : 'attendance-cell-muted'}">${record.ot_in || '-'}</td>
                <td class="${record.ot_out ? 'attendance-cell-time' : 'attendance-cell-muted'}">${record.ot_out || '-'}</td>
                <td class="${record.late_minutes > 0 ? 'table-cell-deduct' : 'attendance-cell-muted'}">${record.late_display}</td>
                <td class="${record.undertime > 0 ? 'table-cell-deduct' : 'attendance-cell-muted'}">${record.undertime_display}</td>
                <td class="attendance-cell-time">${record.total_hours}</td>
                <td>${statusBadge}</td>
            `;
            tbody.appendChild(row);
        });
        
        // Update footer
        document.getElementById('recordRange').textContent = `${startIndex + 1}–${endIndex}`;
        document.getElementById('recordCount').textContent = allRecords.length;
        document.getElementById('recordPlural').textContent = allRecords.length === 1 ? '' : 's';
        
        // Update pagination
        if (totalPages > 1) {
            document.getElementById('pagination').style.display = 'flex';
            updatePagination(totalPages);
        } else {
            document.getElementById('pagination').style.display = 'none';
        }
    }

    function updatePagination(totalPages) {
        const pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';
        
        // Determine which page numbers to show
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        // Adjust if we're near the beginning or end
        if (currentPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
        }
        
        // Add first page and ellipsis if needed
        if (startPage > 1) {
            addPageButton(1);
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 8px';
                ellipsis.style.color = '#9999bb';
                pageNumbers.appendChild(ellipsis);
            }
        }
        
        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            addPageButton(i);
        }
        
        // Add ellipsis and last page if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 8px';
                ellipsis.style.color = '#9999bb';
                pageNumbers.appendChild(ellipsis);
            }
            addPageButton(totalPages);
        }
        
        // Update prev/next buttons
        document.getElementById('prevBtn').disabled = currentPage === 1;
        document.getElementById('nextBtn').disabled = currentPage === totalPages;
    }

    function addPageButton(pageNum) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (pageNum === currentPage ? ' active' : '');
        btn.textContent = pageNum;
        btn.onclick = () => goToPage(pageNum);
        document.getElementById('pageNumbers').appendChild(btn);
    }

    function goToPage(page) {
        currentPage = page;
        displayMainDTR();
        // Scroll to top of table
        document.querySelector('.table-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function changePage(direction) {
        const totalPages = Math.ceil(allRecords.length / recordsPerPage);
        const newPage = currentPage + direction;
        
        if (newPage >= 1 && newPage <= totalPages) {
            goToPage(newPage);
        }
    }

    // Detailed Tab Variables
    let allDetailedRecords = [];
    let currentDetailedPage = 1;
    const detailedRecordsPerPage = 15;

    function loadDetailedTable() {
        const startDate = document.getElementById('detailedStartDate')?.value || '{{ now()->startOfMonth()->format('Y-m-d') }}';
        const endDate = document.getElementById('detailedEndDate')?.value || '{{ now()->endOfMonth()->format('Y-m-d') }}';
        
        // Show loading state
        const tbody = document.getElementById('detailedTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #9999bb;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin: 0 auto;">
                        <circle cx="12" cy="12" r="10" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/>
                    </svg>
                    <p style="margin-top: 12px;">Loading detailed records...</p>
                </td>
            </tr>
        `;
        
        fetch(`{{ route('permanent.attendance.detailed') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                allDetailedRecords = data.records;
                currentDetailedPage = 1;
                displayDetailedTable();
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #8e1e18;">
                            Failed to load detailed records. Please refresh the page.
                        </td>
                    </tr>
                `;
            });
    }

    function filterDetailedRecords() {
        const startDate = document.getElementById('detailedStartDate').value;
        const endDate = document.getElementById('detailedEndDate').value;
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date must be before or equal to end date');
            return;
        }
        
        loadDetailedTable();
    }

    function clearDetailedFilters() {
        document.getElementById('detailedStartDate').value = '{{ now()->startOfMonth()->format('Y-m-d') }}';
        document.getElementById('detailedEndDate').value = '{{ now()->endOfMonth()->format('Y-m-d') }}';
        loadDetailedTable();
    }

    function displayDetailedTable() {
        const tbody = document.getElementById('detailedTableBody');
        tbody.innerHTML = '';
        
        if (allDetailedRecords.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="13" style="text-align: center; padding: 40px; color: #9999bb;">
                        No detailed records found for this period.
                    </td>
                </tr>
            `;
            document.getElementById('detailedRecordRange').textContent = '0';
            document.getElementById('detailedRecordCount').textContent = '0';
            document.getElementById('detailedRecordPlural').textContent = 's';
            document.getElementById('detailedPagination').style.display = 'none';
            return;
        }
        
        const totalPages = Math.ceil(allDetailedRecords.length / detailedRecordsPerPage);
        const startIndex = (currentDetailedPage - 1) * detailedRecordsPerPage;
        const endIndex = Math.min(startIndex + detailedRecordsPerPage, allDetailedRecords.length);
        const currentRecords = allDetailedRecords.slice(startIndex, endIndex);
        
        currentRecords.forEach(record => {
            const row = document.createElement('tr');
            const isOnLeave = record.is_on_leave;
            const hasAmIn = record.am_in && record.am_in !== '-';
            const hasAmOut = record.am_out && record.am_out !== '-';
            const hasPmIn = record.pm_in && record.pm_in !== '-';
            const hasPmOut = record.pm_out && record.pm_out !== '-';
            const isAbsent = !hasAmIn && !hasPmIn && !isOnLeave;
            const isWeekend = record.day === 'Saturday' || record.day === 'Sunday';
            
            // Determine attendance status
            let status = '';
            let statusColor = '';
            let statusBg = '';
            
            if (isOnLeave) {
                status = 'On Leave';
                statusColor = '#6b6a8a';
                statusBg = '#6b6a8a18';
            } else if (!hasAmIn && !hasPmIn) {
                // No clock in at all = Absent
                status = 'Absent';
                statusColor = '#8e1e18';
                statusBg = '#8e1e1818';
            } else if ((hasAmIn && !hasAmOut && !hasPmIn) || (hasPmIn && !hasPmOut && !hasAmIn)) {
                // Clocked in but never clocked out (single period only) = Abandoned
                status = 'Abandoned';
                statusColor = '#d97706';
                statusBg = '#d9770618';
            } else if (hasAmIn && hasAmOut && hasPmIn && hasPmOut) {
                // All 4 logs complete = Present
                status = 'Present';
                statusColor = '#15803d';
                statusBg = '#15803d18';
            } else {
                // Has some attendance but not complete = Incomplete
                status = 'Incomplete';
                statusColor = '#d9bb00';
                statusBg = '#d9bb0018';
            }
            
            // Apply row classes
            if (isWeekend) {
                row.classList.add('day-weekend');
            } else if (isAbsent && !isOnLeave) {
                row.classList.add('day-absent');
            }
            
            // Late display
            let lateDisplay = '<span style="color: #9999bb;">—</span>';
            if (record.late_minutes > 0) {
                const lateHrs = Math.floor(record.late_minutes / 60);
                const lateMins = record.late_minutes % 60;
                lateDisplay = `<span style="color: #a16207; font-weight: 600;">${lateHrs > 0 ? lateHrs + 'h ' + lateMins + 'm' : lateMins + ' min'}</span>`;
            }
            
            // Undertime display
            let undertimeDisplay = '<span style="color: #9999bb;">—</span>';
            if (record.undertime > 0) {
                const utHrs = Math.floor(record.undertime / 60);
                const utMins = record.undertime % 60;
                undertimeDisplay = `<span style="color: #8e1e18; font-weight: 600;">${utHrs > 0 ? utHrs + 'h ' + utMins + 'm' : utMins + ' min'}</span>`;
            }
            
            // Total hours display
            let totalHoursDisplay = record.total_hours || '0h 0m';
            
            // Accredited hours display
            const accredited = record.accredited_minutes || 0;
            const accHrs = Math.floor(accredited / 60);
            const accMins = accredited % 60;
            const accColor = accredited >= 480 ? '#15803d' : (accredited >= 240 ? '#a16207' : '#8e1e18');
            let accreditedDisplay = `<strong style="color: ${accColor};">${accHrs}h ${accMins}m</strong>`;
            
            row.innerHTML = `
                <td><strong>${record.date}</strong></td>
                <td style="color: #6b6a8a;">${record.day}</td>
                <td><span style="font-family: 'Courier New', monospace; color: ${hasAmIn ? '#0b044d' : '#9999bb'};">${record.am_in || '--:--'}</span></td>
                <td><span style="font-family: 'Courier New', monospace; color: ${hasAmOut ? '#0b044d' : '#9999bb'};">${record.am_out || '--:--'}</span></td>
                <td><span style="font-family: 'Courier New', monospace; color: ${hasPmIn ? '#0b044d' : '#9999bb'};">${record.pm_in || '--:--'}</span></td>
                <td><span style="font-family: 'Courier New', monospace; color: ${hasPmOut ? '#0b044d' : '#9999bb'};">${record.pm_out || '--:--'}</span></td>
                <td><span style="font-family: 'Courier New', monospace; color: ${record.ot_in ? '#0b044d' : '#9999bb'};">${record.ot_in || '--:--'}</span></td>
                <td><span style="font-family: 'Courier New', monospace; color: ${record.ot_out ? '#0b044d' : '#9999bb'};">${record.ot_out || '--:--'}</span></td>
                <td>${lateDisplay}</td>
                <td>${undertimeDisplay}</td>
                <td><strong>${totalHoursDisplay}</strong></td>
                <td>${accreditedDisplay}</td>
                <td><span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; background: ${statusBg}; color: ${statusColor};">${status}</span></td>
            `;
            tbody.appendChild(row);
        });
        
        document.getElementById('detailedRecordRange').textContent = `${startIndex + 1}–${endIndex}`;
        document.getElementById('detailedRecordCount').textContent = allDetailedRecords.length;
        document.getElementById('detailedRecordPlural').textContent = allDetailedRecords.length === 1 ? '' : 's';
        
        if (totalPages > 1) {
            document.getElementById('detailedPagination').style.display = 'flex';
            updateDetailedPagination(totalPages);
        } else {
            document.getElementById('detailedPagination').style.display = 'none';
        }
    }

    function updateDetailedPagination(totalPages) {
        const pageNumbers = document.getElementById('detailedPageNumbers');
        pageNumbers.innerHTML = '';
        
        let startPage = Math.max(1, currentDetailedPage - 2);
        let endPage = Math.min(totalPages, currentDetailedPage + 2);
        
        if (currentDetailedPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentDetailedPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
        }
        
        if (startPage > 1) {
            addDetailedPageButton(1);
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 8px';
                ellipsis.style.color = '#9999bb';
                pageNumbers.appendChild(ellipsis);
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            addDetailedPageButton(i);
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 8px';
                ellipsis.style.color = '#9999bb';
                pageNumbers.appendChild(ellipsis);
            }
            addDetailedPageButton(totalPages);
        }
        
        document.getElementById('detailedPrevBtn').disabled = currentDetailedPage === 1;
        document.getElementById('detailedNextBtn').disabled = currentDetailedPage === totalPages;
    }

    function addDetailedPageButton(pageNum) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (pageNum === currentDetailedPage ? ' active' : '');
        btn.textContent = pageNum;
        btn.onclick = () => goToDetailedPage(pageNum);
        document.getElementById('detailedPageNumbers').appendChild(btn);
    }

    function goToDetailedPage(page) {
        currentDetailedPage = page;
        displayDetailedTable();
        document.querySelector('#detailed-tab').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function changeDetailedPage(direction) {
        const totalPages = Math.ceil(allDetailedRecords.length / detailedRecordsPerPage);
        const newPage = currentDetailedPage + direction;
        
        if (newPage >= 1 && newPage <= totalPages) {
            goToDetailedPage(newPage);
        }
    }

    // Export detailed records
    function exportDetailedRecords() {
        if (allDetailedRecords.length === 0) {
            alert('No records to export');
            return;
        }
        
        const startDate = document.getElementById('detailedStartDate').value;
        const endDate = document.getElementById('detailedEndDate').value;
        const dateRange = startDate === endDate ? startDate : `${startDate}_to_${endDate}`;
        
        let csv = 'Date,Day,Time In,Time Out,Late,Undertime,Hours Worked,Status\n';
        
        allDetailedRecords.forEach(record => {
            const isOnLeave = record.is_on_leave;
            const isAbsent = !record.am_in && !record.pm_in && !isOnLeave;
            
            let status = '';
            if (isOnLeave) {
                status = 'On Leave';
            } else if (isAbsent) {
                status = 'Absent';
            } else if (record.late_minutes > 0) {
                status = 'Late';
            } else if (record.accredited_minutes > 0) {
                status = 'Present';
            } else {
                status = 'Incomplete';
            }
            
            csv += `${record.date},${record.day},`;
            csv += `"${record.am_in || ''} ${record.pm_in || ''}".trim(),`;
            csv += `"${record.am_out || ''} ${record.pm_out || ''}".trim(),`;
            csv += `${record.late_display || '-'},${record.undertime_display || '-'},`;
            csv += `${record.accredited_hours_display || '0'},${status}\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Detailed_DTR_{{ $employee->employee_id }}_${dateRange}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>

@include('permanent.attendance.modals.detailedDtrModal')
@include('permanent.chatbot.permanentChatbot')

@endsection
