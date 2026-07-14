@php
    $followers = $followers ?? collect();
    $following = $following ?? collect();
    $isOwnProfile = $isOwnProfile ?? true;
@endphp

<div id="follow-panel-followers" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="follow-title-followers">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeFollowPanel()"></div>
    <div class="absolute inset-x-4 top-24 bottom-8 mx-auto flex max-w-md flex-col overflow-hidden rounded-2xl bv-card shadow-2xl sm:inset-x-8">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 id="follow-title-followers" class="text-lg font-extrabold text-slate-800">Takipçiler</h2>
            <button type="button" onclick="closeFollowPanel()" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 transition hover:bg-rose-50 hover:text-rose-700">Kapat ✕</button>
        </div>
        <div class="flex-1 overflow-y-auto px-3 py-3">
            @forelse($followers as $follower)
                <a href="{{ route('users.show', $follower) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-rose-50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-rose-100 text-sm">
                        @if($follower->profile_photo_path)
                            <img src="{{ asset('storage/' . $follower->profile_photo_path) }}" alt="" class="h-full w-full object-cover">
                        @else
                            👤
                        @endif
                    </div>
                    <span class="truncate text-sm font-bold text-slate-800">{{ $follower->name }}</span>
                </a>
            @empty
                <p class="py-10 text-center text-sm font-medium italic text-slate-400">{{ $isOwnProfile ? 'Henüz takipçin yok.' : 'Henüz takipçisi yok.' }}</p>
            @endforelse
        </div>
    </div>
</div>

<div id="follow-panel-following" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="follow-title-following">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeFollowPanel()"></div>
    <div class="absolute inset-x-4 top-24 bottom-8 mx-auto flex max-w-md flex-col overflow-hidden rounded-2xl bv-card shadow-2xl sm:inset-x-8">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 id="follow-title-following" class="text-lg font-extrabold text-slate-800">Takip edilenler</h2>
            <button type="button" onclick="closeFollowPanel()" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 transition hover:bg-rose-50 hover:text-rose-700">Kapat ✕</button>
        </div>
        <div class="flex-1 overflow-y-auto px-3 py-3">
            @forelse($following as $followed)
                <a href="{{ route('users.show', $followed) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-rose-50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-rose-100 text-sm">
                        @if($followed->profile_photo_path)
                            <img src="{{ asset('storage/' . $followed->profile_photo_path) }}" alt="" class="h-full w-full object-cover">
                        @else
                            👤
                        @endif
                    </div>
                    <span class="truncate text-sm font-bold text-slate-800">{{ $followed->name }}</span>
                </a>
            @empty
                <p class="py-10 text-center text-sm font-medium italic text-slate-400">{{ $isOwnProfile ? 'Henüz kimseyi takip etmiyorsun.' : 'Henüz kimseyi takip etmiyor.' }}</p>
            @endforelse
        </div>
    </div>
</div>

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

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeFollowPanel();
});
</script>
