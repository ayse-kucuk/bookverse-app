@php
    $isOwnProfile = $isOwnProfile ?? true;
    $shelves = [
        'reading' => [
            'label' => 'Okuyorum',
            'books' => $reading,
            'empty' => $isOwnProfile ? 'Şu an aktif olarak okunan kitap yok.' : 'Şu an okuduğu kitap yok.',
            'fallback' => '📖',
        ],
        'will-read' => [
            'label' => 'Okuyacağım',
            'books' => $willRead,
            'empty' => $isOwnProfile ? 'Kütüphaneye henüz gelecek kitap eklenmemiş.' : 'Okuma listesinde kitap yok.',
            'fallback' => '📌',
        ],
        'read' => [
            'label' => 'Okundu',
            'books' => $read,
            'empty' => $isOwnProfile ? 'Henüz biten kitap yok.' : 'Henüz bitirdiği kitap yok.',
            'fallback' => '✓',
        ],
    ];
    $previewLimit = 4;
@endphp

<div class="bv-stagger w-full space-y-4">
    @foreach($shelves as $key => $shelf)
        <section class="bv-card w-full p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Raf</p>
                    <h2 class="bv-display mt-0.5 text-xl font-medium text-[#1c1c1c]">{{ $shelf['label'] }}</h2>
                </div>
                @if($shelf['books']->isNotEmpty())
                    <button type="button" onclick="openShelfPanel('{{ $key }}')" class="text-[10px] font-bold uppercase tracking-wider text-bv-accent transition hover:opacity-70">
                        Tümü ({{ $shelf['books']->count() }}) →
                    </button>
                @endif
            </div>

            @if($shelf['books']->isEmpty())
                <p class="border border-dashed border-[#e8e4de] bg-[#f9f8f6] px-4 py-4 text-center text-xs text-[#9a948d]">{{ $shelf['empty'] }}</p>
            @else
                <div class="grid grid-cols-4 gap-3 sm:gap-4">
                    @foreach($shelf['books']->take($previewLimit) as $book)
                        <a href="{{ route('books.show', $book) }}" class="group block" title="{{ $book->title }}">
                            <div class="aspect-[2/3] w-full overflow-hidden border border-[#e8e4de] bg-[#1c1c1c] transition duration-300 group-hover:border-[#c4a574]">
                                @if($book->image_url)
                                    <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.04]">
                                @else
                                    <div class="flex h-full items-center justify-center text-lg text-white">{{ $shelf['fallback'] }}</div>
                                @endif
                            </div>
                            <p class="mt-1.5 line-clamp-2 text-[11px] font-medium text-[#6b6560] transition group-hover:text-bv-accent">{{ $book->title }}</p>
                        </a>
                    @endforeach
                </div>

                @if($shelf['books']->count() > $previewLimit)
                    <button type="button" onclick="openShelfPanel('{{ $key }}')" class="mt-4 w-full border border-dashed border-[#e8e4de] bg-[#f9f8f6] py-2.5 text-[10px] font-bold uppercase tracking-wider text-bv-accent transition hover:bg-[#f3f0eb]">
                        +{{ $shelf['books']->count() - $previewLimit }} kitap daha
                    </button>
                @endif
            @endif
        </section>
    @endforeach
</div>

@foreach($shelves as $key => $shelf)
    <div id="shelf-panel-{{ $key }}" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="shelf-title-{{ $key }}">
        <div class="absolute inset-0 bg-[#1c1c1c]/60 backdrop-blur-sm" onclick="closeShelfPanel()"></div>
        <div class="absolute inset-x-4 top-20 bottom-6 mx-auto flex max-w-2xl flex-col overflow-hidden border border-[#e8e4de] bg-white shadow-2xl sm:inset-x-8">
            <div class="flex shrink-0 items-center justify-between border-b border-[#e8e4de] bg-white px-5 py-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Raf</p>
                    <h2 id="shelf-title-{{ $key }}" class="bv-display text-xl font-medium text-[#1c1c1c]">{{ $shelf['label'] }}</h2>
                </div>
                <button type="button" onclick="closeShelfPanel()" class="border border-[#e8e4de] px-3 py-1.5 text-xs font-bold text-[#6b6560] transition hover:text-bv-accent">Kapat ✕</button>
            </div>
            <div class="flex-1 overflow-y-auto px-5 py-5">
                @if($shelf['books']->isEmpty())
                    <p class="py-10 text-center text-sm italic text-[#9a948d]">{{ $shelf['empty'] }}</p>
                @else
                    <div class="grid grid-cols-3 gap-4 pb-4 sm:grid-cols-4">
                        @foreach($shelf['books'] as $book)
                            <a href="{{ route('books.show', $book) }}" class="group block space-y-1.5">
                                <div class="aspect-[2/3] w-full overflow-hidden border border-[#e8e4de] bg-[#1c1c1c] transition duration-300 group-hover:border-[#c4a574]">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                                    @else
                                        <div class="flex h-full flex-col items-center justify-center p-2 text-center text-white">
                                            <span class="mb-1 block text-2xl">{{ $shelf['fallback'] }}</span>
                                            <p class="line-clamp-2 px-1 text-[10px] font-bold">{{ $book->title }}</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="line-clamp-2 text-xs font-medium text-[#6b6560] transition group-hover:text-bv-accent">{{ $book->title }}</p>
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

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeShelfPanel(); });
</script>
