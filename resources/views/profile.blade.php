<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Bookverse</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] text-gray-800 font-sans antialiased selection:bg-rose-300 selection:text-gray-900">

    @include('partials.site-nav')

    @php
        $shelves = [
            'reading' => [
                'title' => '📖 Okuyorum',
                'color' => 'text-amber-800',
                'countColor' => 'text-amber-700',
                'books' => $reading,
                'empty' => 'Şu an aktif olarak okunan kitap yok.',
                'fallback' => '📖',
            ],
            'will-read' => [
                'title' => '📌 Okuyacağım',
                'color' => 'text-[#DB2777]',
                'countColor' => 'text-[#DB2777]',
                'books' => $willRead,
                'empty' => 'Kütüphaneye henüz gelecek kitap eklenmemiş.',
                'fallback' => '📌',
            ],
            'read' => [
                'title' => '✅ Okundu',
                'color' => 'text-emerald-700',
                'countColor' => 'text-emerald-700',
                'books' => $read,
                'empty' => 'Henüz biten kitap yok. 💪',
                'fallback' => '✅',
            ],
        ];
        $previewLimit = 4;
    @endphp

    <main class="max-w-3xl mx-auto px-6 py-8 space-y-6 w-full">

        <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-xs flex items-center gap-4 w-full">
            <div class="w-16 h-16 bg-[#DB2777] rounded-xl flex items-center justify-center text-3xl text-white shadow-xs overflow-hidden shrink-0">
                @if($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                @else
                    🌸
                @endif
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $user->name }}</h1>
                <p class="text-xs font-semibold text-gray-400 mt-0.5">Kitap Kurdu Profil Sayfası ✨</p>
            </div>
        </div>

        <div class="space-y-4 w-full">
            @foreach($shelves as $key => $shelf)
                <section class="rounded-2xl border border-rose-100 bg-white p-5 shadow-xs w-full">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black {{ $shelf['color'] }}">{{ $shelf['title'] }}</h2>
                        @if($shelf['books']->isNotEmpty())
                            <button type="button" onclick="openShelfPanel('{{ $key }}')" class="text-xs font-semibold {{ $shelf['countColor'] }} transition hover:opacity-80">
                                Tümü ({{ $shelf['books']->count() }}) →
                            </button>
                        @endif
                    </div>

                    @if($shelf['books']->isEmpty())
                        <p class="text-xs font-medium text-gray-400 italic py-1">{{ $shelf['empty'] }}</p>
                    @else
                        <div class="grid grid-cols-4 gap-4">
                            @foreach($shelf['books']->take($previewLimit) as $book)
                                <a href="/books/{{ $book->id }}" class="group block" title="{{ $book->title }}">
                                    <div class="w-full aspect-[2/3] bg-amber-900 rounded-lg shadow-xs overflow-hidden border border-gray-100 transition duration-300 group-hover:scale-[1.03] group-hover:shadow-md">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-lg text-white">
                                                {{ $shelf['fallback'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mt-1.5 text-xs font-bold text-gray-700 line-clamp-2 group-hover:text-[#DB2777] transition">{{ $book->title }}</p>
                                </a>
                            @endforeach
                        </div>

                        @if($shelf['books']->count() > $previewLimit)
                            <button type="button" onclick="openShelfPanel('{{ $key }}')" class="mt-3 w-full rounded-lg border border-dashed border-rose-200 bg-rose-50/50 py-2 text-xs font-semibold text-[#DB2777] transition hover:bg-rose-50">
                                +{{ $shelf['books']->count() - $previewLimit }} daha
                            </button>
                        @endif
                    @endif
                </section>
            @endforeach
        </div>

        <section class="space-y-4 w-full">
            <h2 class="text-lg font-black text-gray-800">Paylaşımlarım</h2>
            @forelse($posts as $post)
                @include('partials.post-card', ['post' => $post])
            @empty
                <div class="rounded-2xl border border-rose-100 bg-white p-6 text-center text-sm text-gray-500">
                    Henüz paylaşım yapmadın. <a href="{{ route('home') }}" class="font-semibold text-[#DB2777] underline">Akış</a> sayfasından ilk paylaşımını oluştur.
                </div>
            @endforelse
            <div>{{ $posts->links() }}</div>
        </section>

    </main>

    @foreach($shelves as $key => $shelf)
        <div id="shelf-panel-{{ $key }}" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="shelf-title-{{ $key }}">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeShelfPanel()"></div>

            <div class="absolute inset-x-4 top-20 bottom-6 mx-auto flex max-w-2xl flex-col rounded-2xl border border-rose-100 bg-[#FCE7F3] shadow-2xl sm:inset-x-8">
                <div class="flex shrink-0 items-center justify-between border-b border-rose-100 bg-white px-5 py-4 rounded-t-2xl">
                    <h2 id="shelf-title-{{ $key }}" class="text-lg font-black {{ $shelf['color'] }}">{{ $shelf['title'] }}</h2>
                    <button type="button" onclick="closeShelfPanel()" class="rounded-full border border-rose-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-rose-50">
                        Kapat ✕
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    @if($shelf['books']->isEmpty())
                        <p class="text-center text-sm font-medium text-gray-400 italic py-10">{{ $shelf['empty'] }}</p>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4 pb-4">
                            @foreach($shelf['books'] as $book)
                                <a href="/books/{{ $book->id }}" class="group block space-y-1.5 text-center">
                                    <div class="w-full aspect-[2/3] bg-amber-900 rounded-xl shadow-xs overflow-hidden border border-gray-100 transition duration-300 group-hover:scale-[1.02] group-hover:shadow-md">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="flex h-full flex-col items-center justify-center p-2 text-center text-white">
                                                <span class="text-2xl block mb-1">{{ $shelf['fallback'] }}</span>
                                                <p class="text-[10px] font-bold line-clamp-2 px-1">{{ $book->title }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-xs font-bold text-gray-700 line-clamp-2 group-hover:text-[#DB2777] transition">{{ $book->title }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <script>
    let activeShelfPanel = null;

    function openShelfPanel(key) {
        closeShelfPanel();
        const panel = document.getElementById('shelf-panel-' + key);
        if (!panel) return;

        panel.classList.remove('hidden');
        activeShelfPanel = panel;
        document.body.style.overflow = 'hidden';
    }

    function closeShelfPanel() {
        if (activeShelfPanel) {
            activeShelfPanel.classList.add('hidden');
            activeShelfPanel = null;
        }
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeShelfPanel();
        }
    });
    </script>

</body>
</html>
