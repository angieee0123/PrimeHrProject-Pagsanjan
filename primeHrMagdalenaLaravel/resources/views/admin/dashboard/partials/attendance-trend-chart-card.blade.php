{{-- Attendance Trend chart — canvas is driven by initCharts()/loadAttendancePeriod() in
     resources/js/admin/dashboard/charts.js. The Week / Month / Year tabs are the bucket
     size; the picker beside them chooses WHICH week, month or year (AttendanceTrendService).
     The card ships the current week already rendered, so the first paint needs no request. --}}
<div class="chart-card" id="attendanceTrendCard"
     data-endpoint="{{ route('admin.dashboard.attendance-trend') }}"
     style="display:flex;flex-direction:column">
    <div class="chart-header">
        <div>
            <p class="chart-title">Attendance Trend</p>
            <p class="chart-sub" id="attendanceChartSub">{{ $chartData['attendance']['sublabel'] }}</p>
        </div>
        <div class="att-controls">
            <div class="chart-tabs" id="attendancePeriodTabs">
                <button type="button" class="chart-tab active" data-period="week">Week</button>
                <button type="button" class="chart-tab" data-period="month">Month</button>
                <button type="button" class="chart-tab" data-period="year">Year</button>
            </div>
            <div class="att-range">
                <button type="button" class="att-range-step" id="attendancePrev" aria-label="Previous period">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
                {{-- The type follows the tab: a date for a week, a month for a
                     month, a year for a year. Set by the page script. --}}
                <input type="date" class="att-range-input" id="attendanceAnchor"
                       value="{{ $chartData['attendance']['anchor'] }}"
                       min="2000-01-01" max="2100-12-31"
                       aria-label="Period to display">
                <button type="button" class="att-range-step" id="attendanceNext" aria-label="Next period">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        </div>
    </div>
    {{-- The canvas is absolutely positioned inside this wrapper (see
         .attendance-canvas-wrap): Chart.js writes a pixel height onto the
         canvas, and while the card stretches to the grid row, that height would
         feed back into the row and grow it on every resize pass. Taking the
         canvas out of flow lets it fill the card without affecting its height.
         The note sits in the same box for a period with nothing in it. --}}
    <div class="attendance-canvas-wrap" id="attendanceCanvasWrap">
        <canvas id="attendanceChart"></canvas>
        <p class="att-chart-note" id="attendanceChartNote" role="status" hidden></p>
    </div>
</div>
