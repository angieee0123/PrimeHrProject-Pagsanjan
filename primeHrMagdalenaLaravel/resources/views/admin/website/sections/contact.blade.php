@php $v = $content['contact']; @endphp

{{--
    Fourteen fields in one column, every label uppercase and half of them
    jargon ("eyebrow", "office sub-line"). Grouped into the three things they
    actually are, and renamed into words somebody who has never built a web
    page would use.
--}}

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Section heading</h4>
        <p class="wc-group-sub">The wording at the top of the Contact section.</p>
    </div>
    <div class="wc-group-body">
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Small label above the heading</span>
                <input type="text" class="wc-input" name="eyebrow" value="{{ $v['eyebrow'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Heading</span>
                <input type="text" class="wc-input" name="heading" value="{{ $v['heading'] }}">
            </label>
        </div>
        <label class="wc-field">
            <span class="wc-label">Intro sentence</span>
            <input type="text" class="wc-input" name="sub" value="{{ $v['sub'] }}">
        </label>
    </div>
</fieldset>

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Office details</h4>
        <p class="wc-group-sub">Leave any line blank to hide it from the page.</p>
    </div>
    <div class="wc-group-body">
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Office name</span>
                <input type="text" class="wc-input" name="office_title" value="{{ $v['office_title'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Location line</span>
                <input type="text" class="wc-input" name="office_sub" value="{{ $v['office_sub'] }}">
            </label>
        </div>
        <label class="wc-field">
            <span class="wc-label">Address</span>
            <input type="text" class="wc-input" name="address" value="{{ $v['address'] }}">
        </label>
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Phone</span>
                <input type="text" class="wc-input" name="phone" value="{{ $v['phone'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Email</span>
                <input type="text" class="wc-input" name="email" value="{{ $v['email'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Office hours</span>
                <input type="text" class="wc-input" name="hours" value="{{ $v['hours'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Closing note</span>
                <input type="text" class="wc-input" name="closed_note" value="{{ $v['closed_note'] }}">
            </label>
        </div>
    </div>
</fieldset>

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">The message form</h4>
        <p class="wc-group-sub">Wording around the form visitors fill in.</p>
    </div>
    <div class="wc-group-body">
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Form title</span>
                <input type="text" class="wc-input" name="form_title" value="{{ $v['form_title'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Reply-time badge</span>
                <input type="text" class="wc-input" name="form_badge" value="{{ $v['form_badge'] }}">
            </label>
        </div>
        <label class="wc-field">
            <span class="wc-label">Line under the form title</span>
            <input type="text" class="wc-input" name="form_sub" value="{{ $v['form_sub'] }}">
        </label>
        <label class="wc-field">
            <span class="wc-label">Privacy note below the send button</span>
            <textarea class="wc-input wc-area" rows="2" name="form_privacy">{{ $v['form_privacy'] }}</textarea>
        </label>

        {{-- The form posts nowhere: welcome.blade.php intercepts submit and
             shows an alert. Saying so here stops an administrator polishing
             copy for a form that never delivers a message. --}}
        <div class="wc-callout is-warn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div><strong>This form does not send anything yet.</strong> Submitting it shows a confirmation and clears the fields &mdash; no message is stored or emailed. The wording above is editable, but the form needs wiring up before it can be relied on.</div>
        </div>
    </div>
</fieldset>
