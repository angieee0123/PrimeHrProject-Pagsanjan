@php $v = $content['hero']; @endphp

<label class="wc-field">
    <span class="wc-label">Small badge above the headline</span>
    <input type="text" class="wc-input" name="badge" value="{{ $v['badge'] }}">
</label>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Headline</span>
        <input type="text" class="wc-input" name="title" value="{{ $v['title'] }}">
    </label>
    <label class="wc-field">
        <span class="wc-label">Second line, shown in colour</span>
        <input type="text" class="wc-input" name="title_highlight" value="{{ $v['title_highlight'] }}">
    </label>
</div>

<label class="wc-field">
    <span class="wc-label">Intro sentence</span>
    <textarea class="wc-input wc-area" rows="3" name="subtitle">{{ $v['subtitle'] }}</textarea>
</label>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Primary button</span>
        <input type="text" class="wc-input" name="primary_label" value="{{ $v['primary_label'] }}">
    </label>
    <label class="wc-field">
        <span class="wc-label">Secondary button</span>
        <input type="text" class="wc-input" name="secondary_label" value="{{ $v['secondary_label'] }}">
    </label>
</div>

<label class="wc-field">
    <span class="wc-label">Title on the statistics card</span>
    <input type="text" class="wc-input" name="card_title" value="{{ $v['card_title'] }}">
</label>

{{-- The first two figures are counted from the HR database at render time.
     They were typed literals — "17" and "348" — on a public page attached to
     a system that knows the real numbers, so only their wording is editable. --}}
<div class="wc-callout">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
    <div>
        <strong>Two figures are counted automatically.</strong>
        The page currently shows
        <strong>{{ $liveStats['departments'] !== null ? number_format($liveStats['departments']) : 'unavailable' }}</strong> departments and
        <strong>{{ $liveStats['personnel'] !== null ? number_format($liveStats['personnel']) : 'unavailable' }}</strong> personnel,
        read from the system as you load this page. You can reword the labels below; the numbers update themselves
        so the public page can never disagree with your records.
    </div>
</div>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Caption under the departments count</span>
        <input type="text" class="wc-input" name="stat_departments_label" value="{{ $v['stat_departments_label'] }}">
    </label>
    <label class="wc-field">
        <span class="wc-label">Caption under the personnel count</span>
        <input type="text" class="wc-input" name="stat_personnel_label" value="{{ $v['stat_personnel_label'] }}">
    </label>
</div>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Third statistic — number</span>
        <input type="text" class="wc-input" name="stat_extra_value" value="{{ $v['stat_extra_value'] }}">
        <span class="wc-hint">Leave blank to hide this stat.</span>
    </label>
    <label class="wc-field">
        <span class="wc-label">Third statistic — caption</span>
        <input type="text" class="wc-input" name="stat_extra_label" value="{{ $v['stat_extra_label'] }}">
    </label>
</div>

{{--
    Accreditation badges — set once and rarely touched, so they sit behind a
    disclosure rather than in front of the headline somebody actually came to
    edit. Collapsed is presentation only: a <details> does not stop its fields
    being submitted, so a closed block still saves what is inside it.
--}}
<details class="wc-advanced">
<summary>
    <span class="wc-advanced-title">Compliance tags</span>
    <span class="wc-advanced-hint">{{ count($v['tags']) }} shown on the hero card &middot; rarely change</span>
</summary>

<div class="wc-repeat" data-repeat="tags" data-max="8">
    <div class="wc-repeat-head">
        <span class="wc-hint">Small ticked badges beside the hero statistics.</span>
        <button type="button" class="wc-add" data-add>+ Add tag</button>
    </div>
    <div class="wc-rows" data-rows>
        @foreach($v['tags'] as $tag)
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="" value="{{ $tag }}" placeholder="e.g. BIR Compliant">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
        @endforeach
    </div>
    <template data-template>
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="" value="" placeholder="e.g. BIR Compliant">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
    </template>
</div>
</details>
