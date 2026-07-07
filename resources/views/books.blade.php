<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} - Detay</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] text-gray-800 font-sans antialiased selection:bg-rose-300 selection:text-gray-900">

    <nav class="border-b border-[#F472B6]/20 bg-white/90 backdrop-blur-md sticky top-0 z-50 shadow-xs">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <!-- Sol Taraf: Tıklanabilir Logo -->
            <a href="/" class="flex items-center gap-2 group cursor-pointer">
                <span class="text-xl transition group-hover:scale-110 duration-200">📚</span>
                <span class="text-xl font-black text-gray-800 tracking-tight group-hover:text-[#DB2777] transition duration-200">
                    Bookverse <span class="text-[#DB2777] font-medium text-base group-hover:text-[#C2185B]">Books</span>
                </span>
            </a>
            
            <!-- Sağ Taraf: Dinamik Giriş / Profil Alanı -->
            <div class="flex items-center gap-6 text-sm font-semibold">
                @auth
                    <a href="{{ route('profile') }}" class="text-gray-700 font-medium hover:text-[#DB2777] transition">
                        Selam, <span class="text-[#DB2777] font-bold">{{ Auth::user()->name }}</span>! 🌸
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
        
        <div class="md:col-span-1 space-y-6">
            <!-- Kitap Kapağı Alanı -->
            <div class="w-full aspect-[3/4] bg-amber-900 rounded-3xl shadow-sm overflow-hidden border border-amber-950 flex flex-col items-center justify-center text-center text-white relative">
                @if($book->image_url)
                    <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <span class="text-6xl mb-4 relative z-10">📖</span>
                    <h3 class="font-bold text-xl relative z-10 px-2 tracking-tight">{{ $book->title }}</h3>
                    <p class="text-xs text-amber-200 mt-1 relative z-10 font-medium">{{ $book->author }}</p>
                @endif
            </div>

            <div class="bg-white p-5 rounded-3xl border border-rose-100 shadow-xs space-y-3">
                <form action="{{ route('books.status.update', $book->id) }}" method="POST" onchange="this.submit()">
                    @csrf
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Kütüphane Durumu</label>
                    <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-400">
                        <option value="" disabled selected>Kütüphaneme Ekle</option>
                        <option value="okuyacagim" {{ (auth()->user() && auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okuyacagim') ? 'selected' : '' }}>Okuyacağım</option>
                        <option value="okuyorum" {{ (auth()->user() && auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okuyorum') ? 'selected' : '' }}>Okuyorum</option>
                        <option value="okundu" {{ (auth()->user() && auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okundu') ? 'selected' : '' }}>Okundu</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="md:col-span-2 space-y-8">
            
            <div class="bg-white p-8 rounded-3xl border border-rose-100 shadow-xs">
                <span class="bg-[#FDF2F8] text-[#DB2777] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider border border-[#FBCFE8]">
                    {{ $book->category->name ?? 'Genel' }}
                </span>
                
                <h1 class="text-3xl font-black text-gray-800 mt-4 mb-1 tracking-tight">{{ $book->title }}</h1>
                @if(auth()->check() && auth()->user()->is_admin)
    <div class="mt-2 mb-4">
        <a href="{{ route('admin.books.edit', $book->id) }}" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-4 rounded-lg text-xs transition shadow-sm">
            ⚙️ Bu Kitabı Düzenle (Admin)
        </a>
    </div>
@endif
                <p class="text-sm font-semibold text-gray-500 mb-6">Yazar: <span class="text-gray-800">{{ $book->author }}</span></p>
                
                <div class="border-t border-b border-gray-100 py-3 flex gap-6 text-xs text-gray-500 font-semibold my-6">
                    <div>📄 Sayfa Sayısı: <span class="text-gray-800 font-extrabold">{{ $book->page_count ?? 'Belirtilmemiş' }}</span></div>
                    <div class="text-gray-200">|</div>
                    <div>📌 Durum: <span class="text-rose-600 font-extrabold">Yayında</span></div>
                </div>

                <h3 class="text-base font-bold text-gray-800 mb-3">Kitap Açıklaması</h3>
                <p class="text-gray-600 leading-relaxed text-sm md:text-base whitespace-pre-line">{{ $book->description }}</p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-rose-100 shadow-xs space-y-6">
                <h3 class="text-lg font-bold text-gray-800 tracking-tight">💬 Okuyucu Yorumları ({{ $book->comments->count() }})</h3>
                
                @auth
                    <form action="{{ route('books.comment.store', $book->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Düşüncelerini Paylaş</label>
                            <textarea name="content" rows="3" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#DB2777]/50 placeholder:text-gray-400" placeholder="Bu kitap hakkında ne düşünüyorsun, {{ Auth::user()->name }}? 🌸"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-[#DB2777] hover:bg-[#C2185B] text-white text-xs font-bold px-5 py-2.5 rounded-xl transition shadow-xs cursor-pointer">
                                Yorumu Gönder ✨
                            </button>
                        </div>
                    </form>
                @endauth

                @guest
                    <div class="bg-rose-50/50 border border-dashed border-rose-200 p-4 rounded-2xl text-center text-xs font-semibold text-gray-600">
                        🔒 Kitaba yorum yapabilmek için lütfen önce <a href="{{ route('login') }}" class="text-[#DB2777] underline">Giriş Yapın</a>.
                    </div>
                @endguest

                <div class="space-y-4 pt-4 border-t border-gray-50">
                    @forelse($book->comments as $comment)
                        <div class="bg-gray-50/60 p-4 rounded-2xl border border-gray-100/70">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-bold text-gray-800 flex items-center gap-1">
                                    ✨ {{ $comment->user_name }}
                                </span>
                                <span class="text-[10px] font-semibold text-gray-400">
                                    {{ $comment->created_at ? \Carbon\Carbon::parse($comment->created_at)->diffForHumans() : 'Şimdi' }}
                                </span>
                            </div>
                            <p class="text-gray-600 text-xs md:text-sm leading-relaxed whitespace-pre-line">
                                {{ $comment->content }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <p class="text-xs font-semibold text-gray-500">Bu kitaba henüz yorum yapılmamış. İlk yorumu sen yap!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </main>

</body>
</html>