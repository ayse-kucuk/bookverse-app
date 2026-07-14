<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Paylaşım — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <main class="mx-auto max-w-2xl space-y-4 px-4 py-8 sm:px-6">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-400 transition hover:text-rose-600">← Geri</a>
        @include('partials.post-card', ['post' => $post])
    </main>

</body>
</html>
