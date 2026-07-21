<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Profilim — Bookverse'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page space-y-6 py-10">

        {{-- Profil kartı --}}
        <div class="bv-card bv-animate-up flex w-full items-center gap-5 p-7">
            @if($user->profile_photo_path)
                <button type="button" onclick="openPhotoLightbox()" class="bv-photo-trigger flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de]" aria-label="Profil fotoğrafını büyüt">
                    <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                </button>
            @else
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de] bg-[#f3f0eb] text-4xl">
                    📖
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <h1 class="bv-display text-3xl font-medium text-[#1c1c1c]">{{ $user->name }}</h1>
                @include('partials.profile-stats', ['user' => $user])
                <div class="mt-3">
                    <a href="{{ route('account.settings') }}" class="text-[10px] font-bold uppercase tracking-wider text-[#9a948d] transition hover:text-bv-accent">Hesap Ayarları →</a>
                </div>
            </div>
        </div>

        @include('partials.reading-goal', ['user' => $user, 'isOwnProfile' => true])

        @include('partials.profile-shelves', [
            'reading' => $reading,
            'willRead' => $willRead,
            'read' => $read,
            'isOwnProfile' => true,
        ])

        <section class="bv-animate-up-delay-2 w-full space-y-4">
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Paylaşımlarım</h2>
            <div class="bv-stagger space-y-4">
                @forelse($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @empty
                    <div class="bv-card p-8 text-center">
                        <p class="text-sm text-[#9a948d]">Henüz paylaşım yapmadın.</p>
                        <a href="{{ route('home') }}" class="mt-2 inline-block text-xs font-bold uppercase tracking-wider text-bv-accent hover:underline">Akışa git →</a>
                    </div>
                @endforelse
            </div>
            <div>{{ $posts->links() }}</div>
        </section>

    </main>

    @include('partials.follow-lists', ['followers' => $followers, 'following' => $following, 'isOwnProfile' => true])

    @if($user->profile_photo_path)
        <div id="photo-lightbox" class="bv-photo-lightbox fixed inset-0 z-[70] hidden items-center justify-center p-4 sm:p-8" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-[#1c1c1c]/80 backdrop-blur-md" onclick="closePhotoLightbox()"></div>
            <button type="button" onclick="closePhotoLightbox()" class="absolute right-4 top-4 z-20 border border-[#e8e4de] bg-white px-3 py-1.5 text-xs font-bold text-[#6b6560] shadow-lg transition hover:text-bv-accent sm:right-8 sm:top-8">
                ✕ Kapat
            </button>
            <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}"
                class="bv-photo-lightbox-img relative z-10 max-h-[80vh] w-auto max-w-[min(90vw,28rem)] object-contain shadow-2xl sm:max-h-[85vh] sm:max-w-lg">
        </div>
    @endif

    <script>
    let photoLightboxOpen = false;
    function openPhotoLightbox() {
        const lb = document.getElementById('photo-lightbox');
        if (!lb) return;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
        photoLightboxOpen = true;
        document.body.style.overflow = 'hidden';
    }
    function closePhotoLightbox() {
        const lb = document.getElementById('photo-lightbox');
        if (!lb) return;
        lb.classList.add('hidden');
        lb.classList.remove('flex');
        photoLightboxOpen = false;
        if (!document.querySelector('[id^="shelf-panel-"]:not(.hidden)') && !document.querySelector('[id^="follow-panel-"]:not(.hidden)')) {
            document.body.style.overflow = '';
        }
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && photoLightboxOpen) closePhotoLightbox(); });
    </script>

</body>
</html>
