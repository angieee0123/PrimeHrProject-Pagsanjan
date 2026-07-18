{{--
    Bird Schedule Tooltip — self-contained hover tooltip for the `.bird-item`
    rows rendered by partials/early-birds-card.blade.php. No server data needed
    here; it reads each row's `data-schedule` JSON attribute on hover. Must be
    included after early-birds-card so those `.bird-item` elements already
    exist in the DOM when this script's querySelectorAll runs.
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

<script>
(function () {
    const tooltip   = document.getElementById('birdScheduleTooltip');
    const elAmIn    = document.getElementById('bstAmIn');
    const elAmOut   = document.getElementById('bstAmOut');
    const elPmIn    = document.getElementById('bstPmIn');
    const elPmOut   = document.getElementById('bstPmOut');
    const elDates   = document.getElementById('bstDates');
    const elBody    = tooltip.querySelector('.bst-body');
    const elNoSched = document.createElement('div');
    elNoSched.className = 'bst-no-schedule';
    elNoSched.textContent = 'No schedule assigned';

    function positionTooltip(rect) {
        requestAnimationFrame(() => {
            const tw  = tooltip.offsetWidth;
            const th  = tooltip.offsetHeight;
            const gap = 10;
            let left  = rect.left + rect.width / 2 - tw / 2;
            let top   = rect.top - th - gap;
            let arrow = 'bst-arrow-down';

            if (top < gap) {
                top   = rect.bottom + gap;
                arrow = 'bst-arrow-up';
            }
            left = Math.max(gap, Math.min(left, window.innerWidth - tw - gap));

            tooltip.style.left  = left + 'px';
            tooltip.style.top   = top  + 'px';
            tooltip.className   = arrow;
        });
    }

    function showTooltip(el) {
        const raw = el.dataset.schedule;
        elNoSched.remove();

        if (!raw || raw === 'null') {
            elBody.style.display  = 'none';
            elDates.style.display = 'none';
            tooltip.appendChild(elNoSched);
        } else {
            const s = JSON.parse(raw);
            elBody.style.display = '';
            elAmIn.textContent  = s.am_in;
            elAmOut.textContent = s.am_out;
            elPmIn.textContent  = s.pm_in;
            elPmOut.textContent = s.pm_out;

            if (s.start_date || s.end_date) {
                elDates.textContent   = 'Effective: ' + (s.start_date || 'Open') + ' – ' + (s.end_date || 'Ongoing');
                elDates.style.display = '';
            } else {
                elDates.style.display = 'none';
            }
        }

        tooltip.style.display = 'block';
        positionTooltip(el.getBoundingClientRect());
    }

    function hideTooltip() {
        tooltip.style.display = 'none';
    }

    document.querySelectorAll('.bird-item').forEach(el => {
        el.addEventListener('mouseenter', function () { showTooltip(this); });
        el.addEventListener('mouseleave', hideTooltip);
        el.addEventListener('touchstart', function () { showTooltip(this); }, { passive: true });
    });
})();
</script>
