<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <x-ui.head :title="$title ?? null" />
</head>
<body class="min-h-dvh">
    <div class="pointer-events-none fixed inset-0 -z-10 backdrop-dots [mask-image:linear-gradient(to_bottom,black,transparent_75%)]" aria-hidden="true"></div>
    <div class="halo pointer-events-none fixed inset-0 -z-10" aria-hidden="true"></div>

    <x-sidebar />
    <x-mobile-nav />

    <main class="relative lg:pl-[264px]">
        <div class="mx-auto w-full max-w-[1360px] px-4 pt-6 pb-32 sm:px-6 lg:px-10 lg:pt-12 lg:pb-16">
            {{ $slot }}
        </div>
    </main>

    <x-command-palette />
    @livewire('wire-elements-modal')
    <x-mega-downloader />
    <x-ui.overlays />
</body>
</html>
