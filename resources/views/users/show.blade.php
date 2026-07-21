<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => $profileUser->name . ' — Bookverse'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page space-y-6 py-10">

        {{-- Profil kartı --}}
        <section class="bv-card bv-animate-up flex items-center justify-between gap-5 p-7">
            <div class="flex items-center gap-5">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de]">
                    @if($profileUser->profile_photo_path)
                        <img src="{{ asset('storage/' . $profileUser->profile_photo_path) }}" alt="{{ $profileUser->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="flex h-full w-full items-center justify-center bg-[#f3f0eb] text-4xl">📖</span>
                    @endif
                </div>
                <div>
                    <h1 class="bv-display text-3xl font-medium text-[#1c1c1c]">{{ $profileUser->name }}</h1>
                    @include('partials.profile-stats', ['profileUser' => $profileUser])
                </div>
            </div>

            @auth
                @if($viewer->id !== $profileUser->id)
                    @if($isFollowing)
                        <form action="{{ route('users.unfollow', $profileUser) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="border border-[#e8e4de] bg-[#f9f8f6] px-5 py-2 text-xs font-bold uppercase tracking-wider text-[#6b6560] transition hover:border-[#c4a574] hover:text-[#1c1c1c]">
                                Takipten çık
                            </button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $profileUser) }}" method="POST">
                            @csrf
                            <button type="submit" class="bv-btn px-5 py-2 text-xs font-bold uppercase tracking-wider">
                                Takip et
                            </button>
                        </form>
                    @endif
                @endif
            @endauth
        </section>

        @if($profileUser->hasActiveReadingGoal())
            @include('partials.reading-goal', ['profileUser' => $profileUser, 'isOwnProfile' => false])
        @endif

        @include('partials.profile-shelves', [
            'reading' => $reading,
            'willRead' => $willRead,
            'read' => $read,
            'isOwnProfile' => auth()->id() === $profileUser->id,
        ])

        <section class="space-y-4">
            <h2 class="bv-animate-up-delay-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Paylaşımlar</h2>
            <div class="bv-stagger space-y-4">
                @forelse($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @empty
                    <div class="bv-card p-8 text-center text-sm text-[#9a948d]">Henüz paylaşım yok.</div>
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
