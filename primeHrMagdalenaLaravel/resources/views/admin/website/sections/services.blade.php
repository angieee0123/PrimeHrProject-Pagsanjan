@php $v = $content['services']; @endphp

<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Section heading</h4>
        <p class="wc-group-sub">The wording above the service tabs.</p>
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

{{--
    Two levels of repeater: categories become the tabs, each holding its own
    service rows. The editor script indexes the inner list from its own
    position inside the outer row, so adding a category cannot renumber
    another category's services.

    The middle "services in this category" panel is gone — it was a third
    nested grey box and the category card can carry that heading itself.
--}}
<fieldset class="wc-group">
    <div class="wc-group-head">
        <h4 class="wc-group-title">Service categories</h4>
        <p class="wc-group-sub">Each category becomes a tab on the page, holding the services listed under it.</p>
    </div>

    <div class="wc-repeat" data-repeat="categories" data-max="6">
        <div class="wc-repeat-head">
            <span class="wc-sublabel">{{ count($v['categories']) }} categories</span>
            <button type="button" class="wc-add" data-add>+ Add category</button>
        </div>
        <div class="wc-rows is-stacked" data-rows>
            @foreach($v['categories'] as $cat)
            {{-- Collapsed by default. Three fully-expanded categories made this
                 panel over 2,000px tall; the summary carries the name and the
                 service count, which is what you scan for. A closed <details>
                 still submits its fields. --}}
            <details class="wc-card is-group" data-row>
                <summary class="wc-card-summary">
                    <span class="wc-card-summary-title">{{ $cat['label'] }}</span>
                    <span class="wc-card-summary-meta">{{ count($cat['items'] ?? []) }} {{ Str::plural('service', count($cat['items'] ?? [])) }}</span>
                </summary>
                <div class="wc-card-top">
                    <label class="wc-field">
                        <span class="wc-label">Category name</span>
                        <input type="text" class="wc-input" data-name="label" value="{{ $cat['label'] }}" placeholder="Permits &amp; Registration">
                    </label>
                    <label class="wc-field">
                        <span class="wc-label">Tab icon</span>
                        <select class="wc-input wc-select" data-name="icon">
                            @foreach($icons as $ic)
                                <option value="{{ $ic }}" {{ ($cat['icon'] ?? '') === $ic ? 'selected' : '' }}>{{ ucfirst($ic) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="button" class="wc-remove is-aligned" data-remove title="Remove category">&times;</button>
                </div>

                <div class="wc-repeat is-nested" data-repeat="items" data-max="20">
                    <div class="wc-repeat-head">
                        <span class="wc-sublabel">Services in this category</span>
                        <button type="button" class="wc-add" data-add>+ Add service</button>
                    </div>
                    <div class="wc-rows is-stacked" data-rows>
                        @foreach($cat['items'] ?? [] as $s)
                        <div class="wc-card is-sub" data-row>
                            <div class="wc-card-top">
                                <input type="text" class="wc-input" data-name="title" value="{{ $s['title'] }}" placeholder="Service name">
                                <select class="wc-input wc-select" data-name="icon">
                                    @foreach($icons as $ic)
                                        <option value="{{ $ic }}" {{ ($s['icon'] ?? '') === $ic ? 'selected' : '' }}>{{ ucfirst($ic) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="wc-remove" data-remove title="Remove service">&times;</button>
                            </div>
                            <textarea class="wc-input wc-area" rows="2" data-name="desc" placeholder="What this service is">{{ $s['desc'] }}</textarea>
                            <input type="text" class="wc-input" data-name="office" value="{{ $s['office'] }}" placeholder="Responsible office">
                        </div>
                        @endforeach
                    </div>
                    <template data-template>
                        <div class="wc-card is-sub" data-row>
                            <div class="wc-card-top">
                                <input type="text" class="wc-input" data-name="title" value="" placeholder="Service name">
                                <select class="wc-input wc-select" data-name="icon">
                                    @foreach($icons as $ic)
                                        <option value="{{ $ic }}">{{ ucfirst($ic) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="wc-remove" data-remove title="Remove service">&times;</button>
                            </div>
                            <textarea class="wc-input wc-area" rows="2" data-name="desc" placeholder="What this service is"></textarea>
                            <input type="text" class="wc-input" data-name="office" value="" placeholder="Responsible office">
                        </div>
                    </template>
                </div>
            </details>
            @endforeach
        </div>

        <template data-template>
            <details class="wc-card is-group" data-row open>
                <summary class="wc-card-summary">
                    <span class="wc-card-summary-title">New category</span>
                    <span class="wc-card-summary-meta">0 services</span>
                </summary>
                <div class="wc-card-top">
                    <label class="wc-field">
                        <span class="wc-label">Category name</span>
                        <input type="text" class="wc-input" data-name="label" value="" placeholder="Permits &amp; Registration">
                    </label>
                    <label class="wc-field">
                        <span class="wc-label">Tab icon</span>
                        <select class="wc-input wc-select" data-name="icon">
                            @foreach($icons as $ic)
                                <option value="{{ $ic }}">{{ ucfirst($ic) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="button" class="wc-remove is-aligned" data-remove title="Remove category">&times;</button>
                </div>
                <div class="wc-repeat is-nested" data-repeat="items" data-max="20">
                    <div class="wc-repeat-head">
                        <span class="wc-sublabel">Services in this category</span>
                        <button type="button" class="wc-add" data-add>+ Add service</button>
                    </div>
                    <div class="wc-rows is-stacked" data-rows></div>
                    <template data-template>
                        <div class="wc-card is-sub" data-row>
                            <div class="wc-card-top">
                                <input type="text" class="wc-input" data-name="title" value="" placeholder="Service name">
                                <select class="wc-input wc-select" data-name="icon">
                                    @foreach($icons as $ic)
                                        <option value="{{ $ic }}">{{ ucfirst($ic) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="wc-remove" data-remove title="Remove service">&times;</button>
                            </div>
                            <textarea class="wc-input wc-area" rows="2" data-name="desc" placeholder="What this service is"></textarea>
                            <input type="text" class="wc-input" data-name="office" value="" placeholder="Responsible office">
                        </div>
                    </template>
                </div>
            </details>
        </template>
    </div>
</fieldset>
