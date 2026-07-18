        @php
            $bd = $breakdown ?? ['leadership' => 0, 'technical' => 0, 'core' => 0];
            $bdMax = max($bd['leadership'], $bd['technical'], $bd['core'], 1);
            $bdBar = fn ($h) => (int) round(($h / $bdMax) * 100);
            $barLeadershipPct = $bdBar($bd['leadership']);
            $barTechnicalPct  = $bdBar($bd['technical']);
            $barCorePct       = $bdBar($bd['core']);
        @endphp
        <div class="training-breakdown-panel">
            <p class="training-breakdown-panel-title">Breakdown by L&amp;D Category</p>
            <p class="training-breakdown-panel-sub">Verified L&amp;D hours only · FY {{ date('Y') }}</p>
            <div class="training-breakdown-grid">
                <div class="training-breakdown-card">
                    <div class="training-breakdown-card-head">
                        <span class="training-breakdown-dot leadership"></span>
                        <span class="training-breakdown-card-label">Leadership</span>
                        <span class="training-breakdown-card-hours" id="hoursLeadership">{{ $bd['leadership'] }} hrs</span>
                    </div>
                    <div class="training-mini-bar" aria-hidden="true">
                        <div class="training-mini-bar-fill leadership" id="barLeadership" data-hours="{{ $bd['leadership'] }}" data-bar-width="{{ $barLeadershipPct }}"></div>
                    </div>
                </div>
                <div class="training-breakdown-card">
                    <div class="training-breakdown-card-head">
                        <span class="training-breakdown-dot technical"></span>
                        <span class="training-breakdown-card-label">Technical</span>
                        <span class="training-breakdown-card-hours" id="hoursTechnical">{{ $bd['technical'] }} hrs</span>
                    </div>
                    <div class="training-mini-bar" aria-hidden="true">
                        <div class="training-mini-bar-fill technical" id="barTechnical" data-hours="{{ $bd['technical'] }}" data-bar-width="{{ $barTechnicalPct }}"></div>
                    </div>
                </div>
                <div class="training-breakdown-card">
                    <div class="training-breakdown-card-head">
                        <span class="training-breakdown-dot core"></span>
                        <span class="training-breakdown-card-label">Core / Foundation</span>
                        <span class="training-breakdown-card-hours" id="hoursCore">{{ $bd['core'] }} hrs</span>
                    </div>
                    <div class="training-mini-bar" aria-hidden="true">
                        <div class="training-mini-bar-fill core" id="barCore" data-hours="{{ $bd['core'] }}" data-bar-width="{{ $barCorePct }}"></div>
                    </div>
                </div>
            </div>
        </div>
