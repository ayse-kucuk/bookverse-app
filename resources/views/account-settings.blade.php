<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Hesap Ayarları — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <div class="bv-card bv-animate-up rounded-2xl p-7 sm:p-8">
            <div class="mb-8 flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 text-3xl text-white shadow-lg shadow-rose-500/25">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        👤
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Hesap Ayarları</h1>
                    <p class="mt-0.5 text-xs font-semibold text-slate-400">Kullanıcı bilgilerini buradan güncelleyebilirsin.</p>
                </div>
            </div>

            @if (session('status') === 'profile-updated')
                <div class="mb-5 rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-700">
                    Profil bilgilerin güncellendi.
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700" for="name">Ad Soyad</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="bv-input w-full rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-3 text-sm transition">
                    @error('name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700" for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="bv-input w-full rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-3 text-sm transition">
                    @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700" for="profile_photo">Profil Fotoğrafı</label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-3 text-sm file:mr-4 file:cursor-pointer file:rounded-full file:border-0 file:bg-rose-100 file:px-4 file:py-2 file:text-sm file:font-bold file:text-rose-700 hover:file:bg-rose-200">
                    <p class="mt-1 text-xs text-slate-400">JPG, PNG veya WebP. En fazla 2 MB.</p>
                    @error('profile_photo')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700" for="account_visibility">Hesap Gizliliği</label>
                    <select id="account_visibility" name="account_visibility" class="bv-input w-full rounded-2xl border border-slate-200/80 bg-white/60 px-4 py-3 text-sm transition">
                        <option value="public" @selected(old('account_visibility', $user->account_visibility ?? 'public') === 'public')>Herkese açık</option>
                        <option value="followers_only" @selected(old('account_visibility', $user->account_visibility) === 'followers_only')>Yalnızca takipçilerim</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Takipçilere özel hesaplarda paylaşımların ve profilin yalnızca takipçilerin görebilir.</p>
                    @error('account_visibility')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="bv-btn rounded-full px-6 py-3 text-sm font-bold text-white">
                    Kaydet
                </button>
            </form>
        </div>
    </main>

</body>
</html>
