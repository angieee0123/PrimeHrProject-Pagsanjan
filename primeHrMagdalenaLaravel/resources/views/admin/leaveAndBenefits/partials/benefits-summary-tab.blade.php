<section class="table-section" id="benefits-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Benefits Summary — June 2025</h3>
            <p class="table-sub">GSIS · PhilHealth · Pag-IBIG · Leave Credits</p>
        </div>
        <div class="table-actions">
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>GSIS Premium</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                    <th>VL Balance</th>
                    <th>SL Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($benefitsData as $index => $benefit)
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($benefit['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $benefit['name'] }}</p>
                                <p class="emp-id">{{ $benefit['empId'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="deduction">{{ $benefit['gsis'] }}</td>
                    <td class="deduction">{{ $benefit['philhealth'] }}</td>
                    <td class="deduction">{{ $benefit['pagibig'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['vlBalance'] / 15) * 100 }}%; height: 100%; background: #0b044d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d;">{{ $benefit['vlBalance'] }} days</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['slBalance'] / 15) * 100 }}%; height: 100%; background: #15803d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #15803d;">{{ $benefit['slBalance'] }} days</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($benefitsData) }}</strong> of <strong>{{ count($benefitsData) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>
