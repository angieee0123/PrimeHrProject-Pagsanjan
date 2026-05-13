<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Requests</h3>
            <p class="table-sub">6 of 6 records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterType" onchange="applyLeaveFilters()">
                <option value="">All Types</option>
                <option>Vacation Leave</option>
                <option>Sick Leave</option>
                <option>Emergency Leave</option>
                <option>Special Leave</option>
            </select>
            <select class="filter-select" id="filterStatus" onchange="applyLeaveFilters()">
                <option value="">All Status</option>
                <option>Approved</option>
                <option>Pending</option>
                <option>Rejected</option>
            </select>
            <button class="btn-export" onclick="openFileModal()">+ File Leave</button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="payroll-table lb-leave-table">
            <thead>
                <tr>
                    <th>Leave ID</th>
                    <th>Leave Type</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr data-type="Sick Leave" data-status="Approved">
                    <td class="lb-leave-id">LV-2025-002</td>
                    <td class="lb-leave-type">Sick Leave</td>
                    <td>Jun 15, 2025</td>
                    <td>Jun 16, 2025</td>
                    <td class="lb-leave-days">2</td>
                    <td class="lb-leave-reason">Medical consultation</td>
                    <td><span class="badge-status processed">Approved</span></td>
                    <td><button class="btn-view" onclick="openDetailModal('Sick Leave', 'Jun 15, 2025', 'Jun 16, 2025', 2, 'Medical consultation', 'Approved')">View</button></td>
                </tr>
                <tr data-type="Vacation Leave" data-status="Approved">
                    <td class="lb-leave-id">LV-2025-007</td>
                    <td class="lb-leave-type">Vacation Leave</td>
                    <td>Jun 10, 2025</td>
                    <td>Jun 11, 2025</td>
                    <td class="lb-leave-days">2</td>
                    <td class="lb-leave-reason">Rest and recreation</td>
                    <td><span class="badge-status processed">Approved</span></td>
                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Jun 10, 2025', 'Jun 11, 2025', 2, 'Rest and recreation', 'Approved')">View</button></td>
                </tr>
                <tr data-type="Emergency Leave" data-status="Approved">
                    <td class="lb-leave-id">LV-2025-010</td>
                    <td class="lb-leave-type">Emergency Leave</td>
                    <td>May 22, 2025</td>
                    <td>May 22, 2025</td>
                    <td class="lb-leave-days">1</td>
                    <td class="lb-leave-reason">Family emergency</td>
                    <td><span class="badge-status processed">Approved</span></td>
                    <td><button class="btn-view" onclick="openDetailModal('Emergency Leave', 'May 22, 2025', 'May 22, 2025', 1, 'Family emergency', 'Approved')">View</button></td>
                </tr>
                <tr data-type="Sick Leave" data-status="Approved">
                    <td class="lb-leave-id">LV-2025-013</td>
                    <td class="lb-leave-type">Sick Leave</td>
                    <td>May 5, 2025</td>
                    <td>May 6, 2025</td>
                    <td class="lb-leave-days">2</td>
                    <td class="lb-leave-reason">Flu and fever</td>
                    <td><span class="badge-status processed">Approved</span></td>
                    <td><button class="btn-view" onclick="openDetailModal('Sick Leave', 'May 5, 2025', 'May 6, 2025', 2, 'Flu and fever', 'Approved')">View</button></td>
                </tr>
                <tr data-type="Vacation Leave" data-status="Approved">
                    <td class="lb-leave-id">LV-2025-018</td>
                    <td class="lb-leave-type">Vacation Leave</td>
                    <td>Apr 14, 2025</td>
                    <td>Apr 16, 2025</td>
                    <td class="lb-leave-days">3</td>
                    <td class="lb-leave-reason">Family vacation</td>
                    <td><span class="badge-status processed">Approved</span></td>
                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Apr 14, 2025', 'Apr 16, 2025', 3, 'Family vacation', 'Approved')">View</button></td>
                </tr>
                <tr data-type="Vacation Leave" data-status="Pending">
                    <td class="lb-leave-id">LV-2025-021</td>
                    <td class="lb-leave-type">Vacation Leave</td>
                    <td>Jul 7, 2025</td>
                    <td>Jul 9, 2025</td>
                    <td class="lb-leave-days">3</td>
                    <td class="lb-leave-reason">Personal trip</td>
                    <td><span class="badge-status pending">Pending</span></td>
                    <td><button class="btn-view" onclick="openDetailModal('Vacation Leave', 'Jul 7, 2025', 'Jul 9, 2025', 3, 'Personal trip', 'Pending')">View</button></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="table-footer">
        <span id="leaveCount">Showing <strong>6</strong> of <strong>6</strong> records</span>
    </div>
</section>
