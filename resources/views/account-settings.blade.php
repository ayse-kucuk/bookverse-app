<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Hesap Ayarları — Bookverse'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page py-10">

        <div class="mb-8 bv-animate-up">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Ayarlar</p>
            <h1 class="bv-display mt-1 text-4xl font-medium text-[#1c1c1c]">Hesap Ayarları</h1>
        </div>

        <div class="bv-card bv-animate-up p-7 sm:p-8">
            <div class="mb-8 flex items-center gap-5 border-b border-[#f0ece6] pb-7">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de] bg-[#f3f0eb] text-3xl">
                    @if($user->profile_photo_path)
                        <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        📖
                    @endif
                </div>
                <div>
                    <p class="font-semibold text-[#1c1c1c]">{{ $user->name }}</p>
                    <p class="text-xs text-[#9a948d]">{{ $user->email }}</p>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="name">Ad Soyad</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                        class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm transition">
                    @error('name')<p class="mt-1 text-xs text-bv-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="email">E-posta</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                        class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm transition">
                    @error('email')<p class="mt-1 text-xs text-bv-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="profile_photo">Profil Fotoğrafı</label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*"
                        class="w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm file:mr-4 file:cursor-pointer file:border-0 file:bg-[#f3f0eb] file:px-4 file:py-2 file:text-xs file:font-bold file:uppercase file:tracking-wider file:text-[#6b6560] hover:file:bg-[#e8e4de]">
                    <p class="mt-1 text-xs text-[#9a948d]">JPG, PNG veya WebP. En fazla 2 MB.</p>
                    @error('profile_photo')<p class="mt-1 text-xs text-bv-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="account_visibility">Hesap Gizliliği</label>
                    <select id="account_visibility" name="account_visibility"
                        class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm transition">
                        <option value="public" @selected(old('account_visibility', $user->account_visibility ?? 'public') === 'public')>Herkese açık</option>
                        <option value="followers_only" @selected(old('account_visibility', $user->account_visibility) === 'followers_only')>Yalnızca takipçilerim</option>
                    </select>
                    <p class="mt-1 text-xs text-[#9a948d]">Takipçilere özel hesaplarda profilin ve paylaşımların yalnızca takipçilerin görür.</p>
                    @error('account_visibility')<p class="mt-1 text-xs text-bv-accent">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-4 border-t border-[#f0ece6] pt-5">
                    <button type="submit" class="bv-btn px-7 py-3 text-xs font-bold uppercase tracking-wider">
                        Kaydet
                    </button>
                    <a href="{{ route('profile') }}" class="text-xs font-semibold text-[#9a948d] transition hover:text-[#1c1c1c]">Profili görüntüle →</a>
                </div>
            </form>
        </div>

    </main>

</body>
</html>
