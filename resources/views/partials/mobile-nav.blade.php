<nav class="bv-mobile-nav fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white/95 backdrop-blur-md sm:hidden" aria-label="Mobil menü">
    <div class="mx-auto flex max-w-lg items-stretch justify-around px-2 py-1.5">
        <a href="{{ route('home') }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-xl px-2 py-2 text-[10px] font-bold transition {{ request()->routeIs('home') ? 'text-rose-600' : 'text-slate-500' }}">
            <span class="text-lg leading-none">🏠</span>
            <span>Akış</span>
        </a>
        <a href="{{ route('explore') }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-xl px-2 py-2 text-[10px] font-bold transition {{ request()->routeIs('explore') ? 'text-rose-600' : 'text-slate-500' }}">
            <span class="text-lg leading-none">📚</span>
            <span>Keşfet</span>
        </a>
        @auth
            <a href="{{ route('notifications.index') }}" class="relative flex flex-1 flex-col items-center gap-0.5 rounded-xl px-2 py-2 text-[10px] font-bold transition {{ request()->routeIs('notifications.*') ? 'text-rose-600' : 'text-slate-500' }}">
                <span class="relative text-lg leading-none">
                    🔔
                    @if(($navUnreadCount ?? 0) > 0)
                        <span class="absolute -right-2 -top-1 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-rose-600 px-0.5 text-[8px] font-bold text-white">{{ ($navUnreadCount ?? 0) > 9 ? '9+' : ($navUnreadCount ?? 0) }}</span>
                    @endif
                </span>
                <span>Bildirim</span>
            </a>
            <a href="{{ route('profile') }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-xl px-2 py-2 text-[10px] font-bold transition {{ request()->routeIs('profile') ? 'text-rose-600' : 'text-slate-500' }}">
                <span class="text-lg leading-none">👤</span>
                <span>Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-xl px-2 py-2 text-[10px] font-bold transition {{ request()->routeIs('login') ? 'text-rose-600' : 'text-slate-500' }}">
                <span class="text-lg leading-none">🔑</span>
                <span>Giriş</span>
            </a>
            <a href="{{ route('register') }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-xl px-2 py-2 text-[10px] font-bold transition {{ request()->routeIs('register') ? 'text-rose-600' : 'text-slate-500' }}">
                <span class="text-lg leading-none">✨</span>
                <span>Kayıt</span>
            </a>
        @endauth
    </div>
</nav>
