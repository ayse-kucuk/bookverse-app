<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', [
        'title' => __('ui.search.page_title'),
        'description' => __('ui.seo.search_description'),
        'canonical' => route('search', array_filter([
            'q' => $query ?: null,
            'category' => $curCategory ?: null,
            'min_rating' => $curMinRating ?: null,
            'sort' => ($curSort ?? 'relevance') !== 'relevance' ? $curSort : null,
        ])),
    ])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-\[#e8dfd2\]">

    @include('partials.site-nav')

    <main class="bv-page space-y-6 py-6 sm:space-y-8 sm:py-8">
        <header class="bv-animate-up">
            <h1 class="text-xl font-extrabold tracking-tight text-slate-800 sm:text-2xl">{{ __('ui.search.title') }}</h1>
            @if(mb_strlen($query) >= 1)
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('ui.search.results_for', ['query' => $query]) }}
                </p>
            @else
                <p class="mt-1 text-sm text-slate-400">{{ __('ui.search.hint') }}</p>
            @endif
        </header>

        <form action="{{ route('search') }}" method="GET" class="bv-card rounded-2xl p-4 sm:hidden">
            <label for="mobile-search" class="sr-only">{{ __('ui.search.button') }}</label>
            <input
                id="mobile-search"
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="{{ __('ui.search.placeholder') }}"
                enterkeyhint="search"
                class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm"
            >
            <button type="submit" class="bv-btn mt-3 w-full rounded-xl py-2.5 text-xs font-bold uppercase tracking-wider text-white">{{ __('ui.search.button') }}</button>
        </form>

        {{-- Filters --}}
        <form method="GET" action="{{ route('search') }}" class="bv-card rounded-2xl p-4">
            <input type="hidden" name="q" value="{{ $query }}">
            <div class="flex flex-wrap gap-3 sm:items-end">
                <div class="min-w-[10rem] flex-1">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.explore.category') }}</label>
                    <select name="category" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">{{ __('ui.explore.all') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($curCategory == $cat->id)>{{ $cat->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.search.min_rating') }}</label>
                    <select name="min_rating" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">{{ __('ui.explore.all') }}</option>
                        @foreach([1,2,3,4,5] as $r)
                            <option value="{{ $r }}" @selected($curMinRating == $r)>{{ $r }}★+</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.explore.sort') }}</label>
                    <select name="sort" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="relevance" @selected(($curSort ?? 'relevance') === 'relevance')>{{ __('ui.search.sort_relevance') }}</option>
                        <option value="rating" @selected(($curSort ?? '') === 'rating')>{{ __('ui.explore.sort_rating') }}</option>
                        <option value="title" @selected(($curSort ?? '') === 'title')>{{ __('ui.explore.sort_title') }}</option>
                        <option value="latest" @selected(($curSort ?? '') === 'latest')>{{ __('ui.explore.sort_latest') }}</option>
                    </select>
                </div>
                <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-[#f3f0eb] hover:text-bv-accent">{{ __('ui.common.filter') }}</button>
                @if($query || $curCategory || $curMinRating)
                    <a href="{{ route('search') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-400 hover:text-bv-accent">✕</a>
                @endif
            </div>
        </form>

        @if(mb_strlen($query) < 1 && !$curCategory && !$curMinRating)
            <div class="bv-card rounded-2xl p-8 text-center text-sm text-slate-400 sm:p-10">
                {{ __('ui.search.empty_query') }}
            </div>
        @else
            @php
                $total = $books->count() + $users->count() + $posts->count();
            @endphp

            @if($total === 0)
                <div class="bv-card rounded-2xl p-10 text-center text-sm text-slate-400">
                    {{ __('ui.search.no_results') }}
                </div>
            @else
                @if($books->isNotEmpty())
                    <section class="bv-animate-up-delay-1 space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">{{ __('ui.search.books') }} ({{ $books->count() }})</h2>
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
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">{{ __('ui.search.users') }} ({{ $users->count() }})</h2>
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
                                        <p class="text-xs text-slate-400">{{ $user->isPublic() ? __('ui.search.visibility_public') : __('ui.search.visibility_followers') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($posts->isNotEmpty())
                    <section class="space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-400">{{ __('ui.search.posts') }} ({{ $posts->count() }})</h2>
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

    @include('partials.site-footer')
</body>
</html>
