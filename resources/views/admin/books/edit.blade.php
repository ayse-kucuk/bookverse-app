@extends('layouts.admin', ['title' => 'Kitap Düzenle'])

@section('content')
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Kitabı düzenle</h1>
        <p class="mt-1 text-sm text-slate-400">{{ $book->title }}</p>
    </div>

    <form action="{{ route('admin.books.update', $book) }}" method="POST" class="bv-card space-y-4 rounded-2xl p-6">
        @csrf
        @method('PUT')
        @include('admin.books._form', ['book' => $book])
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('admin.books.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50">İptal</a>
            <button type="submit" class="bv-btn rounded-full px-5 py-2 text-xs font-bold text-white">Güncelle</button>
        </div>
    </form>
@endsection
