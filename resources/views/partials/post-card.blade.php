<article class="bv-card bv-card-interactive group p-4 sm:p-5">
    <div class="mb-3 flex items-start justify-between gap-2 sm:mb-4 sm:gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('users.show', $post->user) }}" class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de] transition duration-200 group-hover:border-[#c4a574] sm:h-11 sm:w-11">
                @if($post->user->profile_photo_path)
                    <img src="{{ $post->user->profilePhotoUrl() }}" alt="{{ $post->user->name }}" class="h-full w-full object-cover">
                @else
                    <span class="flex h-full w-full items-center justify-center bg-[#f3f0eb] text-lg">👤</span>
                @endif
            </a>
            <div class="min-w-0">
                <a href="{{ route('users.show', $post->user) }}" class="block truncate text-sm font-semibold text-[#1c1c1c] transition hover:text-bv-accent">{{ $post->user->name }}</a>
                <p class="text-xs text-[#9a948d]">{{ $post->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <span class="shrink-0 border border-[#e8e4de] bg-[#f9f8f6] px-2 py-1 text-[10px] font-bold uppercase tracking-wider {{ $post->isQuote() ? 'text-amber-800' : 'text-bv-accent' }}">
            {{ $post->isQuote() ? 'Alıntı' : 'Düşünce' }}
        </span>
    </div>

    @if($post->book)
        <a href="{{ route('books.show', $post->book) }}" class="mb-4 flex items-center gap-3 border border-[#f0ece6] bg-[#f9f8f6] p-3 transition duration-200 hover:bg-[#f3f0eb]">
            @if($post->book->image_url)
                <img src="{{ $post->book->image_url }}" alt="" class="h-12 w-8 shrink-0 object-cover">
            @endif
            <span class="text-xs font-medium text-[#6b6560]">
                <span class="text-bv-accent">{{ $post->book->title }}</span>
                <span class="text-[#9a948d]"> — {{ $post->book->author }}</span>
            </span>
        </a>
    @endif

    <p class="whitespace-pre-line text-[15px] leading-relaxed text-[#2a2a2a]">
        <a href="{{ route('posts.show', $post) }}" class="transition hover:text-bv-accent">{{ $post->content }}</a>
    </p>

    <div class="mt-4 flex items-center justify-between border-t border-[#f0ece6] pt-3">
        <div class="flex items-center gap-3">
        @auth
            <button
                type="button"
                data-like-toggle
                data-url="{{ route('posts.like.toggle', $post) }}"
                aria-pressed="{{ $post->liked_by_viewer ? 'true' : 'false' }}"
                class="bv-like-btn flex items-center gap-1.5 px-2 py-1 text-xs font-semibold transition duration-200 {{ $post->liked_by_viewer ? 'text-bv-accent' : 'text-[#9a948d] hover:bg-[#f3f0eb] hover:text-[#1c1c1c]' }}"
            >
                <span class="bv-like-icon text-base leading-none">{{ $post->liked_by_viewer ? '❤️' : '🤍' }}</span>
                <span>Beğen</span>
                <span class="bv-like-count font-bold {{ ($post->likes_count ?? 0) > 0 ? '' : 'hidden' }}">{{ $post->likes_count ?? 0 }}</span>
            </button>
        @else
            <div class="flex items-center gap-1.5 text-xs font-semibold text-[#9a948d]">
                <span>❤️</span>
                <span>{{ $post->likes_count ?? 0 }}</span>
            </div>
        @endauth

        <a href="{{ route('posts.show', $post) }}#comments" class="flex items-center gap-1.5 px-2 py-1 text-xs font-semibold text-[#9a948d] transition duration-200 hover:bg-[#f3f0eb] hover:text-[#1c1c1c]">
            <span class="text-base leading-none">💬</span>
            <span>Yorum</span>
            <span class="font-bold {{ ($post->comments_count ?? 0) > 0 ? '' : 'hidden' }}">{{ $post->comments_count ?? 0 }}</span>
        </a>
        </div>

        @auth
            @if(auth()->id() === $post->user_id)
                <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Paylaşımı silmek istiyor musun?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-[#9a948d] transition hover:text-red-700">Sil</button>
                </form>
            @endif
        @endauth
    </div>
</article>
