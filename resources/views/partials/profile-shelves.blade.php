@php
    $isOwnProfile = $isOwnProfile ?? true;
    $shelves = [
        'reading' => [
            'title' => '📖 Okuyorum',
            'color' => 'text-amber-700',
            'countColor' => 'text-amber-600',
            'books' => $reading,
            'empty' => $isOwnProfile ? 'Şu an aktif olarak okunan kitap yok.' : 'Şu an okuduğu kitap yok.',
            'fallback' => '📖',
        ],
        'will-read' => [
            'title' => '📌 Okuyacağım',
            'color' => 'text-rose-600',
            'countColor' => 'text-rose-600',
            'books' => $willRead,
            'empty' => $isOwnProfile ? 'Kütüphaneye henüz gelecek kitap eklenmemiş.' : 'Okuma listesinde kitap yok.',
            'fallback' => '📌',
        ],
        'read' => [
            'title' => '✅ Okundu',
            'color' => 'text-emerald-600',
            'countColor' => 'text-emerald-600',
            'books' => $read,
            'empty' => $isOwnProfile ? 'Henüz biten kitap yok.' : 'Henüz bitirdiği kitap yok.',
            'fallback' => '✅',
        ],
    ];
    $previewLimit = 4;
@endphp

<div class="bv-stagger w-full space-y-4">
    @foreach($shelves as $key => $shelf)
        <section class="bv-card w-full rounded-2xl p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-base font-extrabold {{ $shelf['color'] }}">{{ $shelf['title'] }}</h2>
                @if($shelf['books']->isNotEmpty())
                    <button type="button" onclick="openShelfPanel('{{ $key }}')" class="text-xs font-bold {{ $shelf['countColor'] }} transition hover:opacity-70">
                        Tümü ({{ $shelf['books']->count() }}) →
                    </button>
                @endif
            </div>

            @if($shelf['books']->isEmpty())
                <p class="py-1 text-xs font-medium italic text-slate-400">{{ $shelf['empty'] }}</p>
            @else
                <div class="grid grid-cols-4 gap-3 sm:gap-4">
                    @foreach($shelf['books']->take($previewLimit) as $book)
                        <a href="{{ route('books.show', $book) }}" class="group block" title="{{ $book->title }}">
                            <div class="aspect-[2/3] w-full overflow-hidden rounded-xl bg-slate-800 shadow-sm ring-1 ring-slate-900/10 transition duration-300 group-hover:scale-[1.03] group-hover:shadow-lg group-hover:shadow-rose-500/10">
                                @if($book->image_url)
                                    <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-lg text-white">{{ $shelf['fallback'] }}</div>
                                @endif
                            </div>
                            <p class="mt-1.5 line-clamp-2 text-[11px] font-bold text-slate-600 transition group-hover:text-rose-600">{{ $book->title }}</p>
                        </a>
                    @endforeach
                </div>

                @if($shelf['books']->count() > $previewLimit)
                    <button type="button" onclick="openShelfPanel('{{ $key }}')" class="mt-3 w-full rounded-xl border border-dashed border-rose-200/80 bg-rose-50/50 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50">
                        +{{ $shelf['books']->count() - $previewLimit }} daha
                    </button>
                @endif
            @endif
        </section>
    @endforeach
</div>

@foreach($shelves as $key => $shelf)
    <div id="shelf-panel-{{ $key }}" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="shelf-title-{{ $key }}">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeShelfPanel()"></div>
        <div class="absolute inset-x-4 top-20 bottom-6 mx-auto flex max-w-2xl flex-col overflow-hidden rounded-2xl bv-card shadow-2xl sm:inset-x-8">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-white/90 px-5 py-4">
                <h2 id="shelf-title-{{ $key }}" class="text-lg font-extrabold {{ $shelf['color'] }}">{{ $shelf['title'] }}</h2>
                <button type="button" onclick="closeShelfPanel()" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 transition hover:bg-rose-50 hover:text-rose-700">Kapat ✕</button>
            </div>
            <div class="flex-1 overflow-y-auto px-5 py-5">
                @if($shelf['books']->isEmpty())
                    <p class="py-10 text-center text-sm font-medium italic text-slate-400">{{ $shelf['empty'] }}</p>
                @else
                    <div class="grid grid-cols-3 gap-4 pb-4 sm:grid-cols-4">
                        @foreach($shelf['books'] as $book)
                            <a href="{{ route('books.show', $book) }}" class="group block space-y-1.5 text-center">
                                <div class="aspect-[2/3] w-full overflow-hidden rounded-xl bg-slate-800 shadow-sm ring-1 ring-slate-900/10 transition duration-300 group-hover:scale-[1.02] group-hover:shadow-lg">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full flex-col items-center justify-center p-2 text-center text-white">
                                            <span class="mb-1 block text-2xl">{{ $shelf['fallback'] }}</span>
                                            <p class="line-clamp-2 px-1 text-[10px] font-bold">{{ $book->title }}</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="line-clamp-2 text-xs font-bold text-slate-600 transition group-hover:text-rose-600">{{ $book->title }}</p>
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
    if (typeof closePhotoLightbox === 'function') closePhotoLightbox();
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
    if (!document.querySelector('.bv-photo-lightbox.flex') && !document.querySelector('[id^="follow-panel-"]:not(.hidden)')) {
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeShelfPanel();
});
</script>
