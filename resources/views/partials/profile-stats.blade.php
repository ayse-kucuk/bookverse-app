@php
    $profileUser = $profileUser ?? $user ?? null;
@endphp
@if($profileUser)
    <p class="mt-1.5 text-xs text-[#9a948d]">
        {{ $profileUser->isPublic() ? 'Herkese açık' : 'Yalnızca takipçilere açık' }}
        <span class="mx-1.5 opacity-40">·</span>
        <button type="button" onclick="openFollowPanel('followers')" class="font-semibold text-[#6b6560] transition hover:text-bv-accent">
            {{ $profileUser->followers_count ?? $profileUser->followers()->count() }} takipçi
        </button>
        <span class="mx-1.5 opacity-40">·</span>
        <button type="button" onclick="openFollowPanel('following')" class="font-semibold text-[#6b6560] transition hover:text-bv-accent">
            {{ $profileUser->following_count ?? $profileUser->following()->count() }} takip
        </button>
    </p>
@endif
