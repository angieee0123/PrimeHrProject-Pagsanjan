{{-- Department Distribution card — expects: $departments (collection with name, count, percentage, color), $stats. --}}
<div class="table-section" style="margin:0;display:flex;flex-direction:column">
    <div class="table-header">
        <div>
            <p class="table-title">Department Distribution</p>
            <p class="table-sub">{{ $departments->count() }} departments · {{ number_format($stats['total_employees']) }} employees</p>
        </div>
    </div>
    @php $maxCount = $departments->max('count') ?? 1; @endphp
    {{-- Cap the visible list at 10 department rows (~43px each + top padding),
         then scroll vertically for the rest. flex:1 + min-height:0 still lets it
         shrink to match shorter neighbours in the compact grid row. --}}
    <div class="enterprise-card-body" style="padding:12px 20px 20px;flex:1;min-height:0;max-height:448px;overflow-y:auto" id="deptDistContainer">
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

@push('scripts')
    @vite('resources/js/admin/dashboard/department-distribution-card.js')
@endpush
