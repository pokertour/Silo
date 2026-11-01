<div class="p-6 lg:p-10 space-y-8">
    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-bold">{{ __('app.Mes ISOs') }}</h2>
            <p class="text-slate-500 mt-1">{{ __('app.Gérez tous les fichiers ISO auxquels vous avez accès.') }}</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('upload') }}" wire:navigate class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-500/10 flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                {{ __('app.Ajouter un ISO') }}
            </a>
        </div>
    </header>

    <!-- Filters & Search -->
    <div class="glass p-4 rounded-2xl flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex flex-1 items-center gap-2 overflow-visible min-w-0">
            <!-- Scrollable Buttons Group -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 scrollbar-hide flex-1">
                <button wire:click="$set('filter', 'all')" class="whitespace-nowrap px-4 py-2 text-sm font-medium rounded-xl transition-all {{ $filter === 'all' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-900/50 text-slate-400 hover:text-white' }}">{{ __('app.Tous') }}</button>
                <button wire:click="$set('filter', 'public')" class="whitespace-nowrap px-4 py-2 text-sm font-medium rounded-xl transition-all {{ $filter === 'public' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-900/50 text-slate-400 hover:text-white' }}">{{ __('app.Publics') }}</button>
                
                <div class="w-px h-6 bg-slate-800 mx-2 shrink-0"></div>
                
                @foreach(auth()->user()->hasRole('admin') ? \App\Models\Group::all() : auth()->user()->groups as $group)
                    <button wire:click="$set('filter', '{{ $group->id }}')" class="whitespace-nowrap px-4 py-2 text-sm font-medium rounded-xl transition-all {{ $filter === (string)$group->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-900/50 text-slate-400 hover:text-white' }}">
                        {{ $group->name }}
                    </button>
                @endforeach
            </div>
            
            <div class="w-px h-6 bg-slate-800 mx-2 shrink-0 hidden md:block"></div>
            
            <!-- Tag Selector (Outside scrollable area to prevent clipping) -->
            @php
                $tagOptions = [['value' => '', 'label' => __('app.Toutes les étiquettes')]];
                foreach($availableTags as $tag) {
                    $tagOptions[] = ['value' => $tag->id, 'label' => $tag->name];
                }
            @endphp
            <div class="w-full md:w-56 shrink-0 relative z-50">
                <x-custom-select wire:model.live="selectedTag" :options="$tagOptions" />
            </div>
        </div>

        @php
            $sortOptions = [
                ['value' => 'recent', 'label' => __('app.Plus récents')],
                ['value' => 'oldest', 'label' => __('app.Plus anciens')],
                ['value' => 'name', 'label' => __('app.Nom (A-Z)')],
                ['value' => 'size', 'label' => __('app.Taille (décroissante)')],
            ];
        @endphp
        <div class="w-full md:w-52 shrink-0 relative z-40">
            <x-custom-select wire:model.live="sortBy" :options="$sortOptions" />
        </div>

        <div class="w-full md:w-72 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('app.Rechercher (Nom, OS...)') }}" class="w-full pl-10 pr-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all text-slate-200 placeholder-slate-500">
        </div>
    </div>

    <!-- Bulk actions toolbar -->
    @if(count($selected) > 0)
    <div class="glass p-4 rounded-2xl flex flex-col md:flex-row gap-4 items-center justify-between border-indigo-500/30 bg-indigo-500/5">
        <div class="flex items-center gap-3 text-sm">
            <span class="bg-indigo-500/20 text-indigo-300 font-bold px-3 py-1 rounded-lg">{{ count($selected) }}</span>
            <span class="text-slate-300">{{ __('app.fichier(s) sélectionné(s)') }}</span>
            <button wire:click="clearSelection" class="text-slate-500 hover:text-white text-xs underline underline-offset-2 cursor-pointer">{{ __('app.Tout désélectionner') }}</button>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @php
                $bulkGroupOptions = [['value' => '', 'label' => __('app.Public')]];
                foreach(auth()->user()->hasRole('admin') ? \App\Models\Group::all() : auth()->user()->groups as $g) {
                    $bulkGroupOptions[] = ['value' => $g->id, 'label' => $g->name];
                }
            @endphp
            <div class="w-48">
                <x-custom-select wire:model="bulkGroupId" :options="$bulkGroupOptions" />
            </div>
            <button wire:click="assignGroupSelected" class="px-4 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition-all cursor-pointer">
                {{ __('app.Déplacer vers ce groupe') }}
            </button>
            <button wire:click="deleteSelected" wire:confirm="{{ __('app.Supprimer définitivement les fichiers sélectionnés ?') }}" class="px-4 py-2 text-sm font-medium bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/20 rounded-xl transition-all cursor-pointer">
                {{ __('app.Supprimer') }}
            </button>
        </div>
        @error('bulkGroupId') <span class="text-red-400 text-xs w-full">{{ $message }}</span> @enderror
    </div>
    @endif

    <!-- Grid List -->
    <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">
        @forelse($isos as $file)
        <div class="glass p-6 rounded-3xl hover:border-indigo-500/30 transition-all group relative flex flex-col h-full">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->can('update', $file) || auth()->user()->can('delete', $file))
                        <input type="checkbox" wire:model.live="selected" value="{{ $file->id }}"
                               class="w-4 h-4 rounded border-slate-600 bg-slate-900/50 text-indigo-500 focus:ring-indigo-500/50 cursor-pointer shrink-0">
                    @endif
                <div class="bg-indigo-500/10 p-3 rounded-2xl text-indigo-400">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                </div>
                <div class="flex gap-2 text-xs font-semibold uppercase tracking-wider">
                    @if($file->checksum_verified === false)
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400" title="{{ __('app.Empreinte non conforme !') }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            <span>{{ __('app.Corrompu ?') }}</span>
                        </div>
                    @elseif($file->checksum_verified === true)
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400" title="{{ __('app.Intégrité vérifiée') }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>{{ __('app.Vérifié') }}</span>
                        </div>
                    @endif
                    @if($file->checksum && in_array($file->checksum, $duplicateChecksums))
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400" title="{{ __('app.Un fichier identique existe déjà') }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span>{{ __('app.Doublon') }}</span>
                        </div>
                    @endif
                    @if($file->group_id)
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400" title="Groupe">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>{{ $file->group->name }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400" title="Public">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            <span>{{ __('app.Public') }}</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <h4 class="font-bold text-xl text-slate-200 group-hover:text-indigo-300 transition-colors mb-2 leading-tight">
                <a href="{{ route('isos.show', $file) }}" wire:navigate class="hover:underline decoration-indigo-500/50 underline-offset-4">{{ $file->name }}</a>
            </h4>
            
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mb-4">
                <span class="flex items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    {{ $file->created_at->format('M d, Y') }}
                </span>
                <span class="flex items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    {{ $file->size_human }}
                </span>
            </div>

            @if($file->tags->count() > 0)
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($file->tags as $tag)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}30;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18"></path></svg>
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
            @endif

            @if($file->os_type || $file->version || $file->architecture)
            <div class="flex flex-wrap gap-2 mb-4">
                @if($file->os_type) <span class="px-2.5 py-1 text-[11px] font-medium bg-slate-800 text-slate-300 rounded-md border border-slate-700">{{ $file->os_type }}</span> @endif
                @if($file->version) <span class="px-2.5 py-1 text-[11px] font-medium bg-indigo-500/10 text-indigo-300 rounded-md border border-indigo-500/20">v{{ $file->version }}</span> @endif
                @if($file->architecture) <span class="px-2.5 py-1 text-[11px] font-medium bg-emerald-500/10 text-emerald-300 rounded-md border border-emerald-500/20">{{ $file->architecture }}</span> @endif
            </div>
            @endif

            @if($file->description)
                <p class="text-sm text-slate-500 line-clamp-2 mt-auto min-h-[40px]">{{ $file->description }}</p>
            @else
                <p class="text-sm text-slate-500 italic mt-auto min-h-[40px]">{{ __('app.Aucune description') }}</p>
            @endif

            @if($file->checksum)
            <div class="mt-4 pt-3 border-t border-white/5 text-[10px] text-slate-500 font-mono break-all bg-slate-900/30 p-2.5 rounded-xl border border-slate-700/50 group-hover:bg-indigo-500/5 transition-colors"
                 x-data="{ copied: false }">
                <div class="flex items-center justify-between mb-0.5">
                    <span class="text-slate-400 font-bold uppercase tracking-wider">SHA256 Checksum</span>
                    <button @click="navigator.clipboard.writeText('{{ $file->checksum }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="p-1 text-slate-500 hover:text-indigo-400 transition-colors cursor-pointer" :title="copied ? '{{ __('app.Copié !') }}' : '{{ __('app.Copier') }}'">
                        <svg x-show="!copied" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        <svg x-show="copied" x-cloak width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-400"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </div>
                <span class="opacity-80">{{ $file->checksum }}</span>
            </div>
            @else
            <div class="mt-4 pt-3 border-t border-white/5 text-[10px] text-slate-500 font-mono bg-slate-900/30 p-2.5 rounded-xl border border-slate-700/50 flex items-center gap-2">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin text-amber-400"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>
                <span class="text-amber-400/80 uppercase tracking-wider font-bold">{{ __('app.Checksum en cours de calcul…') }}</span>
            </div>
            @endif

            <div class="mt-6 pt-6 border-t border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-300 ring-2 ring-slate-700/50">
                        {{ strtoupper(substr($file->user->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-medium text-slate-400">{{ $file->user->name }}</span>
                </div>
                
                <div class="flex items-center gap-2">
                    @if(auth()->user()->hasRole('admin') || $file->user_id === auth()->id())
                        <button @click.prevent="$dispatch('openModal', { component: 'edit-iso-modal', arguments: { isoId: {{ $file->id }} } })" class="p-2 text-slate-400 hover:text-indigo-400 bg-slate-800/50 hover:bg-indigo-500/10 rounded-xl transition-all" title="Éditer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        </button>
                        <button @click.prevent="$dispatch('openModal', { component: 'delete-iso-modal', arguments: { isoId: {{ $file->id }} } })" class="p-2 text-slate-400 hover:text-red-400 bg-slate-800/50 hover:bg-red-500/10 rounded-xl transition-all" title="Supprimer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    @endif
                    <div class="w-px h-4 bg-slate-800 mx-1"></div>
                    <button @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $file->id }} } })" class="p-2 text-indigo-400 hover:text-white bg-indigo-500/10 hover:bg-indigo-500 rounded-xl transition-all" title="Partager">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                    </button>
                    <button @click.prevent="$dispatch('start-mega-download', { url: @js(route('download', $file->uuid)), filename: @js($file->name.'.'.pathinfo($file->file_path, PATHINFO_EXTENSION)) })" class="p-2 text-emerald-400 hover:text-white bg-emerald-500/10 hover:bg-emerald-500 rounded-xl transition-all cursor-pointer" title="Télécharger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="lg:col-span-2 2xl:col-span-3 glass p-12 rounded-3xl text-center border-dashed border-2 border-slate-700/50">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-slate-600 mb-6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><circle cx="12" cy="15" r="3"></circle><path d="M12 11v8"></path></svg>
            <h3 class="text-xl font-medium text-slate-300">{{ __('app.Aucun ISO trouvé') }}</h3>
            <p class="text-slate-500 mt-2">{{ __('app.Aucun fichier ne correspond à votre recherche ou filtre.') }}</p>
            @if(empty($filter) && empty($search))
            <a href="{{ route('upload') }}" wire:navigate class="inline-flex items-center gap-2 mt-6 bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-medium transition-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                {{ __('app.Ajouter un ISO') }}
            </a>
            @endif
        </div>
        @endforelse
    </div>

    @if($isos->hasPages())
    <div class="pt-4 flex justify-between">
        {{ $isos->links('vendor.livewire.tailwind') }}
    </div>
    @endif
</div>
