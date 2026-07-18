{{-- Department Distribution card — expects: $departments (collection with name, count, percentage, color), $stats. --}}
<div class="table-section" style="margin:0">
    <div class="table-header">
        <div>
            <p class="table-title">Department Distribution</p>
            <p class="table-sub">{{ $departments->count() }} departments · {{ number_format($stats['total_employees']) }} employees</p>
        </div>
    </div>
    @php $maxCount = $departments->max('count') ?? 1; @endphp
    <div class="enterprise-card-body" style="padding:12px 20px 20px;max-height:100%;overflow-y:auto" id="deptDistContainer">
        @foreach($departments as $d)
        <div class="dept-dist-row">
            <span class="dept-dist-swatch" style="background:{{ $d['color'] }}"></span>
            <div class="dept-dist-info">
                <div class="dept-dist-top">
                    <span class="dept-dist-name">{{ $d['name'] }}</span>
                    <span class="dept-dist-count">{{ $d['count'] }}<em>{{ $d['percentage'] }}%</em></span>
                </div>
                <div class="dept-dist-track">
                    <div class="dept-dist-fill" style="width:{{ $maxCount > 0 ? round($d['count']/$maxCount*100) : 0 }}%;background:{{ $d['color'] }}"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
