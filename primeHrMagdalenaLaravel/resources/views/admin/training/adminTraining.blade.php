@extends('layouts.app')

@section('content')
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Programs</p>
            <div class="stat-icon-wrap" style="background: #0b044d15; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
        </div>
        <h2 class="stat-value">5</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All training programs</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Ongoing</p>
            <div class="stat-icon-wrap" style="background: #15803d15; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
        </div>
        <h2 class="stat-value">2</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">Currently active</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Participants</p>
            <div class="stat-icon-wrap" style="background: #d9bb0015; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">125</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">All enrollments</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Completed</p>
            <div class="stat-icon-wrap" style="background: #8e1e1815; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">1</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Finished programs</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Training Programs</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · 5 programs</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option>All Types</option>
                <option>Leadership</option>
                <option>Technical</option>
                <option>Soft Skills</option>
                <option>Safety</option>
                <option>Compliance</option>
            </select>
            <select class="filter-select">
                <option>All Status</option>
                <option>Scheduled</option>
                <option>Ongoing</option>
                <option>Completed</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="modal-btn-primary" style="padding: 8px 18px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Training
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Training ID</th>
                    <th>Program Title</th>
                    <th>Type</th>
                    <th>Participants</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">TRN-001</td>
                    <td class="position-cell">Leadership Development Program</td>
                    <td><span class="badge-emptype">Leadership</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">25 / 30</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jun 15, 2025</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jul 15, 2025</td>
                    <td><span class="dept-tag">Municipal Hall Conference Room</span></td>
                    <td><span class="badge-status processed">Ongoing</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">TRN-002</td>
                    <td class="position-cell">Digital Literacy Training</td>
                    <td><span class="badge-emptype">Technical</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">18 / 20</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jun 20, 2025</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jun 30, 2025</td>
                    <td><span class="dept-tag">IT Training Center</span></td>
                    <td><span class="badge-status processed">Ongoing</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">TRN-003</td>
                    <td class="position-cell">Customer Service Excellence</td>
                    <td><span class="badge-emptype">Soft Skills</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">30 / 30</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">May 10, 2025</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">May 20, 2025</td>
                    <td><span class="dept-tag">Municipal Hall Conference Room</span></td>
                    <td><span class="badge-status on-hold">Completed</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">TRN-004</td>
                    <td class="position-cell">Financial Management Workshop</td>
                    <td><span class="badge-emptype">Technical</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">12 / 25</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jul 5, 2025</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jul 10, 2025</td>
                    <td><span class="dept-tag">Treasurer Office Training Room</span></td>
                    <td><span class="badge-status pending">Scheduled</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">TRN-005</td>
                    <td class="position-cell">Emergency Response Training</td>
                    <td><span class="badge-emptype">Safety</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">40 / 50</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jul 20, 2025</td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">Jul 22, 2025</td>
                    <td><span class="dept-tag">MDRRM Office</span></td>
                    <td><span class="badge-status pending">Scheduled</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>5</strong> of <strong>5</strong> programs</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

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
</style>
@endsection
