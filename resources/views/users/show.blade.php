<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => $profileUser->name . ' — Bookverse'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page space-y-5 py-6 sm:py-10">

        <div class="grid gap-5 lg:grid-cols-2 lg:items-start">
        {{-- Profil kartı --}}
        <section class="bv-card bv-animate-up flex h-full w-full flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-5 sm:p-6">
            <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-center sm:gap-6 sm:text-left">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-[#e8e4de] sm:h-32 sm:w-32">
                    @if($profileUser->profile_photo_path)
                        <img src="{{ $profileUser->profilePhotoUrl() }}" alt="{{ $profileUser->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="flex h-full w-full items-center justify-center bg-[#f3f0eb] text-4xl sm:text-5xl">📖</span>
                    @endif
                </div>
                <div>
                    <h1 class="bv-display text-2xl font-medium text-[#1c1c1c] sm:text-3xl">{{ $profileUser->name }}</h1>
                    @include('partials.profile-stats', ['profileUser' => $profileUser])
                </div>
            </div>

            @auth
                @if($viewer->id !== $profileUser->id)
                    @if($isFollowing)
                        <form action="{{ route('users.unfollow', $profileUser) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full border border-[#e8e4de] bg-[#f9f8f6] px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-[#6b6560] transition hover:border-[#c4a574] hover:text-[#1c1c1c] sm:w-auto">
                                Takipten çık
                            </button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $profileUser) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="bv-btn w-full px-5 py-2.5 text-xs font-bold uppercase tracking-wider sm:w-auto">
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
        </div>

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
