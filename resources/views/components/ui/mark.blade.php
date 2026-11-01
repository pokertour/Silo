{{-- Icône d'app Silo : carré arrondi en dégradé (suit la couleur d'accent) et silo blanc. --}}
@props(['size' => 32])
@php $gid = 'silo-mark-'.\Illuminate\Support\Str::random(6); @endphp
<svg {{ $attributes->merge(['class' => 'shrink-0']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64" fill="none" aria-hidden="true">
    <defs>
        <linearGradient id="{{ $gid }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" style="stop-color: color-mix(in srgb, var(--accent) 55%, #ffffff)"/>
            <stop offset="1" style="stop-color: var(--accent)"/>
        </linearGradient>
    </defs>
    <rect width="64" height="64" rx="15" fill="url(#{{ $gid }})"/>
    <path d="M17 23v19c0 3.9 6.7 7 15 7s15-3.1 15-7V23" stroke="#fff" stroke-width="3.6" stroke-linejoin="round"/>
    <path d="M17 32.5c0 3.9 6.7 7 15 7s15-3.1 15-7" stroke="#fff" stroke-opacity=".55" stroke-width="3"/>
    <ellipse cx="32" cy="23" rx="15" ry="6.5" fill="#fff"/>
</svg>
