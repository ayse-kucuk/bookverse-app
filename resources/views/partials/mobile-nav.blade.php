<nav
    class="fixed bottom-0 left-0 right-0 z-50 border-t border-[#e8e4de] bg-[#f9f8f6]/95 backdrop-blur-md sm:hidden"
    aria-label="Mobil menü"
    style="padding-bottom: env(safe-area-inset-bottom, 0px);"
>
    <div class="mx-auto flex max-w-lg items-stretch justify-around px-1 py-1">
        <a href="{{ route('home') }}" class="flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('home') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
            <span class="text-lg leading-none" aria-hidden="true">🏠</span>
            <span>Akış</span>
        </a>
        <a href="{{ route('explore') }}" class="flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('explore') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
            <span class="text-lg leading-none" aria-hidden="true">📚</span>
            <span>Keşfet</span>
        </a>
        <a href="{{ route('search') }}" class="flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('search') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
            <span class="text-lg leading-none" aria-hidden="true">🔎</span>
            <span>Ara</span>
        </a>
        @auth
            <a href="{{ route('notifications.index') }}" class="relative flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('notifications.*') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
                <span class="relative text-lg leading-none" aria-hidden="true">
                    🔔
                    @if(($navUnreadCount ?? 0) > 0)
                        <span class="absolute -right-2 -top-1 flex h-3.5 min-w-3.5 items-center justify-center bg-[#1c1c1c] px-0.5 text-[8px] font-bold text-white">{{ ($navUnreadCount ?? 0) > 9 ? '9+' : ($navUnreadCount ?? 0) }}</span>
                    @endif
                </span>
                <span>Bildirim</span>
            </a>
            <a href="{{ route('profile') }}" class="flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('profile') || request()->routeIs('account.*') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
                <span class="text-lg leading-none" aria-hidden="true">👤</span>
                <span>Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('login') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
                <span class="text-lg leading-none" aria-hidden="true">🔑</span>
                <span>Giriş</span>
            </a>
            <a href="{{ route('register') }}" class="flex min-h-[3.25rem] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-bold uppercase tracking-wider transition {{ request()->routeIs('register') ? 'text-[#1c1c1c]' : 'text-[#9a948d]' }}">
                <span class="text-lg leading-none" aria-hidden="true">✨</span>
                <span>Kayıt</span>
            </a>
        @endauth
    </div>
</nav>
