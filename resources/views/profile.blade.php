<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Bookverse</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] text-gray-800 font-sans antialiased selection:bg-rose-300 selection:text-gray-900">

    <nav class="border-b border-[#F472B6]/20 bg-white/90 backdrop-blur-md sticky top-0 z-50 shadow-xs">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 group cursor-pointer">
                <span class="text-xl transition group-hover:scale-110 duration-200">📚</span>
                <span class="text-xl font-black text-gray-800 tracking-tight group-hover:text-[#DB2777] transition duration-200">
                    Bookverse <span class="text-[#DB2777] font-medium text-base">Books</span>
                </span>
            </a>
            <div class="text-sm font-semibold">
                <a href="/" class="text-gray-600 hover:text-[#DB2777] transition">← Keşfet'e Dön</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-12 space-y-10">
        
        <div class="bg-white p-8 rounded-3xl border border-rose-100 shadow-xs flex items-center gap-4">
            <div class="w-16 h-16 bg-[#DB2777] rounded-2xl flex items-center justify-center text-3xl text-white shadow-xs">
                🌸
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $user->name }}</h1>
                <p class="text-xs font-semibold text-gray-400 mt-0.5">Kitap Kurdu Profil Sayfası ✨</p>
            </div>
        </div>

        <div class="space-y-8">
            
            <div class="bg-white p-6 rounded-3xl border border-rose-100 shadow-xs">
                <h3 class="text-base font-black text-amber-800 flex items-center gap-2 mb-6">
                    📖 Okuyorum ({{ $reading->count() }})
                </h3>
                @if($reading->isEmpty())
                    <p class="text-xs font-medium text-gray-400 italic py-2">Şu an aktif olarak okunan kitap yok.</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                        @foreach($reading as $book)
                            <a href="/books/{{ $book->id }}" class="group block relative space-y-2 text-center">
                                <div class="w-full aspect-[3/4] bg-amber-900 rounded-2xl shadow-xs overflow-hidden border border-gray-100 transition duration-300 group-hover:scale-105 group-hover:shadow-md flex flex-col items-center justify-center p-3">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center text-white">
                                            <span class="text-2xl block mb-1">📖</span>
                                            <p class="text-[9px] font-bold tracking-tight line-clamp-2 px-1">{{ $book->title }}</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-[10px] font-bold text-gray-700 truncate px-1 group-hover:text-[#DB2777] transition">{{ $book->title }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white p-6 rounded-3xl border border-rose-100 shadow-xs">
                <h3 class="text-base font-black text-[#DB2777] flex items-center gap-2 mb-6">
                    📌 Okuyacağım ({{ $willRead->count() }})
                </h3>
                @if($willRead->isEmpty())
                    <p class="text-xs font-medium text-gray-400 italic py-2">Kütüphaneye henüz gelecek kitap eklenmemiş.</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                        @foreach($willRead as $book)
                            <a href="/books/{{ $book->id }}" class="group block relative space-y-2 text-center">
                                <div class="w-full aspect-[3/4] bg-amber-900 rounded-2xl shadow-xs overflow-hidden border border-gray-100 transition duration-300 group-hover:scale-105 group-hover:shadow-md flex flex-col items-center justify-center p-3">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center text-white">
                                            <span class="text-2xl block mb-1">📌</span>
                                            <p class="text-[9px] font-bold tracking-tight line-clamp-2 px-1">{{ $book->title }}</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-[10px] font-bold text-gray-700 truncate px-1 group-hover:text-[#DB2777] transition">{{ $book->title }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white p-6 rounded-3xl border border-rose-100 shadow-xs">
                <h3 class="text-base font-black text-emerald-700 flex items-center gap-2 mb-6">
                    ✅ Okundu ({{ $read->count() }})
                </h3>
                @if($read->isEmpty())
                    <p class="text-xs font-medium text-gray-400 italic py-2">Henüz biten kitap yok. 💪</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                        @foreach($read as $book)
                            <a href="/books/{{ $book->id }}" class="group block relative space-y-2 text-center">
                                <div class="w-full aspect-[3/4] bg-amber-900 rounded-2xl shadow-xs overflow-hidden border border-gray-100 transition duration-300 group-hover:scale-105 group-hover:shadow-md flex flex-col items-center justify-center p-3">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center text-white">
                                            <span class="text-2xl block mb-1">✅</span>
                                            <p class="text-[9px] font-bold tracking-tight line-clamp-2 px-1">{{ $book->title }}</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-[10px] font-bold text-gray-700 truncate px-1 group-hover:text-[#DB2777] transition">{{ $book->title }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </main>

</body>
</html>