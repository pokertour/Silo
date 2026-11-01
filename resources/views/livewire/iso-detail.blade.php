<div class="p-6 lg:p-10 space-y-8">
    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-start justify-between gap-6">
        <div class="min-w-0">
            <a href="{{ route('isos') }}" wire:navigate class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-400 transition-colors mb-3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                {{ __('app.Retour à la liste') }}
            </a>
            <h2 class="text-3xl font-bold break-words">{{ $isoFile->name }}</h2>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                @if($isoFile->group_id)
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold uppercase tracking-wider">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        {{ $isoFile->group->name }}
                    </span>
                @else
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold uppercase tracking-wider">{{ __('app.Public') }}</span>
                @endif
                @if($isoFile->os_type) <span class="px-2.5 py-1 text-[11px] font-medium bg-slate-800 text-slate-300 rounded-md border border-slate-700">{{ $isoFile->os_type }}</span> @endif
                @if($isoFile->version) <span class="px-2.5 py-1 text-[11px] font-medium bg-indigo-500/10 text-indigo-300 rounded-md border border-indigo-500/20">v{{ $isoFile->version }}</span> @endif
                @if($isoFile->architecture) <span class="px-2.5 py-1 text-[11px] font-medium bg-emerald-500/10 text-emerald-300 rounded-md border border-emerald-500/20">{{ $isoFile->architecture }}</span> @endif
                @foreach($isoFile->tags as $tag)
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}30;">{{ $tag->name }}</span>
                @endforeach
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @can('update', $isoFile)
                <button @click.prevent="$dispatch('openModal', { component: 'edit-iso-modal', arguments: { isoId: {{ $isoFile->id }} } })" class="p-2.5 text-slate-400 hover:text-indigo-400 bg-slate-800/50 hover:bg-indigo-500/10 rounded-xl transition-all" title="{{ __('app.Éditer') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                </button>
            @endcan
            @can('delete', $isoFile)
                <button @click.prevent="$dispatch('openModal', { component: 'delete-iso-modal', arguments: { isoId: {{ $isoFile->id }} } })" class="p-2.5 text-slate-400 hover:text-red-400 bg-slate-800/50 hover:bg-red-500/10 rounded-xl transition-all" title="{{ __('app.Supprimer') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            @endcan
            @can('share', $isoFile)
                <button @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $isoFile->id }} } })" class="flex items-center gap-2 px-4 py-2.5 text-indigo-400 hover:text-white bg-indigo-500/10 hover:bg-indigo-500 rounded-xl transition-all font-medium text-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                    {{ __('app.Partager') }}
                </button>
            @endcan
            <button @click.prevent="$dispatch('start-mega-download', { url: @js(route('download', $isoFile->uuid)), filename: @js($isoFile->name.'.'.pathinfo($isoFile->file_path, PATHINFO_EXTENSION)) })" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl transition-all font-medium text-sm cursor-pointer shadow-lg shadow-emerald-500/10">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                {{ __('app.Télécharger') }}
            </button>
        </div>
    </header>

    @if($duplicates->isNotEmpty())
    <div class="glass p-4 rounded-2xl border-amber-500/30 bg-amber-500/5 flex items-start gap-3">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-400 shrink-0 mt-0.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <div class="text-sm">
            <p class="font-semibold text-amber-300">{{ __('app.Doublon détecté') }}</p>
            <p class="text-slate-400 mt-1">{{ __('app.Ce fichier a la même empreinte SHA-256 que :') }}
                @foreach($duplicates as $dup)
                    <a href="{{ route('isos.show', $dup) }}" wire:navigate class="text-indigo-400 hover:underline">{{ $dup->name }}</a>@if(!$loop->last), @endif
                @endforeach
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Infos -->
        <div class="xl:col-span-2 space-y-8">
            <div class="glass p-6 rounded-3xl">
                <h3 class="font-bold text-lg mb-4">{{ __('app.Informations') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500 uppercase text-xs font-bold tracking-wider mb-1">{{ __('app.Taille') }}</dt>
                        <dd class="text-slate-200 font-medium">{{ $isoFile->size_human }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 uppercase text-xs font-bold tracking-wider mb-1">{{ __('app.Téléversé par') }}</dt>
                        <dd class="text-slate-200 font-medium flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-300 ring-2 ring-slate-700/50">{{ strtoupper(substr($isoFile->user->name, 0, 1)) }}</span>
                            {{ $isoFile->user->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 uppercase text-xs font-bold tracking-wider mb-1">{{ __('app.Date d\'ajout') }}</dt>
                        <dd class="text-slate-200 font-medium">{{ $isoFile->created_at->translatedFormat('d F Y, H:i') }}</dd>
                    </div>
                    @if(auth()->user()->hasRole('admin'))
                    <div>
                        <dt class="text-slate-500 uppercase text-xs font-bold tracking-wider mb-1">{{ __('app.Stockage') }}</dt>
                        <dd class="text-slate-200 font-medium font-mono text-xs">{{ $isoFile->disk }} · {{ $isoFile->file_path }}</dd>
                    </div>
                    @endif
                </dl>

                @if($isoFile->description)
                    <p class="mt-6 text-sm text-slate-400 bg-black/20 p-4 rounded-2xl">{{ $isoFile->description }}</p>
                @endif

                <!-- Checksum -->
                <div class="mt-6 text-[11px] text-slate-500 font-mono break-all bg-slate-900/30 p-3 rounded-xl border border-slate-700/50" x-data="{ copied: false }">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-400 font-bold uppercase tracking-wider flex items-center gap-2">
                            SHA-256
                            @if($isoFile->checksum_verified === true)
                                <span class="flex items-center gap-1 text-emerald-400 normal-case" title="{{ __('app.Correspond à l\'empreinte fournie à l\'upload') }}">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    {{ __('app.Intégrité vérifiée') }}
                                </span>
                            @elseif($isoFile->checksum_verified === false)
                                <span class="flex items-center gap-1 text-red-400 normal-case" title="{{ __('app.Attendu :') }} {{ $isoFile->expected_checksum }}">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    {{ __('app.Empreinte non conforme !') }}
                                </span>
                            @endif
                        </span>
                        <div class="flex items-center gap-2">
                            @if($isoFile->checksum)
                            <button @click="navigator.clipboard.writeText('{{ $isoFile->checksum }}'); copied = true; setTimeout(() => copied = false, 2000)" class="p-1 text-slate-500 hover:text-indigo-400 transition-colors cursor-pointer" :title="copied ? '{{ __('app.Copié !') }}' : '{{ __('app.Copier') }}'">
                                <svg x-show="!copied" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                <svg x-show="copied" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-400"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                            @endif
                            @can('update', $isoFile)
                            <button wire:click="recalculateChecksum" wire:loading.attr="disabled" class="p-1 text-slate-500 hover:text-amber-400 transition-colors cursor-pointer" title="{{ __('app.Recalculer') }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </button>
                            @endcan
                        </div>
                    </div>
                    @if($isoFile->checksum)
                        <span class="opacity-80">{{ $isoFile->checksum }}</span>
                    @else
                        <span class="flex items-center gap-2 text-amber-400/80">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>
                            {{ __('app.Checksum en cours de calcul…') }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Share links -->
            @can('share', $isoFile)
            <div class="glass p-6 rounded-3xl">
                <h3 class="font-bold text-lg mb-4">{{ __('app.Liens de partage') }} <span class="text-slate-500 text-sm font-normal">({{ $isoFile->shareLinks->count() }})</span></h3>
                @forelse($isoFile->shareLinks as $link)
                    <div class="flex items-center justify-between gap-4 py-3 {{ !$loop->last ? 'border-b border-white/5' : '' }}" x-data="{ copied: false }">
                        <div class="min-w-0 text-sm">
                            <div class="flex items-center gap-2">
                                <code class="text-xs text-indigo-300 truncate">{{ route('share.show', $link->token) }}</code>
                                <button @click="navigator.clipboard.writeText('{{ route('share.show', $link->token) }}'); copied = true; setTimeout(() => copied = false, 2000)" class="p-1 text-slate-500 hover:text-indigo-400 transition-colors cursor-pointer shrink-0">
                                    <svg x-show="!copied" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    <svg x-show="copied" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-400"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </button>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                                <span>{{ $link->download_count }}{{ $link->max_downloads ? ' / ' . $link->max_downloads : '' }} {{ __('app.téléchargements') }}</span>
                                @if($link->password)<span class="text-amber-400/70">{{ __('app.Protégé') }}</span>@endif
                                @if($link->expires_at)
                                    <span class="{{ $link->expires_at->isPast() ? 'text-red-400' : '' }}">{{ __('app.Expiration') }} {{ $link->expires_at->diffForHumans() }}</span>
                                @endif
                                @unless($link->isValid())<span class="text-red-400 font-bold uppercase">{{ __('app.Expirée') }}</span>@endunless
                            </div>
                        </div>
                        <button wire:click="deleteShareLink({{ $link->id }})" wire:confirm="{{ __('app.Supprimer ce lien de partage ?') }}" class="p-2 text-slate-500 hover:text-red-400 bg-slate-800/50 hover:bg-red-500/10 rounded-xl transition-all shrink-0" title="{{ __('app.Supprimer') }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 italic">{{ __('app.Aucun lien de partage pour ce fichier.') }}</p>
                @endforelse
            </div>
            @endcan
        </div>

        <!-- Activity -->
        <div class="glass p-6 rounded-3xl h-fit">
            <h3 class="font-bold text-lg mb-4">{{ __('app.Historique') }}</h3>
            <div class="space-y-4">
                @forelse($activities as $activity)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ match($activity->event) { 'created' => 'bg-emerald-400', 'updated' => 'bg-indigo-400', 'deleted' => 'bg-red-400', default => 'bg-slate-500' } }}"></div>
                        <div class="min-w-0">
                            <p class="text-slate-300">
                                <span class="font-medium">{{ $activity->causer?->name ?? __('app.Système') }}</span>
                                <span class="text-slate-500">· {{ __('app.' . $activity->event) }}</span>
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 italic">{{ __('app.Aucune activité enregistrée.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
