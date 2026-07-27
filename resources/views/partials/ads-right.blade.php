<div class="space-y-4">
    <p class="hidden text-center text-[10px] font-bold uppercase tracking-widest text-[#9a948d] xl:block">{{ __('ui.ads.label') }}</p>

    <div class="bv-card border border-dashed border-[#e8e4de] bg-rose-50/40 p-5 text-center">
        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500">{{ __('ui.ads.sponsored') }}</p>
        <p class="mt-2 text-sm font-semibold leading-snug text-[#2a2a2a]">{{ __('ui.ads.sponsored_text') }}</p>
        <a href="{{ route('explore') }}" class="mt-3 inline-block text-xs font-bold uppercase tracking-wider text-bv-accent transition hover:opacity-80">{{ __('ui.ads.explore') }}</a>
    </div>

    <div class="bv-card border border-dashed border-[#e8e4de] p-5 text-center">
        <p class="text-[11px] font-bold uppercase tracking-wider text-[#9a948d]">{{ __('ui.ads.slot') }}</p>
        <p class="mt-2 text-sm text-[#6b6560]">{{ __('ui.ads.slot_text') }}</p>
    </div>
</div>
