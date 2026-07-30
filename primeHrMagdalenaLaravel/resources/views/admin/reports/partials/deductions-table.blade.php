<thead>
    <tr>
        <th>Deduction</th>
        <th>Category</th>
        <th>Employees</th>
        <th>Total Amount</th>
        <th>% of Itemised</th>
    </tr>
</thead>
<tbody>
    @foreach($report['rows'] as $r)
    <tr>
        <td class="emp-name">{{ $r['name'] }}</td>
        <td><span class="dept-tag">{{ $r['category'] }}</span></td>
        <td class="report-count">{{ $r['employees'] }}</td>
        <td class="deduction">₱{{ number_format($r['amount'], 2) }}</td>
        <td>
            <div class="report-bar-cell">
                <div class="report-bar-track">
                    <div class="report-bar-fill is-deduction" style="width: {{ min(100, $r['pct']) }}%;"></div>
                </div>
                <span class="report-bar-label">{{ $r['pct'] }}%</span>
            </div>
        </td>
    </tr>
    @endforeach
</tbody>
<tfoot>
    <tr class="report-total-row">
        <td colspan="3">TOTAL ({{ $report['rows']->count() }} {{ \Illuminate\Support\Str::plural('type', $report['rows']->count()) }})</td>
        <td class="deduction">₱{{ number_format($report['totals']['amount'], 2) }}</td>
        <td>100%</td>
    </tr>
</tfoot>
