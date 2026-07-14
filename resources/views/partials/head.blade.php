<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Bookverse' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<style>
    :root {
        --bv-primary: #db2777;
        --bv-primary-dark: #be185d;
        --bv-accent: #f59e0b;
        --bv-warm: #fb923c;
    }

    body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

    .bv-mesh {
        background-color: #f9f7f8;
        background-image:
            radial-gradient(ellipse 80% 60% at 8% -8%, rgba(219, 39, 119, 0.08), transparent),
            radial-gradient(ellipse 55% 45% at 96% 6%, rgba(251, 146, 60, 0.07), transparent),
            radial-gradient(ellipse 65% 50% at 50% 108%, rgba(244, 114, 182, 0.06), transparent);
    }

    details summary::-webkit-details-marker { display: none; }

    .bv-card {
        background: #ffffff;
        border: 1px solid #f1e8ec;
        box-shadow: 0 4px 24px -4px rgba(15, 23, 42, 0.06), 0 0 0 1px rgba(219, 39, 119, 0.04);
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }
    .bv-surface-matte {
        background: #ffffff;
        border: 1px solid #e8dfe3;
        box-shadow: 0 16px 48px -12px rgba(15, 23, 42, 0.14);
    }
    .bv-card-interactive:hover {
        box-shadow: 0 16px 48px -12px rgba(219, 39, 119, 0.14), 0 0 0 1px rgba(251, 146, 60, 0.08);
        transform: translateY(-2px);
    }

    .bv-btn {
        background: linear-gradient(135deg, #e11d48 0%, #db2777 45%, #ea580c 100%);
        background-size: 200% 200%;
        box-shadow: 0 4px 14px -2px rgba(219, 39, 119, 0.4);
        transition: all 0.25s ease;
    }
    .bv-btn:hover {
        background-position: 100% 0;
        box-shadow: 0 8px 24px -4px rgba(234, 88, 12, 0.4);
        transform: translateY(-2px);
    }
    .bv-btn:active { transform: translateY(0); }

    .bv-input:focus {
        outline: none;
        border-color: #db2777;
        box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12);
    }

    .bv-gradient-text {
        background: linear-gradient(135deg, #be185d 0%, #db2777 45%, #ea580c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .bv-nav-active {
        background: rgba(219, 39, 119, 0.1);
        color: #be185d;
    }

    @media (max-width: 639px) {
        body { padding-bottom: 4.75rem; }
    }

    /* Photo lightbox */
    .bv-photo-lightbox {
        animation: bv-backdrop-in 0.3s ease both;
    }
    .bv-photo-lightbox-img {
        animation: bv-photo-zoom-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .bv-photo-trigger {
        cursor: zoom-in;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .bv-photo-trigger:hover {
        transform: scale(1.04);
        box-shadow: 0 8px 24px -4px rgba(219, 39, 119, 0.35);
    }

    /* Animations */
    @keyframes bv-fade-up {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes bv-nav-slide {
        from { opacity: 0; transform: translateY(-16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes bv-logo-float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50%      { transform: translateY(-2px) rotate(-1deg); }
    }
    @keyframes bv-backdrop-in {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes bv-photo-zoom-in {
        from { opacity: 0; transform: scale(0.8); }
        to   { opacity: 1; transform: scale(1); }
    }

    .bv-animate-nav {
        animation: bv-nav-slide 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .bv-animate-up {
        animation: bv-fade-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .bv-animate-up-delay-1 { animation: bv-fade-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.1s both; }
    .bv-animate-up-delay-2 { animation: bv-fade-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.2s both; }
    .bv-animate-up-delay-3 { animation: bv-fade-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.3s both; }

    .bv-stagger > * {
        animation: bv-fade-up 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .bv-stagger > *:nth-child(1)  { animation-delay: 0.04s; }
    .bv-stagger > *:nth-child(2)  { animation-delay: 0.08s; }
    .bv-stagger > *:nth-child(3)  { animation-delay: 0.12s; }
    .bv-stagger > *:nth-child(4)  { animation-delay: 0.16s; }
    .bv-stagger > *:nth-child(5)  { animation-delay: 0.20s; }
    .bv-stagger > *:nth-child(6)  { animation-delay: 0.24s; }
    .bv-stagger > *:nth-child(7)  { animation-delay: 0.28s; }
    .bv-stagger > *:nth-child(8)  { animation-delay: 0.32s; }
    .bv-stagger > *:nth-child(9)  { animation-delay: 0.36s; }
    .bv-stagger > *:nth-child(10) { animation-delay: 0.40s; }
    .bv-stagger > *:nth-child(11) { animation-delay: 0.44s; }
    .bv-stagger > *:nth-child(12) { animation-delay: 0.48s; }

    .bv-logo-icon {
        animation: bv-logo-float 5s ease-in-out infinite;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.15));
    }
    .group:hover .bv-logo-icon {
        animation-play-state: paused;
    }

    @media (prefers-reduced-motion: reduce) {
        .bv-animate-nav, .bv-animate-up, .bv-animate-up-delay-1,
        .bv-animate-up-delay-2, .bv-animate-up-delay-3,
        .bv-stagger > *, .bv-logo-icon,
        .bv-photo-lightbox, .bv-photo-lightbox-img {
            animation: none !important;
        }
        .bv-card-interactive:hover, .bv-btn:hover, .bv-photo-trigger:hover { transform: none; }
    }
</style>
