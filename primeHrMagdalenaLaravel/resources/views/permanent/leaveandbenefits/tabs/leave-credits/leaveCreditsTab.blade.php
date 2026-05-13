<section class="table-section" style="margin-bottom: 24px;">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Credits Balance</h3>
            <p class="table-sub">Current year leave balances and usage · Updated in real-time</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterLeaveCategory" onchange="filterLeaveCredits()">
                <option value="all">All Leave Types</option>
                <option value="accrued">Accrued Only</option>
                <option value="fixed">Fixed Only</option>
                <option value="available">With Balance</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Report
            </button>
        </div>
    </div>
    <div class="table-wrapper" style="overflow-x: auto;">
        <table class="payroll-table" style="min-width: 900px;">
            <thead>
                <tr>
                    <th style="width: 80px; text-align: center;">Code</th>
                    <th style="min-width: 200px;">Leave Type</th>
                    <th style="width: 120px; text-align: center;">Total Credits</th>
                    <th style="width: 110px; text-align: center;">Used</th>
                    <th style="width: 110px; text-align: center;">Pending</th>
                    <th style="width: 120px; text-align: center;">Available</th>
                    <th style="width: 100px; text-align: center;">Type</th>
                    <th style="width: 140px; text-align: center;">Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes ?? [] as $type)
                <tr class="leave-credit-row" data-type="{{ $type->is_accrued ? 'accrued' : 'fixed' }}" data-available="{{ $type->annual_limit }}">
                    <td style="text-align: center;">
                        <div style="display: inline-block; padding: 6px 10px; background: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }}; color: white; border-radius: 6px; font-weight: 700; font-size: 11px; letter-spacing: 0.5px;">
                            {{ $type->leave_code }}
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$loop->index % 6] }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }}" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                            <div>
                                <p style="font-size: 13px; color: #0b044d; font-weight: 600; margin: 0; line-height: 1.3;">{{ $type->leave_name }}</p>
                                @if($type->attachment_info)
                                <p style="font-size: 11px; color: #6b6a8a; margin: 2px 0 0 0; line-height: 1.4;">{{ Str::limit($type->attachment_info, 60) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $totalCredits = $type->annual_limit > 0 ? $type->annual_limit : 0;
                        @endphp
                        <div style="font-size: 15px; font-weight: 700; color: #0b044d;">
                            {{ number_format($totalCredits, 1) }}
                        </div>
                        <div style="font-size: 10px; color: #9ca3af; margin-top: 2px;">days</div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $used = 0;
                        @endphp
                        <div style="display: inline-block; padding: 4px 10px; background: #fee2e2; border-radius: 6px;">
                            <span style="font-size: 14px; font-weight: 700; color: #991b1b;">{{ number_format($used, 1) }}</span>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $pending = 0;
                        @endphp
                        <div style="display: inline-block; padding: 4px 10px; background: #fef3c7; border-radius: 6px;">
                            <span style="font-size: 14px; font-weight: 700; color: #92400e;">{{ number_format($pending, 1) }}</span>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $available = $totalCredits - $used - $pending;
                        @endphp
                        <div style="display: inline-block; padding: 6px 12px; background: #d1fae5; border-radius: 6px; border: 2px solid #10b981;">
                            <span style="font-size: 15px; font-weight: 800; color: #065f46;">{{ number_format($available, 1) }}</span>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @if($type->is_accrued)
                            <span style="display: inline-block; padding: 4px 10px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 3px;">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                    <polyline points="17 6 23 6 23 12"/>
                                </svg>
                                Accrued
                            </span>
                        @else
                            <span style="display: inline-block; padding: 4px 10px; background: #f3f4f6; color: #4b5563; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 3px;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                </svg>
                                Fixed
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @php
                            $percentage = $totalCredits > 0 ? (($available / $totalCredits) * 100) : 0;
                            $barColor = $percentage > 70 ? '#10b981' : ($percentage > 30 ? '#f59e0b' : '#ef4444');
                        @endphp
                        <div style="width: 100%; max-width: 120px; margin: 0 auto;">
                            <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $percentage }}%; height: 100%; background: {{ $barColor }}; transition: width 0.3s ease;"></div>
                            </div>
                            <div style="font-size: 10px; color: #6b7280; margin-top: 4px; font-weight: 600;">{{ number_format($percentage, 0) }}%</div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No leave credits available</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Leave balances will appear here once initialized</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="table-footer" style="margin-top: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0;" id="leaveCreditsCount">Showing <strong>{{ $leaveTypes->count() }}</strong> of <strong>{{ $leaveTypes->count() }}</strong> leave types</p>
        </div>
        <div class="pagination" id="leaveCreditspagination">
            <button class="page-btn" id="prevPageBtn" onclick="changeLeaveCreditsPage('prev')" disabled>‹</button>
            <button class="page-btn active" data-page="1">1</button>
            <button class="page-btn" id="nextPageBtn" onclick="changeLeaveCreditsPage('next')" disabled>›</button>
        </div>
    </div>
    
    <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Total Leave Credits</div>
                <div style="font-size: 28px; font-weight: 800;">{{ $leaveTypes->sum('annual_limit') }}</div>
                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">days allocated</div>
            </div>
            <div>
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Available Balance</div>
                <div style="font-size: 28px; font-weight: 800;">{{ $leaveTypes->sum('annual_limit') }}</div>
                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">days remaining</div>
            </div>
            <div>
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Leave Types</div>
                <div style="font-size: 28px; font-weight: 800;">{{ $leaveTypes->count() }}</div>
                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">types available</div>
            </div>
            <div>
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Utilization Rate</div>
                <div style="font-size: 28px; font-weight: 800;">0%</div>
                <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">of total credits</div>
            </div>
        </div>
    </div>
