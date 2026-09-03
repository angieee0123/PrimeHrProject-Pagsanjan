<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Monetization Requests</h3>
            <p class="table-sub">{{ $monetizationRequests->count() }} of {{ $monetizationRequests->count() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterMonetStatus" onchange="applyMonetizationFilters()">
                <option value="">All Status</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="Disapproved">Disapproved</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            {{-- Same navy pill as "File Leave" — see the .lb-btn-primary-solid
                 rules in employeeLeaveAndBenefits.css for why both classes are needed. --}}
            <button class="btn-export lb-btn-primary-solid" onclick="openMonetizationModal()">+ Request Monetization</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Request No.</th>
                    <th>VL Days</th>
                    <th>SL Days</th>
                    <th>Total Days</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monetizationRequests as $monet)
                @php $totalDays = (float) $monet->vl_days + (float) $monet->sl_days; @endphp
                <tr data-status="{{ ucfirst($monet->status) }}">
                    <td class="emp-id">{{ $monet->request_number }}</td>
                    <td class="days-count">{{ number_format((float) $monet->vl_days, 1) }} {{ (float) $monet->vl_days == 1 ? 'day' : 'days' }}</td>
                    <td class="days-count">{{ number_format((float) $monet->sl_days, 1) }} {{ (float) $monet->sl_days == 1 ? 'day' : 'days' }}</td>
                    <td class="days-count">{{ number_format($totalDays, 1) }} {{ $totalDays == 1 ? 'day' : 'days' }}</td>
                    <td class="work-date">₱{{ number_format((float) $monet->computed_amount, 2) }}</td>
                    <td>
                        @if($monet->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($monet->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($monet->status === 'disapproved')
                            <span class="badge-status rejected">Disapproved</span>
                        @else
                            <span class="badge-status cancelled">Cancelled</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn-view" data-monet-id="{{ $monet->id }}" onclick="openMonetizationDetail({{ $monet->id }})">View</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="lb-empty-cell">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="lb-empty-icon">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="lb-empty-title">No monetization requests found</p>
                        <p class="lb-empty-sub">Your monetization requests will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p id="monetCount">Showing <strong>{{ $monetizationRequests->count() }}</strong> of <strong>{{ $monetizationRequests->count() }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn">‹</button>
            <button class="page-btn active">1</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>
