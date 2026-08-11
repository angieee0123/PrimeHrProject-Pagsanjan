{{--
    The seal plus the name beside it, as it appears in the navbar of every
    public-facing page.

    Same story as the gov bar: four copies of "Pagsanjan, Laguna" / "Municipal
    Government", only one of which followed Website Content → Logo & branding.
    The seal already came from SiteContentService::logoUrl(); the wording did
    not.
--}}
@php $brand = \App\Services\SiteContentService::section('brand'); @endphp

<div class="pub-logo">
    <div class="pub-logo-seal">
        <img src="{{ \App\Services\SiteContentService::logoUrl() }}" alt="{{ $brand['name'] }}"
             onerror="this.style.display='none'"
             style="width:36px;height:36px;border-radius:50%;object-fit:cover">
    </div>
    <div>
        <span class="pub-logo-name">{{ $brand['name'] }}</span>
        <span class="pub-logo-sub">{{ $brand['sub'] }}</span>
    </div>
</div>
