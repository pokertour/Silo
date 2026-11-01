{{-- Barre latérale (desktop), façon macOS. --}}
@php $usage = \App\Support\Navigation::libraryUsage(auth()->user()); @endphp
<aside class="fixed inset-y-0 left-0 z-40 hidden w-[250px] flex-col border-r border-line bg-bg-2 backdrop-blur-2xl backdrop-saturate-150 lg:flex">
    <div class="flex h-16 items-center justify-between pr-3 pl-5">
        <a href="{{ route('dashboard') }}" wire:navigate><x-ui.logo :size="28" /></a>
        <button type="button" class="icon-btn" @click="$store.theme.toggle()" :title="$store.theme.current === 'light' ? @js(__('app.Thème sombre')) : @js(__('app.Thème clair'))" aria-label="{{ __('app.Changer de thème') }}">
            <i :class="$store.theme.current === 'light' ? 'ri-moon-line' : 'ri-sun-line'"></i>
        </button>
    </div>

    <div class="px-3">
        <button type="button" @click="$dispatch('open-palette')"
                class="flex h-8 w-full items-center gap-2 rounded-lg bg-surface-2 px-2.5 text-[13px] text-ink-3 transition hover:text-ink-2">
            <i class="ri-search-line text-[15px]"></i>
            <span class="flex-1 text-left">{{ __('app.Rechercher…') }}</span>
            <span class="text-[11px]">⌘K</span>
        </button>
    </div>

    <div class="mt-4 flex-1 overflow-y-auto px-3 pb-4">
        <x-nav-links />
    </div>

    <div class="border-t border-line p-3">
        <p class="mb-2 px-2.5 text-xs text-ink-3">
            {{ trans_choice('app.:count image disque|:count images disque', $usage['count'], ['count' => $usage['count']]) }}
            · {{ \Illuminate\Support\Number::fileSize($usage['size'], precision: 1) }}
        </p>
        <x-user-card notifications-key="notifications-sidebar" />
    </div>
</aside>
