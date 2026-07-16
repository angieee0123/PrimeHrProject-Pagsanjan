{{--
    Shared modal-footer action button. Replaces the hand-copied
    <button class="modal-btn-primary" onclick="...">...</button> markup
    duplicated across modal footers, now that modal-btn-primary/ghost/danger/
    danger-outline/success have one canonical definition in glassSystem.css
    instead of ~10 divergent per-page ones.

    Usage:
        <x-modal-btn variant="ghost" onclick="closeAddEmployee()">Cancel</x-modal-btn>
        <x-modal-btn type="submit">Save Changes</x-modal-btn>
        <x-modal-btn variant="danger" onclick="confirmDelete()">Delete</x-modal-btn>

    onclick/style/id/disabled/data-* etc. aren't declared in @props, so Blade
    passes them through automatically via $attributes — no special-casing
    needed (unlike x-modal's `close` prop, which wraps a plain id in a
    `{{ $close }}()` call convention; that doesn't fit here since onclick
    handlers on these buttons often take arguments, e.g. confirmDelete(123)).
--}}
@props([
    'variant' => 'primary', // primary | ghost | danger | danger-outline | success
    'type' => 'button',
])
<button type="{{ $type }}" {{ $attributes->class(["modal-btn-{$variant}"]) }}>
    {{ $slot }}
</button>
