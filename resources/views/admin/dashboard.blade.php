@extends('layouts.admin', ['title' => 'Yönetim'])

@section('content')
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Genel bakış</h1>
        <p class="mt-1 text-sm text-slate-400">Bookverse yönetim paneli</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['label' => 'Kullanıcı', 'value' => $stats['users'], 'href' => route('admin.users.index')],
            ['label' => 'Kitap', 'value' => $stats['books'], 'href' => route('admin.books.index')],
            ['label' => 'Kategori', 'value' => $stats['categories'], 'href' => route('admin.categories.index')],
            ['label' => 'Paylaşım', 'value' => $stats['posts'], 'href' => route('home')],
            ['label' => 'Yorum', 'value' => $stats['comments'], 'href' => route('admin.books.index')],
            ['label' => 'Admin', 'value' => $stats['admins'], 'href' => route('admin.users.index', ['role' => 'admin'])],
        ] as $card)
            <a href="{{ $card['href'] }}" class="bv-card bv-card-interactive rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ $card['value'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="bv-card rounded-2xl p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-800">Son kitaplar</h2>
                <a href="{{ route('admin.books.create') }}" class="text-xs font-bold text-bv-accent hover:text-bv-accent">+ Ekle</a>
            </div>
            <ul class="space-y-2">
                @forelse($recentBooks as $book)
                    <li class="flex items-center justify-between gap-2 rounded-xl bg-slate-50/80 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-800">{{ $book->title }}</p>
                            <p class="text-[10px] text-slate-400">{{ $book->category?->name }}</p>
                        </div>
                        <a href="{{ route('admin.books.edit', $book) }}" class="shrink-0 text-xs font-bold text-bv-accent">Düzenle</a>
                    </li>
                @empty
                    <li class="text-sm text-slate-400">Henüz kitap yok.</li>
                @endforelse
            </ul>
        </section>

        <section class="bv-card rounded-2xl p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-800">Son kullanıcılar</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-bv-accent hover:text-bv-accent">Tümü</a>
            </div>
            <ul class="space-y-2">
                @forelse($recentUsers as $user)
                    <li class="flex items-center justify-between gap-2 rounded-xl bg-slate-50/80 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-800">{{ $user->name }}</p>
                            <p class="truncate text-[10px] text-slate-400">{{ $user->email }}</p>
                        </div>
                        @if($user->is_admin)
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-bv-accent">Admin</span>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-slate-400">Henüz kullanıcı yok.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <section class="bv-card rounded-2xl p-5">
        <h2 class="mb-3 text-sm font-extrabold text-slate-800">Son yorumlar</h2>
        <ul class="space-y-3">
            @forelse($recentComments as $comment)
                <li class="rounded-xl border border-slate-100 px-3 py-3">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-bold text-slate-700">
                            {{ $comment->user?->name ?? 'Anonim' }}
                            <span class="font-medium text-slate-400">· {{ $comment->book?->title }}</span>
                        </p>
                        <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Yorumu silmek istiyor musun?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[10px] font-bold text-slate-400 hover:text-bv-accent">Sil</button>
                        </form>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($comment->content, 160) }}</p>
                </li>
            @empty
                <li class="text-sm text-slate-400">Henüz yorum yok.</li>
            @endforelse
        </ul>
    </section>
@endsection
