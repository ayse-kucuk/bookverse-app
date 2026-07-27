<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', [
        'title' => __('ui.pages.about_title') . ' — Bookverse',
        'description' => __('ui.seo.about_description'),
        'canonical' => route('about'),
    ])
</head>
<body class="bv-mesh flex min-h-screen flex-col antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page flex-1 py-8 sm:py-12">
        <div class="mx-auto max-w-2xl bv-animate-up">
            <span class="bv-badge mb-4">📖 Bookverse</span>
            <h1 class="bv-hero-title text-4xl sm:text-5xl">{{ __('ui.pages.about_title') }}</h1>
            <p class="mt-5 text-sm leading-relaxed text-[#6b6560]">{{ __('ui.pages.about_lead') }}</p>

            <section class="bv-card mt-8 rounded-2xl p-6 sm:p-8">
                <h2 class="bv-display text-2xl font-medium text-[#1c1c1c]">{{ __('ui.pages.about_features_title') }}</h2>
                <ul class="mt-4 space-y-3 text-sm leading-relaxed text-[#6b6560]">
                    @foreach(__('ui.pages.about_features') as $feature)
                        <li class="flex gap-2">
                            <span class="text-bv-accent" aria-hidden="true">✦</span>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="mt-6 rounded-2xl border border-[#e8e4de] bg-[#f9f8f6] p-6 sm:p-8">
                <h2 class="text-[10px] font-bold uppercase tracking-wider text-[#9a948d]">{{ __('ui.pages.about_tech_title') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-[#6b6560]">{{ __('ui.pages.about_tech') }}</p>
            </section>
        </div>
    </main>

    @include('partials.site-footer')
</body>
</html>
