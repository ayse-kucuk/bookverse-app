@extends('layouts.admin', ['title' => 'Kullanıcılar'])

@section('content')
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Kullanıcılar</h1>
        <p class="mt-1 text-sm text-slate-400">{{ $users->total() }} kayıt</p>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="bv-card flex flex-col gap-3 rounded-2xl p-4 sm:flex-row sm:items-end">
        <div class="min-w-0 flex-1">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Ara</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="İsim veya e-posta" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="sm:w-40">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Rol</label>
            <select name="role" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Tümü</option>
                <option value="admin" @selected($role === 'admin')>Admin</option>
                <option value="user" @selected($role === 'user')>Kullanıcı</option>
            </select>
        </div>
        <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-700">Filtrele</button>
    </form>

    <div class="bv-card overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Kullanıcı</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3">İstatistik</th>
                        <th class="px-4 py-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-rose-50/40">
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($user->is_admin)
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">Admin</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">Kullanıcı</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-500">
                                {{ $user->posts_count }} paylaşım · {{ $user->followers_count }} takipçi
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.show', $user) }}" class="text-xs font-bold text-slate-400 hover:text-slate-700">Profil</a>
                                    <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700">
                                            {{ $user->is_admin ? 'Admin kaldır' : 'Admin yap' }}
                                        </button>
                                    </form>
                                    @if(! $user->is_admin)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Kullanıcıyı silmek istiyor musun?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-slate-400 hover:text-rose-600">Sil</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">Kullanıcı bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $users->links() }}</div>
@endsection
