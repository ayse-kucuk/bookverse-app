<article class="bv-card bv-card-interactive group rounded-2xl p-5">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.show', $post->user) }}" class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full ring-2 ring-rose-100 transition duration-200 group-hover:ring-rose-300">
                @if($post->user->profile_photo_path)
                    <img src="{{ asset('storage/' . $post->user->profile_photo_path) }}" alt="{{ $post->user->name }}" class="h-full w-full object-cover">
                @else
                    <span class="flex h-full w-full items-center justify-center bg-gradient-to-br from-rose-100 to-orange-100 text-lg">👤</span>
                @endif
            </a>
            <div>
                <a href="{{ route('users.show', $post->user) }}" class="text-sm font-bold text-slate-800 transition hover:text-rose-700">{{ $post->user->name }}</a>
                <p class="text-xs text-slate-400">{{ $post->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $post->isQuote() ? 'bg-amber-100/80 text-amber-700' : 'bg-rose-100/80 text-rose-700' }}">
            {{ $post->isQuote() ? 'Alıntı' : 'Düşünce' }}
        </span>
    </div>

    @if($post->book)
        <a href="{{ route('books.show', $post->book) }}" class="mb-4 flex items-center gap-3 rounded-xl bg-slate-50/80 p-3 transition duration-200 hover:bg-rose-50/80">
            @if($post->book->image_url)
                <img src="{{ $post->book->image_url }}" alt="" class="h-12 w-8 shrink-0 rounded-md object-cover shadow-sm">
            @endif
            <span class="text-xs font-semibold text-slate-600">
                <span class="text-rose-600">{{ $post->book->title }}</span>
                <span class="text-slate-400"> — {{ $post->book->author }}</span>
            </span>
        </a>
    @endif

    <p class="whitespace-pre-line text-[15px] leading-relaxed text-slate-700">
        <a href="{{ route('posts.show', $post) }}" class="transition hover:text-rose-700">{{ $post->content }}</a>
    </p>

    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
        @auth
            <button
                type="button"
                data-like-toggle
                data-url="{{ route('posts.like.toggle', $post) }}"
                aria-pressed="{{ $post->liked_by_viewer ? 'true' : 'false' }}"
                class="bv-like-btn flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-semibold transition duration-200 {{ $post->liked_by_viewer ? 'text-rose-600' : 'text-slate-400 hover:bg-rose-50 hover:text-rose-600' }}"
            >
                <span class="bv-like-icon text-base leading-none">{{ $post->liked_by_viewer ? '❤️' : '🤍' }}</span>
                <span>Beğen</span>
                <span class="bv-like-count font-bold {{ ($post->likes_count ?? 0) > 0 ? '' : 'hidden' }}">{{ $post->likes_count ?? 0 }}</span>
            </button>
        @else
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                <span>❤️</span>
                <span>{{ $post->likes_count ?? 0 }}</span>
            </div>
        @endauth

        @auth
            @if(auth()->id() === $post->user_id)
                <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Paylaşımı silmek istiyor musun?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-slate-400 transition hover:text-rose-600">Sil</button>
                </form>
            @endif
        @endauth
    </div>
</article>
