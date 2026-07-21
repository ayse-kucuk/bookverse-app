<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => ($title ?? 'Admin') . ' — Bookverse'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">
    @include('partials.site-nav')

    <div class="bv-page flex flex-col gap-6 py-8 lg:flex-row">
        <aside class="w-full shrink-0 lg:w-56">
            <div class="bv-card sticky top-24 rounded-2xl p-3">
                <p class="mb-2 px-3 pt-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Yönetim</p>
                <nav class="space-y-0.5 text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard') ? 'bv-nav-active' : 'text-slate-600 hover:bg-\[#f3f0eb\] hover:text-bv-accent' }}">
                        Genel bakış
                    </a>
                    <a href="{{ route('admin.books.index') }}" class="block rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.books.*') ? 'bv-nav-active' : 'text-slate-600 hover:bg-\[#f3f0eb\] hover:text-bv-accent' }}">
                        Kitaplar
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="block rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.categories.*') ? 'bv-nav-active' : 'text-slate-600 hover:bg-\[#f3f0eb\] hover:text-bv-accent' }}">
                        Kategoriler
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bv-nav-active' : 'text-slate-600 hover:bg-\[#f3f0eb\] hover:text-bv-accent' }}">
                        Kullanıcılar
                    </a>
                    <a href="{{ url('/api/documentation') }}" target="_blank" rel="noopener" class="block rounded-xl px-3 py-2.5 text-slate-600 transition hover:bg-\[#f3f0eb\] hover:text-bv-accent">
                        API Docs ↗
                    </a>
                </nav>
                <div class="mt-3 border-t border-slate-100 px-3 pt-3">
                    <a href="{{ route('home') }}" class="text-xs font-bold text-slate-400 transition hover:text-bv-accent">← Siteye dön</a>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1 space-y-5">
            @if($errors->any())
                <div class="bv-card rounded-2xl border border-red-200/60 bg-red-50/80 px-4 py-3 text-sm font-semibold text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
