{{-- Travel party avatar cluster: the filer plus every companion on a travel order.
     Hovering a face enlarges it and shows a name tooltip (same interaction as the
     Detailed Time Record timeline). Companion rings are colored by response status.
     Expects: $order — TravelOrder with employee + companions.employee loaded. --}}
@once
<style>
.tp-cluster { display: flex; align-items: center; flex-shrink: 0; }
.tp-chip {
    position: relative;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    margin-left: -9px;
    cursor: pointer;
}
.tp-chip:first-child { margin-left: 0; }
.tp-chip:hover { z-index: 30; }
.tp-avatar-img {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    color: #fff;
    font-size: 10.5px;
    font-weight: 700;
    box-sizing: border-box;
    background: linear-gradient(135deg, #0b044d, #4338ca);
    transition: transform .25s cubic-bezier(.4,0,.2,1), filter .25s ease;
}
.tp-chip:hover .tp-avatar-img {
    transform: translateY(-14px) scale(2.6);
    filter: drop-shadow(0 14px 22px rgba(11,4,77,.35));
}
.tp-chip.tp-filer    .tp-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px #0b044d; }
.tp-chip.tp-accepted .tp-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px #22c55e; }
.tp-chip.tp-pending  .tp-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px #f59e0b; }
.tp-chip.tp-rejected .tp-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px #ef4444; opacity: .7; }

.tp-status-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    border: 1.5px solid #fff;
}
.tp-chip.tp-accepted .tp-status-dot { background: #22c55e; }
.tp-chip.tp-pending  .tp-status-dot { background: #f59e0b; }
.tp-chip.tp-rejected .tp-status-dot { background: #ef4444; }

/* custom tooltip, mirroring the .dtl-avatar-chip pattern */
.tp-chip:hover::after {
    content: attr(data-tooltip);
    position: absolute; bottom: calc(100% + 12px); left: 50%; transform: translateX(-50%);
    background: #1a1f36; color: #fff; font-size: 11px; font-weight: 500;
    padding: 6px 10px; border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,.18); z-index: 40; pointer-events: none;
    max-width: 240px; width: max-content; white-space: normal; text-align: center; line-height: 1.4;
}
.tp-chip:hover::before {
    content: '';
    position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #1a1f36; z-index: 40; pointer-events: none;
}
</style>
@endonce
@php
    $tpPhotoUrl = function ($tpEmp) {
        if (!$tpEmp || !$tpEmp->photo) return null;
        return \Illuminate\Support\Str::startsWith($tpEmp->photo, ['/', 'http']) ? $tpEmp->photo : asset('storage/' . $tpEmp->photo);
    };
    $tpInitials = fn ($tpEmp) => strtoupper(substr($tpEmp->first_name ?? 'E', 0, 1) . substr($tpEmp->last_name ?? 'E', 0, 1));
    $tpFiler = $order->employee;
@endphp
<div class="tp-cluster">
    @if($tpFiler)
    <span class="tp-chip tp-filer" data-tooltip="{{ $tpFiler->first_name }} {{ $tpFiler->last_name }} • Filed this travel order">
        @if($tpPhotoUrl($tpFiler))
            <img src="{{ $tpPhotoUrl($tpFiler) }}" alt="{{ $tpFiler->first_name }}" class="tp-avatar-img">
        @else
            <span class="tp-avatar-img">{{ $tpInitials($tpFiler) }}</span>
        @endif
    </span>
    @endif
    @foreach($order->companions as $tpCompanion)
        @php $tpEmp = $tpCompanion->employee; @endphp
        @continue(!$tpEmp)
        <span class="tp-chip tp-{{ $tpCompanion->status }}" data-tooltip="{{ $tpEmp->first_name }} {{ $tpEmp->last_name }} • Companion · {{ ucfirst($tpCompanion->status) }}">
            @if($tpPhotoUrl($tpEmp))
                <img src="{{ $tpPhotoUrl($tpEmp) }}" alt="{{ $tpEmp->first_name }}" class="tp-avatar-img">
            @else
                <span class="tp-avatar-img">{{ $tpInitials($tpEmp) }}</span>
            @endif
            <span class="tp-status-dot"></span>
        </span>
    @endforeach
</div>
