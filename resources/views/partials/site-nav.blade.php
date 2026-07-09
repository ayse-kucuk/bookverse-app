<nav class="border-b border-[#F472B6]/20 bg-white/90 backdrop-blur-md sticky top-0 z-50 shadow-xs">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 group cursor-pointer">
            <span class="text-xl transition group-hover:scale-110 duration-200">📚</span>
            <span class="text-xl font-black text-gray-800 tracking-tight group-hover:text-[#DB2777] transition duration-200">
                Bookverse <span class="text-[#DB2777] font-medium text-base">Books</span>
            </span>
        </a>

        <div class="flex items-center gap-5 text-sm font-semibold">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-[#DB2777]' : 'text-gray-600' }} hover:text-[#DB2777] transition">Akış</a>
            <a href="{{ route('explore') }}" class="{{ request()->routeIs('explore') ? 'text-[#DB2777]' : 'text-gray-600' }} hover:text-[#DB2777] transition">Keşfet</a>

            @auth
                <span class="text-gray-300">|</span>
                <details class="relative">
                    <summary class="list-none inline-flex h-9 w-9 cursor-pointer items-center justify-center overflow-hidden rounded-full border border-rose-200 bg-rose-50 text-lg text-[#DB2777] transition hover:bg-rose-100">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                        @else
                            👤
                        @endif
                    </summary>
                    <div class="absolute right-0 z-50 mt-2 w-44 rounded-2xl border border-rose-100 bg-white p-2 shadow-lg">
                        <a href="{{ route('profile') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-rose-50 hover:text-[#DB2777]">Profil</a>
                        <a href="{{ route('account.settings') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-rose-50 hover:text-[#DB2777]">Hesap Ayarları</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 w-full rounded-xl border-t border-rose-50 px-3 py-2 text-left text-xs font-semibold text-gray-500 transition hover:bg-rose-50 hover:text-[#DB2777] cursor-pointer">Çıkış Yap</button>
                        </form>
                    </div>
                </details>
            @endauth

            @guest
                <span class="text-gray-300">|</span>
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-[#DB2777] transition">Giriş Yap</a>
                <a href="{{ route('register') }}" class="bg-[#DB2777] hover:bg-[#C2185B] text-white px-4 py-2 rounded-xl transition shadow-xs">Kayıt Ol</a>
            @endguest
        </div>
    </div>
</nav>
