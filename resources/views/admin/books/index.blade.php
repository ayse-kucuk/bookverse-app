@extends('layouts.admin', ['title' => __('ui.admin.book_title')])

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">{{ __('ui.admin.book_title') }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ __('ui.common.record_count', ['count' => $books->total()]) }}</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="bv-btn rounded-full px-4 py-2 text-xs font-bold text-white">{{ __('ui.admin.book_add') }}</a>
    </div>

    <form method="GET" action="{{ route('admin.books.index') }}" class="bv-card flex flex-col gap-3 rounded-2xl p-4 sm:flex-row sm:items-end">
        <div class="min-w-0 flex-1">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.nav.search') }}</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('ui.admin.search_placeholder_book') }}" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="sm:w-44">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.admin.col_category') }}</label>
            <select name="category" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">{{ __('ui.common.all') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($currentCategory == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-\[#f3f0eb\] hover:text-bv-accent">{{ __('ui.common.filter') }}</button>
    </form>

    <div class="bv-card overflow-hidden rounded-2xl">
        <div class="bv-table-wrap">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('ui.admin.col_book') }}</th>
                        <th class="px-4 py-3">{{ __('ui.admin.col_category') }}</th>
                        <th class="px-4 py-3">{{ __('ui.admin.col_comments') }}</th>
                        <th class="px-4 py-3">{{ __('ui.admin.col_protection') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('ui.admin.col_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($books as $book)
                        <tr class="hover:bg-\[#f3f0eb\]/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-8 shrink-0 overflow-hidden rounded-md bg-slate-800">
                                        @if($book->image_url)
                                            <img src="{{ $book->image_url }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-800">{{ $book->title }}</p>
                                        <p class="truncate text-xs text-slate-400">{{ $book->author }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-500">{{ $book->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-500">{{ $book->comments_count }}</td>
                            <td class="px-4 py-3">
                                @if($book->is_protected)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ __('ui.admin.protected') }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ __('ui.admin.unprotected') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('books.show', $book) }}" class="text-xs font-bold text-slate-400 hover:text-slate-700">{{ __('ui.common.view') }}</a>
                                    <a href="{{ route('admin.books.edit', $book) }}" class="text-xs font-bold text-bv-accent hover:text-bv-accent">{{ __('ui.common.edit') }}</a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" onsubmit="return confirm(@json(__('ui.admin.confirm_delete_book')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-slate-400 hover:text-bv-accent">{{ __('ui.common.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">{{ __('ui.admin.book_not_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $books->links() }}</div>
@endsection
