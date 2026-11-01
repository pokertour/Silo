<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-ui.head :title="$title ?? null" />
</head>
<body x-data class="min-h-dvh">

    <x-sidebar />
    <x-mobile-nav />

    <main class="relative lg:pl-[250px]">
        <div class="mx-auto w-full max-w-[1320px] px-4 pt-6 pb-32 sm:px-6 lg:px-12 lg:pt-11 lg:pb-16">
            {{ $slot }}
        </div>
    </main>

    <x-command-palette />
    @livewire('wire-elements-modal')
    <x-mega-downloader />
    <x-ui.overlays />
</body>
</html>
