@php
    $comments = $post->relationLoaded('comments')
        ? $post->comments->sortBy('created_at')
        : collect();
@endphp

<section id="comments" class="bv-card scroll-mt-24 rounded-2xl p-5">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-sm font-extrabold text-slate-800">
            Yorumlar
            <span class="font-bold text-slate-400">({{ $post->comments_count ?? $comments->count() }})</span>
        </h2>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200/60 bg-emerald-50/80 px-3 py-2 text-xs font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif

    @auth
        <form action="{{ route('posts.comments.store', $post) }}" method="POST" class="mb-5 space-y-3">
            @csrf
            <label for="post-comment-{{ $post->id }}" class="sr-only">Yorum yaz</label>
            <textarea
                id="post-comment-{{ $post->id }}"
                name="content"
                rows="3"
                required
                maxlength="1000"
                placeholder="Düşünceni yaz..."
                class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm @error('content') border-rose-400 @enderror"
            >{{ old('content') }}</textarea>
            @error('content')
                <p class="text-xs font-semibold text-bv-accent">{{ $message }}</p>
            @enderror
            <div class="flex justify-end">
                <button type="submit" class="bv-btn rounded-full px-4 py-2 text-xs font-bold text-white">Yorum yap</button>
            </div>
        </form>
    @else
        <p class="mb-5 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-3 text-center text-xs font-semibold text-slate-500">
            Yorum yapmak için <a href="{{ route('login') }}" class="text-bv-accent hover:underline">giriş yap</a>.
        </p>
    @endauth

    @if($comments->isEmpty())
        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center text-sm font-semibold text-slate-400">
            Henüz yorum yok. İlk yorumu sen yap!
        </p>
    @else
        <ul class="space-y-3">
            @foreach($comments as $comment)
                <li class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('users.show', $comment->user) }}" class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-\[#f3f0eb\] to-\[#f9f8f6\] text-sm ring-1 ring-\[#e8e4de\]">
                                @if($comment->user->profile_photo_path)
                                    <img src="{{ $comment->user->profilePhotoUrl() }}" alt="{{ $comment->user->name }}" class="h-full w-full object-cover">
                                @else
                                    👤
                                @endif
                            </a>
                            <div>
                                <a href="{{ route('users.show', $comment->user) }}" class="text-xs font-bold text-slate-800 transition hover:text-bv-accent">{{ $comment->user->name }}</a>
                                <p class="text-[10px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @auth
                            @if(auth()->id() === $comment->user_id || auth()->id() === $post->user_id)
                                <form action="{{ route('posts.comments.destroy', [$post, $comment]) }}" method="POST" onsubmit="return confirm('Yorumu silmek istiyor musun?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[10px] font-semibold text-slate-400 transition hover:text-bv-accent">Sil</button>
                                </form>
                            @endif
                        @endauth
                    </div>
                    <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $comment->content }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</section>
