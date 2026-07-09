<article class="rounded-2xl border border-rose-100 bg-white p-5 shadow-xs">
    <div class="mb-3 flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.show', $post->user) }}" class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-rose-200 bg-rose-50 text-lg">
                @if($post->user->profile_photo_path)
                    <img src="{{ asset('storage/' . $post->user->profile_photo_path) }}" alt="{{ $post->user->name }}" class="h-full w-full object-cover">
                @else
                    👤
                @endif
            </a>
            <div>
                <a href="{{ route('users.show', $post->user) }}" class="text-sm font-bold text-gray-800 hover:text-[#DB2777] transition">{{ $post->user->name }}</a>
                <p class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $post->isQuote() ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-[#DB2777]' }}">
            {{ $post->isQuote() ? 'Alıntı' : 'Düşünce' }}
        </span>
    </div>

    @if($post->book)
        <a href="{{ route('books.show', $post->book) }}" class="mb-3 inline-flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-rose-50 hover:text-[#DB2777]">
            📖 {{ $post->book->title }} — {{ $post->book->author }}
        </a>
    @endif

    <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $post->content }}</p>

    @auth
        @if(auth()->id() === $post->user_id)
            <form action="{{ route('posts.destroy', $post) }}" method="POST" class="mt-4" onsubmit="return confirm('Paylaşımı silmek istiyor musun?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-semibold text-gray-400 transition hover:text-rose-600">Sil</button>
            </form>
        @endif
    @endauth
</article>
