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
    {{-- The canvas is absolutely positioned inside this wrapper (see
         .attendance-canvas-wrap): Chart.js writes a pixel height onto the
         canvas, and while the card stretches to the grid row, that height would
         feed back into the row and grow it on every resize pass. Taking the
         canvas out of flow lets it fill the card without affecting its height. --}}
    <div class="attendance-canvas-wrap">
        <canvas id="attendanceChart"></canvas>
    </div>
</div>
