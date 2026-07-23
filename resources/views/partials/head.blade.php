<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Bookverse' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<style>
    :root {
        --bv-bg: #f9f8f6;
        --bv-bg-warm: #f3f0eb;
        --bv-surface: #ffffff;
        --bv-charcoal: #1c1c1c;
        --bv-text: #2a2a2a;
        --bv-text-muted: #6b6560;
        --bv-border: #e8e4de;
        --bv-border-soft: #f0ece6;
        --bv-accent: #a67c52;
        --bv-accent-dark: #8b6540;
        --bv-accent-light: #c4a574;
        --bv-accent-muted: rgba(166, 124, 82, 0.1);
        --bv-accent-muted-strong: rgba(166, 124, 82, 0.16);
    }

    body {
        font-family: 'DM Sans', system-ui, sans-serif;
        color: var(--bv-text);
    }

    .bv-display {
        font-family: 'Cormorant Garamond', Georgia, serif;
    }

    .bv-mesh {
        background-color: var(--bv-bg);
        background-image:
            radial-gradient(ellipse 70% 50% at 0% 0%, rgba(166, 124, 82, 0.04), transparent 55%),
            radial-gradient(ellipse 50% 40% at 100% 10%, rgba(196, 165, 116, 0.05), transparent 50%);
    }

    details summary::-webkit-details-marker { display: none; }

    .bv-topbar {
        background: var(--bv-charcoal);
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.68rem;
        letter-spacing: 0.06em;
    }

    /* Tam genişlik sayfa kabuğu — minimum kenar boşluğu */
    .bv-page {
        width: 100%;
        max-width: 100%;
        padding-inline: 0.875rem;
        box-sizing: border-box;
    }
    @media (min-width: 640px) {
        .bv-page { padding-inline: 1rem; }
    }
    @media (min-width: 1280px) {
        .bv-page { padding-inline: 1.25rem; }
    }

    html {
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    body {
        overflow-x: hidden;
    }

    /* iOS'ta input zoom'unu engelle */
    @media (max-width: 639px) {
        input.bv-input,
        select.bv-input,
        textarea.bv-input,
        input[type="search"],
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="url"],
        input[type="number"],
        select,
        textarea {
            font-size: 16px !important;
        }
    }

    .bv-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .bv-touch-target {
        min-height: 44px;
        min-width: 44px;
    }

    .bv-card {
        background: var(--bv-surface);
        border: 1px solid var(--bv-border-soft);
        box-shadow: 0 1px 2px rgba(28, 28, 28, 0.03), 0 8px 32px -12px rgba(28, 28, 28, 0.06);
        transition: box-shadow 0.3s ease, transform 0.3s ease, border-color 0.3s ease;
    }

    .bv-surface-matte {
        background: var(--bv-surface);
        border: 1px solid var(--bv-border);
        box-shadow: 0 20px 50px -16px rgba(28, 28, 28, 0.12);
    }

    .bv-card-interactive:hover {
        border-color: var(--bv-border);
        box-shadow: 0 12px 40px -14px rgba(28, 28, 28, 0.1);
        transform: translateY(-1px);
    }

    .bv-btn {
        background: var(--bv-charcoal);
        color: #fff;
        box-shadow: 0 4px 14px -4px rgba(28, 28, 28, 0.35);
        transition: all 0.25s ease;
    }
    .bv-btn:hover {
        background: #2f2f2f;
        box-shadow: 0 8px 24px -6px rgba(28, 28, 28, 0.3);
        transform: translateY(-1px);
    }
    .bv-btn:active { transform: translateY(0); }

    .bv-btn-outline {
        background: transparent;
        color: var(--bv-charcoal);
        border: 1px solid var(--bv-border);
        box-shadow: none;
    }
    .bv-btn-outline:hover {
        background: var(--bv-bg-warm);
        border-color: var(--bv-accent-light);
        color: var(--bv-accent-dark);
        transform: none;
    }

    .bv-input:focus {
        outline: none;
        border-color: var(--bv-accent-light);
        box-shadow: 0 0 0 3px var(--bv-accent-muted);
    }

    .bv-gradient-text,
    .bv-accent-text {
        color: var(--bv-accent);
    }

    .bv-gradient-text {
        font-style: italic;
    }

    .bv-nav-link {
        color: var(--bv-text-muted);
        letter-spacing: 0.14em;
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        transition: color 0.2s ease;
        position: relative;
        padding-bottom: 0.35rem;
    }
    .bv-nav-link:hover { color: var(--bv-charcoal); }
    .bv-nav-link.is-active {
        color: var(--bv-charcoal);
    }
    .bv-nav-link.is-active::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 1px;
        background: var(--bv-charcoal);
    }

    .bv-nav-active {
        color: var(--bv-charcoal) !important;
        background: transparent !important;
    }

    .bv-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 9999px;
        border: 1px solid var(--bv-border);
        background: var(--bv-bg-warm);
        padding: 0.35rem 0.75rem;
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--bv-text-muted);
    }

    .bv-hero-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: clamp(2.5rem, 6vw, 4.25rem);
        line-height: 1.05;
        font-weight: 500;
        color: var(--bv-charcoal);
        letter-spacing: -0.02em;
    }

    .text-bv-accent { color: var(--bv-accent); }
    .text-bv-muted { color: var(--bv-text-muted); }
    .bg-bv-soft { background: var(--bv-accent-muted); }
    .hover\:bg-bv-soft:hover { background: var(--bv-accent-muted); }
    .border-bv { border-color: var(--bv-border); }
    .ring-bv { --tw-ring-color: var(--bv-accent-light); }

    @media (max-width: 639px) {
        body {
            padding-bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px));
        }
        .bv-hero-title {
            font-size: clamp(2rem, 9vw, 2.75rem);
        }
    }

    .bv-photo-lightbox { animation: bv-backdrop-in 0.3s ease both; }
    .bv-photo-lightbox-img { animation: bv-photo-zoom-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .bv-photo-trigger {
        cursor: zoom-in;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .bv-photo-trigger:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 24px -6px rgba(28, 28, 28, 0.15);
    }

    @keyframes bv-fade-up {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes bv-nav-slide {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes bv-backdrop-in {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes bv-photo-zoom-in {
        from { opacity: 0; transform: scale(0.92); }
        to   { opacity: 1; transform: scale(1); }
    }

    .bv-animate-nav { animation: bv-nav-slide 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .bv-animate-up { animation: bv-fade-up 0.55s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .bv-animate-up-delay-1 { animation: bv-fade-up 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.08s both; }
    .bv-animate-up-delay-2 { animation: bv-fade-up 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.16s both; }
    .bv-animate-up-delay-3 { animation: bv-fade-up 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.24s both; }

    .bv-stagger > * { animation: bv-fade-up 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .bv-stagger > *:nth-child(1)  { animation-delay: 0.03s; }
    .bv-stagger > *:nth-child(2)  { animation-delay: 0.06s; }
    .bv-stagger > *:nth-child(3)  { animation-delay: 0.09s; }
    .bv-stagger > *:nth-child(4)  { animation-delay: 0.12s; }
    .bv-stagger > *:nth-child(5)  { animation-delay: 0.15s; }
    .bv-stagger > *:nth-child(6)  { animation-delay: 0.18s; }
    .bv-stagger > *:nth-child(7)  { animation-delay: 0.21s; }
    .bv-stagger > *:nth-child(8)  { animation-delay: 0.24s; }
    .bv-stagger > *:nth-child(9)  { animation-delay: 0.27s; }
    .bv-stagger > *:nth-child(10) { animation-delay: 0.30s; }
    .bv-stagger > *:nth-child(11) { animation-delay: 0.33s; }
    .bv-stagger > *:nth-child(12) { animation-delay: 0.36s; }

    @media (prefers-reduced-motion: reduce) {
        .bv-animate-nav, .bv-animate-up, .bv-animate-up-delay-1,
        .bv-animate-up-delay-2, .bv-animate-up-delay-3,
        .bv-stagger > *, .bv-photo-lightbox, .bv-photo-lightbox-img {
            animation: none !important;
        }
        .bv-card-interactive:hover, .bv-btn:hover, .bv-photo-trigger:hover { transform: none; }
    }

    /* ── Toast bildirimleri ── */
    #bv-toast-container {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        pointer-events: none;
    }
    @media (max-width: 639px) {
        #bv-toast-container {
            bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px));
            left: 1rem;
            right: 1rem;
        }
        .bv-toast {
            min-width: 0;
            max-width: none;
            width: 100%;
        }
    }
    .bv-toast {
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 260px;
        max-width: 380px;
        background: var(--bv-charcoal);
        color: #fff;
        padding: 0.85rem 1rem;
        font-size: 0.8rem;
        font-weight: 500;
        letter-spacing: 0.01em;
        box-shadow: 0 16px 40px -12px rgba(28,28,28,0.35);
        animation: bv-toast-in 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .bv-toast.bv-toast-success .bv-toast-bar { background: #a67c52; }
    .bv-toast.bv-toast-error   .bv-toast-bar { background: #c0392b; }
    .bv-toast.bv-toast-info    .bv-toast-bar { background: #6b6560; }
    .bv-toast-bar {
        width: 3px;
        align-self: stretch;
        border-radius: 2px;
        flex-shrink: 0;
    }
    .bv-toast-close {
        margin-left: auto;
        flex-shrink: 0;
        color: rgba(255,255,255,0.5);
        cursor: pointer;
        font-size: 1rem;
        line-height: 1;
        transition: color 0.15s;
    }
    .bv-toast-close:hover { color: #fff; }
    .bv-toast.bv-toast-out {
        animation: bv-toast-out 0.25s ease forwards;
    }
    @keyframes bv-toast-in {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes bv-toast-out {
        from { opacity: 1; transform: translateY(0); }
        to   { opacity: 0; transform: translateY(8px); }
    }
    @media (prefers-reduced-motion: reduce) {
        .bv-toast, .bv-toast.bv-toast-out { animation: none !important; }
    }
</style>
