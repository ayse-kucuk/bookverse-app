<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Arama — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-\[#e8dfd2\]">

    @include('partials.site-nav')

    <main class="bv-page space-y-8 py-8">
        <header class="bv-animate-up">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Arama</h1>
            @if(mb_strlen($query) >= 1)
                <p class="mt-1 text-sm text-slate-500">
                    <span class="font-semibold text-bv-accent">"{{ $query }}"</span> için sonuçlar
                </p>
            @else
                <p class="mt-1 text-sm text-slate-400">Kitap, kullanıcı veya paylaşım ara.</p>
            @endif
        </header>

        @if(mb_strlen($query) < 1)
            <div class="bv-card rounded-2xl p-10 text-center text-sm text-slate-400">
                Navbar'daki arama kutusunu kullanarak kitap, kullanıcı veya paylaşım ara.
            </div>
        @else
            @php
                $total = $books->count() + $users->count() + $posts->count();
            @endphp

            @if($total === 0)
                <div class="bv-card rounded-2xl p-10 text-center text-sm text-slate-400">
                    Sonuç bulunamadı. Farklı bir kelime dene.
                </div>
            @else
                @if($books->isNotEmpty())
                    <section class="bv-animate-up-delay-1 space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">Kitaplar ({{ $books->count() }})</h2>
                        <div class="bv-stagger space-y-2">
                            @foreach($books as $book)
                                <a href="{{ route('books.show', $book) }}" class="bv-card bv-card-interactive flex items-center gap-4 rounded-2xl p-4 transition">
                                    <div class="h-16 w-11 shrink-0 overflow-hidden rounded-lg bg-slate-800 shadow-sm">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-white">📖</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-800 transition group-hover:text-bv-accent">{{ $book->title }}</p>
                                        <p class="text-xs text-slate-400">{{ $book->author }}</p>
                                        @if($book->ratings_count > 0)
                                            <p class="mt-0.5 text-[10px] font-semibold text-amber-600">★ {{ $book->formattedAverageRating() }} ({{ $book->ratings_count }})</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($users->isNotEmpty())
                    <section class="bv-animate-up-delay-2 space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">Kullanıcılar ({{ $users->count() }})</h2>
                        <div class="bv-stagger space-y-2">
                            @foreach($users as $user)
                                <a href="{{ route('users.show', $user) }}" class="bv-card bv-card-interactive flex items-center gap-3 rounded-2xl p-4 transition">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-\[#f3f0eb\] to-\[#f9f8f6\] text-lg ring-2 ring-\[#e8e4de\]">
                                        @if($user->profile_photo_path)
                                            <img src="{{ $user->profilePhotoUrl() }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            👤
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $user->isPublic() ? 'Herkese açık' : 'Takipçilere özel' }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($posts->isNotEmpty())
                    <section class="space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">Paylaşımlar ({{ $posts->count() }})</h2>
                        <div class="bv-stagger space-y-4">
                            @foreach($posts as $post)
                                @include('partials.post-card', ['post' => $post])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        @endif
    </main>

</body>
</html>
