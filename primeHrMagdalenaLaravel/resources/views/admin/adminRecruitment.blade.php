@extends('layouts.app')

@section('content')
<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Job Postings</p>
            <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.1);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">4</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All positions</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Open Positions</p>
            <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.1);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">3</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">Currently accepting</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Applicants</p>
            <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.1);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">59</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">All applications</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Available Slots</p>
            <div class="stat-icon-wrap" style="background: rgba(142, 30, 24, 0.1);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="12" cy="12" r="6"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">5</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Positions to fill</p>
        </div>
    </div>
</div>

<!-- View Mode & Filters -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; gap: 8px;">
        <button class="view-mode-btn active" data-view="grid" onclick="switchView('grid')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
            </svg>
            Grid View
        </button>
        <button class="view-mode-btn" data-view="table" onclick="switchView('table')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/>
                <line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/>
                <line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            List View
        </button>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <select class="filter-select">
            <option>All Departments</option>
            <option>Office of the Mayor</option>
            <option>Office of the Mun. Engineer</option>
            <option>Municipal Health Office</option>
            <option>MSWD – Pagsanjan</option>
        </select>
        <select class="filter-select">
            <option>All Status</option>
            <option>Open</option>
            <option>Closed</option>
        </select>
        <button class="btn-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </button>
        <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Post Job
        </button>
    </div>
</div>

<!-- Grid View -->
<div id="gridView" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; margin-bottom: 22px;">
    @php
    $jobs = [
        ['id' => 'JOB-001', 'title' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'type' => 'Permanent', 'slots' => 1, 'applicants' => 12, 'status' => 'Open', 'deadline' => 'Jun 30, 2025'],
        ['id' => 'JOB-002', 'title' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'type' => 'Permanent', 'slots' => 1, 'applicants' => 8, 'status' => 'Open', 'deadline' => 'Jul 5, 2025'],
        ['id' => 'JOB-003', 'title' => 'Nurse II', 'dept' => 'Municipal Health Office', 'type' => 'Permanent', 'slots' => 2, 'applicants' => 24, 'status' => 'Closed', 'deadline' => 'Jun 15, 2025'],
        ['id' => 'JOB-004', 'title' => 'Social Welfare Officer', 'dept' => 'MSWD – Pagsanjan', 'type' => 'Casual', 'slots' => 1, 'applicants' => 15, 'status' => 'Open', 'deadline' => 'Jul 10, 2025'],
    ];
    @endphp

    @foreach($jobs as $job)
    <div class="stat-card" style="cursor: pointer; transition: all 0.2s; position: relative;">
        <div style="position: absolute; top: 16px; right: 16px;">
            <span class="badge-status {{ $job['status'] === 'Open' ? 'processed' : 'on-hold' }}">{{ $job['status'] }}</span>
        </div>
        <div style="margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; font-weight: 700;">
                    {{ $job['slots'] }}
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 10px; color: #9999bb; font-weight: 600; margin-bottom: 2px;">{{ $job['id'] }}</p>
                    <h4 style="font-size: 14px; font-weight: 700; color: #0b044d; margin: 0; line-height: 1.3;">{{ $job['title'] }}</h4>
                </div>
            </div>
            <p style="font-size: 12px; color: #6b6a8a; margin-bottom: 8px;">{{ $job['dept'] }}</p>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                <span class="badge-emptype">{{ $job['type'] }}</span>
                <span style="font-size: 11px; color: #9999bb; background: #f7f6ff; padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                    {{ $job['applicants'] }} applicants
                </span>
            </div>
        </div>
        <div style="border-top: 1px solid #f0effe; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="font-size: 10px; color: #9999bb; margin-bottom: 2px;">Deadline</p>
                <p style="font-size: 12px; color: #0b044d; font-weight: 600;">{{ $job['deadline'] }}</p>
            </div>
            <div style="display: flex; gap: 6px;">
                <button class="btn-view">View</button>
                <button class="btn-edit">Edit</button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Table View -->
<section id="tableView" class="table-section" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Job Postings</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · 4 of 4 postings</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Job ID</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Slots</th>
                    <th>Applicants</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                <tr>
                    <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">{{ $job['id'] }}</td>
                    <td class="position-cell">{{ $job['title'] }}</td>
                    <td><span class="dept-tag">{{ $job['dept'] }}</span></td>
                    <td><span class="badge-emptype">{{ $job['type'] }}</span></td>
                    <td style="font-size: 13px; color: #6b6a8a; text-align: center;">{{ $job['slots'] }}</td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">{{ $job['applicants'] }}</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $job['deadline'] }}</td>
                    <td><span class="badge-status {{ $job['status'] === 'Open' ? 'processed' : 'on-hold' }}">{{ $job['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>4</strong> of <strong>4</strong> postings</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<style>
.view-mode-btn {
    padding: 8px 16px; border-radius: 8px; border: 1.5px solid #e4e3f0;
    background: #fff; color: #6b6a8a; font-size: 12.5px; font-weight: 600;
    cursor: pointer; font-family: 'Poppins', sans-serif;
    display: flex; align-items: center; gap: 6px; transition: all 0.2s;
}
.view-mode-btn.active {
    border: 2px solid #0b044d; background: #0b044d; color: #fff;
}
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
</style>

<script>
function switchView(view) {
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');
    const buttons = document.querySelectorAll('.view-mode-btn');
    
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === view) {
            btn.classList.add('active');
        }
    });
    
    if (view === 'grid') {
        gridView.style.display = 'grid';
        tableView.style.display = 'none';
    } else {
        gridView.style.display = 'none';
        tableView.style.display = 'block';
    }
}
</script>
@endsection
