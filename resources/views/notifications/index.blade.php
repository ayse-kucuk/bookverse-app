<!DOCTYPE html>
<html lang="tr">
<head>
    @include('partials.head', ['title' => 'Bildirimler — Bookverse'])
</head>
<body class="bv-mesh min-h-screen text-slate-800 antialiased selection:bg-rose-200">

    @include('partials.site-nav')

    <main class="mx-auto max-w-2xl space-y-6 px-4 py-8 sm:px-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Bildirimler</h1>
        </div>

        @if(session('success'))
            <div class="bv-card rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="space-y-2">
            @forelse($notifications as $notification)
                <a href="{{ route('notifications.open', $notification) }}" data-notification-read="{{ route('notifications.read', $notification) }}" class="bv-card flex items-start gap-3 rounded-2xl p-4 transition hover:bg-rose-50/50 {{ $notification->isUnread() ? 'ring-1 ring-rose-200/80' : '' }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-rose-100 text-sm">
                        @if($notification->actor->profile_photo_path)
                            <img src="{{ asset('storage/' . $notification->actor->profile_photo_path) }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ $notification->type === \App\Models\Notification::TYPE_POST_LIKE ? '❤️' : '👤' }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800">{{ $notification->message() }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if($notification->isUnread())
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
                    @endif
                </a>
            @empty
                <div class="bv-card rounded-2xl p-10 text-center text-sm text-slate-400">
                    Henüz bildirimin yok.
                </div>
            @endforelse
        </div>

        <div>{{ $notifications->links() }}</div>
    </main>

</body>
</html>
