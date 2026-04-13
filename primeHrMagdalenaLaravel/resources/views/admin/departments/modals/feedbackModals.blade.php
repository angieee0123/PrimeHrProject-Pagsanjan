<!-- Success Modal -->
<div id="success-modal" class="fb-overlay">
    <div class="fb-box" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-success">SUCCESS</span>
        <h3 class="fb-title">Department Registered!</h3>
        <p class="fb-desc">The department has been successfully added to the system.</p>
        <button class="fb-btn fb-btn-success" onclick="closeSuccessModal()">Done</button>
    </div>
</div>

<!-- Failed Modal -->
<div id="failed-modal" class="fb-overlay">
    <div class="fb-box" onclick="event.stopPropagation()">
        <div class="fb-icon-wrap fb-icon-failed">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <span class="fb-eyebrow fb-eyebrow-failed">FAILED</span>
        <h3 class="fb-title">Registration Failed</h3>
        <p class="fb-desc" id="failed-msg">Something went wrong. Please check the form and try again.</p>
        <button class="fb-btn fb-btn-failed" onclick="closeFailedModal()">Try Again</button>
    </div>
</div>
