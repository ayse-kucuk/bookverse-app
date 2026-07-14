@extends('layouts.admin', ['title' => 'Kitaplar'])

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Kitaplar</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $books->total() }} kayıt</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="bv-btn rounded-full px-4 py-2 text-xs font-bold text-white">+ Yeni kitap</a>
    </div>

    <form method="GET" action="{{ route('admin.books.index') }}" class="bv-card flex flex-col gap-3 rounded-2xl p-4 sm:flex-row sm:items-end">
        <div class="min-w-0 flex-1">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Ara</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="Başlık veya yazar" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="sm:w-44">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Kategori</label>
            <select name="category" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Tümü</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($currentCategory == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-700">Filtrele</button>
    </form>

    <div class="bv-card overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Kitap</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Yorum</th>
                        <th class="px-4 py-3">Koruma</th>
                        <th class="px-4 py-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($books as $book)
                        <tr class="hover:bg-rose-50/40">
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
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Kilitli</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">Açık</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('books.show', $book) }}" class="text-xs font-bold text-slate-400 hover:text-slate-700">Gör</a>
                                    <a href="{{ route('admin.books.edit', $book) }}" class="text-xs font-bold text-rose-600 hover:text-rose-700">Düzenle</a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Bu kitabı silmek istiyor musun?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-slate-400 hover:text-rose-600">Sil</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">Kitap bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $books->links() }}</div>
@endsection
