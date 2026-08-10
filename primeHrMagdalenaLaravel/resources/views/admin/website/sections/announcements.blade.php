@php $v = $content['announcements']; @endphp

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

<label class="wc-field">
    <span class="wc-label">Heading above the smaller updates</span>
    <input type="text" class="wc-input" name="side_heading" value="{{ $v['side_heading'] }}">
</label>

{{-- The newest entry by date becomes the featured item automatically, so an
     administrator never has to keep this list in order by hand. --}}
<div class="wc-callout">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <div><strong>The newest date is featured.</strong> Whichever entry has the latest date gets the large panel; the rest fall into the “{{ $v['side_heading'] }}” list. You do not need to reorder them.</div>
</div>

<div class="wc-repeat" data-repeat="items" data-max="20">
    <div class="wc-repeat-head">
        <span class="wc-label">Announcements</span>
        <button type="button" class="wc-add" data-add>+ Add announcement</button>
    </div>
    <div class="wc-rows is-stacked" data-rows>
        @foreach($v['items'] as $a)
        <div class="wc-card" data-row>
            <div class="wc-card-top">
                <input type="date" class="wc-input wc-date" data-name="date" value="{{ \Illuminate\Support\Carbon::parse($a['date'])->format('Y-m-d') }}">
                <select class="wc-input wc-select" data-name="tag">
                    @foreach($tags as $t)
                        <option value="{{ $t }}" {{ $a['tag'] === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
            </div>
            <input type="text" class="wc-input" data-name="title" value="{{ $a['title'] }}" placeholder="Title">
            <textarea class="wc-input wc-area" rows="2" data-name="excerpt" placeholder="Short summary shown on the page">{{ $a['excerpt'] }}</textarea>
        </div>
        @endforeach
    </div>
    <template data-template>
        <div class="wc-card" data-row>
            <div class="wc-card-top">
                <input type="date" class="wc-input wc-date" data-name="date" value="{{ now()->format('Y-m-d') }}">
                <select class="wc-input wc-select" data-name="tag">
                    @foreach($tags as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
                <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
            </div>
            <input type="text" class="wc-input" data-name="title" value="" placeholder="Title">
            <textarea class="wc-input wc-area" rows="2" data-name="excerpt" placeholder="Short summary shown on the page"></textarea>
        </div>
    </template>
</div>
