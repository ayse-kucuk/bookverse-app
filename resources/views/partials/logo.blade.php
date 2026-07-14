@php
    $size = $size ?? 'md';
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-9 w-9',
        'lg' => 'h-11 w-11',
        'xl' => 'h-14 w-14',
    ];
    $iconSize = $sizes[$size] ?? $sizes['md'];
@endphp

<span class="bv-logo-wrap relative flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 via-pink-500 to-orange-400 shadow-lg shadow-rose-500/30 ring-1 ring-white/20 transition duration-300 group-hover:scale-105 group-hover:shadow-rose-500/45 {{ $iconSize }}">
    <svg class="bv-logo-icon h-[58%] w-[58%]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <linearGradient id="bv-book-spine-{{ $size }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95"/>
                <stop offset="100%" stop-color="#ffe4e6" stop-opacity="0.9"/>
            </linearGradient>
            <linearGradient id="bv-book-page-{{ $size }}" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.75"/>
                <stop offset="100%" stop-color="#ffffff" stop-opacity="0.95"/>
            </linearGradient>
        </defs>
        <path d="M4 5.5C4 4.67 4.67 4 5.5 4H9v16H5.5C4.67 20 4 19.33 4 18.5V5.5Z" fill="url(#bv-book-spine-{{ $size }})"/>
        <path d="M9 4h2.5c.83 0 1.5.67 1.5 1.5v14c0 .83-.67 1.5-1.5 1.5H9V4Z" fill="white" fill-opacity="0.35"/>
        <path d="M12 5.5c0-.83.67-1.5 1.5-1.5H18.5c.83 0 1.5.67 1.5 1.5v13c0 .83-.67 1.5-1.5 1.5H13.5c-.83 0-1.5-.67-1.5-1.5V5.5Z" fill="url(#bv-book-page-{{ $size }})"/>
        <path d="M13 8h5M13 11h4.5M13 14h5M13 17h3.5" stroke="#be185d" stroke-width="0.7" stroke-linecap="round" stroke-opacity="0.45"/>
        <circle cx="18" cy="6.5" r="1.2" fill="#fbbf24" fill-opacity="0.95"/>
    </svg>
    <span class="bv-logo-glow pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-rose-400/30 to-orange-400/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
</span>
