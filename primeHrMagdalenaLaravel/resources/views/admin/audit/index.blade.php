@extends('layouts.app')

@php use App\Models\User; @endphp

@section('content')
@include('admin.topbar.auditTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

    {{-- Filter Toolbar --}}
    <div class="filter-card">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <select class="fc-select" id="actionFilter" onchange="filterAudits()">
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <select class="fc-select" id="userFilter" onchange="filterAudits()">
                    <option value="">All Users</option>
                    @foreach($auditUsers as $auditUser)
                        <option value="{{ $auditUser->username }}">{{ $auditUser->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="dateFrom" title="Date from" onchange="filterAudits()">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" id="dateTo" title="Date to" onchange="filterAudits()">
            </div>
        </div>
        <div class="filter-card-actions">
            <button class="btn-ghost" onclick="resetFilters()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Reset
            </button>
        </div>
    </div>

    <div class="table-section">
        <div class="table-header">
            <div>
                <p class="table-title">Audit Trail</p>
                <p class="table-sub">Keep Track of Changes Made to Records · {{ $audits->total() }} total records</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="payroll-table" id="audits-table">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)" style="cursor: pointer; width: 6%;">
                            #
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.3;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </th>
                        <th onclick="sortTable(1)" style="cursor: pointer; width: 15%;">
                            User
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.3;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </th>
                        <th onclick="sortTable(2)" style="cursor: pointer; width: 12%;">
                            Action
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.3;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </th>
                        <th onclick="sortTable(3)" style="cursor: pointer; width: 25%;">
                            Changes
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.3;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </th>
                        <th style="width: 15%;">URL</th>
                        <th style="width: 8%; text-align: center;">IP</th>
                        <th style="width: 10%;">Agent</th>
                        <th onclick="sortTable(7)" style="cursor: pointer; width: 9%; text-align: right;">
                            Timestamp
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.3;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </th>
                    </tr>
                </thead>
                <tbody id="auditsTableBody">
                    @forelse($audits as $audit)
                        @php
                            $user = User::find($audit->user_id);
                            $actionClass = match($audit->event) {
                                'created' => 'processed',
                                'updated' => 'on-hold',
                                'deleted' => 'declined',
                                default => '',
                            };
                            $hasChanges = !empty($audit->old_values) || !empty($audit->new_values);
                        @endphp
                        <tr data-event="{{ $audit->event }}"
                            data-user="{{ $user?->username ?? '' }}"
                            data-date="{{ $audit->created_at?->format('Y-m-d') ?? '' }}">
                            <td><span style="color: var(--gp-text-soft); font-size: 12px;">#{{ $audit->id }}</span></td>
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background: var(--gp-pri); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:11px; flex-shrink:0;">
                                        {{ strtoupper(substr($user?->username ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="emp-name" style="font-size: 12.5px;">{{ $user?->username ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-status {{ $actionClass }}">{{ ucfirst($audit->event) }}</span>
                                <span style="font-size: 11px; color: var(--gp-text-soft); display: block; margin-top: 2px;">{{ class_basename($audit->auditable_type) }}</span>
                            </td>
                            <td>
                                @if($hasChanges)
                                    <div class="audit-changes" style="font-size: 11.5px; line-height: 1.6;">
                                        @if(!empty($audit->old_values) && !empty($audit->new_values))
                                            @foreach($audit->new_values as $key => $newVal)
                                                @php $oldVal = $audit->old_values[$key] ?? null; @endphp
                                                @if($oldVal != $newVal)
                                                    <div style="margin-bottom: 2px;">
                                                        <span style="color: var(--gp-text-soft);">{{ $key }}:</span>
                                                        <span style="color: var(--theme-danger); text-decoration: line-through; margin-right: 4px;">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                                                        <span style="color: var(--theme-success);">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @elseif(!empty($audit->new_values))
                                            @foreach($audit->new_values as $key => $newVal)
                                                <div style="margin-bottom: 2px;">
                                                    <span style="color: var(--gp-text-soft);">{{ $key }}:</span>
                                                    <span style="color: var(--theme-success);">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                                </div>
                                            @endforeach
                                        @elseif(!empty($audit->old_values))
                                            @foreach($audit->old_values as $key => $oldVal)
                                                <div style="margin-bottom: 2px;">
                                                    <span style="color: var(--gp-text-soft);">{{ $key }}:</span>
                                                    <span style="color: var(--theme-danger);">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @else
                                    <span style="color: var(--gp-text-soft); font-size: 11.5px;">—</span>
                                @endif
                            </td>
                            <td><span style="font-size: 12px; color: var(--gp-text-mid); word-break: break-all;">{{ $audit->url }}</span></td>
                            <td style="text-align: center;"><span style="font-size: 12px; color: var(--gp-text-mid); font-family: monospace;">{{ $audit->ip_address }}</span></td>
                            <td>
                                <span style="font-size: 11px; color: var(--gp-text-soft); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;" title="{{ $audit->user_agent }}">{{ $audit->user_agent }}</span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <span style="font-size: 12px; color: var(--gp-text-mid);">{{ $audit->created_at?->format('M d, Y') }}</span>
                                <span style="font-size: 11px; color: var(--gp-text-soft); display: block;">{{ $audit->created_at?->format('h:i A') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--gp-text-mid);">
                                No audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div style="display: flex; align-items: center; gap: 12px;">
                <p>Showing <strong id="showingStart">{{ $audits->firstItem() ?? 0 }}</strong>–<strong id="showingEnd">{{ $audits->lastItem() ?? 0 }}</strong> of <strong id="totalRecords">{{ $audits->total() }}</strong> records</p>
                <select id="rowsPerPageSelect" onchange="changeRowsPerPage(this.value)" style="padding: 6px 12px; border: 1.5px solid var(--gp-border); border-radius: 6px; font-size: 12px; font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif; color: var(--gp-pri); background: #fff; cursor: pointer;">
                    <option value="15" selected>15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
            <div class="pagination" id="paginationControls">
                @if($audits->hasPages())
                    @if($audits->onFirstPage())
                        <button class="page-btn" disabled style="opacity:0.4; cursor:default;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                    @else
                        <a href="{{ $audits->previousPageUrl() }}" class="page-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </a>
                    @endif

                    @foreach($audits->getUrlRange(max(1, $audits->currentPage() - 2), min($audits->lastPage(), $audits->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="page-btn {{ $page == $audits->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endforeach

                    @if($audits->hasMorePages())
                        <a href="{{ $audits->nextPageUrl() }}" class="page-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    @else
                        <button class="page-btn" disabled style="opacity:0.4; cursor:default;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
let allRows = [];
let currentPage = 1;
let rowsPerPage = 15;
let sortColumn = -1;
let sortAscending = true;

document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('auditsTableBody');
    if (tbody) {
        allRows = Array.from(tbody.querySelectorAll('tr[data-event]'));
        updatePagination();
    }
});

function filterAudits() {
    const searchTerm = (document.getElementById('auditSearchInput')?.value || '').toLowerCase();
    const actionFilter = document.getElementById('actionFilter')?.value || '';
    const userFilter = document.getElementById('userFilter')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';

    let visibleCount = 0;
    const total = allRows.length;

    allRows.forEach(row => {
        const event = row.dataset.event || '';
        const user = (row.dataset.user || '').toLowerCase();
        const date = row.dataset.date || '';
        const text = row.textContent.toLowerCase();

        let show = true;
        if (actionFilter && event !== actionFilter) show = false;
        if (userFilter && user !== userFilter.toLowerCase()) show = false;
        if (dateFrom && date < dateFrom) show = false;
        if (dateTo && date > dateTo) show = false;
        if (searchTerm && !text.includes(searchTerm)) show = false;

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    document.getElementById('showingStart').textContent = visibleCount > 0 ? 1 : 0;
    document.getElementById('showingEnd').textContent = visibleCount;
    document.getElementById('totalRecords').textContent = visibleCount;
}

function resetFilters() {
    const searchInput = document.getElementById('auditSearchInput');
    if (searchInput) searchInput.value = '';
    const actionFilter = document.getElementById('actionFilter');
    if (actionFilter) actionFilter.value = '';
    const userFilter = document.getElementById('userFilter');
    if (userFilter) userFilter.value = '';
    const dateFrom = document.getElementById('dateFrom');
    if (dateFrom) dateFrom.value = '';
    const dateTo = document.getElementById('dateTo');
    if (dateTo) dateTo.value = '';
    filterAudits();
}

function sortTable(columnIndex) {
    const tbody = document.getElementById('auditsTableBody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-event]'));

    if (sortColumn === columnIndex) {
        sortAscending = !sortAscending;
    } else {
        sortColumn = columnIndex;
        sortAscending = true;
    }

    rows.sort((a, b) => {
        let aValue, bValue;

        switch (columnIndex) {
            case 0:
                aValue = parseInt(a.cells[0].textContent.replace('#', '').trim()) || 0;
                bValue = parseInt(b.cells[0].textContent.replace('#', '').trim()) || 0;
                return sortAscending ? aValue - bValue : bValue - aValue;
            case 1:
                aValue = a.cells[1].textContent.trim().toLowerCase();
                bValue = b.cells[1].textContent.trim().toLowerCase();
                break;
            case 2:
                aValue = a.cells[2].textContent.trim().toLowerCase();
                bValue = b.cells[2].textContent.trim().toLowerCase();
                break;
            case 3:
                aValue = a.cells[3].textContent.trim().toLowerCase();
                bValue = b.cells[3].textContent.trim().toLowerCase();
                break;
            case 7:
                aValue = a.dataset.date || '';
                bValue = b.dataset.date || '';
                break;
            default:
                return 0;
        }

        if (aValue < bValue) return sortAscending ? -1 : 1;
        if (aValue > bValue) return sortAscending ? 1 : -1;
        return 0;
    });

    const headers = document.querySelectorAll('#audits-table th');
    headers.forEach((header, index) => {
        const svg = header.querySelector('svg');
        if (svg) {
            if (index === columnIndex) {
                svg.style.transform = sortAscending ? 'rotate(0deg)' : 'rotate(180deg)';
                svg.style.opacity = '1';
            } else {
                svg.style.transform = 'rotate(0deg)';
                svg.style.opacity = '0.3';
            }
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

function changeRowsPerPage(value) {
    rowsPerPage = value === 'all' ? 9999 : parseInt(value);
    currentPage = 1;
    updatePagination();
}

function updatePagination() {
    const visibleRows = allRows.filter(r => r.style.display !== 'none');
    const totalVisible = visibleRows.length;
    const totalPages = Math.ceil(totalVisible / rowsPerPage);

    const tbody = document.getElementById('auditsTableBody');
    const emptyRow = tbody.querySelector('tr:not([data-event])');

    visibleRows.forEach((row, index) => {
        row.style.display = (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) ? '' : 'none';
    });

    if (emptyRow) emptyRow.style.display = 'none';

    const start = totalVisible > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
    const end = Math.min(currentPage * rowsPerPage, totalVisible);
    document.getElementById('showingStart').textContent = start;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalRecords').textContent = totalVisible;

    updatePaginationButtons(totalPages);
}

function updatePaginationButtons(totalPages) {
    const paginationControls = document.getElementById('paginationControls');
    paginationControls.innerHTML = '';

    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>';
        prevBtn.onclick = () => { currentPage--; updatePagination(); };
        paginationControls.appendChild(prevBtn);
    }

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
        btn.textContent = i;
        btn.onclick = () => { currentPage = i; updatePagination(); };
        paginationControls.appendChild(btn);
    }

    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>';
        nextBtn.onclick = () => { currentPage++; updatePagination(); };
        paginationControls.appendChild(nextBtn);
    }
}
</script>
@endpush
