<thead>
    <tr>
        <th>Employee</th>
        <th>Training / Seminar</th>
        <th>Conducted By</th>
        <th>Dates</th>
        <th>Hours</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
    @foreach($report['rows'] as $r)
    <tr>
        <td class="emp-name">{{ $r['name'] }}</td>
        <td>{{ $r['title'] }}</td>
        <td><span class="dept-tag">{{ $r['conductor'] }}</span></td>
        <td>{{ $r['dates'] }}</td>
        <td class="report-count">{{ number_format($r['hours'], 1) }}</td>
        <td><span class="badge-status {{ $r['badge'] }}">{{ $r['status'] }}</span></td>
    </tr>
    @endforeach
</tbody>
<tfoot>
    <tr class="report-total-row">
        <td colspan="4">TOTAL ({{ $report['rows']->count() }} {{ \Illuminate\Support\Str::plural('record', $report['rows']->count()) }})</td>
        <td class="report-count is-strong">{{ number_format($report['totals']['hours'], 1) }}</td>
        <td></td>
    </tr>
</tfoot>
