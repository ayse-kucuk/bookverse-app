<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookverse - Akış</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] min-h-screen text-gray-800 font-sans antialiased">

    @include('partials.site-nav')

    <div class="max-w-6xl mx-auto px-6 py-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_240px] items-start">

            <main class="space-y-6 min-w-0">
                @if(session('success'))
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
                @endif

                @auth
                    <section class="rounded-3xl border border-rose-100 bg-white p-6 shadow-xs">
                        <h1 class="text-lg font-black text-gray-800 mb-4">Ne paylaşmak istersin?</h1>
                        <form action="{{ route('posts.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="flex flex-wrap gap-3">
                                <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600">
                                    <input type="radio" name="type" value="thought" checked class="text-[#DB2777]"> Düşünce
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600">
                                    <input type="radio" name="type" value="quote" class="text-[#DB2777]"> Kitap alıntısı
                                </label>
                            </div>
                            <textarea name="content" rows="4" required placeholder="Okuduğun kitap hakkında düşünceni veya sevdiğin bir alıntıyı paylaş..." class="w-full rounded-2xl border border-rose-200 px-4 py-3 text-sm focus:border-[#DB2777] focus:outline-none focus:ring-2 focus:ring-rose-100">{{ old('content') }}</textarea>
                            @error('content')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-500">İlgili kitap (alıntı için zorunlu)</label>
                                <select name="book_id" class="w-full rounded-2xl border border-rose-200 px-4 py-2.5 text-sm focus:border-[#DB2777] focus:outline-none">
                                    <option value="">Kitap seç...</option>
                                    @foreach($books as $book)
                                        <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }} — {{ $book->author }}</option>
                                    @endforeach
                                </select>
                                @error('book_id')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit" class="rounded-2xl bg-[#DB2777] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">Paylaş</button>
                        </form>
                    </section>
                @else
                    <section class="rounded-3xl border border-dashed border-rose-200 bg-white/80 p-6 text-center">
                        <p class="text-sm text-gray-600">Paylaşımları görmek ve kendi düşüncelerini yazmak için <a href="{{ route('login') }}" class="font-semibold text-[#DB2777] underline">giriş yap</a>.</p>
                    </section>
                @endauth

                <section class="space-y-4">
                    <h2 class="text-base font-black text-gray-800">Akış</h2>
                    @forelse($posts as $post)
                        @include('partials.post-card', ['post' => $post])
                    @empty
                        <div class="rounded-2xl border border-rose-100 bg-white p-8 text-center text-sm text-gray-500">
                            Henüz paylaşım yok. İlk paylaşımı sen yap!
                        </div>
                    @endforelse
                </section>

                <div>{{ $posts->links() }}</div>
            </main>

            <aside class="lg:sticky lg:top-24 space-y-3">
                <section class="rounded-2xl border border-rose-100 bg-white p-4 shadow-xs">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-black text-gray-800">📚 Kitapları Keşfet</h2>
                        <a href="{{ route('explore') }}" class="text-[10px] font-semibold text-[#DB2777] hover:underline">Tümü →</a>
                    </div>

                    @if($exploreBooks->isEmpty())
                        <p class="text-xs text-gray-400 italic">Henüz kitap yok.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($exploreBooks as $book)
                                <a href="{{ route('books.show', $book) }}" class="group flex items-center gap-3 rounded-xl p-2 transition hover:bg-rose-50">
                                    <div class="h-14 w-10 shrink-0 overflow-hidden rounded-md border border-gray-100 bg-amber-900">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-xs text-white">📖</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-800 line-clamp-2 group-hover:text-[#DB2777] transition">{{ $book->title }}</p>
                                        <p class="text-[10px] text-gray-400 truncate">{{ $book->author }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <a href="{{ route('explore') }}" class="mt-4 block w-full rounded-xl border border-dashed border-rose-200 bg-rose-50/60 py-2 text-center text-xs font-semibold text-[#DB2777] transition hover:bg-rose-50">
                            Daha fazla kitap gör
                        </a>
                    @endif
                </section>
            </aside>

        </div>
    </div>
</body>
</html>
