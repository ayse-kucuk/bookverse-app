<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $book->title . ' — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-\[#e8dfd2\]">

    @include('partials.site-nav')

    <main class="bv-page grid gap-6 py-6 sm:gap-8 sm:py-10 md:grid-cols-3">

        @if(session('success'))
            <div class="bv-card bv-animate-up rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-700 md:col-span-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bv-card bv-animate-up rounded-2xl border border-rose-200/60 bg-rose-50/80 px-4 py-3 text-sm font-semibold text-rose-700 md:col-span-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="bv-animate-up order-2 space-y-4 md:order-1 md:col-span-1 md:space-y-5">
            <div class="bv-card mx-auto aspect-[3/4] w-full max-w-[220px] overflow-hidden rounded-2xl shadow-xl shadow-slate-900/10 transition duration-500 hover:shadow-rose-500/10 sm:max-w-none md:mx-0">
                @if($book->image_url)
                    <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full flex-col items-center justify-center bg-gradient-to-br from-slate-700 to-slate-900 p-6 text-center text-white">
                        <span class="mb-4 text-6xl">📖</span>
                        <h3 class="text-xl font-bold tracking-tight">{{ $book->title }}</h3>
                        <p class="mt-1 text-xs font-medium text-slate-300">{{ $book->author }}</p>
                    </div>
                @endif
            </div>

            @auth
                <div class="bv-card rounded-2xl p-5">
                    <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">{{ __('ui.book.your_rating') }}</label>
                    @include('partials.stars-input', ['bookId' => $book->id, 'current' => $userRating])
                </div>

                <div class="bv-card rounded-2xl p-5">
                    <form action="{{ route('books.status.update', $book->id) }}" method="POST" onchange="this.submit()">
                        @csrf
                        <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">{{ __('ui.book.library_status') }}</label>
                        <select name="status" class="bv-input w-full rounded-xl border border-slate-200/80 bg-white/60 px-3 py-2.5 text-xs font-semibold text-slate-700 transition">
                            <option value="" disabled {{ !auth()->user()->books()->where('book_id', $book->id)->exists() ? 'selected' : '' }}>{{ __('ui.book.add_to_library') }}</option>
                            <option value="okuyacagim" {{ (auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okuyacagim') ? 'selected' : '' }}>{{ __('ui.book.status_will_read') }}</option>
                            <option value="okuyorum" {{ (auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okuyorum') ? 'selected' : '' }}>{{ __('ui.book.status_reading') }}</option>
                            <option value="okundu" {{ (auth()->user()->books()->where('book_id', $book->id)->first()?->pivot->status == 'okundu') ? 'selected' : '' }}>{{ __('ui.book.status_read') }}</option>
                        </select>
                    </form>
                </div>
            @endauth
        </div>

        <div class="bv-animate-up-delay-1 order-1 space-y-5 md:order-2 md:col-span-2 md:space-y-6">

            <div class="bv-card rounded-2xl p-5 sm:p-7 sm:p-8">
                <span class="inline-block rounded-full bg-rose-100/80 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-bv-accent">
                    {{ $book->category->name ?? __('ui.common.general') }}
                </span>

                <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">{{ $book->title }}</h1>

                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="mt-3">
                        <a href="{{ route('admin.books.edit', $book) }}" class="inline-block rounded-full bg-amber-500 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-amber-600">
                            ⚙️ {{ __('ui.book.edit_book') }}
                        </a>
                    </div>
                @endif

                <p class="mb-4 mt-2 text-sm font-semibold text-slate-400">{{ __('ui.book.author') }}: <span class="text-slate-700">{{ $book->author }}</span></p>

                <div id="book-rating-summary" class="mb-6 flex flex-wrap items-center gap-3" data-book-rating-summary>
                    @if($book->ratings_count > 0)
                        @include('partials.stars-display', ['value' => (float) $book->average_rating, 'size' => 'md'])
                        <span class="text-sm font-bold text-slate-700">{{ $book->formattedAverageRating() }}</span>
                        <span class="text-xs text-slate-400">{{ __('ui.book.ratings_count', ['count' => $book->ratings_count]) }}</span>
                    @else
                        <span class="text-xs font-semibold text-slate-400">{{ __('ui.book.no_ratings') }}</span>
                    @endif
                </div>

                <div class="my-6 flex flex-wrap gap-4 border-y border-slate-100 py-3 text-xs font-semibold text-slate-400">
                    <div>📄 <span class="text-slate-700">{{ $book->page_count ?? __('ui.book.pages_unspecified') }}</span> {{ __('ui.book.pages') }}</div>
                    <div class="hidden sm:block text-slate-200">|</div>
                    <div>📌 {{ __('ui.book.status') }}: <span class="text-bv-accent">{{ __('ui.book.published') }}</span></div>
                </div>

                <h3 class="mb-2 text-sm font-extrabold uppercase tracking-wider text-slate-400">{{ __('ui.book.description') }}</h3>
                <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600 md:text-base">{{ $book->description }}</p>
            </div>

            <div class="bv-card bv-animate-up-delay-2 space-y-5 rounded-2xl p-5 sm:space-y-6 sm:p-7 sm:p-8">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <h3 class="text-lg font-extrabold tracking-tight text-slate-800">
                        {{ __('ui.book.reviews') }}
                        <span class="text-slate-400">({{ $book->comments->count() }})</span>
                    </h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.book.reviews_hint') }}</p>
                </div>

                @auth
                    @php
                        $reviewRating = (int) old('rating', $userReview?->rating ?? $userRating ?? 0);
                        $reviewContent = old('content', $userReview?->content ?? '');
                    @endphp

                    <form action="{{ route('books.comment.store', $book->id) }}" method="POST" class="space-y-4" data-review-form>
                        @csrf

                        <div>
                            <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                {{ $userReview ? __('ui.book.update_review') : __('ui.book.write_review') }}
                            </label>

                            <input type="hidden" name="rating" value="{{ $reviewRating }}" data-review-rating required>

                            <div class="mb-3 flex flex-wrap items-center gap-3" data-review-stars role="group" aria-label="{{ __('ui.book.review_rating_aria') }}">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button
                                        type="button"
                                        data-review-star
                                        data-value="{{ $i }}"
                                        class="text-2xl leading-none transition duration-150 {{ $reviewRating >= $i ? 'text-amber-400' : 'text-slate-200 hover:text-amber-300' }}"
                                        title="{{ __('ui.book.star_title', ['count' => $i]) }}"
                                    >★</button>
                                @endfor
                                <span class="text-xs font-semibold text-slate-500" data-review-star-label>
                                    @if ($reviewRating)
                                        {{ $reviewRating }}/5
                                    @else
                                        {{ __('ui.book.pick_rating') }}
                                    @endif
                                </span>
                            </div>

                            @error('rating')
                                <p class="mb-2 text-xs font-semibold text-bv-accent">{{ $message }}</p>
                            @enderror

                            <textarea
                                name="content"
                                rows="4"
                                required
                                minlength="10"
                                maxlength="2000"
                                class="bv-input w-full resize-none rounded-2xl border border-slate-200/80 bg-white/60 p-4 text-sm font-medium text-slate-700 transition placeholder:text-slate-400 @error('content') border-rose-400 @enderror"
                                placeholder="{{ __('ui.book.review_placeholder') }}"
                            >{{ $reviewContent }}</textarea>

                            @error('content')
                                <p class="mt-1 text-xs font-semibold text-bv-accent">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[11px] text-slate-400">
                                @if($userReview)
                                    {{ __('ui.book.already_reviewed') }}
                                @else
                                    {{ __('ui.book.one_review') }}
                                @endif
                            </p>
                            <button type="submit" class="bv-btn rounded-full px-5 py-2.5 text-xs font-bold text-white">
                                {{ $userReview ? __('ui.book.save_review') : __('ui.book.publish_review') }}
                            </button>
                        </div>
                    </form>
                @endauth

                @guest
                    <div class="rounded-2xl border border-dashed border-[#e8e4de]/80 bg-rose-50/50 p-4 text-center text-xs font-semibold text-slate-500">
                        {!! __('ui.book.login_to_review', ['login' => '<a href="'.route('login').'" class="font-bold text-bv-accent underline decoration-rose-300 underline-offset-2">'.e(__('ui.feed.login_link')).'</a>']) !!}
                    </div>
                @endguest

                <div class="space-y-3 border-t border-slate-100 pt-4">
                    @forelse($book->comments as $comment)
                        <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                            <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    @if($comment->user)
                                        <a href="{{ route('users.show', $comment->user) }}" class="text-xs font-bold text-slate-700 transition hover:text-bv-accent">
                                            {{ $comment->user->name }}
                                        </a>
                                    @else
                                        <span class="text-xs font-bold text-slate-700">{{ __('ui.book.anonymous') }}</span>
                                    @endif

                                    @if($comment->rating)
                                        <div class="mt-1">
                                            @include('partials.stars-display', ['value' => (float) $comment->rating, 'size' => 'sm'])
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-semibold text-slate-400">
                                        {{ $comment->created_at ? $comment->created_at->diffForHumans() : __('ui.book.now') }}
                                    </span>

                                    @auth
                                        @if((int) $comment->user_id === (int) auth()->id() || auth()->user()->is_admin)
                                            <form action="{{ route('books.comment.destroy', [$book->id, $comment]) }}" method="POST" onsubmit="return confirm(@json(__('ui.book.delete_confirm')))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 transition hover:text-bv-accent">{{ __('ui.book.delete') }}</button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                            <p class="whitespace-pre-line text-xs leading-relaxed text-slate-600 md:text-sm">{{ $comment->content }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 py-8 text-center">
                            <p class="text-xs font-semibold text-slate-400">{{ __('ui.book.no_reviews') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </main>

    <script>
        (function () {
            const form = document.querySelector('[data-review-form]');
            if (!form) return;

            const input = form.querySelector('[data-review-rating]');
            const label = form.querySelector('[data-review-star-label]');
            const stars = form.querySelectorAll('[data-review-star]');
            const pickLabel = @json(__('ui.book.pick_rating'));
            const pickRequired = @json(__('ui.book.pick_rating_required'));

            function paint(value) {
                stars.forEach((star) => {
                    const v = parseInt(star.dataset.value, 10);
                    star.classList.toggle('text-amber-400', v <= value);
                    star.classList.toggle('text-slate-200', v > value);
                });
                if (label) {
                    label.textContent = value ? (value + '/5') : pickLabel;
                }
            }

            stars.forEach((star) => {
                star.addEventListener('click', () => {
                    const value = parseInt(star.dataset.value, 10);
                    input.value = String(value);
                    paint(value);
                });
            });

            form.addEventListener('submit', (e) => {
                if (!parseInt(input.value, 10)) {
                    e.preventDefault();
                    if (label) label.textContent = pickRequired;
                    label?.classList.add('text-bv-accent');
                }
            });
        })();
    </script>

</body>
</html>
