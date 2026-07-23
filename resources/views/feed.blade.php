<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Bookverse — Akış'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    @guest
        <section class="bv-animate-up mx-auto max-w-6xl px-4 pb-10 pt-12 sm:px-6 sm:pt-16">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="space-y-6">
                    <span class="bv-badge">📖 Okuma Topluluğu</span>
                    <h1 class="bv-hero-title">
                        Okumanın<br>
                        <span class="bv-gradient-text">Yeni Tanımı</span>
                    </h1>
                    <p class="max-w-md text-sm leading-relaxed text-[#6b6560]">
                        Kitaplarını keşfet, raflarına ekle, düşüncelerini paylaş. Sade ve huzurlu bir okuma deneyimi.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="bv-btn inline-flex px-6 py-3 text-xs font-bold uppercase tracking-wider">Hemen Başla</a>
                        <a href="{{ route('explore') }}" class="bv-btn-outline inline-flex px-6 py-3 text-xs font-bold uppercase tracking-wider">Kitapları Keşfet</a>
                    </div>
                </div>
                <div class="relative hidden lg:flex lg:justify-center">
                    <div class="aspect-[3/4] w-full max-w-[220px] overflow-hidden border border-[#e8e4de] bg-white shadow-[0_16px_40px_-16px_rgba(28,28,28,0.12)]">
                        @if($exploreBooks->first()?->image_url)
                            <img src="{{ $exploreBooks->first()->image_url }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center bg-[#f3f0eb] text-4xl">📚</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endguest

    <div class="bv-page py-8 {{ auth()->guest() ? 'pt-0' : 'pt-4' }}">
        <div class="grid items-start gap-6 xl:grid-cols-[220px_minmax(0,1fr)_280px] xl:gap-8">

            @include('partials.ads-left')

            <main class="min-w-0 space-y-6">
                @auth
                    <section class="bv-card bv-animate-up p-6 sm:p-7">
                        <h1 class="bv-display mb-1 text-2xl font-medium text-[#1c1c1c]">Ne paylaşmak istersin?</h1>
                        <p class="mb-5 text-xs text-[#9a948d]">Düşüncelerini veya sevdiğin alıntıları toplulukla paylaş.</p>
                        <form action="{{ route('posts.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="flex flex-wrap gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-2 border border-[#e8e4de] bg-[#f9f8f6] px-3 py-1.5 text-xs font-semibold text-[#6b6560] transition has-[:checked]:border-[#a67c52] has-[:checked]:bg-[#f3f0eb] has-[:checked]:text-[#1c1c1c]">
                                    <input type="radio" name="type" value="thought" checked class="accent-[#a67c52]"> Düşünce
                                </label>
                                <label class="inline-flex cursor-pointer items-center gap-2 border border-[#e8e4de] bg-[#f9f8f6] px-3 py-1.5 text-xs font-semibold text-[#6b6560] transition has-[:checked]:border-[#a67c52] has-[:checked]:bg-[#f3f0eb] has-[:checked]:text-[#1c1c1c]">
                                    <input type="radio" name="type" value="quote" class="accent-[#a67c52]"> Kitap alıntısı
                                </label>
                            </div>
                            <textarea name="content" rows="4" required placeholder="Okuduğun kitap hakkında düşünceni veya sevdiğin bir alıntıyı paylaş..." class="bv-input w-full resize-none border border-[#e8e4de] bg-white px-4 py-3 text-sm transition">{{ old('content') }}</textarea>
                            @error('content')<p class="text-xs text-red-700">{{ $message }}</p>@enderror

                            <div>
                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]">İlgili kitap (alıntı için zorunlu)</label>
                                <select name="book_id" class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-2.5 text-sm transition">
                                    <option value="">Kitap seç...</option>
                                    @foreach($books as $book)
                                        <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }} — {{ $book->author }}</option>
                                    @endforeach
                                </select>
                                @error('book_id')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit" class="bv-btn px-6 py-2.5 text-xs font-bold uppercase tracking-wider">Paylaş</button>
                        </form>
                    </section>
                @else
                    <section class="bv-card bv-animate-up border-dashed p-8 text-center">
                        <p class="text-sm text-[#6b6560]">Paylaşımları görmek ve kendi düşüncelerini yazmak için <a href="{{ route('login') }}" class="font-semibold text-bv-accent underline decoration-[#c4a574] underline-offset-2">giriş yap</a>.</p>
                    </section>
                @endauth

                <section class="space-y-4">
                    <h2 class="bv-animate-up-delay-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Akış</h2>
                    <div class="bv-stagger space-y-4">
                        @forelse($posts as $post)
                            @include('partials.post-card', ['post' => $post])
                        @empty
                            <div class="bv-card p-10 text-center text-sm text-[#9a948d]">
                                @auth
                                    Henüz paylaşım yok. İlk paylaşımı sen yap veya <a href="{{ route('explore') }}" class="font-semibold text-bv-accent underline decoration-[#c4a574] underline-offset-2">Keşfet</a> sayfasından kitaplara göz at.
                                @else
                                    Henüz paylaşım yok. <a href="{{ route('register') }}" class="font-semibold text-bv-accent underline decoration-[#c4a574] underline-offset-2">Kayıt ol</a> ve akışa katıl.
                                @endauth
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="pt-2">{{ $posts->links() }}</div>
            </main>

            <aside class="bv-animate-up-delay-2 mx-auto w-full max-w-xs space-y-4 xl:mx-0 xl:max-w-none lg:sticky lg:top-24">
                <section class="bv-card p-4">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Kitap Önerileri</h2>
                        <a href="{{ route('explore') }}" class="text-[10px] font-bold uppercase tracking-wider text-bv-accent transition hover:opacity-80">Tümü →</a>
                    </div>

                    @include('partials.ai-recommendations-modal')

                    @if($exploreBooks->isEmpty())
                        <p class="text-xs italic text-[#9a948d]">Henüz kitap yok.</p>
                    @else
                        <div class="space-y-1">
                            @foreach($exploreBooks->take(3) as $book)
                                <a href="{{ route('books.show', $book) }}" class="group flex items-center gap-2.5 rounded-lg p-1.5 transition duration-200 hover:bg-[#f3f0eb]">
                                    <div class="h-10 w-7 shrink-0 overflow-hidden border border-[#e8e4de] bg-[#1c1c1c]">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        @else
                                            <div class="flex h-full items-center justify-center text-[10px] text-white">📖</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[11px] font-semibold text-[#2a2a2a] transition group-hover:text-bv-accent">{{ $book->title }}</p>
                                        <p class="truncate text-[10px] text-[#9a948d]">{{ $book->author }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <a href="{{ route('explore') }}" class="mt-3 block w-full border border-dashed border-[#e8e4de] bg-[#f9f8f6] py-2 text-center text-[10px] font-bold uppercase tracking-wider text-bv-accent transition duration-200 hover:bg-[#f3f0eb]">
                            Daha fazla
                        </a>
                    @endif
                </section>

                @include('partials.ads-right')
            </aside>

        </div>
    </div>
</body>
</html>
