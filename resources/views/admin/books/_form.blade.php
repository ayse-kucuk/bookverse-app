@php
    $book = $book ?? null;
@endphp

<div>
    <label class="mb-1 block text-xs font-bold text-slate-600">Kitap adı</label>
    <input type="text" name="title" value="{{ old('title', $book?->title) }}" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm @error('title') border-rose-400 @enderror">
    @error('title')
        <p class="mt-1 text-xs font-semibold text-bv-accent">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="mb-1 block text-xs font-bold text-slate-600">Yazar</label>
    <input type="text" name="author" value="{{ old('author', $book?->author) }}" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
</div>

<div>
    <label class="mb-1 block text-xs font-bold text-slate-600">Kategori</label>
    <select name="category_id" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <option value="">Seçin...</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $book?->category_id) == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-xs font-bold text-slate-600">Sayfa sayısı</label>
        <input type="number" name="page_count" min="1" value="{{ old('page_count', $book?->page_count) }}" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
    </div>
    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
            <input type="checkbox" name="is_protected" value="1" class="rounded border-slate-300 text-bv-accent focus:ring-rose-500" @checked(old('is_protected', $book?->is_protected ?? true))>
            Silinmeye karşı koru
        </label>
    </div>
</div>

<div>
    <label class="mb-1 block text-xs font-bold text-slate-600">Kapak URL</label>
    <input type="url" name="image_url" value="{{ old('image_url', $book?->image_url) }}" required placeholder="https://..." class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
</div>

<div>
    <label class="mb-1 block text-xs font-bold text-slate-600">Açıklama</label>
    <textarea name="description" rows="5" required class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">{{ old('description', $book?->description) }}</textarea>
</div>
