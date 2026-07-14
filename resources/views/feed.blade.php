<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Bookverse — Akış'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="grid items-start gap-6 xl:grid-cols-[150px_minmax(0,1fr)_260px] xl:gap-8">

            @include('partials.ads-left')

            <main class="min-w-0 space-y-6">
                @if(session('success'))
                    <div class="bv-card bv-animate-up rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
                @endif

                @auth
                    <section class="bv-card bv-animate-up rounded-2xl p-6 sm:p-7">
                        <h1 class="mb-1 text-xl font-extrabold tracking-tight text-slate-800">Ne paylaşmak istersin?</h1>
                        <p class="mb-5 text-xs text-slate-400">Düşüncelerini veya sevdiğin alıntıları toplulukla paylaş.</p>
                        <form action="{{ route('posts.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="flex flex-wrap gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-rose-200/80 bg-rose-50/50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition duration-200 has-[:checked]:border-rose-400 has-[:checked]:bg-rose-100 has-[:checked]:text-rose-700">
                                    <input type="radio" name="type" value="thought" checked class="accent-rose-600"> Düşünce
                                </label>
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-rose-200/80 bg-rose-50/50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition duration-200 has-[:checked]:border-rose-400 has-[:checked]:bg-rose-100 has-[:checked]:text-rose-700">
                                    <input type="radio" name="type" value="quote" class="accent-rose-600"> Kitap alıntısı
                                </label>
                            </div>
                            <textarea name="content" rows="4" required placeholder="Okuduğun kitap hakkında düşünceni veya sevdiğin bir alıntıyı paylaş..." class="bv-input w-full resize-none rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-3 text-sm transition">{{ old('content') }}</textarea>
                            @error('content')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror

                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-500">İlgili kitap (alıntı için zorunlu)</label>
                                <select name="book_id" class="bv-input w-full rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-2.5 text-sm transition">
                                    <option value="">Kitap seç...</option>
                                    @foreach($books as $book)
                                        <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }} — {{ $book->author }}</option>
                                    @endforeach
                                </select>
                                @error('book_id')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit" class="bv-btn rounded-full px-6 py-2.5 text-sm font-bold text-white">Paylaş</button>
                        </form>
                    </section>
                @else
                    <section class="bv-card bv-animate-up rounded-2xl border-dashed p-8 text-center">
                        <p class="text-sm text-slate-500">Paylaşımları görmek ve kendi düşüncelerini yazmak için <a href="{{ route('login') }}" class="font-bold text-rose-600 underline decoration-rose-300 underline-offset-2">giriş yap</a>.</p>
                    </section>
                @endauth

                <section class="space-y-4">
                    <h2 class="bv-animate-up-delay-1 text-sm font-extrabold uppercase tracking-widest text-slate-400">Akış</h2>
                    <div class="bv-stagger space-y-4">
                        @forelse($posts as $post)
                            @include('partials.post-card', ['post' => $post])
                        @empty
                            <div class="bv-card rounded-2xl p-10 text-center text-sm text-slate-400">
                                @auth
                                    Henüz paylaşım yok. İlk paylaşımı sen yap veya <a href="{{ route('explore') }}" class="font-bold text-rose-600 underline decoration-rose-300 underline-offset-2">Keşfet</a> sayfasından kitaplara göz at.
                                @else
                                    Henüz paylaşım yok. <a href="{{ route('register') }}" class="font-bold text-rose-600 underline decoration-rose-300 underline-offset-2">Kayıt ol</a> ve akışa katıl.
                                @endauth
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="pt-2">{{ $posts->links() }}</div>
            </main>

            <aside class="bv-animate-up-delay-2 space-y-4 lg:sticky lg:top-24">
                <section class="bv-card rounded-2xl p-5">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-extrabold text-slate-800">Kitapları Keşfet</h2>
                        <a href="{{ route('explore') }}" class="text-[10px] font-bold text-rose-600 transition hover:text-rose-700">Tümü →</a>
                    </div>

                    @if($exploreBooks->isEmpty())
                        <p class="text-xs italic text-slate-400">Henüz kitap yok.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($exploreBooks as $book)
                                <a href="{{ route('books.show', $book) }}" class="group flex items-center gap-3 rounded-xl p-2 transition duration-200 hover:bg-rose-50/80">
                                    <div class="h-14 w-10 shrink-0 overflow-hidden rounded-lg bg-slate-800 shadow-sm ring-1 ring-slate-900/10">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                                        @else
                                            <div class="flex h-full items-center justify-center text-xs text-white">📖</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="line-clamp-2 text-xs font-bold text-slate-700 transition group-hover:text-rose-600">{{ $book->title }}</p>
                                        <p class="truncate text-[10px] text-slate-400">{{ $book->author }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <a href="{{ route('explore') }}" class="mt-4 block w-full rounded-xl border border-dashed border-rose-200/80 bg-rose-50/50 py-2.5 text-center text-xs font-bold text-rose-600 transition duration-200 hover:bg-rose-50">
                            Daha fazla kitap gör
                        </a>
                    @endif
                </section>

                @include('partials.ads-right')
            </aside>

        </div>
    </div>
</body>
</html>
