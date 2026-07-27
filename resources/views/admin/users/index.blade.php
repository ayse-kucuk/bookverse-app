@extends('layouts.admin', ['title' => __('ui.admin.users')])

@section('content')
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">{{ __('ui.admin.users') }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ __('ui.common.record_count', ['count' => $users->total()]) }}</p>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="bv-card flex flex-col gap-3 rounded-2xl p-4 sm:flex-row sm:items-end">
        <div class="min-w-0 flex-1">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.nav.search') }}</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('ui.admin.search_placeholder_user') }}" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="sm:w-40">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.admin.col_role') }}</label>
            <select name="role" class="bv-input w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">{{ __('ui.admin.role_all') }}</option>
                <option value="admin" @selected($role === 'admin')>{{ __('ui.admin.role_admin') }}</option>
                <option value="user" @selected($role === 'user')>{{ __('ui.admin.role_user') }}</option>
            </select>
        </div>
        <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-\[#f3f0eb\] hover:text-bv-accent">{{ __('ui.common.filter') }}</button>
    </form>

    <div class="bv-card overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('ui.admin.col_user') }}</th>
                        <th class="px-4 py-3">{{ __('ui.admin.col_role') }}</th>
                        <th class="px-4 py-3">{{ __('ui.admin.col_stats') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('ui.admin.col_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-\[#f3f0eb\]/40">
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($user->is_admin)
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-bv-accent">{{ __('ui.admin.role_admin') }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ __('ui.admin.role_user') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-500">
                                {{ __('ui.admin.user_posts', ['count' => $user->posts_count]) }} · {{ __('ui.admin.user_followers', ['count' => $user->followers_count]) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.show', $user) }}" class="text-xs font-bold text-slate-400 hover:text-slate-700">{{ __('ui.profile.settings') !== 'Hesap Ayarları →' ? __('ui.common.view') : __('ui.common.view') }}</a>
                                    <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-bv-accent hover:text-bv-accent">
                                            {{ $user->is_admin ? __('ui.admin.remove_admin') : __('ui.admin.make_admin') }}
                                        </button>
                                    </form>
                                    @if(! $user->is_admin)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm(@json(__('ui.admin.confirm_delete_user')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-slate-400 hover:text-bv-accent">{{ __('ui.common.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">{{ __('ui.admin.user_not_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $users->links() }}</div>
@endsection
