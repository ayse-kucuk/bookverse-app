@php
    $current = (int) ($current ?? 0);
    $bookId = $bookId ?? null;
@endphp

<div class="space-y-2" data-rating-widget data-url="{{ route('books.rating.update', $bookId) }}" data-current="{{ $current }}">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1" role="group" aria-label="Kitap puanı">
            @for ($i = 1; $i <= 5; $i++)
                <button
                    type="button"
                    data-rating-star
                    data-value="{{ $i }}"
                    class="bv-star-btn text-2xl leading-none transition duration-150 {{ $current >= $i ? 'text-amber-400' : 'text-slate-200 hover:text-amber-300' }}"
                    title="{{ $i }} yıldız"
                >★</button>
            @endfor
        </div>
        <span class="bv-rating-label text-xs font-semibold text-slate-500" data-rating-label>
            @if ($current)
                Senin puanın: <span class="text-amber-600">{{ $current }}/5</span>
            @else
                <span class="text-slate-400">Puanlamak için yıldıza tıkla</span>
            @endif
        </span>
    </div>
</div>
