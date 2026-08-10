@php $v = $content['brand']; @endphp

{{--
    The seal.

    It sits inside this section's form for layout, but takes no part in it: the
    file input carries no `name`, so FormData skips it, and both buttons are
    type="button". Uploading posts to its own endpoint instead.

    That separation is the point. Saving this section's *text* can never touch
    the file, and replacing the file never has to round-trip a stored path
    through a hidden input that a client could rewrite into something else.
--}}
<div class="wc-logo" data-logo
     data-upload-url="{{ route('admin.website.logo') }}"
     data-default="{{ $logoIsDefault ? '1' : '0' }}">
    <div class="wc-logo-preview">
        <img src="{{ $logoUrl }}" alt="Current logo" data-logo-img>
    </div>
    <div class="wc-logo-body">
        <span class="wc-label">Municipal seal</span>
        <p class="wc-logo-state" data-logo-state>
            {{ $logoIsDefault
                ? 'Currently using the logo shipped with the system.'
                : 'Using an uploaded logo.' }}
        </p>
        <p class="wc-hint">
            Shown on the public page, both sidebars, the sign-in screens, the payslip
            modals and the printed leave and pass-slip forms &mdash; replacing it here
            changes all of them. JPG, PNG or WEBP, at least 64&times;64, up to 2&nbsp;MB.
            A square image works best.
        </p>
        <div class="wc-logo-actions">
            <label class="wc-add wc-logo-pick">
                Choose image&hellip;
                <input type="file" accept="image/jpeg,image/png,image/webp" data-logo-input hidden>
            </label>
            <button type="button" class="wc-logo-reset" data-logo-reset @disabled($logoIsDefault)>
                Use the original
            </button>
        </div>
        <p class="wc-msg" data-logo-msg hidden></p>
    </div>
</div>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Name beside the seal</span>
        <input type="text" class="wc-input" name="name" value="{{ $v['name'] }}">
    </label>
    <label class="wc-field">
        <span class="wc-label">Line under the name</span>
        <input type="text" class="wc-input" name="sub" value="{{ $v['sub'] }}">
    </label>
</div>

<label class="wc-field">
    <span class="wc-label">Sign-in button wording</span>
    <input type="text" class="wc-input" name="portal_label" value="{{ $v['portal_label'] }}">
    <span class="wc-hint">The button always links to the sign-in page; only the wording is editable.</span>
</label>

<div class="wc-repeat" data-repeat="nav_links" data-max="8">
    <div class="wc-repeat-head">
        <span class="wc-label">Navigation links</span>
        <button type="button" class="wc-add" data-add>+ Add link</button>
    </div>
    <div class="wc-rows" data-rows>
        @foreach($v['nav_links'] as $link)
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="label" value="{{ $link['label'] }}" placeholder="Label">
            <input type="text" class="wc-input" data-name="anchor" value="{{ $link['anchor'] }}" placeholder="#services">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
        @endforeach
    </div>
    <span class="wc-hint">A link must be a same-page anchor such as <code>#services</code>, or a full <code>https://</code> address.</span>
    <template data-template>
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="label" value="" placeholder="Label">
            <input type="text" class="wc-input" data-name="anchor" value="#" placeholder="#services">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
    </template>
</div>
