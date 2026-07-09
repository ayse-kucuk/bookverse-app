<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profileUser->name }} - Profil</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] text-gray-800 font-sans antialiased">

    @include('partials.site-nav')

    <main class="max-w-3xl mx-auto px-6 py-8 space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div>
        @endif

        <section class="rounded-3xl border border-rose-100 bg-white p-6 shadow-xs flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden border border-rose-200 bg-rose-50 flex items-center justify-center text-3xl">
                    @if($profileUser->profile_photo_path)
                        <img src="{{ asset('storage/' . $profileUser->profile_photo_path) }}" alt="{{ $profileUser->name }}" class="h-full w-full object-cover">
                    @else
                        👤
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800">{{ $profileUser->name }}</h1>
                    <p class="text-xs font-semibold text-gray-400 mt-1">
                        {{ $profileUser->isPublic() ? 'Herkese açık hesap' : 'Yalnızca takipçilere açık' }}
                        · {{ $profileUser->followers()->count() }} takipçi
                    </p>
                </div>
            </div>

            @auth
                @if($viewer->id !== $profileUser->id)
                    @if($isFollowing)
                        <form action="{{ route('users.unfollow', $profileUser) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-rose-50">Takipten çık</button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $profileUser) }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-full bg-[#DB2777] px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Takip et</button>
                        </form>
                    @endif
                @endif
            @endauth
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-black text-gray-800">Paylaşımlar</h2>
            @forelse($posts as $post)
                @include('partials.post-card', ['post' => $post])
            @empty
                <div class="rounded-2xl border border-rose-100 bg-white p-6 text-center text-sm text-gray-500">Henüz paylaşım yok.</div>
            @endforelse
            <div>{{ $posts->links() }}</div>
        </section>
    </main>
</body>
</html>
