@php
    $profileUser = $profileUser ?? $user ?? null;
@endphp
@if($profileUser)
    <p class="mt-1 text-xs font-semibold text-slate-400">
        {{ $profileUser->isPublic() ? 'Herkese açık hesap' : 'Yalnızca takipçilere açık' }}
        ·
        <button type="button" onclick="openFollowPanel('followers')" class="font-bold text-rose-600 transition hover:text-rose-700 hover:underline">
            {{ $profileUser->followers_count ?? $profileUser->followers()->count() }} takipçi
        </button>
        ·
        <button type="button" onclick="openFollowPanel('following')" class="font-bold text-rose-600 transition hover:text-rose-700 hover:underline">
            {{ $profileUser->following_count ?? $profileUser->following()->count() }} takip
        </button>
    </p>
@endif
