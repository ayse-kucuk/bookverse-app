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

<span class="relative flex shrink-0 items-center justify-center border border-[#e8e4de] bg-[#f9f8f6] transition duration-300 group-hover:border-[#c4a574] {{ $iconSize }}">
    <svg class="h-[55%] w-[55%]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M5 4.5C5 3.67 5.67 3 6.5 3H10v18H6.5C5.67 21 5 20.33 5 19.5V4.5Z" fill="#1c1c1c"/>
        <path d="M10 3h2c.55 0 1 .45 1 1v17c0 .55-.45 1-1 1h-2V3Z" fill="#a67c52" fill-opacity="0.35"/>
        <path d="M12 4.5c0-.83.67-1.5 1.5-1.5H18c.83 0 1.5.67 1.5 1.5v14c0 .83-.67 1.5-1.5 1.5h-4.5c-.83 0-1.5-.67-1.5-1.5V4.5Z" fill="#ffffff" stroke="#e8e4de"/>
        <path d="M13.5 8h4.5M13.5 11h4M13.5 14h4.5M13.5 17h3.5" stroke="#a67c52" stroke-width="0.6" stroke-linecap="round" stroke-opacity="0.7"/>
    </svg>
</span>
