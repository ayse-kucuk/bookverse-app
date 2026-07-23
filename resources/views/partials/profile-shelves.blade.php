@php
    $isOwnProfile = $isOwnProfile ?? true;
    $shelves = [
        'reading' => [
            'label' => 'Okuyorum',
            'books' => $reading,
            'empty' => $isOwnProfile ? 'Şu an okunan kitap yok.' : 'Şu an okuduğu kitap yok.',
            'fallback' => '📖',
        ],
        'will-read' => [
            'label' => 'Okuyacağım',
            'books' => $willRead,
            'empty' => $isOwnProfile ? 'Listede kitap yok.' : 'Okuma listesinde kitap yok.',
            'fallback' => '📌',
        ],
        'read' => [
            'label' => 'Okundu',
            'books' => $read,
            'empty' => $isOwnProfile ? 'Henüz biten kitap yok.' : 'Henüz bitirdiği kitap yok.',
            'fallback' => '✓',
        ],
    ];
    $previewLimit = 20;
@endphp

<section class="bv-card bv-animate-up w-full p-4 sm:p-5">
    <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Kitaplık</p>

    <div class="divide-y divide-[#f0ece6]">
        @foreach($shelves as $key => $shelf)
            <div class="py-3.5 first:pt-0 last:pb-0">
                <div class="mb-2.5 flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-[#1c1c1c]">{{ $shelf['label'] }}</h2>
                    @if($shelf['books']->isNotEmpty())
                        <button type="button" onclick="openShelfPanel('{{ $key }}')" class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-bv-accent transition hover:opacity-70">
                            Tümü ({{ $shelf['books']->count() }})
                        </button>
                    @endif
                </div>

                @if($shelf['books']->isEmpty())
                    <p class="text-[11px] italic text-[#9a948d]">{{ $shelf['empty'] }}</p>
                @else
                    <div
                        data-shelf-scroll
                        class="flex cursor-grab gap-2.5 overflow-x-auto pb-1 active:cursor-grabbing [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    >
                        @foreach($shelf['books']->take($previewLimit) as $book)
                            <a href="{{ route('books.show', $book) }}" class="group w-14 shrink-0 sm:w-16" title="{{ $book->title }}" draggable="false">
                                <div class="aspect-[2/3] w-full overflow-hidden border border-[#e8e4de] bg-[#1c1c1c] transition duration-200 group-hover:border-[#c4a574]">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="pointer-events-none h-full w-full object-cover" draggable="false">
                                    @else
                                        <div class="flex h-full items-center justify-center text-xs text-white">{{ $shelf['fallback'] }}</div>
                                    @endif
                                </div>
                                <p class="mt-1 line-clamp-2 text-[9px] leading-snug text-[#9a948d] transition group-hover:text-bv-accent sm:text-[10px]">{{ $book->title }}</p>
                            </a>
                        @endforeach
                        @if($shelf['books']->count() > $previewLimit)
                            <button type="button" onclick="openShelfPanel('{{ $key }}')" class="flex w-14 shrink-0 flex-col items-center justify-center self-start border border-dashed border-[#e8e4de] bg-[#f9f8f6] text-[9px] font-bold uppercase tracking-wide text-bv-accent transition hover:bg-[#f3f0eb] sm:w-16 sm:aspect-[2/3]">
                                +{{ $shelf['books']->count() - $previewLimit }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</section>

@foreach($shelves as $key => $shelf)
    <div id="shelf-panel-{{ $key }}" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="shelf-title-{{ $key }}">
        <div class="absolute inset-0 bg-[#1c1c1c]/60 backdrop-blur-sm" onclick="closeShelfPanel()"></div>
        <div class="absolute inset-x-3 top-16 bottom-20 mx-auto flex max-w-lg flex-col overflow-hidden border border-[#e8e4de] bg-white shadow-2xl sm:inset-x-8 sm:top-20 sm:bottom-6" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
            <div class="flex shrink-0 items-center justify-between border-b border-[#e8e4de] bg-white px-4 py-3">
                <h2 id="shelf-title-{{ $key }}" class="text-sm font-semibold text-[#1c1c1c]">{{ $shelf['label'] }}</h2>
                <button type="button" onclick="closeShelfPanel()" class="text-xs font-bold text-[#9a948d] transition hover:text-bv-accent">Kapat ✕</button>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-4">
                @if($shelf['books']->isEmpty())
                    <p class="py-8 text-center text-sm italic text-[#9a948d]">{{ $shelf['empty'] }}</p>
                @else
                    <div class="grid grid-cols-4 gap-3 pb-2 sm:grid-cols-5">
                        @foreach($shelf['books'] as $book)
                            <a href="{{ route('books.show', $book) }}" class="group block">
                                <div class="aspect-[2/3] w-full overflow-hidden border border-[#e8e4de] bg-[#1c1c1c] transition group-hover:border-[#c4a574]">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full items-center justify-center text-sm text-white">{{ $shelf['fallback'] }}</div>
                                    @endif
                                </div>
                                <p class="mt-1 line-clamp-2 text-[10px] font-medium text-[#6b6560] group-hover:text-bv-accent">{{ $book->title }}</p>
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

// Yatay sürükleyerek kaydırma
document.querySelectorAll('[data-shelf-scroll]').forEach((row) => {
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;
    let moved = false;

    row.addEventListener('mousedown', (e) => {
        isDown = true;
        moved = false;
        startX = e.pageX - row.offsetLeft;
        scrollLeft = row.scrollLeft;
    });

    row.addEventListener('mouseleave', () => { isDown = false; });
    row.addEventListener('mouseup', () => { isDown = false; });

    row.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - row.offsetLeft;
        const walk = (x - startX) * 1.2;
        if (Math.abs(walk) > 4) moved = true;
        row.scrollLeft = scrollLeft - walk;
    });

    row.addEventListener('click', (e) => {
        if (moved) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);
});
</script>
