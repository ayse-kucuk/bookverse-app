@extends('layouts.admin', ['title' => 'Kategoriler'])

@section('content')
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Kategoriler</h1>
        <p class="mt-1 text-sm text-slate-400">Kitap kategorilerini yönet</p>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" class="bv-card space-y-3 rounded-2xl p-5">
        @csrf
        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Yeni kategori</p>
        <div class="grid gap-3 sm:grid-cols-2">
            <input type="text" name="name" placeholder="Kategori adı" required value="{{ old('name') }}" class="bv-input rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            <input type="text" name="description" placeholder="Açıklama (opsiyonel)" value="{{ old('description') }}" class="bv-input rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        </div>
        <button type="submit" class="bv-btn rounded-full px-4 py-2 text-xs font-bold text-white">Ekle</button>
    </form>

    <div class="space-y-3">
        @forelse($categories as $category)
            <div class="bv-card rounded-2xl p-4">
                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    <form id="category-update-{{ $category->id }}" action="{{ route('admin.categories.update', $category) }}" method="POST" class="contents">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Ad</label>
                            <input type="text" name="name" value="{{ $category->name }}" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-slate-400">Açıklama</label>
                            <input type="text" name="description" value="{{ $category->description }}" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                    </form>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-slate-400">{{ $category->books_count }} kitap</span>
                        <button type="submit" form="category-update-{{ $category->id }}" class="rounded-full bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-rose-600">Kaydet</button>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Kategoriyi silmek istiyor musun?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-400 hover:border-rose-200 hover:text-rose-600">Sil</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bv-card rounded-2xl p-8 text-center text-sm text-slate-400">Henüz kategori yok.</div>
        @endforelse
    </div>

    <div>{{ $categories->links() }}</div>
@endsection
