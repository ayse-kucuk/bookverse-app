<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Bildirimler — Bookverse'])
</head>
<body class="bv-mesh min-h-screen antialiased selection:bg-[#e8dfd2]">

    @include('partials.site-nav')

    <main class="bv-page space-y-6 py-10">
        <div class="bv-animate-up flex items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">Gelen Kutusu</p>
                <h1 class="bv-display mt-1 text-4xl font-medium text-[#1c1c1c]">Bildirimler</h1>
            </div>
        </div>

        <div class="space-y-2">
            @forelse($notifications as $notification)
                <a href="{{ route('notifications.open', $notification) }}"
                   data-notification-read="{{ route('notifications.read', $notification) }}"
                   class="bv-card flex items-start gap-4 p-4 transition hover:bg-[#f9f8f6] {{ $notification->isUnread() ? 'border-l-2 border-l-[#a67c52]' : '' }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e8e4de] bg-[#f3f0eb] text-sm">
                        @if($notification->actor->profile_photo_path)
                            <img src="{{ $notification->actor->profilePhotoUrl() }}" alt="" class="h-full w-full object-cover">
                        @else
                            @php
                                $icon = match($notification->type) {
                                    \App\Models\Notification::TYPE_POST_LIKE => '❤️',
                                    \App\Models\Notification::TYPE_POST_COMMENT => '💬',
                                    default => '👤',
                                };
                            @endphp
                            {{ $icon }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-[#2a2a2a]">{{ $notification->message() }}</p>
                        <p class="mt-0.5 text-xs text-[#9a948d]">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if($notification->isUnread())
                        <span class="mt-1.5 h-2 w-2 shrink-0 bg-[#a67c52]"></span>
                    @endif
                </a>
            @empty
                <div class="bv-card p-12 text-center">
                    <p class="text-sm text-[#9a948d]">Henüz bildirimin yok.</p>
                </div>
            @endforelse
        </div>

        <div>{{ $notifications->links() }}</div>
    </main>

</body>
</html>
