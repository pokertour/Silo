<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-ui.head :title="$title ?? null">{{ $meta ?? '' }}</x-ui.head>
    @livewireStyles
</head>
<body x-data class="relative min-h-dvh">

    <header class="absolute inset-x-0 top-0 z-10 flex items-center justify-between px-5 py-5 sm:px-8">
        <x-ui.logo :size="28" />
        <div class="flex items-center gap-1">
            <x-lang-switcher />
            <button type="button" class="icon-btn" @click="$store.theme.toggle()" aria-label="{{ __('app.Changer de thème') }}">
                <i :class="$store.theme.current === 'light' ? 'ri-moon-line' : 'ri-sun-line'"></i>
            </button>
        </div>
    </header>

    {{-- Contenu centré sur toute la hauteur de l'écran (en-tête et pied en surimpression) --}}
    <main class="grid min-h-dvh place-items-center px-4 pt-24 pb-20">
        {{ $slot }}
    </main>

    <footer class="absolute inset-x-0 bottom-0 px-8 py-6 text-center text-xs text-ink-3">
        {{ \App\Models\SystemSetting::get('app_name', 'Silo') }} · {{ __('app.Coffre-fort d\'images disque') }}
    </footer>

    <x-mega-downloader />
    <x-ui.overlays />
    @livewireScripts
</body>
</html>
