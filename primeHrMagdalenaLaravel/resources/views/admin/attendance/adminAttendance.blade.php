@extends('layouts.app')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endpush

@section('content')
@php
$avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

$monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$currentMonthName = $monthNames[$month];
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">DTR Submitted</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $completeCount }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">{{ $incompleteCount }} incomplete</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Present</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPresent }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">{{ $currentMonthName }} {{ $year }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Absences</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalAbsent }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Across all personnel</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Overtime Hours</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalOT }} hrs</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">{{ $totalLate }} late arrivals</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Daily Time Record — {{ $currentMonthName }} {{ $year }}</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ count($attendanceRecords) }} records</p>
        </div>
        <div class="table-actions">
            <form method="GET" action="{{ route('admin.attendance') }}" id="filterForm" style="display: contents;">
                <select class="filter-select" name="month" onchange="document.getElementById('filterForm').submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $monthNames[$m] }}</option>
                    @endfor
                </select>
                <select class="filter-select" name="year" onchange="document.getElementById('filterForm').submit()">
                    @for($y = 2023; $y <= 2027; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select class="filter-select" name="department" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <select class="filter-select" name="status" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Status</option>
                    <option value="Complete" {{ request('status') == 'Complete' ? 'selected' : '' }}>Complete</option>
                    <option value="Incomplete" {{ request('status') == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                </select>
            </form>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="payroll-summary-bar" style="margin-top: 0; margin-bottom: 16px;">
        <div class="psummary-item">
            <span>Total Present</span>
            <strong style="color: #15803d;">{{ $totalPresent }} days</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Total Absent</span>
            <strong style="color: #8e1e18;">{{ $totalAbsent }} days</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Late Arrivals</span>
            <strong style="color: #a16207;">{{ $totalLate }} times</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Overtime</span>
            <strong>{{ $totalOT }} hrs</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Records</span>
            <strong>{{ count($attendanceRecords) }}</strong>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Half Day</th>
                    <th>OT Hours</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceRecords as $index => $record)
                @php
                    $rate = $record['rate'];
                @endphp
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($record['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $record['name'] }}</p>
                                <p class="emp-id">{{ $record['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                    <td style="color: #15803d; font-weight: 600; font-size: 13px;">{{ $record['present'] }}</td>
                    <td style="color: {{ $record['absent'] > 0 ? '#8e1e18' : '#9999bb' }}; font-weight: {{ $record['absent'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['absent'] }}</td>
                    <td style="color: {{ $record['late'] > 0 ? '#a16207' : '#9999bb' }}; font-weight: {{ $record['late'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['late'] }}</td>
                    <td style="color: {{ $record['halfday'] > 0 ? '#a16207' : '#9999bb' }}; font-size: 13px;">{{ $record['halfday'] }}</td>
                    <td style="color: {{ $record['overtime'] > 0 ? '#0b044d' : '#9999bb' }}; font-weight: {{ $record['overtime'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['overtime'] > 0 ? $record['overtime'] . ' hrs' : '—' }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ $rate }}%; height: 100%; background: {{ $rate >= 90 ? '#15803d' : ($rate >= 75 ? '#d9bb00' : '#8e1e18') }}; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d; white-space: nowrap;">{{ $rate }}%</span>
                        </div>
                    </td>
                    <td><span class="badge-status {{ $record['status'] === 'Complete' ? 'processed' : 'pending' }}">{{ $record['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick="openDTRModal({{ json_encode($record) }}, {{ $index }})">DTR</button>
                            <button class="btn-detailed" onclick="openDetailedDTRModal({{ $record['employee_id'] }}, '{{ $record['name'] }}', '{{ $record['id'] }}')">Detailed DTR</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($attendanceRecords) }}</strong> of <strong>{{ count($attendanceRecords) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<!-- DTR Detail Modal -->
<div id="dtrModal" class="modal-overlay" style="display: none;" onclick="closeDTRModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DTR · <span id="dtrPeriod"></span></span>
                <h3 class="modal-title" id="dtrName"></h3>
                <p class="modal-sub"><span id="dtrPosition"></span> · <span id="dtrDept"></span></p>
            </div>
            <button class="modal-close" onclick="closeDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar lg" id="dtrAvatar" style="width: 60px; height: 60px; font-size: 20px;"></div>
                <div>
                    <p class="modal-emp-id" id="dtrEmpId"></p>
                    <span class="badge-status" id="dtrStatus"></span>
                </div>
            </div>

            <div class="modal-section-label">ATTENDANCE SUMMARY</div>
            <div class="modal-row"><span>Working Days</span><strong id="dtrWorkingDays"></strong></div>
            <div class="modal-row"><span>Days Present</span><strong style="color: #15803d;" id="dtrPresent"></strong></div>
            <div class="modal-row"><span>Days Absent</span><strong style="color: #8e1e18;" id="dtrAbsent"></strong></div>
            <div class="modal-row"><span>Late Arrivals</span><strong style="color: #a16207;" id="dtrLate"></strong></div>
            <div class="modal-row"><span>Half Days</span><strong style="color: #a16207;" id="dtrHalfday"></strong></div>

            <div class="modal-section-label" style="margin-top: 16px;">OVERTIME</div>
            <div class="modal-row"><span>Total OT Hours</span><strong style="color: #0b044d;" id="dtrOT"></strong></div>

            <div class="modal-net-row" style="margin-top: 16px;">
                <span>ATTENDANCE RATE</span>
                <strong id="dtrRate"></strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeDTRModal()">Close</button>
            <button class="modal-btn-primary" onclick="downloadDTR()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download DTR
            </button>
        </div>
    </div>
</div>

<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="modal-box modal-box-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DETAILED DTR · <span id="detailedPeriod">{{ $currentMonthName }} {{ $year }}</span></span>
                <h3 class="modal-title" id="detailedName"></h3>
                <p class="modal-sub" id="detailedEmpId"></p>
            </div>
            <button class="modal-close" onclick="closeDetailedDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="detailed-dtr-filters" style="padding: 16px 24px; border-bottom: 1px solid #f0effe; display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 12px; font-weight: 600; color: #6b6a8a;">Date Range:</span>
                <input type="date" id="detailedStartDate" class="filter-select-sm" style="width: auto;">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" id="detailedEndDate" class="filter-select-sm" style="width: auto;">
                <button class="btn-filter" onclick="loadDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                <div style="flex: 1;"></div>
                <button class="btn-export-sm" onclick="exportDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </button>
            </div>
            <div style="max-height: 500px; overflow-y: auto; padding: 24px;">
                <div id="detailedDTRLoading" style="text-align: center; padding: 40px; color: #9999bb;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin: 0 auto;">
                        <circle cx="12" cy="12" r="10" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/>
                    </svg>
                    <p style="margin-top: 12px;">Loading attendance records...</p>
                </div>
                <table class="detailed-dtr-table" id="detailedDTRTable" style="display: none;">
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
                            <th>Undertime</th>
                            <th>Late</th>
                            <th>Total Hours</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="detailedDTRBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer" style="background: #fafafe; border-top: 1px solid #f0effe;">
            <div style="flex: 1; display: flex; gap: 16px; font-size: 12px;">
                <div><span style="color: #9999bb;">Total Days:</span> <strong id="detailedTotalDays" style="color: #0b044d;">0</strong></div>
                <div><span style="color: #9999bb;">Present:</span> <strong id="detailedTotalPresent" style="color: #15803d;">0</strong></div>
                <div><span style="color: #9999bb;">Absent:</span> <strong id="detailedTotalAbsent" style="color: #8e1e18;">0</strong></div>
                <div><span style="color: #9999bb;">Late:</span> <strong id="detailedTotalLate" style="color: #a16207;">0</strong></div>
            </div>
            <button class="modal-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
        </div>
    </div>
</div>

<!-- Edit DTR Modal -->
<div id="editModal" class="modal-overlay" style="display: none;" onclick="closeEditModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">EDIT DTR RECORD</span>
                <h3 class="modal-title" id="editName"></h3>
                <p class="modal-sub" id="editEmpId"></p>
            </div>
            <button class="modal-close" onclick="closeEditModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-field">
                    <label>Days Present</label>
                    <input type="number" min="0" id="editPresent" class="form-input">
                </div>
                <div class="form-field">
                    <label>Days Absent</label>
                    <input type="number" min="0" id="editAbsent" class="form-input">
                </div>
                <div class="form-field">
                    <label>Late Arrivals</label>
                    <input type="number" min="0" id="editLate" class="form-input">
                </div>
                <div class="form-field">
                    <label>Half Days</label>
                    <input type="number" min="0" id="editHalfday" class="form-input">
                </div>
                <div class="form-field">
                    <label>Overtime (hrs)</label>
                    <input type="number" min="0" step="0.5" id="editOT" class="form-input">
                </div>
                <div class="form-field">
                    <label>Status</label>
                    <select id="editStatus" class="form-input">
                        <option>Complete</option>
                        <option>Incomplete</option>
                    </select>
                </div>
            </div>
            <div class="modal-net-row" style="margin-top: 16px;">
                <span>ATTENDANCE RATE PREVIEW</span>
                <strong id="editRatePreview">0%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button class="modal-btn-primary" onclick="saveEdit()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Correct Attendance Time Modal -->
<div id="correctModal" class="modal-overlay" style="display: none;" onclick="closeCorrectModal()">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">CORRECT ATTENDANCE TIME</span>
                <h3 class="modal-title" id="correctEmployeeName"></h3>
                <p class="modal-sub" id="correctDate"></p>
            </div>
            <button class="modal-close" onclick="closeCorrectModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="correctForm" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" id="correctAttendanceId" name="attendance_id">
                <input type="hidden" id="correctEmployeeId" name="employee_id">
                <input type="hidden" id="correctDateValue" name="date">
                
                <div class="form-grid">
                    <div class="form-field">
                        <label>AM In</label>
                        <input type="time" id="correctAmIn" name="am_in" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>AM Out</label>
                        <input type="time" id="correctAmOut" name="am_out" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>PM In</label>
                        <input type="time" id="correctPmIn" name="pm_in" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>PM Out</label>
                        <input type="time" id="correctPmOut" name="pm_out" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>OT In</label>
                        <input type="time" id="correctOtIn" name="ot_in" class="form-input" onchange="calculateTotalHours()">
                    </div>
                    <div class="form-field">
                        <label>OT Out</label>
                        <input type="time" id="correctOtOut" name="ot_out" class="form-input" onchange="calculateTotalHours()">
                    </div>
                </div>
                
                <div class="modal-net-row" style="margin-top: 16px;">
                    <span>CALCULATED TOTAL HOURS</span>
                    <strong id="calculatedTotalHours" style="color: #0b044d;">0.0 hrs</strong>
                </div>
                
                <div class="form-field" style="margin-top: 16px;">
                    <label>Reason for Correction <span style="color: #8e1e18;">*</span></label>
                    <textarea id="correctReason" name="reason" class="form-input" rows="3" placeholder="Explain why this correction is needed..." required></textarea>
                </div>
                
                <div class="form-field" style="margin-top: 16px;">
                    <label>Supporting Documents (PDF, JPG, PNG) <span style="color: #8e1e18;">*</span></label>
                    <input type="file" id="correctAttachments" name="attachments[]" class="form-input" accept=".pdf,.jpg,.jpeg,.png" multiple required style="padding: 8px;">
                    <p style="font-size: 11px; color: #9999bb; margin-top: 4px;">Required: Upload one or more documents (max 5MB each)</p>
                    <div id="filePreview" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-ghost" onclick="closeCorrectModal()">Cancel</button>
                <button type="submit" class="modal-btn-primary" id="correctSubmitBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Correction
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-edit-time {
    padding: 4px 12px; background: #0b044d; color: #fff;
    border: none; border-radius: 4px;
    font-size: 11px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-edit-time:hover { background: #1a0f6e; }
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
.btn-detailed {
    padding: 6px 12px; background: #fff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
    white-space: nowrap;
}
.btn-detailed:hover { background: #f7f6ff; border-color: #0b044d; }
.row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
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
.payroll-summary-bar {
    display: flex; align-items: center; gap: 20px;
    padding: 14px 24px; background: #fafafe;
    border: 1px solid #f0effe; border-radius: 8px;
}
.psummary-item { display: flex; flex-direction: column; gap: 2px; }
.psummary-item span { font-size: 11px; color: #9999bb; font-weight: 500; }
.psummary-item strong { font-size: 13px; color: #0b044d; font-weight: 600; }
.psummary-divider { width: 1px; height: 28px; background: #e8e7f5; }

/* Modal Styles */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(11, 4, 77, 0.4); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; animation: fadeIn 0.2s;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal-box {
    background: #fff; border-radius: 12px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.2);
    animation: slideUp 0.3s;
}
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header {
    padding: 24px 24px 20px; border-bottom: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.modal-eyebrow {
    font-size: 11px; font-weight: 700; color: #9999bb;
    letter-spacing: 0.5px; text-transform: uppercase;
}
.modal-title {
    font-size: 20px; font-weight: 700; color: #0b044d; margin: 4px 0 2px;
}
.modal-sub {
    font-size: 13px; color: #6b6a8a;
}
.modal-close {
    width: 32px; height: 32px; border-radius: 6px;
    background: #f7f6ff; border: 1px solid #e8e7f5;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s;
}
.modal-close:hover { background: #e8e7f5; }
.modal-close svg { stroke: #0b044d; }
.modal-body {
    padding: 24px; max-height: 60vh; overflow-y: auto;
}
.modal-emp-row {
    display: flex; align-items: center; gap: 12px;
    padding: 16px; background: #fafafe; border-radius: 8px;
    border: 1px solid #f0effe; margin-bottom: 20px;
}
.emp-avatar.lg {
    width: 60px; height: 60px; font-size: 20px;
}
.modal-emp-id {
    font-size: 12px; color: #9999bb; font-weight: 600; margin-bottom: 6px;
}
.modal-section-label {
    font-size: 11px; font-weight: 700; color: #9999bb;
    letter-spacing: 0.5px; margin-bottom: 12px;
}
.modal-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid #f7f6ff;
}
.modal-row span { font-size: 13px; color: #6b6a8a; }
.modal-row strong { font-size: 14px; color: #0b044d; font-weight: 600; }
.modal-net-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 16px; background: #f7f6ff; border-radius: 8px;
    border: 1px solid #e8e7f5;
}
.modal-net-row span { font-size: 11px; font-weight: 700; color: #9999bb; letter-spacing: 0.5px; }
.modal-net-row strong { font-size: 18px; color: #0b044d; font-weight: 700; }
.modal-footer {
    padding: 16px 24px; border-top: 1px solid #f0effe;
    display: flex; gap: 8px; justify-content: flex-end;
}
.modal-btn-ghost {
    padding: 10px 20px; background: #fff; color: #6b6a8a;
    border: 1px solid #e8e7f5; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.modal-btn-ghost:hover { background: #f7f6ff; }
.modal-btn-primary {
    padding: 10px 20px; background: #0b044d; color: #fff;
    border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
    display: flex; align-items: center; gap: 8px;
}
.modal-btn-primary:hover { background: #1a0f6e; }
.form-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
}
.form-field label {
    display: block; font-size: 12px; font-weight: 600;
    color: #6b6a8a; margin-bottom: 6px;
}
.form-input {
    width: 100%; padding: 10px 12px; border: 1px solid #e8e7f5;
    border-radius: 6px; font-size: 13px; font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}
.form-input:focus {
    outline: none; border-color: #0b044d; box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.1);
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.modal-box-wide {
    max-width: 1400px;
    width: 95%;
}
.filter-select-sm {
    padding: 6px 12px; border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-family: 'Poppins', sans-serif;
    background: #fff; color: #0b044d; cursor: pointer;
    transition: all 0.2s;
}
.filter-select-sm:focus {
    outline: none; border-color: #0b044d;
}
input[type="date"].filter-select-sm {
    padding: 6px 12px;
    cursor: pointer;
}
input[type="date"].filter-select-sm::-webkit-calendar-picker-indicator {
    cursor: pointer;
}
.btn-filter {
    padding: 6px 16px; background: #0b044d; color: #fff;
    border: none; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
    display: flex; align-items: center; gap: 6px;
}
.btn-filter:hover { background: #1a0f6e; }
.btn-export-sm {
    padding: 6px 12px; background: #0b044d; color: #fff;
    border: none; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
    display: flex; align-items: center; gap: 6px;
}
.btn-export-sm:hover { background: #1a0f6e; }
.detailed-dtr-table {
    width: 100%; border-collapse: collapse;
    font-size: 12px;
}
.detailed-dtr-table thead th {
    background: #f7f6ff; color: #6b6a8a;
    font-weight: 600; text-align: left;
    padding: 10px 12px; border-bottom: 2px solid #e8e7f5;
    position: sticky; top: 0; z-index: 10;
}
.detailed-dtr-table tbody td {
    padding: 10px 12px; border-bottom: 1px solid #f7f6ff;
    color: #0b044d;
}
.detailed-dtr-table tbody tr:hover {
    background: #fafafe;
}
.log-missing {
    color: #8e1e18; font-weight: 600; font-size: 11px;
}
.log-present {
    color: #15803d; font-weight: 600;
}
.log-late {
    color: #a16207; font-weight: 600;
}
.day-weekend {
    background: #fafafe !important;
    color: #9999bb;
}
.day-absent {
    background: #fff5f5 !important;
}
textarea.form-input {
    resize: vertical;
    font-family: 'Poppins', sans-serif;
}
.file-preview-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f7f6ff;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 11px;
    color: #0b044d;
}
.file-preview-item svg {
    flex-shrink: 0;
}
</style>

<script>
const avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
const getInitials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

let currentEditId = null;
let currentDTRRecord = null;
let currentDetailedEmployeeId = null;
let currentDetailedEmployeeName = null;
let currentDetailedEmployeeEmpId = null;

function openDTRModal(record, index) {
    currentDTRRecord = record;
    const workingDays = record.present + record.absent + record.halfday;
    const rate = workingDays > 0 ? Math.round((record.present / workingDays) * 100) : 0;

    document.getElementById('dtrPeriod').textContent = '{{ $currentMonthName }} {{ $year }}'.toUpperCase();
    document.getElementById('dtrName').textContent = record.name;
    document.getElementById('dtrPosition').textContent = record.position;
    document.getElementById('dtrDept').textContent = record.dept;
    document.getElementById('dtrEmpId').textContent = record.id;

    const avatar = document.getElementById('dtrAvatar');
    avatar.textContent = getInitials(record.name);
    avatar.style.background = avatarColors[index % avatarColors.length];

    const statusBadge = document.getElementById('dtrStatus');
    statusBadge.textContent = record.status;
    statusBadge.className = 'badge-status ' + (record.status === 'Complete' ? 'processed' : 'pending');

    document.getElementById('dtrWorkingDays').textContent = workingDays + ' days';
    document.getElementById('dtrPresent').textContent = record.present + ' days';
    document.getElementById('dtrAbsent').textContent = record.absent + ' days';
    document.getElementById('dtrLate').textContent = record.late + ' times';
    document.getElementById('dtrHalfday').textContent = record.halfday + ' days';
    document.getElementById('dtrOT').textContent = record.overtime + ' hrs';
    document.getElementById('dtrRate').textContent = rate + '%';

    document.getElementById('dtrModal').style.display = 'flex';
}

function closeDTRModal() {
    document.getElementById('dtrModal').style.display = 'none';
    currentDTRRecord = null;
}

function downloadDTR() {
    if (!currentDTRRecord) return;

    // Show loading state
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/></svg> Generating...';
    btn.disabled = true;

    setTimeout(() => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

    const workingDays = currentDTRRecord.present + currentDTRRecord.absent + currentDTRRecord.halfday;
    const rate = workingDays > 0 ? Math.round((currentDTRRecord.present / workingDays) * 100) : 0;

    // Header
    doc.setFillColor(11, 4, 77);
    doc.rect(0, 0, 210, 40, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(20);
    doc.setFont(undefined, 'bold');
    doc.text('DAILY TIME RECORD', 105, 15, { align: 'center' });

    doc.setFontSize(11);
    doc.setFont(undefined, 'normal');
    doc.text('Municipal Government of Pagsanjan', 105, 23, { align: 'center' });
    doc.text('{{ $currentMonthName }} {{ $year }}', 105, 30, { align: 'center' });

    // Employee Info
    doc.setTextColor(0, 0, 0);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('EMPLOYEE INFORMATION', 20, 55);

    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text('Name:', 20, 65);
    doc.setFont(undefined, 'bold');
    doc.text(currentDTRRecord.name, 50, 65);

    doc.setFont(undefined, 'normal');
    doc.text('Employee ID:', 20, 72);
    doc.setFont(undefined, 'bold');
    doc.text(currentDTRRecord.id, 50, 72);

    doc.setFont(undefined, 'normal');
    doc.text('Position:', 20, 79);
    doc.setFont(undefined, 'bold');
    doc.text(currentDTRRecord.position, 50, 79);

    doc.setFont(undefined, 'normal');
    doc.text('Department:', 20, 86);
    doc.setFont(undefined, 'bold');
    doc.text(currentDTRRecord.dept, 50, 86);

    // Attendance Summary
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('ATTENDANCE SUMMARY', 20, 105);

    // Table headers
    doc.setFillColor(247, 246, 255);
    doc.rect(20, 110, 170, 10, 'F');

    doc.setFontSize(9);
    doc.setFont(undefined, 'bold');
    doc.text('METRIC', 25, 116);
    doc.text('VALUE', 160, 116);

    // Table rows
    const rows = [
        { label: 'Working Days', value: workingDays + ' days', color: [0, 0, 0] },
        { label: 'Days Present', value: currentDTRRecord.present + ' days', color: [21, 128, 61] },
        { label: 'Days Absent', value: currentDTRRecord.absent + ' days', color: [142, 30, 24] },
        { label: 'Late Arrivals', value: currentDTRRecord.late + ' times', color: [161, 98, 7] },
        { label: 'Half Days', value: currentDTRRecord.halfday + ' days', color: [161, 98, 7] },
    ];

    let yPos = 126;
    doc.setFont(undefined, 'normal');
    rows.forEach((row, i) => {
        if (i % 2 === 0) {
            doc.setFillColor(250, 250, 254);
            doc.rect(20, yPos - 5, 170, 8, 'F');
        }
        doc.setTextColor(107, 106, 138);
        doc.text(row.label, 25, yPos);
        doc.setTextColor(...row.color);
        doc.setFont(undefined, 'bold');
        doc.text(row.value, 160, yPos);
        doc.setFont(undefined, 'normal');
        yPos += 8;
    });

    // Overtime Section
    yPos += 10;
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(0, 0, 0);
    doc.text('OVERTIME', 20, yPos);

    yPos += 10;
    doc.setFillColor(247, 246, 255);
    doc.rect(20, yPos - 5, 170, 8, 'F');

    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(107, 106, 138);
    doc.text('Total OT Hours', 25, yPos);
    doc.setTextColor(11, 4, 77);
    doc.setFont(undefined, 'bold');
    doc.text(currentDTRRecord.overtime + ' hrs', 160, yPos);

    // Attendance Rate
    yPos += 20;
    doc.setFillColor(11, 4, 77);
    doc.rect(20, yPos - 8, 170, 15, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text('ATTENDANCE RATE', 25, yPos);

    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text(rate + '%', 160, yPos);

    // Status
    yPos += 20;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    doc.setFont(undefined, 'normal');
    doc.text('Status:', 20, yPos);

    const statusColor = currentDTRRecord.status === 'Complete' ? [21, 128, 61] : [161, 98, 7];
    doc.setTextColor(...statusColor);
    doc.setFont(undefined, 'bold');
    doc.text(currentDTRRecord.status, 50, yPos);

    // Footer
    doc.setFontSize(8);
    doc.setTextColor(153, 153, 187);
    doc.setFont(undefined, 'normal');
    doc.text('Generated on ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), 105, 280, { align: 'center' });
    doc.text('Municipal Government of Pagsanjan - Human Resource Management Office', 105, 285, { align: 'center' });

    // Save PDF
    const fileName = `DTR_${currentDTRRecord.id}_${currentDTRRecord.name.replace(/\s+/g, '_')}_{{ $currentMonthName }}_{{ $year }}.pdf`;
    doc.save(fileName);

    // Reset button
    btn.innerHTML = originalHTML;
    btn.disabled = false;
    }, 500);
}

function openEditModal(record) {
    currentEditId = record.employee_id;

    document.getElementById('editName').textContent = record.name;
    document.getElementById('editEmpId').textContent = record.id;
    document.getElementById('editPresent').value = record.present;
    document.getElementById('editAbsent').value = record.absent;
    document.getElementById('editLate').value = record.late;
    document.getElementById('editHalfday').value = record.halfday;
    document.getElementById('editOT').value = record.overtime;
    document.getElementById('editStatus').value = record.status;

    updateRatePreview();

    // Add event listeners for live preview
    ['editPresent', 'editAbsent', 'editHalfday'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateRatePreview);
    });

    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function updateRatePreview() {
    const present = parseInt(document.getElementById('editPresent').value) || 0;
    const absent = parseInt(document.getElementById('editAbsent').value) || 0;
    const halfday = parseInt(document.getElementById('editHalfday').value) || 0;
    const workingDays = present + absent + halfday;
    const rate = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;
    document.getElementById('editRatePreview').textContent = rate + '%';
}

function saveEdit() {
    // TODO: Implement save functionality with AJAX
    alert('Save functionality to be implemented');
    closeEditModal();
}

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDTRModal();
        closeEditModal();
        closeDetailedDTRModal();
        closeCorrectModal();
    }
});

// Correct Attendance Functions
let currentCorrectAttendanceId = null;

function calculateTotalHours() {
    const amIn = document.getElementById('correctAmIn').value;
    const pmOut = document.getElementById('correctPmOut').value;
    const otIn = document.getElementById('correctOtIn').value;
    const otOut = document.getElementById('correctOtOut').value;
    
    let totalMinutes = 0;
    
    // Calculate main work hours (AM In to PM Out)
    if (amIn && pmOut) {
        const timeIn = new Date('1970-01-01 ' + amIn);
        const timeOut = new Date('1970-01-01 ' + pmOut);
        let workMinutes = (timeOut - timeIn) / 1000 / 60;
        
        // Handle overnight shift (if PM Out is before AM In)
        if (workMinutes < 0) {
            workMinutes += 24 * 60;
        }
        
        // Deduct 1-hour break (60 minutes)
        workMinutes -= 60;
        
        totalMinutes += Math.max(0, workMinutes);
    }
    
    // Add overtime hours
    if (otIn && otOut) {
        const otTimeIn = new Date('1970-01-01 ' + otIn);
        const otTimeOut = new Date('1970-01-01 ' + otOut);
        let otMinutes = (otTimeOut - otTimeIn) / 1000 / 60;
        
        // Handle overnight OT
        if (otMinutes < 0) {
            otMinutes += 24 * 60;
        }
        
        totalMinutes += Math.max(0, otMinutes);
    }
    
    // Calculate late minutes (if AM In is after 8:10 AM)
    if (amIn) {
        const amInTime = new Date('1970-01-01 ' + amIn);
        const lateThreshold = new Date('1970-01-01 08:10:00');
        if (amInTime > lateThreshold) {
            const lateMinutes = (amInTime - lateThreshold) / 1000 / 60;
            totalMinutes -= lateMinutes;
        }
    }
    
    // Calculate undertime (if PM Out is before 5:00 PM)
    if (pmOut) {
        const pmOutTime = new Date('1970-01-01 ' + pmOut);
        const expectedOut = new Date('1970-01-01 17:00:00');
        if (pmOutTime < expectedOut) {
            const undertimeMinutes = (expectedOut - pmOutTime) / 1000 / 60;
            totalMinutes -= undertimeMinutes;
        }
    }
    
    const totalHours = Math.max(0, totalMinutes / 60);
    document.getElementById('calculatedTotalHours').textContent = totalHours.toFixed(1) + ' hrs';
    
    // Color code based on expected hours
    const display = document.getElementById('calculatedTotalHours');
    if (totalHours >= 8) {
        display.style.color = '#15803d'; // Green for full day
    } else if (totalHours >= 4) {
        display.style.color = '#d9bb00'; // Yellow for half day
    } else if (totalHours > 0) {
        display.style.color = '#a16207'; // Orange for undertime
    } else {
        display.style.color = '#8e1e18'; // Red for no hours
    }
}

function openCorrectModal(attendanceId, date) {
    currentCorrectAttendanceId = attendanceId;
    
    fetch(`/admin/attendance/record/${attendanceId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('correctEmployeeName').textContent = data.employee_name;
            document.getElementById('correctDate').textContent = new Date(data.date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('correctAttendanceId').value = data.is_new ? '' : data.id;
            document.getElementById('correctEmployeeId').value = data.employee_id;
            document.getElementById('correctDateValue').value = data.date;
            
            // Convert time format from HH:MM:SS or H:MM AM/PM to HH:MM
            const convertTime = (time) => {
                if (!time) return '';
                // If already in HH:MM format, return as is
                if (/^\d{2}:\d{2}$/.test(time)) return time;
                // If in HH:MM:SS format, extract HH:MM
                if (/^\d{2}:\d{2}:\d{2}$/.test(time)) return time.substring(0, 5);
                // If in 12-hour format, convert to 24-hour
                try {
                    const date = new Date('1970-01-01 ' + time);
                    return date.toTimeString().substring(0, 5);
                } catch (e) {
                    return '';
                }
            };
            
            document.getElementById('correctAmIn').value = convertTime(data.am_in);
            document.getElementById('correctAmOut').value = convertTime(data.am_out);
            document.getElementById('correctPmIn').value = convertTime(data.pm_in);
            document.getElementById('correctPmOut').value = convertTime(data.pm_out);
            document.getElementById('correctOtIn').value = convertTime(data.ot_in);
            document.getElementById('correctOtOut').value = convertTime(data.ot_out);
            
            document.getElementById('correctReason').value = '';
            document.getElementById('correctAttachments').value = '';
            document.getElementById('filePreview').innerHTML = '';
            
            // Calculate initial total hours
            calculateTotalHours();
            
            document.getElementById('correctModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading attendance record');
        });
}

function closeCorrectModal() {
    document.getElementById('correctModal').style.display = 'none';
    currentCorrectAttendanceId = null;
}

document.getElementById('correctAttachments').addEventListener('change', function(e) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach(file => {
        const item = document.createElement('div');
        item.className = 'file-preview-item';
        
        const icon = file.type === 'application/pdf' 
            ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>'
            : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        
        item.innerHTML = icon + '<span>' + file.name + '</span>';
        preview.appendChild(item);
    });
});

document.getElementById('correctForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btn = document.getElementById('correctSubmitBtn');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/></svg> Saving...';
    btn.disabled = true;
    
    fetch('/admin/attendance/correct', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance corrected successfully!');
            closeCorrectModal();
            loadDetailedDTR();
        } else {
            alert('Error: ' + (data.message || 'Failed to correct attendance'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error correcting attendance');
    })
    .finally(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
});

// Detailed DTR Functions
function openDetailedDTRModal(employeeId, name, empId) {
    currentDetailedEmployeeId = employeeId;
    currentDetailedEmployeeName = name;
    currentDetailedEmployeeEmpId = empId;

    document.getElementById('detailedName').textContent = name;
    document.getElementById('detailedEmpId').textContent = empId;
    
    // Set default date range to current month
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('detailedStartDate').value = firstDay.toISOString().split('T')[0];
    document.getElementById('detailedEndDate').value = lastDay.toISOString().split('T')[0];
    
    document.getElementById('detailedDTRModal').style.display = 'flex';

    loadDetailedDTR();
}

function closeDetailedDTRModal() {
    document.getElementById('detailedDTRModal').style.display = 'none';
    currentDetailedEmployeeId = null;
}

function loadDetailedDTR() {
    if (!currentDetailedEmployeeId) return;

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }

    document.getElementById('detailedDTRLoading').style.display = 'block';
    document.getElementById('detailedDTRTable').style.display = 'none';

    fetch(`/admin/attendance/detailed/${currentDetailedEmployeeId}?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            renderDetailedDTR(data);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailedDTRLoading').innerHTML = '<p style="color: #8e1e18;">Error loading attendance records</p>';
        });
}

function renderDetailedDTR(data) {
    const tbody = document.getElementById('detailedDTRBody');
    tbody.innerHTML = '';

    let totalPresent = 0;
    let totalAbsent = 0;
    let totalLate = 0;
    
    // Update period display
    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    const startFormatted = new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const endFormatted = new Date(endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('detailedPeriod').textContent = startFormatted + ' - ' + endFormatted;

    data.records.forEach(record => {
        const tr = document.createElement('tr');

        // Check if weekend
        const isWeekend = record.day === 'Saturday' || record.day === 'Sunday';
        if (isWeekend) {
            tr.className = 'day-weekend';
        }

        // Check if absent (no logs on working day)
        const hasAnyLog = record.am_in || record.am_out || record.pm_in || record.pm_out;
        const isAbsent = !isWeekend && !hasAnyLog;
        if (isAbsent) {
            tr.className = 'day-absent';
            totalAbsent++;
        } else if (hasAnyLog) {
            totalPresent++;
        }

        // Check if late
        const isLate = record.late_minutes > 0;
        if (isLate) totalLate++;

        tr.innerHTML = `
            <td><strong>${record.date}</strong></td>
            <td>${record.day}</td>
            <td>${record.am_in || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.am_out || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.pm_in || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.pm_out || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.ot_in || '—'}</td>
            <td>${record.ot_out || '—'}</td>
            <td>${record.undertime > 0 ? '<span class="log-late">' + record.undertime + ' min</span>' : '—'}</td>
            <td>${isLate ? '<span class="log-late">' + record.late_minutes + ' min</span>' : '—'}</td>
            <td><strong>${record.total_hours}</strong></td>
            <td><button class="btn-edit-time" onclick="openCorrectModal(${record.attendance_id ? record.attendance_id : "'new_" + currentDetailedEmployeeId + "_" + record.date_key + "'"}, '${record.date}')" title="${record.attendance_id ? 'Edit time records' : 'Add time records'}">Edit</button></td>
        `;

        tbody.appendChild(tr);
    });

    // Update summary
    document.getElementById('detailedTotalDays').textContent = data.records.length;
    document.getElementById('detailedTotalPresent').textContent = totalPresent;
    document.getElementById('detailedTotalAbsent').textContent = totalAbsent;
    document.getElementById('detailedTotalLate').textContent = totalLate;

    document.getElementById('detailedDTRLoading').style.display = 'none';
    document.getElementById('detailedDTRTable').style.display = 'table';
}

function exportDetailedDTR() {
    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    window.location.href = `/admin/attendance/detailed/${currentDetailedEmployeeId}/export?start_date=${startDate}&end_date=${endDate}`;
}
</script>
@endsection
