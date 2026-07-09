<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Ayarlari - Bookverse</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[#FCE7F3] text-gray-800 font-sans antialiased selection:bg-rose-300 selection:text-gray-900">

    @include('partials.site-nav')

    <main class="max-w-3xl mx-auto px-4 py-12">
        <div class="bg-white p-8 rounded-3xl border border-rose-100 shadow-xs">
            <div class="mb-6 flex items-center gap-4">
                <div class="w-16 h-16 bg-[#DB2777] rounded-2xl flex items-center justify-center text-3xl text-white shadow-xs overflow-hidden">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        👤
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Hesap Ayarlari</h1>
                    <p class="text-xs font-semibold text-gray-400 mt-0.5">Kullanici bilgilerini buradan guncelleyebilirsin.</p>
                </div>
            </div>

            @if (session('status') === 'profile-updated')
                <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    Profil bilgilerin guncellendi.
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700" for="name">Ad Soyad</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border border-rose-200 px-4 py-3 text-sm focus:border-[#DB2777] focus:outline-none focus:ring-2 focus:ring-rose-100">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700" for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-2xl border border-rose-200 px-4 py-3 text-sm focus:border-[#DB2777] focus:outline-none focus:ring-2 focus:ring-rose-100">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700" for="profile_photo">Profil Fotografi</label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="w-full rounded-2xl border border-rose-200 px-4 py-3 text-sm file:mr-4 file:rounded-full file:border-0 file:bg-rose-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#DB2777] hover:file:bg-rose-200">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG veya WebP. En fazla 2 MB.</p>
                    @error('profile_photo')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700" for="account_visibility">Hesap Gizliliği</label>
                    <select id="account_visibility" name="account_visibility" class="w-full rounded-2xl border border-rose-200 px-4 py-3 text-sm focus:border-[#DB2777] focus:outline-none focus:ring-2 focus:ring-rose-100">
                        <option value="public" @selected(old('account_visibility', $user->account_visibility ?? 'public') === 'public')>Herkese açık</option>
                        <option value="followers_only" @selected(old('account_visibility', $user->account_visibility) === 'followers_only')>Yalnızca takipçilerim</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Takipçilere özel hesaplarda paylaşımların ve profilin yalnızca takipçilerin görebilir.</p>
                    @error('account_visibility')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="rounded-2xl bg-[#DB2777] px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                    Kaydet
                </button>
            </form>
        </div>
    </main>

</body>
</html>
