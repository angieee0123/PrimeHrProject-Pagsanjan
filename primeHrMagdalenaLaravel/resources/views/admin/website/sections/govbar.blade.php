@php $v = $content['govbar']; @endphp

<label class="wc-field">
    <span class="wc-label">Left side</span>
    <input type="text" class="wc-input" name="left" value="{{ $v['left'] }}">
    <span class="wc-hint">Appears beside the flag icon at the very top of the page.</span>
</label>

<label class="wc-field">
    <span class="wc-label">Right side</span>
    <input type="text" class="wc-input" name="right" value="{{ $v['right'] }}">
</label>
