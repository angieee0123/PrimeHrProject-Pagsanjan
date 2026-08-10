@php $v = $content['cta']; @endphp

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
    <span class="wc-label">Paragraph</span>
    <textarea class="wc-input wc-area" rows="3" name="text">{{ $v['text'] }}</textarea>
</label>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Button label</span>
        <input type="text" class="wc-input" name="button_label" value="{{ $v['button_label'] }}">
        <span class="wc-hint">Always links to the sign-in page.</span>
    </label>
    <label class="wc-field">
        <span class="wc-label">Small print under the button</span>
        <input type="text" class="wc-input" name="note" value="{{ $v['note'] }}">
    </label>
</div>

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Card title</span>
        <input type="text" class="wc-input" name="card_label" value="{{ $v['card_label'] }}">
    </label>
    <label class="wc-field">
        <span class="wc-label">Line under the card title</span>
        <input type="text" class="wc-input" name="card_sub" value="{{ $v['card_sub'] }}">
    </label>
</div>

<div class="wc-repeat" data-repeat="features" data-max="12">
    <div class="wc-repeat-head">
        <span class="wc-label">Feature list</span>
        <button type="button" class="wc-add" data-add>+ Add feature</button>
    </div>
    <div class="wc-rows" data-rows>
        @foreach($v['features'] as $f)
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="" value="{{ $f }}" placeholder="e.g. Payroll Processing">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
        @endforeach
    </div>
    <template data-template>
        <div class="wc-row" data-row>
            <input type="text" class="wc-input" data-name="" value="" placeholder="e.g. Payroll Processing">
            <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
        </div>
    </template>
</div>
