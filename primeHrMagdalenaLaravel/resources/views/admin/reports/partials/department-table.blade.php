<thead>
    <tr>
        <th>Department / Office</th>
        <th>Headcount</th>
        <th>Gross Payroll</th>
        <th>Net Payroll</th>
        <th>% of Total</th>
    </tr>
</thead>
<tbody>
    @foreach($report['rows'] as $r)
    <tr>
        <td><span class="dept-tag">{{ $r['dept'] }}</span></td>
        <td class="report-count">{{ $r['headcount'] }}</td>
        <td class="pay-cell">₱{{ number_format($r['gross'], 2) }}</td>
        <td class="net-pay">₱{{ number_format($r['net'], 2) }}</td>
        <td>
            <div class="report-bar-cell">
                <div class="report-bar-track">
                    <div class="report-bar-fill" style="width: {{ min(100, $r['pct']) }}%;"></div>
                </div>
                <span class="report-bar-label">{{ $r['pct'] }}%</span>
            </div>
        </td>
    </tr>
    @endforeach
</tbody>
<tfoot>
    <tr class="report-total-row">
        <td>TOTAL ({{ $report['rows']->count() }} {{ \Illuminate\Support\Str::plural('department', $report['rows']->count()) }})</td>
        <td class="report-count">{{ $report['rows']->sum('headcount') }}</td>
        <td class="pay-cell">₱{{ number_format($report['totals']['gross'], 2) }}</td>
        <td class="net-pay">₱{{ number_format($report['totals']['net'], 2) }}</td>
        <td>100%</td>
    </tr>
</tfoot>
