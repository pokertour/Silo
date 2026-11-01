<x-layouts.guest :title="$share->isoFile->name">
    <x-slot:meta>
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $share->isoFile->name }}">
        <meta property="og:description" content="{{ __('app.Télécharger') }} · {{ $share->isoFile->size_human }}{{ $share->isoFile->version ? ' · v'.$share->isoFile->version : '' }}">
        <meta property="og:site_name" content="{{ \App\Models\SystemSetting::get('app_name', 'Silo') }}">
        <meta name="twitter:card" content="summary">
        <meta name="robots" content="noindex">
    </x-slot:meta>
    <div class="w-full max-w-lg glass rounded-3xl p-8 shadow-2xl relative overflow-hidden text-center">
        <div class="flex justify-center mb-6">
            <div class="bg-indigo-600 p-4 rounded-3xl shadow-lg shadow-indigo-500/20">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </div>
        </div>
        <h1 class="text-2xl font-bold mb-1">{{ $share->isoFile->name }}</h1>
        <div class="flex items-center justify-center gap-2 text-slate-500 text-sm mb-8">
            <span>{{ $share->isoFile->size_human }}</span>
            @if($share->isoFile->version)
            <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
            <span>{{ $share->isoFile->version }}</span>
            @endif
        </div>

        @if($share->isoFile->checksum)
            <div class="mb-8 text-left bg-black/20 rounded-2xl p-4" x-data="{ copied: false }">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-widest">SHA-256</span>
                    <button @click="navigator.clipboard.writeText('{{ $share->isoFile->checksum }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs text-slate-500 hover:text-indigo-400 transition-colors cursor-pointer flex items-center gap-1">
                        <span x-show="!copied">{{ __('app.Copier') }}</span>
                        <span x-show="copied" x-cloak class="text-emerald-400">{{ __('app.Copié !') }}</span>
                    </button>
                </div>
                <code class="text-xs text-slate-400 break-all select-all">{{ $share->isoFile->checksum }}</code>
            </div>
        @endif

        @if($share->isoFile->description)
            <p class="text-slate-400 text-sm mb-10 bg-black/20 p-4 rounded-2xl italic">"{{ $share->isoFile->description }}"</p>
        @endif

        <button @click.prevent="$dispatch('start-mega-download', { url: @js(route('share.download', $share->token)), filename: @js($share->isoFile->name.'.'.pathinfo($share->isoFile->file_path, PATHINFO_EXTENSION)) })" class="cursor-pointer w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-500/20 transition-all flex items-center justify-center gap-2 text-lg">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            {{ __('app.Télécharger') }}
        </button>

        <div class="mt-10 pt-6 border-t border-white/5 flex items-center justify-center gap-6 text-xs text-slate-500 font-medium uppercase tracking-widest">
            <div class="flex items-center gap-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                Silo
            </div>
            @if($share->expires_at)
            <div class="flex items-center gap-1 text-amber-500/60">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                {{ __('app.Expiration') }} {{ $share->expires_at->diffForHumans() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.guest>
