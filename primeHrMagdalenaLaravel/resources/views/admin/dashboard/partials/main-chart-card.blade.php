{{-- Main payroll/employees chart — canvas is driven by initCharts()/switchMainChart()/switchPeriodChart() in the page script. No server data needed here (chart data is @json'd separately into JS globals). --}}
<div class="chart-card">
    <div class="chart-header">
        <div>
            <p class="chart-title" id="dynamicChartTitle">Payroll by Designation</p>
            <p class="chart-sub" id="dynamicChartSub">Total payroll amounts per designation</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <div style="display:flex;gap:6px">
                <button id="tabSalary" class="chart-tab active" onclick="switchMainChart('salary')" style="font-size:11px">Payroll by Designation</button>
                <button id="tabEmployees" class="chart-tab" onclick="switchMainChart('employees')" style="font-size:11px">Employees</button>
            </div>
            <div class="chart-tabs" id="periodTabs" style="border-left:1px solid #e2e8f0;padding-left:8px">
                <button class="chart-tab active" onclick="switchPeriodChart('week')">Week</button>
                <button class="chart-tab" onclick="switchPeriodChart('month')">Month</button>
                <button class="chart-tab" onclick="switchPeriodChart('year')">Year</button>
            </div>
        </div>
    </div>
    <canvas id="dynamicChart" style="max-height:310px"></canvas>
</div>
