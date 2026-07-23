<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Çift aşamalı doğrulama') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            @if($fromRegistration)
                {{ __('Hesabını oluştururken 2FA’yı seçtin. Authenticator uygulamanla QR kodu tara veya secret anahtarı gir, ardından 6 haneli kodu onayla.') }}
            @else
                {{ __('Authenticator uygulamanla QR kodu tara ve 6 haneli kodu gir.') }}
            @endif
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(!empty($recoveryCodes))
        <div class="space-y-4">
            <p class="text-sm font-semibold text-gray-900">{{ __('Kurtarma kodlarını kaydet') }}</p>
            <p class="text-xs text-gray-600">{{ __('Bu kodlar yalnızca bir kez gösterilir. Authenticator’a erişemezsen bunlarla giriş yapabilirsin.') }}</p>
            <ul class="grid grid-cols-2 gap-2 font-mono text-xs">
                @foreach($recoveryCodes as $code)
                    <li class="rounded-md bg-gray-50 px-2 py-2 text-center ring-1 ring-gray-200">{{ $code }}</li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('two-factor.setup.finish') }}">
                @csrf
                <x-primary-button class="w-full justify-center">
                    {{ __('Tamamladım, devam et') }}
                </x-primary-button>
            </form>
        </div>
    @else
        <div class="mb-4 flex justify-center rounded-lg bg-white p-4 ring-1 ring-gray-200">
            {!! $qrSvg !!}
        </div>
        <p class="mb-4 break-all text-center font-mono text-xs text-gray-600">{{ $secret }}</p>

        <form method="POST" action="{{ route('two-factor.setup.confirm') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="code" :value="__('6 haneli doğrulama kodu')" />
                <x-text-input
                    id="code"
                    class="mt-1 block w-full tracking-widest"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end">
                <x-primary-button>
                    {{ __('Doğrula ve aktifleştir') }}
                </x-primary-button>
            </div>
        </form>

        @if($fromRegistration)
            <form method="POST" action="{{ route('two-factor.setup.skip') }}" class="mt-3 text-center">
                @csrf
                <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('Şimdilik geç') }}
                </button>
            </form>
        @else
            <div class="mt-3 text-center">
                <a href="{{ route('account.settings') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('İptal') }}
                </a>
            </div>
        @endif
    @endif
</x-guest-layout>
