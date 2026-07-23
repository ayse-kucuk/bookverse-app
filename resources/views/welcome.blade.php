<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Bookverse — Keşfet'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <header class="bv-page bv-animate-up pb-6 pt-8 text-center sm:pb-8 sm:pt-16">
        <span class="bv-badge mb-4">Kitap Koleksiyonu</span>
        <h1 class="bv-hero-title">
            <span class="bv-gradient-text">Okuma Dünyası</span>
        </h1>
        <p class="bv-animate-up-delay-1 mx-auto mt-4 max-w-md text-sm leading-relaxed text-[#6b6560]">Klasiklerden modern eserlere — keşfet, oku, paylaş.</p>
    </header>

    <main class="bv-page pb-24">
        @auth
            @if(Auth::user()->is_admin)
                <div class="bv-animate-up-delay-1 mb-6 flex justify-end">
                    <a href="{{ route('admin.dashboard') }}" class="bv-btn inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold text-white">
                        🛠️ Yönetim Paneli
                    </a>
                </div>
            @endif
        @endauth

        <form method="GET" action="{{ route('explore') }}" class="bv-card bv-animate-up-delay-1 mb-6 flex flex-col gap-3 rounded-2xl p-4 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1">
                <label for="explore-q" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Ara</label>
                <input id="explore-q" type="search" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Kitap veya yazar..." class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            </div>
            <div class="sm:w-40">
                <label for="explore-category" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Kategori</label>
                <select id="explore-category" name="category" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Tümü</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" @selected(($currentCategory ?? null) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-36">
                <label for="explore-sort" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Sırala</label>
                <select id="explore-sort" name="sort" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="latest" @selected(($currentSort ?? 'latest') === 'latest')>En yeni</option>
                    <option value="title" @selected(($currentSort ?? '') === 'title')>Başlık (A-Z)</option>
                    <option value="rating" @selected(($currentSort ?? '') === 'rating')>En yüksek puan</option>
                </select>
            </div>
            <button type="submit" class="bv-btn rounded-xl px-5 py-2 text-sm font-bold text-white">Filtrele</button>
        </form>

        <div class="bv-stagger grid gap-5 md:grid-cols-2">
            @forelse($books as $book)
                <article class="bv-card bv-card-interactive group flex overflow-hidden rounded-2xl">
                    <div class="relative w-28 shrink-0 sm:w-32">
                        @if($book->image_url)
                            <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-110">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-900 text-3xl">📖</div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent to-black/10"></div>
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col justify-between p-5">
                        <div>
                            <span class="inline-block border border-[#e8e4de] bg-[#f9f8f6] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-bv-accent">
                                {{ $book->category->name ?? 'Genel' }}
                            </span>
                            <h2 class="mt-2 text-lg font-semibold leading-tight tracking-tight text-[#1c1c1c] transition duration-200 group-hover:text-bv-accent">{{ $book->title }}</h2>
                            <p class="mt-0.5 text-xs font-semibold text-slate-400">{{ $book->author }}</p>
                            @if($book->ratings_count > 0)
                                <div class="mt-2 flex items-center gap-1.5">
                                    @include('partials.stars-display', ['value' => (float) $book->average_rating, 'size' => 'sm'])
                                    <span class="text-[10px] font-bold text-amber-600">{{ $book->formattedAverageRating() }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $book->ratings_count }})</span>
                                </div>
                            @endif
                            <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $book->description }}</p>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <span class="flex items-center gap-1 text-[10px] font-semibold text-slate-400">
                                💬 {{ $book->comments_count > 0 ? $book->comments_count . ' yorum' : 'Yorum yok' }}
                            </span>
                            <a href="{{ route('books.show', $book) }}" class="bv-btn px-4 py-2 text-[11px] font-bold uppercase tracking-wider">
                                Detayları Gör →
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bv-card col-span-full rounded-2xl p-10 text-center text-sm text-slate-400">
                    Bu filtrelere uygun kitap bulunamadı.
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $books->links() }}</div>
    </main>

</body>
</html>
