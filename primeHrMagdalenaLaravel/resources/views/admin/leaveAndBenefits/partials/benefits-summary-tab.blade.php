<section class="table-section" id="benefits-tab" style="display: none;" data-total-rows="{{ count($benefitsData) }}">
    <div class="table-header">
        <div>
            <h3 class="table-title">Benefits Summary — June 2025</h3>
            <p class="table-sub">GSIS · PhilHealth · Pag-IBIG · Leave Credits</p>
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
                            @if(isset($benefit['photo']) && $benefit['photo'])
                                <img src="{{ $benefit['photo'] }}" alt="{{ $benefit['name'] }}" class="emp-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ecebf6;">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border:2px solid #ecebf6;">
                                    {{ getInitials($benefit['name']) }}
                                </div>
                            @endif
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
                            <div style="flex: 1; height: 6px; background: #f2f1fb; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['vlBalance'] / 15) * 100 }}%; height: 100%; background: #0b044d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d;">{{ $benefit['vlBalance'] }} days</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f2f1fb; border-radius: 3px; min-width: 50px;">
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
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="benefitsFooter">Showing <strong id="benefitsRowStart">1</strong>-<strong id="benefitsRowEnd">{{ min(10, count($benefitsData)) }}</strong> of <strong id="benefitsRowTotal">{{ count($benefitsData) }}</strong> records</p>
            <select id="benefitsRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeBenefitsRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="benefitsPaginationControls"></div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/leaveAndBenefits/benefits-summary-tab.js')
@endpush
