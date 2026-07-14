<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => $profileUser->name . ' — Profil'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <main class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6">
        @if(session('success'))
            <div class="bv-card bv-animate-up rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bv-card bv-animate-up rounded-2xl border border-red-200/60 bg-red-50/80 px-4 py-3 text-sm font-semibold text-red-700">{{ session('error') }}</div>
        @endif

        <section class="bv-card bv-animate-up flex items-center justify-between gap-4 rounded-2xl p-6">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-rose-100 to-orange-100 text-3xl ring-2 ring-rose-200/60">
                    @if($profileUser->profile_photo_path)
                        <img src="{{ asset('storage/' . $profileUser->profile_photo_path) }}" alt="{{ $profileUser->name }}" class="h-full w-full object-cover">
                    @else
                        👤
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-800">{{ $profileUser->name }}</h1>
                    @include('partials.profile-stats', ['profileUser' => $profileUser])
                </div>
            </div>

            @auth
                @if($viewer->id !== $profileUser->id)
                    @if($isFollowing)
                        <form action="{{ route('users.unfollow', $profileUser) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-bold text-slate-500 transition duration-200 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">Takipten çık</button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $profileUser) }}" method="POST">
                            @csrf
                            <button type="submit" class="bv-btn rounded-full px-5 py-2 text-sm font-bold text-white">Takip et</button>
                        </form>
                    @endif
                @endif
            @endauth
        </section>

        @include('partials.profile-shelves', [
            'reading' => $reading,
            'willRead' => $willRead,
            'read' => $read,
            'isOwnProfile' => auth()->id() === $profileUser->id,
        ])

        <section class="space-y-4">
            <h2 class="bv-animate-up-delay-1 text-sm font-extrabold uppercase tracking-widest text-slate-400">Paylaşımlar</h2>
            <div class="bv-stagger space-y-4">
                @forelse($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @empty
                    <div class="bv-card rounded-2xl p-6 text-center text-sm text-slate-400">Henüz paylaşım yok.</div>
                @endforelse
            </div>
            <div>{{ $posts->links() }}</div>
        </section>
    </main>

    @include('partials.follow-lists', [
        'followers' => $followers,
        'following' => $following,
        'isOwnProfile' => auth()->id() === $profileUser->id,
    ])
</body>
</html>
