<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookverse - Keşfet</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] min-h-screen text-gray-800 font-sans antialiased selection:bg-[#F472B6] selection:text-white">

    @include('partials.site-nav')

    <header class="max-w-4xl mx-auto pt-12 pb-8 px-4 text-center">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-3 tracking-tight">🌸 Okuma Dünyası</h1>
    </header>

    <main class="max-w-4xl mx-auto pb-24 px-4">
        @auth
            @if(Auth::user()->is_admin)
                <div class="mb-6 flex justify-end">
                    <a href="{{ route('admin.books.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-[#DB2777] px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-[#C2185B]">
                        ➕ Kitap Ekle
                    </a>
                </div>
            @endif
        @endauth

        <div class="grid md:grid-cols-2 gap-6">
            @foreach($books as $book)
                <div class="bg-white p-6 rounded-3xl shadow-xs border border-[#F472B6]/20 flex flex-col justify-between hover:shadow-md transition duration-200">
                    <div>
                        <span class="bg-[#FDF2F8] text-[#DB2777] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider border border-[#FBCFE8]">
                            {{ $book->category->name ?? 'Genel' }}
                        </span>
                        
                        <h2 class="text-2xl font-bold text-gray-800 mt-4 mb-1 tracking-tight">{{ $book->title }}</h2>
                        <p class="text-xs font-semibold text-gray-500 mb-4">Yazar: <span class="text-gray-700 font-bold">{{ $book->author }}</span></p>
                        <p class="text-gray-600 leading-relaxed text-xs md:text-sm line-clamp-3">{{ $book->description }}</p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-50 flex justify-between items-center text-xs text-gray-400">
                        <span class="bg-gray-50 text-gray-600 px-3 py-1.5 rounded-lg font-medium flex items-center gap-1">
                            💬 {{ $book->comments->count() > 0 ? $book->comments->count() . ' Yorum' : 'Yorum Yok' }}
                        </span>
                        
                        <a href="/books/{{ $book->id }}" class="bg-[#8C6239] hover:bg-[#6E4722] text-white font-bold px-4 py-2.5 rounded-xl transition shadow-xs text-center">
                            Detayları Gör →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

</body>
</html>