@php
    $followers = $followers ?? collect();
    $following = $following ?? collect();
    $isOwnProfile = $isOwnProfile ?? true;
@endphp

@foreach(['followers' => ['title' => 'Takipçiler', 'items' => $followers], 'following' => ['title' => 'Takip Edilenler', 'items' => $following]] as $type => $panel)
    <div id="follow-panel-{{ $type }}" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="follow-title-{{ $type }}">
        <div class="absolute inset-0 bg-[#1c1c1c]/60 backdrop-blur-sm" onclick="closeFollowPanel()"></div>
        <div class="absolute inset-x-4 top-24 bottom-8 mx-auto flex max-w-md flex-col overflow-hidden border border-[#e8e4de] bg-white shadow-2xl sm:inset-x-8">
            <div class="flex shrink-0 items-center justify-between border-b border-[#e8e4de] px-5 py-4">
                <h2 id="follow-title-{{ $type }}" class="bv-display text-xl font-medium text-[#1c1c1c]">{{ $panel['title'] }}</h2>
                <button type="button" onclick="closeFollowPanel()" class="border border-[#e8e4de] px-3 py-1.5 text-xs font-bold text-[#6b6560] transition hover:text-bv-accent">Kapat ✕</button>
            </div>
            <div class="flex-1 overflow-y-auto px-3 py-3">
                @forelse($panel['items'] as $person)
                    <a href="{{ route('users.show', $person) }}" class="flex items-center gap-3 px-3 py-3 transition hover:bg-[#f3f0eb]">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de] bg-[#f3f0eb] text-sm">
                            @if($person->profile_photo_path)
                                <img src="{{ $person->profilePhotoUrl() }}" alt="" class="h-full w-full object-cover">
                            @else
                                👤
                            @endif
                        </div>
                        <span class="truncate text-sm font-medium text-[#2a2a2a]">{{ $person->name }}</span>
                    </a>
                @empty
                    @php
                        $emptyMsg = $type === 'followers'
                            ? ($isOwnProfile ? 'Henüz takipçin yok.' : 'Henüz takipçisi yok.')
                            : ($isOwnProfile ? 'Henüz kimseyi takip etmiyorsun.' : 'Henüz kimseyi takip etmiyor.');
                    @endphp
                    <p class="py-10 text-center text-sm italic text-[#9a948d]">{{ $emptyMsg }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endforeach

<script>
let activeFollowPanel = null;

function openFollowPanel(type) {
    closeFollowPanel();
    const panel = document.getElementById('follow-panel-' + type);
    if (!panel) return;
    panel.classList.remove('hidden');
    activeFollowPanel = panel;
    document.body.style.overflow = 'hidden';
}

function closeFollowPanel() {
    if (activeFollowPanel) {
        activeFollowPanel.classList.add('hidden');
        activeFollowPanel = null;
    }
    if (!document.querySelector('.bv-photo-lightbox.flex') && !document.querySelector('[id^="shelf-panel-"]:not(.hidden)')) {
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFollowPanel(); });
</script>
