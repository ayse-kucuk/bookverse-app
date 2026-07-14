<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => $book->title . ' — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <main class="mx-auto grid max-w-5xl gap-8 px-4 py-10 md:grid-cols-3 sm:px-6">

        @if(session('success'))
            <div class="bv-card bv-animate-up rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-700 md:col-span-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="bv-animate-up space-y-5 md:col-span-1">
            <div class="bv-card aspect-[3/4] w-full overflow-hidden rounded-2xl shadow-xl shadow-slate-900/10 transition duration-500 hover:shadow-rose-500/10">
                @if($book->image_url)
                    <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full flex-col items-center justify-center bg-gradient-to-br from-slate-700 to-slate-900 p-6 text-center text-white">
                        <span class="mb-4 text-6xl">📖</span>
                        <h3 class="text-xl font-bold tracking-tight">{{ $book->title }}</h3>
                        <p class="mt-1 text-xs font-medium text-slate-300">{{ $book->author }}</p>
                    </div>
                @endif
            </div>

            @auth
                <div class="bv-card rounded-2xl p-5">
                    <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Puanın</label>
                    @include('partials.stars-input', ['bookId' => $book->id, 'current' => $userRating])
                </div>

                <div class="bv-card rounded-2xl p-5">
                    <form action="{{ route('books.status.update', $book->id) }}" method="POST" onchange="this.submit()">
                        @csrf
                        <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Kütüphane Durumu</label>
                        <select name="status" class="bv-input w-full rounded-xl border border-slate-200/80 bg-white/60 px-3 py-2.5 text-xs font-semibold text-slate-700 transition">
                            <option value="" disabled {{ !auth()->user()->books()->where('book_id', $book->id)->exists() ? 'selected' : '' }}>Kütüphaneme Ekle</option>
                            <option value="okuyacagim" {{ (auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okuyacagim') ? 'selected' : '' }}>Okuyacağım</option>
                            <option value="okuyorum" {{ (auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okuyorum') ? 'selected' : '' }}>Okuyorum</option>
                            <option value="okundu" {{ (auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okundu') ? 'selected' : '' }}>Okundu</option>
                        </select>
                    </form>
                </div>
            @endauth
        </div>

        <div class="bv-animate-up-delay-1 space-y-6 md:col-span-2">

            <div class="bv-card rounded-2xl p-7 sm:p-8">
                <span class="inline-block rounded-full bg-rose-100/80 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-700">
                    {{ $book->category->name ?? 'Genel' }}
                </span>

                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-800">{{ $book->title }}</h1>

                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="mt-3">
                        <a href="{{ route('admin.books.edit', $book) }}" class="inline-block rounded-full bg-amber-500 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-amber-600">
                            ⚙️ Bu Kitabı Düzenle
                        </a>
                    </div>
                @endif

                <p class="mb-4 mt-2 text-sm font-semibold text-slate-400">Yazar: <span class="text-slate-700">{{ $book->author }}</span></p>

                <div id="book-rating-summary" class="mb-6 flex flex-wrap items-center gap-3" data-book-rating-summary>
                    @if($book->ratings_count > 0)
                        @include('partials.stars-display', ['value' => (float) $book->average_rating, 'size' => 'md'])
                        <span class="text-sm font-bold text-slate-700">{{ $book->formattedAverageRating() }}</span>
                        <span class="text-xs text-slate-400">({{ $book->ratings_count }} puan)</span>
                    @else
                        <span class="text-xs font-semibold text-slate-400">Henüz puanlanmamış — ilk puanı sen ver!</span>
                    @endif
                </div>

                <div class="my-6 flex flex-wrap gap-4 border-y border-slate-100 py-3 text-xs font-semibold text-slate-400">
                    <div>📄 <span class="text-slate-700">{{ $book->page_count ?? 'Belirtilmemiş' }}</span> sayfa</div>
                    <div class="hidden sm:block text-slate-200">|</div>
                    <div>📌 Durum: <span class="text-rose-600">Yayında</span></div>
                </div>

                <h3 class="mb-2 text-sm font-extrabold uppercase tracking-wider text-slate-400">Açıklama</h3>
                <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600 md:text-base">{{ $book->description }}</p>
            </div>

            <div class="bv-card bv-animate-up-delay-2 space-y-6 rounded-2xl p-7 sm:p-8">
                <h3 class="text-lg font-extrabold tracking-tight text-slate-800">💬 Okuyucu Yorumları <span class="text-slate-400">({{ $book->comments->count() }})</span></h3>

                @auth
                    <form action="{{ route('books.comment.store', $book->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Düşüncelerini Paylaş</label>
                            <textarea name="content" rows="3" required class="bv-input w-full resize-none rounded-2xl border border-slate-200/80 bg-white/60 p-4 text-sm font-medium text-slate-700 transition placeholder:text-slate-400" placeholder="Bu kitap hakkında ne düşünüyorsun, {{ Auth::user()->name }}?"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bv-btn rounded-full px-5 py-2.5 text-xs font-bold text-white">
                                Yorumu Gönder
                            </button>
                        </div>
                    </form>
                @endauth

                @guest
                    <div class="rounded-2xl border border-dashed border-rose-200/80 bg-rose-50/50 p-4 text-center text-xs font-semibold text-slate-500">
                        🔒 Yorum yapmak için <a href="{{ route('login') }}" class="font-bold text-rose-600 underline decoration-rose-300 underline-offset-2">giriş yap</a>.
                    </div>
                @endguest

                <div class="space-y-3 border-t border-slate-100 pt-4">
                    @forelse($book->comments as $comment)
                        <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                            <div class="mb-1.5 flex items-center justify-between">
                                @if($comment->user)
                                    <a href="{{ route('users.show', $comment->user) }}" class="flex items-center gap-1 text-xs font-bold text-slate-700 transition hover:text-rose-600">
                                        ✨ {{ $comment->user->name }}
                                    </a>
                                @else
                                    <span class="flex items-center gap-1 text-xs font-bold text-slate-700">✨ Anonim</span>
                                @endif
                                <span class="text-[10px] font-semibold text-slate-400">
                                    {{ $comment->created_at ? \Carbon\Carbon::parse($comment->created_at)->diffForHumans() : 'Şimdi' }}
                                </span>
                            </div>
                            <p class="whitespace-pre-line text-xs leading-relaxed text-slate-600 md:text-sm">{{ $comment->content }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 py-8 text-center">
                            <p class="text-xs font-semibold text-slate-400">Bu kitaba henüz yorum yapılmamış. İlk yorumu sen yap!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </main>

</body>
</html>
