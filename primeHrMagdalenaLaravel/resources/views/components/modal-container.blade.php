{{--
    Shared shell for the ".modal-container" modal family — a separate,
    incompatible system from <x-modal> (.modal-box): different class names
    (.modal-container, .modal-subtitle), and opened/closed via
    classList.add/remove('active') on the overlay rather than inline
    style.display. Used across admin/deductions, admin/leaveAndBenefits, and
    admin/payroll modals, each of which used to duplicate ~200 lines of near-
    identical CSS in its own <style> block.

    Usage:
        <x-modal-container id="addDeductionTypeModal" close="closeAddDeductionTypeModal"
                             title="Add Deduction Type"
                             subtitle="Create a new deduction type for payroll processing">
            <form id="addDeductionTypeForm" ...>...</form>
        </x-modal-container>

    Set close-on-overlay-click="false" for the (rarer) modals that only
    dismiss via their own close button, not by clicking the backdrop.
--}}
@props([
    'id',
    'maxWidth' => null,
    'close' => null,
    'closeOnOverlayClick' => true,
    'title' => null,
    'subtitle' => null,
])

<div id="{{ $id }}" {{ $attributes->class(['modal-overlay']) }} @if($close && $closeOnOverlayClick) onclick="{{ $close }}(event)" @endif>
    <div class="modal-container" onclick="event.stopPropagation()" @if($maxWidth) style="max-width: {{ $maxWidth }};" @endif>
        @if($title)
            <div class="modal-header">
                <div>
                    <h3 class="modal-title">{{ $title }}</h3>
                    @isset($subtitle)<p class="modal-subtitle">{{ $subtitle }}</p>@endisset
                </div>
                @if($close)
                    <button type="button" class="modal-close" onclick="{{ $close }}()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                @endif
            </div>
        @endif
        <div class="modal-body">
            {{ $slot }}
        </div>
    </div>
</div>
