<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('partials.head', ['title' => config('app.name', 'Bookverse')])
    </head>
    <body class="bv-mesh font-sans text-slate-800 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('home') }}" class="bv-animate-nav group mb-8 flex items-center gap-3">
                @include('partials.logo', ['size' => 'lg'])
                <span class="text-2xl font-extrabold tracking-tight text-slate-800">Bookverse<span class="bv-gradient-text">.</span></span>
            </a>

            <div class="bv-card bv-animate-up-delay-1 w-full max-w-md overflow-hidden rounded-2xl p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
