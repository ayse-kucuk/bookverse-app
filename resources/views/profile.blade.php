<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Profilim — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <main class="mx-auto w-full max-w-3xl space-y-6 px-4 py-8 sm:px-6">

        <div class="bv-card bv-animate-up flex w-full items-center gap-4 rounded-2xl p-6">
            @if($user->profile_photo_path)
                <button type="button" onclick="openPhotoLightbox()" class="bv-photo-trigger flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl shadow-lg shadow-rose-500/25 ring-2 ring-rose-200/60" aria-label="Profil fotoğrafını büyüt">
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                </button>
            @else
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 text-3xl text-white shadow-lg shadow-rose-500/25">
                    🌸
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">{{ $user->name }}</h1>
                @include('partials.profile-stats', ['user' => $user])
            </div>
        </div>

        @include('partials.profile-shelves', [
            'reading' => $reading,
            'willRead' => $willRead,
            'read' => $read,
            'isOwnProfile' => true,
        ])

        <section class="bv-animate-up-delay-2 w-full space-y-4">
            <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">Paylaşımlarım</h2>
            <div class="bv-stagger space-y-4">
            @forelse($posts as $post)
                @include('partials.post-card', ['post' => $post])
            @empty
                <div class="bv-card rounded-2xl p-6 text-center text-sm text-slate-400">
                    Henüz paylaşım yapmadın. <a href="{{ route('home') }}" class="font-bold text-rose-600 underline decoration-rose-300 underline-offset-2">Akış</a> sayfasından ilk paylaşımını oluştur.
                </div>
            @endforelse
            </div>
            <div>{{ $posts->links() }}</div>
        </section>

    </main>

    @include('partials.follow-lists', ['followers' => $followers, 'following' => $following, 'isOwnProfile' => true])

    @if($user->profile_photo_path)
        <div id="photo-lightbox" class="bv-photo-lightbox fixed inset-0 z-[70] hidden items-center justify-center p-4 sm:p-8" role="dialog" aria-modal="true" aria-label="Profil fotoğrafı">
            <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-md" onclick="closePhotoLightbox()"></div>
            <button type="button" onclick="closePhotoLightbox()" class="absolute right-4 top-4 z-20 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-slate-600 shadow-lg transition hover:bg-rose-50 hover:text-rose-700 sm:right-8 sm:top-8">
                ✕ Kapat
            </button>
            <img
                src="{{ asset('storage/' . $user->profile_photo_path) }}"
                alt="{{ $user->name }}"
                class="bv-photo-lightbox-img relative z-10 max-h-[80vh] w-auto max-w-[min(90vw,28rem)] rounded-2xl object-contain shadow-2xl ring-4 ring-white/30 sm:max-h-[85vh] sm:max-w-lg"
            >
        </div>
    @endif

    <script>
    let photoLightboxOpen = false;

    function openPhotoLightbox() {
        const lightbox = document.getElementById('photo-lightbox');
        if (!lightbox) return;
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        photoLightboxOpen = true;
        document.body.style.overflow = 'hidden';
    }

    function closePhotoLightbox() {
        const lightbox = document.getElementById('photo-lightbox');
        if (!lightbox) return;
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        photoLightboxOpen = false;
        if (!document.querySelector('[id^="shelf-panel-"]:not(.hidden)') && !document.querySelector('[id^="follow-panel-"]:not(.hidden)')) {
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && photoLightboxOpen) closePhotoLightbox();
    });
    </script>

</body>
</html>
