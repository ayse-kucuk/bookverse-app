@php
    $profileUser = $profileUser ?? $user ?? null;
    $isOwnProfile = $isOwnProfile ?? true;
    $goal = $profileUser?->readingGoalStats() ?? [];
    $year = $goal['year'] ?? now()->year;
@endphp

@if($profileUser)
    <section class="bv-card h-full w-full p-5 sm:p-6">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9a948d]">{{ __('ui.goal.title') }}</p>
                <h2 class="mt-0.5 text-lg font-semibold text-[#1c1c1c]">{{ $year }}</h2>
            </div>
            @if($goal['completed'] ?? false)
                <span class="border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700">{{ __('ui.goal.completed') }}</span>
            @endif
        </div>

        @if($goal['target'])
            <div class="space-y-3">
                <div class="flex items-end justify-between gap-2">
                    <div>
                        <span class="bv-display text-3xl font-light text-[#1c1c1c] sm:text-4xl">{{ $goal['current'] }}</span>
                        <span class="ml-1 text-sm text-[#9a948d]">{{ __('ui.goal.books', ['count' => $goal['target']]) }}</span>
                    </div>
                    <span class="text-sm font-bold text-bv-accent">%{{ $goal['percentage'] }}</span>
                </div>

                <div class="h-px w-full bg-[#e8e4de]">
                    <div class="h-px bg-[#a67c52] transition-all duration-700" style="width: {{ $goal['percentage'] }}%"></div>
                </div>

                <p class="text-xs text-[#9a948d]">
                    @if($goal['completed'])
                        {{ __('ui.goal.congrats', ['year' => $year]) }}
                    @else
                        {{ __('ui.goal.remaining', ['count' => $goal['remaining']]) }}
                    @endif
                </p>
            </div>
        @else
            <div class="border border-dashed border-[#e8e4de] bg-[#f9f8f6] px-4 py-6 text-center">
                <p class="text-sm text-[#6b6560]">
                    @if($isOwnProfile)
                        {{ __('ui.goal.none_own') }}
                    @else
                        {{ __('ui.goal.none_other', ['name' => $profileUser->name]) }}
                    @endif
                </p>
                @if($isOwnProfile)
                    <p class="mt-1 text-xs text-[#9a948d]">{{ __('ui.goal.set_below') }}</p>
                @endif
            </div>

            @if(($goal['current'] ?? 0) > 0)
                <p class="mt-3 text-xs text-[#9a948d]">{{ __('ui.goal.progress_so_far', ['year' => $year, 'count' => $goal['current']]) }}</p>
            @endif
        @endif

        @if($isOwnProfile)
            <form action="{{ route('reading-goal.update') }}" method="POST" class="mt-5 flex flex-wrap items-end gap-3 border-t border-[#f0ece6] pt-5">
                @csrf
                <div class="min-w-[8rem] flex-1">
                    <label for="reading-goal-input" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-[#9a948d]">{{ __('ui.goal.label', ['year' => $year]) }}</label>
                    <input
                        id="reading-goal-input"
                        type="number"
                        name="reading_goal"
                        min="1"
                        max="365"
                        value="{{ old('reading_goal', $profileUser->hasActiveReadingGoal() ? $profileUser->reading_goal : '') }}"
                        placeholder="{{ __('ui.goal.placeholder') }}"
                        class="bv-input w-full border border-[#e8e4de] bg-white px-3 py-2 text-sm"
                    >
                </div>
                <button type="submit" class="bv-btn px-5 py-2 text-xs font-bold uppercase tracking-wider">
                    {{ $profileUser->hasActiveReadingGoal() ? __('ui.goal.update') : __('ui.goal.set') }}
                </button>
            </form>

            @if($profileUser->hasActiveReadingGoal())
                <form action="{{ route('reading-goal.destroy') }}" method="POST" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-[10px] font-semibold uppercase tracking-wider text-[#9a948d] transition hover:text-[#1c1c1c]">{{ __('ui.goal.remove') }}</button>
                </form>
            @endif
        @endif
    </section>
@endif
