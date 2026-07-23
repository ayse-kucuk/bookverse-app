<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Authenticator uygulamanızdaki 6 haneli kodu veya bir kurtarma kodunu girin.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Doğrulama kodu')" />
            <x-text-input
                id="code"
                class="block mt-1 w-full tracking-widest"
                type="text"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('login') }}">
                {{ __('Girişe dön') }}
            </a>

            <x-primary-button>
                {{ __('Doğrula') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
