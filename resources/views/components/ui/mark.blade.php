{{-- Monogramme Silo : une pile de disques formant un silo. Suit la couleur d'accent. --}}
@props(['size' => 32])
<svg {{ $attributes->merge(['class' => 'shrink-0']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64" fill="none" aria-hidden="true">
    <path d="M11 17v30c0 4.97 9.4 9 21 9s21-4.03 21-9V17" fill="var(--surface-2)" stroke="var(--accent)" stroke-width="3.2" stroke-linejoin="round"/>
    <path d="M11 27.5c0 4.97 9.4 9 21 9s21-4.03 21-9M11 37.5c0 4.97 9.4 9 21 9s21-4.03 21-9" stroke="var(--accent)" stroke-opacity=".45" stroke-width="2.6"/>
    <ellipse cx="32" cy="17" rx="21" ry="9" fill="var(--accent)"/>
    <ellipse cx="32" cy="17" rx="6.5" ry="2.6" fill="var(--surface-2)"/>
</svg>
