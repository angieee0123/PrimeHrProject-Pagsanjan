            <div class="stat-card training-stat-hero">
                <div class="stat-top">
                    <p class="stat-label">Total L&amp;D Hours</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statTotalHours">{{ $stats['total_hours'] }}</p>
                <div class="training-goal-track" aria-hidden="true">
                    <div class="training-goal-fill" id="trainingGoalFill" data-goal-width="{{ min(100, (int) round(($stats['total_hours'] / 40) * 100)) }}"></div>
                </div>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub" id="statGoalSub">{{ $stats['total_hours'] }} of 40 hrs · FY {{ date('Y') }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Verified</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statVerifiedCount">{{ $stats['verified'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">Hours credited to PDS</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Pending</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statPendingCount">{{ $stats['pending'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">Awaiting HR verification</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Rejected</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statRejectedCount">{{ $stats['rejected'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Needs correction or re-upload</p>
                </div>
            </div>