</section>

<div class="credits-grid">
    <div class="credit-card">
        <div class="credit-header">
            <div>
                <label>Vacation Leave</label>
                <h2 class="lb-credit-value lb-credit-value-primary">10</h2>
                <p>days remaining</p>
            </div>
            <div class="credit-stats">
                <p>Earned: <strong>15</strong></p>
                <p>Used: <strong class="lb-credit-used">5</strong></p>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill lb-progress-primary lb-w-67"></div>
        </div>
        <div class="progress-labels">
            <span>0</span>
            <span>15 days max</span>
        </div>
    </div>
    <div class="credit-card">
        <div class="credit-header">
            <div>
                <label>Sick Leave</label>
                <h2 class="lb-credit-value lb-credit-value-success">11</h2>
                <p>days remaining</p>
            </div>
            <div class="credit-stats">
                <p>Earned: <strong>15</strong></p>
                <p>Used: <strong class="lb-credit-used">4</strong></p>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill lb-progress-success lb-w-73"></div>
        </div>
        <div class="progress-labels">
            <span>0</span>
            <span>15 days max</span>
        </div>
    </div>
    <div class="credit-card">
        <div class="credit-header">
            <div>
                <label>Emergency Leave</label>
                <h2 class="lb-credit-value lb-credit-value-danger">2</h2>
                <p>days remaining</p>
            </div>
            <div class="credit-stats">
                <p>Earned: <strong>3</strong></p>
                <p>Used: <strong class="lb-credit-used">1</strong></p>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill lb-progress-danger lb-w-67"></div>
        </div>
        <div class="progress-labels">
            <span>0</span>
            <span>3 days max</span>
        </div>
    </div>
    <div class="credit-card">
        <div class="credit-header">
            <div>
                <label>Special Leave</label>
                <h2 class="lb-credit-value lb-credit-value-warning">3</h2>
                <p>days remaining</p>
            </div>
            <div class="credit-stats">
                <p>Earned: <strong>3</strong></p>
                <p>Used: <strong class="lb-credit-used">0</strong></p>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill lb-progress-warning lb-w-100"></div>
        </div>
        <div class="progress-labels">
            <span>0</span>
            <span>3 days max</span>
        </div>
    </div>
</div>
