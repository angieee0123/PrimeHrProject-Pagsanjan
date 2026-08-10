@php $v = $content['about']; @endphp

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Section heading</h4>
        <p class="wc-group-sub">The wording at the top of the About section.</p>
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
        <h4 class="wc-group-title">Profile card</h4>
        <p class="wc-group-sub">The panel beside the municipal seal.</p>
    </div>
    <div class="wc-group-body">
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Card title</span>
                <input type="text" class="wc-input" name="frame_tag" value="{{ $v['frame_tag'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Card sub-line</span>
                <input type="text" class="wc-input" name="frame_meta" value="{{ $v['frame_meta'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Municipality name</span>
                <input type="text" class="wc-input" name="profile_name" value="{{ $v['profile_name'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Classification badge</span>
                <input type="text" class="wc-input" name="profile_badge" value="{{ $v['profile_badge'] }}">
            </label>
        </div>

        {{-- Label/value pairs mirroring records that live elsewhere. They
             change when the municipality does, not when the page does, so they
             fold away. Collapsed is presentation only — a <details> does not
             stop its fields being submitted. --}}
        <details class="wc-advanced">
        <summary>
            <span class="wc-advanced-title">Profile facts</span>
            <span class="wc-advanced-hint">{{ count($v['facts']) }} rows &middot; rarely change</span>
        </summary>

        <div class="wc-repeat" data-repeat="facts" data-max="12">
            <div class="wc-repeat-head">
                <span class="wc-hint">Departments and Personnel here are plain text &mdash; the hero banner counts those two from the system instead.</span>
                <button type="button" class="wc-add" data-add>+ Add fact</button>
            </div>
            <div class="wc-col-labels is-pair"><span>Label</span><span>Value</span><span></span></div>
            <div class="wc-rows" data-rows>
                @foreach($v['facts'] as $f)
                <div class="wc-row" data-row>
                    <input type="text" class="wc-input" data-name="label" value="{{ $f['label'] }}" placeholder="Province">
                    <input type="text" class="wc-input" data-name="value" value="{{ $f['value'] }}" placeholder="Laguna">
                    <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
                </div>
                @endforeach
            </div>
            <template data-template>
                <div class="wc-row" data-row>
                    <input type="text" class="wc-input" data-name="label" value="" placeholder="Province">
                    <input type="text" class="wc-input" data-name="value" value="" placeholder="Laguna">
                    <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
                </div>
            </template>
        </div>
        </details>
    </div>
</fieldset>

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Description</h4>
        <p class="wc-group-sub">The write-up about the municipality.</p>
    </div>
    <div class="wc-group-body">
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Sub-heading</span>
                <input type="text" class="wc-input" name="body_heading" value="{{ $v['body_heading'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Words shown in colour</span>
                <input type="text" class="wc-input" name="body_highlight" value="{{ $v['body_highlight'] }}">
            </label>
        </div>

        <div class="wc-repeat" data-repeat="paragraphs" data-max="6">
            <div class="wc-repeat-head">
                <span class="wc-sublabel">Paragraphs</span>
                <button type="button" class="wc-add" data-add>+ Add paragraph</button>
            </div>
            <div class="wc-rows is-stacked" data-rows>
                @foreach($v['paragraphs'] as $p)
                <div class="wc-row is-wide" data-row>
                    <textarea class="wc-input wc-area" rows="3" data-name="">{{ $p }}</textarea>
                    <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
                </div>
                @endforeach
            </div>
            <template data-template>
                <div class="wc-row is-wide" data-row>
                    <textarea class="wc-input wc-area" rows="3" data-name=""></textarea>
                    <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
                </div>
            </template>
        </div>
    </div>
</fieldset>

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Vision &amp; mission</h4>
    </div>
    <div class="wc-group-body">
        <div class="wc-grid">
            <label class="wc-field">
                <span class="wc-label">Vision label</span>
                <input type="text" class="wc-input" name="vision_label" value="{{ $v['vision_label'] }}">
            </label>
            <label class="wc-field">
                <span class="wc-label">Mission label</span>
                <input type="text" class="wc-input" name="mission_label" value="{{ $v['mission_label'] }}">
            </label>
        </div>
        <label class="wc-field">
            <span class="wc-label">Vision statement</span>
            <textarea class="wc-input wc-area" rows="3" name="vision">{{ $v['vision'] }}</textarea>
        </label>
        <label class="wc-field">
            <span class="wc-label">Mission statement</span>
            <textarea class="wc-input wc-area" rows="3" name="mission">{{ $v['mission'] }}</textarea>
        </label>
    </div>
</fieldset>
