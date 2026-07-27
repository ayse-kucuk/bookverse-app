<footer class="bv-page mt-12 border-t border-[#e8e4de] py-8 sm:mt-16 sm:py-10">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
            <a href="{{ route('home') }}" class="bv-display inline-block text-lg font-semibold tracking-[0.18em] text-[#1c1c1c] transition hover:text-bv-accent">
                BOOKVERSE
            </a>
            <p class="text-xs text-[#9a948d]">{{ __('ui.footer.tagline') }}</p>
            <p class="text-[10px] text-[#9a948d]">© {{ date('Y') }} Bookverse. {{ __('ui.footer.rights') }}</p>
        </div>

        <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-semibold uppercase tracking-wider text-[#6b6560]" aria-label="Footer">
            <a href="{{ route('about') }}" class="transition hover:text-[#1c1c1c]">{{ __('ui.footer.about') }}</a>
            <a href="{{ route('privacy') }}" class="transition hover:text-[#1c1c1c]">{{ __('ui.footer.privacy') }}</a>
            <a href="{{ url('/api/documentation') }}" target="_blank" rel="noopener" class="transition hover:text-[#1c1c1c]">{{ __('ui.footer.api_docs') }}</a>
            <a href="https://github.com/ayse-kucuk/bookverse-app" target="_blank" rel="noopener" class="transition hover:text-[#1c1c1c]">{{ __('ui.footer.github') }}</a>
        </nav>
    </div>
</footer>
