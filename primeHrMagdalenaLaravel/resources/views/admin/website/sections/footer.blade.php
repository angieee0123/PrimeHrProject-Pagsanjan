@php $v = $content['footer']; @endphp

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Organisation name</span>
        <input type="text" class="wc-input" name="name" value="{{ $v['name'] }}">
        <span class="wc-hint">Also used as the browser tab title.</span>
    </label>
    <label class="wc-field">
        <span class="wc-label">Line under the name</span>
        <input type="text" class="wc-input" name="sub" value="{{ $v['sub'] }}">
    </label>
</div>

<div class="wc-repeat" data-repeat="links" data-max="8">
    <div class="wc-repeat-head">
        <span class="wc-label">Footer links</span>
        <button type="button" class="wc-add" data-add>+ Add link</button>
    </div>
    <div class="wc-rows" data-rows>
        @foreach($v['links'] as $link)
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="label" value="{{ $link['label'] }}" placeholder="Label">
            <input type="text" class="wc-input" data-name="anchor" value="{{ $link['anchor'] }}" placeholder="#privacy">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
        @endforeach
    </div>
    <span class="wc-hint">A same-page anchor such as <code>#contact</code>, or a full <code>https://</code> address. The default Privacy, Terms and Sitemap anchors do not lead anywhere yet.</span>
    <template data-template>
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="label" value="" placeholder="Label">
            <input type="text" class="wc-input" data-name="anchor" value="#" placeholder="#privacy">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
    </template>
</div>

<label class="wc-field">
    <span class="wc-label">Copyright wording</span>
    <input type="text" class="wc-input" name="copyright" value="{{ $v['copyright'] }}">
    <span class="wc-hint">The year is added automatically &mdash; it currently renders as &ldquo;&copy; {{ date('Y') }} {{ $v['copyright'] }}&rdquo;.</span>
</label>
