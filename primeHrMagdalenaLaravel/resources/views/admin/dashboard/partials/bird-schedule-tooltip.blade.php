{{--
    Bird Schedule Tooltip — self-contained hover tooltip for the `.bird-item`
    rows rendered by partials/early-birds-card.blade.php. No server data needed
    here; it reads each row's `data-schedule` JSON attribute on hover. The script
    is bundled as a Vite module (always deferred until after the DOM is parsed),
    so it no longer depends on include order the way the old inline <script> did.
--}}
<div id="birdScheduleTooltip" style="display:none" aria-hidden="true">
    <div class="bst-header">&#128197; Work Schedule</div>
    <div class="bst-body">
        <div class="bst-row">
            <span class="bst-period">AM</span>
            <span class="bst-time" id="bstAmIn">—</span>
            <span class="bst-sep">→</span>
            <span class="bst-time" id="bstAmOut">—</span>
        </div>
        <div class="bst-row">
            <span class="bst-period">PM</span>
            <span class="bst-time" id="bstPmIn">—</span>
            <span class="bst-sep">→</span>
            <span class="bst-time" id="bstPmOut">—</span>
        </div>
    </div>
    <div class="bst-dates" id="bstDates" style="display:none"></div>
</div>

@push('scripts')
    @vite('resources/js/dashboard/bird-schedule-tooltip.js')
@endpush
