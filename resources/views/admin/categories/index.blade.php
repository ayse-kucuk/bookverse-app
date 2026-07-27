@extends('layouts.admin', ['title' => __('ui.admin.category_title')])

@section('content')
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">{{ __('ui.admin.category_title') }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ __('ui.admin.category_sub') }}</p>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" class="bv-card space-y-3 rounded-2xl p-5">
        @csrf
        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-400">{{ __('ui.admin.category_new') }}</p>
        <div class="grid gap-3 sm:grid-cols-2">
            <input type="text" name="name" placeholder="{{ __('ui.admin.category_name_placeholder') }}" required value="{{ old('name') }}" class="bv-input rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            <input type="text" name="description" placeholder="{{ __('ui.admin.category_desc_placeholder') }}" value="{{ old('description') }}" class="bv-input rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <button type="submit" class="bv-btn rounded-full px-4 py-2 text-xs font-bold text-white">{{ __('ui.common.add') }}</button>
    </form>

    <div class="space-y-3">
        @forelse($categories as $category)
            <div class="bv-card rounded-2xl p-4">
                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    <form id="category-update-{{ $category->id }}" action="{{ route('admin.categories.update', $category) }}" method="POST" class="contents">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">{{ __('ui.admin.category_name_placeholder') }}</label>
                            <input type="text" name="name" value="{{ $category->name }}" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">{{ __('ui.admin.category_desc_placeholder') }}</label>
                            <input type="text" name="description" value="{{ $category->description }}" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                    </form>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-slate-400">{{ __('ui.admin.category_book_count', ['count' => $category->books_count]) }}</span>
                        <button type="submit" form="category-update-{{ $category->id }}" class="rounded-full bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-rose-600">{{ __('ui.common.save') }}</button>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm(@json(__('ui.admin.confirm_delete_category')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-400 hover:border-\[#e8e4de\] hover:text-bv-accent">{{ __('ui.common.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bv-card rounded-2xl p-8 text-center text-sm text-slate-400">{{ __('ui.admin.category_no_content') }}</div>
        @endforelse
    </div>

    <div>{{ $categories->links() }}</div>
@endsection
