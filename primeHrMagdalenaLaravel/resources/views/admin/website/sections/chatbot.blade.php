@php $v = $content['chatbot']; @endphp

<div class="wc-grid">
    <label class="wc-field">
        <span class="wc-label">Name shown in the chat window</span>
        <input type="text" class="wc-input" name="name" value="{{ $v['name'] }}">
    </label>
    <label class="wc-field">
        <span class="wc-label">Grey text in the message box</span>
        <input type="text" class="wc-input" name="placeholder" value="{{ $v['placeholder'] }}">
    </label>
</div>

<label class="wc-field">
    <span class="wc-label">First thing the assistant says</span>
    <textarea class="wc-input wc-area" rows="3" name="greeting">{{ $v['greeting'] }}</textarea>
    <span class="wc-hint">Shown when the widget opens, and again after a visitor clears the conversation.</span>
</label>

<div class="wc-repeat" data-repeat="quick_actions" data-max="8">
    <div class="wc-repeat-head">
        <span class="wc-label">Quick question chips</span>
        <button type="button" class="wc-add" data-add>+ Add chip</button>
    </div>
    <div class="wc-rows is-stacked" data-rows>
        @foreach($v['quick_actions'] as $qa)
        <div class="wc-card" data-row>
            <div class="wc-card-top">
                <input type="text" class="wc-input" data-name="label" value="{{ $qa['label'] }}" placeholder="Chip label">
                <select class="wc-input wc-select" data-name="icon">
                    @foreach($chipIcons as $ic)
                        <option value="{{ $ic }}" {{ ($qa['icon'] ?? '') === $ic ? 'selected' : '' }}>{{ ucfirst($ic) }}</option>
                    @endforeach
                </select>
                <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
            </div>
            <input type="text" class="wc-input" data-name="question" value="{{ $qa['question'] }}" placeholder="The question this chip asks">
        </div>
        @endforeach
    </div>
    <template data-template>
        <div class="wc-card" data-row>
            <div class="wc-card-top">
                <input type="text" class="wc-input" data-name="label" value="" placeholder="Chip label">
                <select class="wc-input wc-select" data-name="icon">
                    @foreach($chipIcons as $ic)
                        <option value="{{ $ic }}">{{ ucfirst($ic) }}</option>
                    @endforeach
                </select>
                <button type="button" class="wc-remove" data-remove title="Remove">&times;</button>
            </div>
            <input type="text" class="wc-input" data-name="question" value="" placeholder="The question this chip asks">
        </div>
    </template>
</div>

{{-- The public widget talks to a separate service, not the in-app AI
     Assistant. Worth stating here: the copy is editable, the answers are not
     coming from anything this editor controls. --}}
<div class="wc-callout is-warn">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <div><strong>Answers come from a separate chatbot service.</strong> This widget posts to <code>127.0.0.1:5000</code>, not to the PRIME HRIS AI Assistant. If that service is not running, visitors are told the assistant could not be reached. Only the wording above is controlled here.</div>
</div>
