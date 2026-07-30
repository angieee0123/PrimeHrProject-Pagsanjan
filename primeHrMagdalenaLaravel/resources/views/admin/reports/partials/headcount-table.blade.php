<thead>
    <tr>
        <th>Department / Office</th>
        <th>Permanent</th>
        <th>Job Order</th>
        <th>Total</th>
        <th>% of Personnel</th>
    </tr>
</thead>
<tbody>
    @foreach($report['rows'] as $r)
    <tr>
        <td><span class="dept-tag">{{ $r['dept'] }}</span></td>
        <td class="report-count">{{ $r['permanent'] }}</td>
        <td class="report-count">{{ $r['joborder'] }}</td>
        <td class="report-count is-strong">{{ $r['total'] }}</td>
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
        <td class="report-count">{{ $report['rows']->sum('permanent') }}</td>
        <td class="report-count">{{ $report['rows']->sum('joborder') }}</td>
        <td class="report-count is-strong">{{ $report['totals']['total'] }}</td>
        <td>100%</td>
    </tr>
</tfoot>
