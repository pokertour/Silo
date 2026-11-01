@php
    $file = $share->isoFile;
    $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
@endphp
<x-layouts.guest :title="$file->name">
    <x-slot:meta>
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $file->name }}">
        <meta property="og:description" content="{{ __('app.Télécharger') }} · {{ $file->size_human }}{{ $file->version ? ' · v'.$file->version : '' }}">
        <meta property="og:site_name" content="{{ \App\Models\SystemSetting::get('app_name', 'Silo') }}">
        <meta name="twitter:card" content="summary">
        <meta name="robots" content="noindex">
    </x-slot:meta>

    <div class="w-full max-w-xl animate-rise">
        <div class="card overflow-hidden">
            <div class="relative flex flex-col items-center px-6 pt-10 pb-8 text-center sm:px-10">
                <div class="pointer-events-none absolute inset-0 backdrop-dots opacity-70 [mask-image:radial-gradient(circle_at_50%_30%,black,transparent_70%)]"></div>
                <x-ui.disc :file="$file" size="xl" class="relative animate-spin-slow" />
                <p class="eyebrow relative mt-7">{{ __('app.Fichier partagé avec vous') }}</p>
                <h1 class="relative mt-2 text-balance font-display text-3xl font-semibold tracking-tight text-ink">{{ $file->name }}</h1>
                <p class="relative mt-3 flex flex-wrap justify-center gap-1.5">
                    <span class="badge">{{ $file->size_human }}</span>
                    @if ($extension)<span class="badge">{{ $extension }}</span>@endif
                    @if ($file->version)<span class="badge">v{{ $file->version }}</span>@endif
                    @if ($file->architecture)<span class="badge">{{ $file->architecture }}</span>@endif
                </p>
                @if ($file->description)
                    <p class="relative mt-5 max-w-md text-[15px] text-ink-2">{{ $file->description }}</p>
                @endif
            </div>

            @if ($file->checksum)
                <div class="mx-6 mb-6 rounded-2xl border border-line bg-bg-2 p-4 sm:mx-10" x-data="copyable(@js($file->checksum))">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="spec"><i class="ri-fingerprint-line"></i> SHA-256</span>
                        <button type="button" class="btn btn-ghost btn-sm -mr-2" @click="copy()">
                            <i :class="copied ? 'ri-check-line text-ok' : 'ri-file-copy-line'"></i><span x-text="copied ? @js(__('app.Copié !')) : @js(__('app.Copier'))"></span>
                        </button>
                    </div>
                    <code class="block font-mono text-[12.5px] leading-relaxed break-all text-ink-2 select-all">{{ $file->checksum }}</code>
                </div>
            @endif

            <div class="border-t border-line bg-surface-2/50 p-6 sm:px-10">
                <button type="button" class="btn btn-primary btn-lg w-full"
                        @click.prevent="$dispatch('start-mega-download', { url: @js(route('share.download', $share->token)), filename: @js($file->name.'.'.$extension) })">
                    <i class="ri-download-2-line"></i>{{ __('app.Télécharger') }}
                </button>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-1 text-xs text-ink-3">
                    @if ($share->expires_at)
                        <span><i class="ri-time-line"></i> {{ __('app.Expiration') }} {{ $share->expires_at->diffForHumans() }}</span>
                    @endif
                    @if ($share->max_downloads)
                        <span><i class="ri-download-2-line"></i> {{ max(0, $share->max_downloads - $share->download_count) }} {{ __('app.téléchargement(s) restant(s)') }}</span>
                    @endif
                    <span><i class="ri-refresh-line"></i> {{ __('app.Reprise des téléchargements interrompus') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
