<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', [
        'title' => __('ui.pages.privacy_title') . ' — Bookverse',
        'description' => __('ui.seo.privacy_description'),
        'canonical' => route('privacy'),
        'robots' => 'noindex,follow',
    ])
</head>
<body class="bv-mesh flex min-h-screen flex-col antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page flex-1 py-8 sm:py-12">
        <div class="mx-auto max-w-2xl bv-animate-up">
            <p class="text-[10px] font-bold uppercase tracking-wider text-[#9a948d]">
                {{ __('ui.pages.privacy_updated', ['date' => '2026']) }}
            </p>
            <h1 class="bv-hero-title mt-2 text-4xl sm:text-5xl">{{ __('ui.pages.privacy_title') }}</h1>
            <p class="mt-5 text-sm leading-relaxed text-[#6b6560]">{{ __('ui.pages.privacy_intro') }}</p>

            <div class="mt-8 space-y-5">
                @foreach(__('ui.pages.privacy_sections') as $section)
                    <section class="bv-card rounded-2xl p-6">
                        <h2 class="font-semibold text-[#1c1c1c]">{{ $section['title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-[#6b6560]">{{ $section['body'] }}</p>
                    </section>
                @endforeach
            </div>
        </div>
    </main>

    @include('partials.site-footer')
</body>
</html>
