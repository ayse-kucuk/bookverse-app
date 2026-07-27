<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', [
        'title' => __('ui.errors.404_title') . ' — Bookverse',
        'robots' => 'noindex,nofollow',
    ])
</head>
<body class="bv-mesh flex min-h-screen flex-col antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page flex flex-1 flex-col items-center justify-center py-16 text-center sm:py-24">
        <div class="bv-animate-up max-w-md">
            <p class="bv-display text-8xl font-medium leading-none text-[#e8e4de] sm:text-9xl">404</p>
            <h1 class="bv-hero-title mt-4 text-3xl sm:text-4xl">{{ __('ui.errors.404_title') }}</h1>
            <p class="mt-4 text-sm leading-relaxed text-[#6b6560]">{{ __('ui.errors.404_message') }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="bv-btn inline-flex px-6 py-3 text-xs font-bold uppercase tracking-wider">
                    {{ __('ui.errors.404_home') }}
                </a>
                <a href="{{ route('explore') }}" class="bv-btn-outline inline-flex px-6 py-3 text-xs font-bold uppercase tracking-wider">
                    {{ __('ui.errors.404_explore') }}
                </a>
            </div>
        </div>
    </main>

    @include('partials.site-footer')
</body>
</html>
