<nav class="bv-animate-nav sticky top-0 z-50 px-4 pt-4 sm:px-6">
    <div class="bv-card mx-auto max-w-6xl rounded-2xl px-4 py-3 sm:px-5">
        <div class="flex items-center gap-3 sm:gap-4">
            <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2.5">
                @include('partials.logo', ['size' => 'md'])
                <span class="hidden text-lg font-extrabold tracking-tight text-slate-800 sm:inline">
                    Bookverse<span class="bv-gradient-text">.</span>
                </span>
            </a>

            <div class="relative min-w-0 flex-1" data-live-search-wrap>
                <form action="{{ route('search') }}" method="GET" class="min-w-0" data-live-search-form>
                    <label for="nav-search" class="sr-only">Ara</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                        <input
                            id="nav-search"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Kitap, kullanıcı, paylaşım..."
                            autocomplete="off"
                            data-live-search-input
                            data-suggest-url="{{ route('search.suggest') }}"
                            class="bv-input w-full rounded-full border border-slate-200 bg-white py-2 pl-9 pr-4 text-xs font-medium text-slate-800 transition placeholder:text-slate-400 sm:text-sm"
                        >
                    </div>
                </form>
                <div
                    data-live-search-dropdown
                    class="bv-surface-matte absolute left-0 right-0 top-[calc(100%+0.5rem)] z-[100] hidden max-h-80 overflow-y-auto rounded-2xl py-2"
                    role="listbox"
                    aria-label="Arama önerileri"
                ></div>
            </div>

            <div class="flex shrink-0 items-center gap-0.5 text-sm font-semibold sm:gap-1">
                <a href="{{ route('home') }}"
                   class="hidden rounded-full px-2.5 py-1.5 transition duration-200 sm:inline {{ request()->routeIs('home') ? 'bv-nav-active' : 'text-slate-500 hover:bg-rose-50 hover:text-rose-700' }}">
                    Akış
                </a>
                <a href="{{ route('explore') }}"
                   class="hidden rounded-full px-2.5 py-1.5 transition duration-200 sm:inline {{ request()->routeIs('explore') ? 'bv-nav-active' : 'text-slate-500 hover:bg-rose-50 hover:text-rose-700' }}">
                    Keşfet
                </a>

                @auth
                    <details class="relative" data-notification-dropdown>
                        <summary class="list-none relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-base transition hover:bg-rose-50" aria-label="Bildirimler" data-notification-bell data-unread-count="{{ $navUnreadCount ?? 0 }}">
                            🔔
                            <span data-notification-badge class="{{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }} absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-bold text-white">{{ ($navUnreadCount ?? 0) > 9 ? '9+' : ($navUnreadCount ?? 0) }}</span>
                        </summary>
                        <div class="bv-surface-matte absolute right-0 z-[110] mt-3 w-72 overflow-hidden rounded-2xl sm:w-80">
                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Bildirimler</p>
                                @if(($navUnreadCount ?? 0) > 0)
                                    <button type="button" data-notification-read-all="{{ route('notifications.read-all') }}" class="text-[10px] font-bold text-rose-600 hover:text-rose-700">Tümünü oku</button>
                                @endif
                            </div>
                            <div class="max-h-72 overflow-y-auto" data-notification-list>
                                @forelse($navNotifications ?? [] as $notification)
                                    <a href="{{ route('notifications.open', $notification) }}" data-notification-read="{{ route('notifications.read', $notification) }}" class="flex items-start gap-3 px-4 py-3 transition hover:bg-rose-50 bg-rose-50/40">
                                        <span class="text-sm">{{ $notification->type === \App\Models\Notification::TYPE_POST_LIKE ? '❤️' : '👤' }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold leading-snug text-slate-800">{{ $notification->message() }}</p>
                                            <p class="mt-0.5 text-[10px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <p class="px-4 py-6 text-center text-xs text-slate-400">Bildirim yok</p>
                                @endforelse
                            </div>
                            <a href="{{ route('notifications.index') }}" data-notification-see-all="{{ route('notifications.read-all') }}" class="block border-t border-slate-200 bg-slate-50 py-2.5 text-center text-xs font-bold text-rose-600 transition hover:bg-rose-50">
                                Tüm bildirimleri gör
                            </a>
                        </div>
                    </details>

                    <details class="relative ml-0.5 sm:ml-1">
                        <summary class="list-none flex h-9 w-9 cursor-pointer items-center justify-center overflow-hidden rounded-full ring-2 ring-rose-200/80 ring-offset-2 transition duration-200 hover:ring-rose-400">
                            @if(Auth::user()->profile_photo_path)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center bg-gradient-to-br from-rose-100 to-orange-100 text-sm">👤</span>
                            @endif
                        </summary>
                        <div class="bv-surface-matte absolute right-0 mt-3 w-48 overflow-hidden rounded-2xl p-1.5">
                            <a href="{{ route('profile') }}" class="block rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-rose-50 hover:text-rose-700">Profil</a>
                            @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">Yönetim Paneli</a>
                            @endif
                            <a href="{{ route('account.settings') }}" class="block rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-rose-50 hover:text-rose-700">Hesap Ayarları</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="mt-0.5 w-full cursor-pointer rounded-xl border-t border-slate-100 px-3 py-2.5 text-left text-xs font-semibold text-slate-400 transition hover:bg-rose-50 hover:text-rose-700">Çıkış Yap</button>
                            </form>
                        </div>
                    </details>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="hidden rounded-full px-2.5 py-1.5 text-slate-500 transition hover:text-rose-700 md:inline">Giriş</a>
                    <a href="{{ route('register') }}" class="bv-btn rounded-full px-3 py-1.5 text-[10px] font-bold text-white sm:px-4 sm:py-2 sm:text-xs">Kayıt</a>
                @endguest
            </div>
        </div>
    </div>
</nav>

@include('partials.bv-ajax')
@include('partials.mobile-nav')
