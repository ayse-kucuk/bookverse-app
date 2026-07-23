<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- 2FA opt-in -->
        <div class="mt-4 rounded-md border border-gray-200 bg-gray-50 p-3">
            <label for="enable_two_factor" class="inline-flex items-start gap-2">
                <input
                    id="enable_two_factor"
                    type="checkbox"
                    name="enable_two_factor"
                    value="1"
                    class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    @checked(old('enable_two_factor'))
                >
                <span>
                    <span class="block text-sm font-medium text-gray-800">{{ __('Çift aşamalı doğrulama (2FA) kurulsun') }}</span>
                    <span class="mt-0.5 block text-xs text-gray-600">{{ __('Kayıttan sonra Authenticator uygulamasıyla kurulumu tamamlayabilirsin. İstersen şimdi atlayıp sonra da açabilirsin.') }}</span>
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Zaten kayıtlı mısınız?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Kayıt ol') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
