<div class="bv-topbar bv-animate-nav hidden sm:block">
    <div class="bv-page flex items-center justify-between py-2">
        <p class="font-medium">Okuma topluluğuna hoş geldin</p>
        <p class="font-medium">Keşfet · Oku · Paylaş</p>
    </div>
</div>

<nav class="bv-animate-nav sticky top-0 z-50 border-b border-[#e8e4de] bg-[#f9f8f6]/90 backdrop-blur-md" style="padding-top: env(safe-area-inset-top, 0px);">
    <div class="bv-page">
        <div class="flex items-center gap-2 py-3 sm:gap-4 sm:py-4">
            <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2 sm:gap-3">
                @include('partials.logo', ['size' => 'md'])
                <span class="bv-display hidden text-xl font-semibold tracking-[0.22em] text-[#1c1c1c] sm:inline">
                    BOOKVERSE
                </span>
            </a>

            <div class="hidden flex-1 items-center justify-center gap-8 lg:flex">
                <a href="{{ route('home') }}" class="bv-nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">Akış</a>
                <a href="{{ route('explore') }}" class="bv-nav-link {{ request()->routeIs('explore') ? 'is-active' : '' }}">Keşfet</a>
            </div>

            <div class="relative min-w-0 flex-1 lg:max-w-xs" data-live-search-wrap>
                <form action="{{ route('search') }}" method="GET" class="min-w-0" data-live-search-form>
                    <label for="nav-search" class="sr-only">Ara</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#9a948d]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
                        </span>
                        <input
                            id="nav-search"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Ara..."
                            autocomplete="off"
                            enterkeyhint="search"
                            data-live-search-input
                            data-suggest-url="{{ route('search.suggest') }}"
                            class="bv-input w-full border border-[#e8e4de] bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-[#2a2a2a] transition placeholder:text-[#9a948d]"
                        >
                    </div>
                </form>
                <div
                    data-live-search-dropdown
                    class="bv-surface-matte absolute left-0 right-0 top-[calc(100%+0.5rem)] z-[100] hidden max-h-[min(70vh,20rem)] overflow-y-auto py-2 sm:max-h-80"
                    role="listbox"
                    aria-label="Arama önerileri"
                ></div>
            </div>

            <div class="flex shrink-0 items-center gap-0.5 sm:gap-2">
                @auth
                    <details class="relative hidden sm:block" data-notification-dropdown>
                        <summary class="list-none relative flex h-10 w-10 cursor-pointer items-center justify-center text-[#6b6560] transition hover:text-[#1c1c1c]" aria-label="Bildirimler" data-notification-bell data-unread-count="{{ $navUnreadCount ?? 0 }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                            <span data-notification-badge class="{{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }} absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center bg-[#1c1c1c] px-1 text-[9px] font-bold text-white">{{ ($navUnreadCount ?? 0) > 9 ? '9+' : ($navUnreadCount ?? 0) }}</span>
                        </summary>
                        <div class="bv-surface-matte absolute right-0 z-[110] mt-3 w-72 overflow-hidden sm:w-80">
                            <div class="flex items-center justify-between border-b border-[#e8e4de] px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#6b6560]">Bildirimler</p>
                                @if(($navUnreadCount ?? 0) > 0)
                                    <button type="button" data-notification-read-all="{{ route('notifications.read-all') }}" class="text-[10px] font-bold text-bv-accent hover:opacity-80">Tümünü oku</button>
                                @endif
                            </div>
                            <div class="max-h-72 overflow-y-auto" data-notification-list>
                                @forelse($navNotifications ?? [] as $notification)
                                    <a href="{{ route('notifications.open', $notification) }}" data-notification-read="{{ route('notifications.read', $notification) }}" class="flex items-start gap-3 px-4 py-3 transition hover:bg-[#f3f0eb] bg-[#f9f8f6]/80">
                                        <span class="text-sm">{{ $notification->type === \App\Models\Notification::TYPE_POST_LIKE ? '❤️' : ($notification->type === \App\Models\Notification::TYPE_POST_COMMENT ? '💬' : '👤') }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium leading-snug text-[#2a2a2a]">{{ $notification->message() }}</p>
                                            <p class="mt-0.5 text-[10px] text-[#9a948d]">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <p class="px-4 py-6 text-center text-xs text-[#9a948d]">Bildirim yok</p>
                                @endforelse
                            </div>
                            <a href="{{ route('notifications.index') }}" data-notification-see-all="{{ route('notifications.read-all') }}" class="block border-t border-[#e8e4de] bg-[#f9f8f6] py-2.5 text-center text-xs font-bold text-bv-accent transition hover:bg-[#f3f0eb]">
                                Tüm bildirimleri gör
                            </a>
                        </div>
                    </details>

                    <details class="relative">
                        <summary class="list-none flex h-10 w-10 cursor-pointer items-center justify-center overflow-hidden rounded-full border border-[#e8e4de] transition hover:border-[#c4a574]">
                            @if(Auth::user()->profile_photo_path)
                                <img src="{{ Auth::user()->profilePhotoUrl() }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center bg-[#f3f0eb] text-xs text-[#6b6560]">👤</span>
                            @endif
                        </summary>
                        <div class="bv-surface-matte absolute right-0 z-[110] mt-3 w-48 overflow-hidden p-1.5">
                            <a href="{{ route('profile') }}" class="block px-3 py-2.5 text-xs font-medium text-[#6b6560] transition hover:bg-[#f3f0eb] hover:text-[#1c1c1c]">Profil</a>
                            @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 text-xs font-medium text-bv-accent transition hover:bg-[#f3f0eb]">Yönetim Paneli</a>
                            @endif
                            <a href="{{ route('account.settings') }}" class="block px-3 py-2.5 text-xs font-medium text-[#6b6560] transition hover:bg-[#f3f0eb] hover:text-[#1c1c1c]">Hesap Ayarları</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="mt-0.5 w-full cursor-pointer border-t border-[#f0ece6] px-3 py-2.5 text-left text-xs font-medium text-[#9a948d] transition hover:bg-[#f3f0eb] hover:text-[#1c1c1c]">Çıkış Yap</button>
                            </form>
                        </div>
                    </details>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="hidden px-3 py-2 text-xs font-semibold uppercase tracking-wider text-[#6b6560] transition hover:text-[#1c1c1c] sm:inline">Giriş</a>
                    <a href="{{ route('register') }}" class="bv-btn px-3 py-2 text-[10px] font-bold uppercase tracking-wider sm:px-4 sm:text-xs">Kayıt</a>
                @endguest
            </div>
        </div>
    </div>
</nav>

@include('partials.bv-ajax')
@include('partials.mobile-nav')
