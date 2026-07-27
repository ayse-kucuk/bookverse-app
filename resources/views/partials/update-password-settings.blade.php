<section class="bv-card bv-animate-up mt-6 p-5 sm:p-7 sm:p-8">
    <div class="mb-6 border-b border-[#f0ece6] pb-5">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">{{ __('ui.settings.security') }}</p>
        <h2 class="mt-1 text-xl font-semibold text-[#1c1c1c]">{{ __('ui.settings.password_title') }}</h2>
        <p class="mt-1 text-sm text-[#6b6560]">{{ __('ui.settings.password_lead') }}</p>
    </div>

    <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="current_password">
                {{ __('ui.settings.current_password') }}
            </label>
            <input
                id="current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                required
                class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm transition"
            >
            @if ($errors->updatePassword->has('current_password'))
                <p class="mt-1 text-xs text-bv-accent">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="password">
                {{ __('ui.settings.new_password') }}
            </label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                required
                class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm transition"
            >
            @if ($errors->updatePassword->has('password'))
                <p class="mt-1 text-xs text-bv-accent">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]" for="password_confirmation">
                {{ __('ui.settings.confirm_password') }}
            </label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                class="bv-input w-full border border-[#e8e4de] bg-white px-4 py-3 text-sm transition"
            >
            @if ($errors->updatePassword->has('password_confirmation'))
                <p class="mt-1 text-xs text-bv-accent">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4 border-t border-[#f0ece6] pt-5">
            <button type="submit" class="bv-btn px-7 py-3 text-xs font-bold uppercase tracking-wider">
                {{ __('ui.settings.password_save') }}
            </button>
            @if (session('status') === 'password-updated')
                <p class="text-xs font-semibold text-emerald-700">{{ __('ui.settings.password_updated') }}</p>
            @endif
        </div>
    </form>
</section>
