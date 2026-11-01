{{-- Navigation mobile : barre supérieure, tiroir et barre d'onglets. --}}
<div x-data="{ drawer: false }" x-on:livewire:navigating.window="drawer = false" class="lg:hidden">
    <header class="sticky top-0 z-40 flex h-14 items-center gap-1 border-b border-line bg-bg/85 px-3 backdrop-blur-xl" style="padding-top: env(safe-area-inset-top)">
        <button type="button" class="icon-btn" @click="drawer = true" aria-label="{{ __('app.Menu') }}"><i class="ri-menu-2-line"></i></button>
        <a href="{{ route('dashboard') }}" wire:navigate class="mr-auto ml-1"><x-ui.logo :size="26" /></a>
        <button type="button" class="icon-btn" @click="$dispatch('open-palette')" aria-label="{{ __('app.Rechercher…') }}"><i class="ri-search-line"></i></button>
        @livewire('notifications-dropdown', [], key('notifications-mobile'))
    </header>

    {{-- Tiroir --}}
    <div x-show="drawer" x-cloak class="fixed inset-0 z-50">
        <div x-show="drawer" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="drawer = false"></div>
        <aside x-show="drawer" x-trap.noscroll="drawer" @keydown.escape.window="drawer = false"
               x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-x-full"
               x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-x-full"
               class="absolute inset-y-0 left-0 flex w-[86%] max-w-[320px] flex-col border-r border-line bg-bg-2">
            <div class="flex h-14 items-center justify-between px-4">
                <x-ui.logo :size="26" />
<div class="flex items-center gap-1">
                    <button type="button" class="icon-btn" @click="$store.theme.toggle()" :title="$store.theme.current === 'light' ? @js(__('app.Thème sombre')) : @js(__('app.Thème clair'))" aria-label="{{ __('app.Changer de thème') }}">
            <i :class="$store.theme.current === 'light' ? 'ri-moon-clear-line' : 'ri-sun-line'"></i>
        </button>
                    <button type="button" class="icon-btn" @click="drawer = false" aria-label="{{ __('app.Fermer') }}"><i class="ri-close-line"></i></button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-4"><x-nav-links /></div>
            <div class="space-y-3 border-t border-line p-4" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom))">
                <x-user-card />
            </div>
        </aside>
    </div>

    {{-- Barre d'onglets --}}
    @php
        $tabs = [
            ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'ri-dashboard-3-line', 'label' => __('app.Accueil')],
            ['route' => 'isos', 'active' => 'isos*', 'icon' => 'ri-album-line', 'label' => __('app.Bibliothèque')],
            ['route' => 'upload', 'active' => 'upload', 'icon' => 'ri-add-line', 'label' => __('app.Ajouter'), 'primary' => true],
            ['route' => 'share-links', 'active' => 'share-links', 'icon' => 'ri-links-line', 'label' => __('app.Partages')],
            ['route' => 'profile', 'active' => 'profile', 'icon' => 'ri-user-3-line', 'label' => __('app.Profil')],
        ];
    @endphp
    <nav class="fixed inset-x-3 bottom-3 z-40 grid grid-cols-5 items-center rounded-2xl border border-line-strong bg-elevated/90 px-1 py-1.5 shadow-2xl backdrop-blur-xl"
         style="margin-bottom: env(safe-area-inset-bottom)" aria-label="{{ __('app.Navigation') }}">
        @foreach ($tabs as $tab)
            @php $active = request()->routeIs($tab['active']); @endphp
            <a href="{{ route($tab['route']) }}" wire:navigate class="flex flex-col items-center gap-0.5 py-1 text-[10.5px] font-medium {{ $active ? 'text-ink' : 'text-ink-3' }}">
                @if ($tab['primary'] ?? false)
                    <span class="-mt-5 grid size-12 place-items-center rounded-2xl bg-accent text-2xl text-on-accent shadow-lg ring-4 ring-bg"><i class="{{ $tab['icon'] }}"></i></span>
                @else
                    <i class="{{ $tab['icon'] }} text-[22px] leading-none {{ $active ? 'text-accent-ink' : '' }}"></i>
                @endif
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
