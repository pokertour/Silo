{{-- Sélecteur de langue compact. --}}
@php
    $locales = ['en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'de' => 'Deutsch'];
    $current = app()->getLocale();
@endphp
<div x-data="{ open: false }" class="relative" @keydown.escape="open = false">
    <button type="button" @click="open = !open" class="btn btn-ghost btn-sm font-mono uppercase" aria-haspopup="true" :aria-expanded="open">
        <i class="ri-translate-2"></i>{{ $current }}
    </button>
    <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
         class="absolute right-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-line-strong bg-elevated p-1 shadow-2xl">
        @foreach ($locales as $code => $label)
            <a href="{{ route('lang.switch', $code) }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm {{ $code === $current ? 'bg-surface-2 text-ink' : 'text-ink-2 hover:bg-surface-2 hover:text-ink' }}">
                {{ $label }}<span class="font-mono text-[10px] text-ink-3 uppercase">{{ $code }}</span>
            </a>
        @endforeach
    </div>
</div>
