<thead>
    <tr>
        <th>Employee ID</th>
        <th>Name</th>
        <th>Department</th>
        <th>Gross Pay</th>
        <th>Total Deductions</th>
        <th>Net Pay</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
    @foreach($report['rows'] as $r)
    <tr>
        <td class="emp-id">{{ $r['code'] }}</td>
        <td class="emp-name">{{ $r['name'] }}</td>
        <td><span class="dept-tag">{{ $r['dept'] }}</span></td>
        <td class="pay-cell">₱{{ number_format($r['gross'], 2) }}</td>
        <td class="deduction">₱{{ number_format($r['deductions'], 2) }}</td>
        <td class="net-pay">₱{{ number_format($r['net'], 2) }}</td>
        <td><span class="badge-status {{ $r['badge'] }}">{{ $r['status'] }}</span></td>
    </tr>
    @endforeach
</tbody>
<tfoot>
    <tr class="report-total-row">
        <td colspan="3">TOTAL ({{ $report['rows']->count() }} {{ \Illuminate\Support\Str::plural('employee', $report['rows']->count()) }})</td>
        <td class="pay-cell">₱{{ number_format($report['totals']['gross'], 2) }}</td>
        <td class="deduction">₱{{ number_format($report['totals']['deductions'], 2) }}</td>
        <td class="net-pay">₱{{ number_format($report['totals']['net'], 2) }}</td>
        <td></td>
    </tr>
</tfoot>
