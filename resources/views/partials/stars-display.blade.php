@php
    $value = isset($value) ? (float) $value : 0;
    $max = $max ?? 5;
    $size = $size ?? 'md';
    $sizes = [
        'sm' => 'text-sm',
        'md' => 'text-lg',
        'lg' => 'text-2xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span class="inline-flex items-center gap-0.5 {{ $sizeClass }}" aria-label="{{ $value }} / {{ $max }} yıldız">
    @for ($i = 1; $i <= $max; $i++)
        @if ($value >= $i)
            <span class="text-amber-400">★</span>
        @elseif ($value >= $i - 0.5)
            <span class="text-amber-400">⯨</span>
        @else
            <span class="text-slate-200">★</span>
        @endif
    @endfor
</span>
