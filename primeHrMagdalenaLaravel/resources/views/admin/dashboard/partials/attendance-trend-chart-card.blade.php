{{-- Attendance Trend chart — canvas is driven by initCharts()/switchAttendanceChart() in the page script. --}}
<div class="chart-card" style="display:flex;flex-direction:column">
    <div class="chart-header">
        <div>
            <p class="chart-title">Attendance Trend</p>
            <p class="chart-sub">Daily attendance rate</p>
        </div>
        <div class="chart-tabs">
            <button class="chart-tab active" onclick="switchAttendanceChart('week')">Week</button>
            <button class="chart-tab" onclick="switchAttendanceChart('month')">Month</button>
            <button class="chart-tab" onclick="switchAttendanceChart('year')">Year</button>
        </div>
    </div>
    <div style="flex:1;padding:0 16px 16px 16px;display:flex;align-items:stretch">
        <canvas id="attendanceChart" style="width:100%;height:100%"></canvas>
    </div>
</div>
