<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-ui.head :title="$title ?? null">{{ $meta ?? '' }}</x-ui.head>
    @livewireStyles
</head>
<body x-data class="min-h-dvh">

    <div class="flex min-h-dvh flex-col">
        <header class="flex items-center justify-between px-5 py-5 sm:px-8">
            <x-ui.logo :size="28" />
            <div class="flex items-center gap-1">
                <x-lang-switcher />
                <button type="button" class="icon-btn" @click="$store.theme.toggle()" aria-label="{{ __('app.Changer de thème') }}">
                    <i :class="$store.theme.current === 'light' ? 'ri-moon-clear-line' : 'ri-sun-line'"></i>
                </button>
            </div>
        </header>
        <main class="flex flex-1 items-center justify-center px-4 py-8">
            {{ $slot }}
        </main>
        <footer class="px-8 py-6 text-center text-xs text-ink-3">
            {{ \App\Models\SystemSetting::get('app_name', 'Silo') }} · {{ __('app.Coffre-fort d\'images disque') }}
        </footer>
    </div>

    <x-mega-downloader />
    <x-ui.overlays />
    @livewireScripts
</body>
</html>
