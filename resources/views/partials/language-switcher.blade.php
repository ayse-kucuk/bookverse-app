@php
    $currentLocale = app()->getLocale();
    $locales = config('locales.available', ['tr', 'en']);
    $labels = config('locales.labels', []);
@endphp

<div class="bv-lang-switcher flex items-center gap-0.5 rounded-full border border-[#e8e4de] bg-white p-0.5" role="group" aria-label="{{ __('ui.nav.language') }}">
    @foreach($locales as $locale)
        <a
            href="{{ route('locale.switch', $locale) }}"
            hreflang="{{ $locale }}"
            class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider transition {{ $currentLocale === $locale ? 'is-active bg-[#1c1c1c] text-white' : 'text-[#9a948d] hover:text-[#1c1c1c]' }}"
            @if($currentLocale === $locale) aria-current="true" @endif
        >
            {{ strtoupper($locale) }}
        </a>
    @endforeach
</div>
