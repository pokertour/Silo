@props(['isoFiles', 'viewMode', 'filter', 'groups'])

<div class="space-y-6">
    <!-- Filters & Actions -->
    <div class="flex items-center gap-2 mb-2 w-full overflow-x-auto pb-2 scrollbar-hide">
        <button wire:click="$set('filter', 'all')" 
            class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-all {{ $filter === 'all' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-900/50 text-slate-400 hover:text-slate-200' }}">
            {{ __('app.Tous') }}
        </button>
        <button wire:click="$set('filter', 'public')" 
            class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-all {{ $filter === 'public' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-900/50 text-slate-400 hover:text-slate-200' }}">
            {{ __('app.Fichiers Publics') }}
        </button>
        
        <div class="w-px h-6 bg-slate-800 mx-2"></div>
        
        @foreach($groups as $group)
            <button wire:click="$set('filter', '{{ $group->id }}')" 
                class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-all {{ $filter === (string)$group->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-900/50 text-slate-400 hover:text-slate-200' }}">
                {{ $group->name }}
            </button>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold">{{ __('app.Fichiers Récents') }}</h3>
        <div class="flex items-center gap-2">
            <button wire:click="toggleViewMode" 
                class="p-2 glass rounded-lg transition-colors {{ $viewMode === 'list' ? 'bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/30' : 'text-slate-400 hover:text-white' }}" 
                title="{{ $viewMode === 'grid' ? __('app.Vue Liste') : __('app.Vue Grille') }}">
                 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    @if($viewMode === 'grid')
                        <line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line>
                    @else
                        <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M12 9v12"/>
                    @endif
                 </svg>
            </button>
            <a href="{{ route('upload') }}" wire:navigate class="p-2 glass rounded-lg text-indigo-400 hover:text-indigo-300">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-3 rounded-xl">
            {{ session('message') }}
        </div>
    @endif

    <div class="{{ $viewMode === 'list' ? 'flex flex-col gap-4' : 'grid grid-cols-1 md:grid-cols-2 gap-6' }}">
        @forelse($isoFiles as $file)
            @if($viewMode === 'grid')
            <!-- ISO Grid Card -->
            <div class="glass p-6 rounded-3xl hover:border-indigo-500/30 transition-all group relative flex flex-col h-full">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-indigo-500/10 p-3 rounded-2xl text-indigo-400">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <div class="flex gap-2 text-xs font-semibold uppercase tracking-wider">
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
                    {{ $file->name }}
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

                @if($file->os_type || $file->version || $file->architecture)
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($file->os_type) <span class="px-2.5 py-1 text-[11px] font-medium bg-slate-800 text-slate-300 rounded-md border border-slate-700">{{ $file->os_type }}</span> @endif
                    @if($file->version) <span class="px-2.5 py-1 text-[11px] font-medium bg-indigo-500/10 text-indigo-300 rounded-md border border-indigo-500/20">v{{ $file->version }}</span> @endif
                    @if($file->architecture) <span class="px-2.5 py-1 text-[11px] font-medium bg-emerald-500/10 text-emerald-300 rounded-md border border-emerald-500/20">{{ $file->architecture }}</span> @endif
                </div>
                @endif
                
                @if($file->tags && $file->tags->count() > 0)
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($file->tags as $tag)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}30;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18"></path></svg>
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
                @endif

                @if($file->description)
                    <p class="text-sm text-slate-500 line-clamp-2 mt-auto min-h-[40px]">{{ $file->description }}</p>
                @else
                    <p class="text-sm text-slate-500 italic mt-auto min-h-[40px]">{{ __('app.Aucune description') }}</p>
                @endif

                @if($file->checksum)
                <div class="mt-4 p-3 bg-slate-900/40 rounded-xl border border-white/5 overflow-hidden">
                    <div class="text-[10px] uppercase font-bold text-slate-600 mb-1">SHA256 Checksum</div>
                    <div class="text-[11px] font-mono text-indigo-400 truncate" title="{{ $file->checksum }}">{{ substr($file->checksum, 0, 10) }}...{{ substr($file->checksum, -10) }}</div>
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
            @else
            <!-- ISO List Card -->
            <div class="glass p-4 rounded-3xl hover:border-indigo-500/30 transition-all group relative flex flex-col xl:flex-row xl:items-center gap-4 xl:gap-6 justify-between">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1 min-w-0">
                    <div class="bg-indigo-500/10 p-3 rounded-2xl text-indigo-400 shrink-0 w-max">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3">
                            <h4 class="font-bold text-lg text-slate-200 group-hover:text-indigo-300 transition-colors truncate">
                                {{ $file->name }}
                            </h4>
                            @if($file->group_id)
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-400 rounded-md shrink-0">{{ $file->group->name }}</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 rounded-md shrink-0">{{ __('app.Public') }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mt-1">
                            <span class="flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                {{ $file->size_human }}
                            </span>
                            <span class="w-1 h-1 rounded-full bg-slate-600 hidden sm:block"></span>
                            <span class="hidden sm:inline-flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                {{ $file->created_at->format('M d, Y') }}
                            </span>
                            @if($file->os_type || $file->version || $file->architecture)
                                <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                <div class="flex gap-1.5">
                                    @if($file->os_type) <span class="text-slate-300 font-medium">{{ $file->os_type }}</span> @endif
                                    @if($file->version) <span class="text-indigo-300 font-medium">v{{ $file->version }}</span> @endif
                                    @if($file->architecture) <span class="text-emerald-300 font-medium">{{ $file->architecture }}</span> @endif
                                </div>
                            @endif
                            <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                            <div class="flex items-center gap-1.5" title="{{ current(explode(' ', $file->user->name)) }}">
                                <div class="w-4 h-4 rounded-full bg-slate-800 flex items-center justify-center text-[8px] font-bold text-slate-300">
                                    {{ strtoupper(substr($file->user->name, 0, 1)) }}
                                </div>
                                <span class="hidden sm:inline">{{ current(explode(' ', $file->user->name)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 border-t xl:border-none border-white/5 pt-4 xl:pt-0 justify-end">
                    @if(auth()->user()->hasRole('admin') || $file->user_id === auth()->id())
                        <button @click.prevent="$dispatch('openModal', { component: 'edit-iso-modal', arguments: { isoId: {{ $file->id }} } })" class="p-2 text-slate-400 hover:text-indigo-400 bg-slate-800/20 hover:bg-slate-800 rounded-xl transition-all" title="Éditer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        </button>
                        <button @click.prevent="$dispatch('openModal', { component: 'delete-iso-modal', arguments: { isoId: {{ $file->id }} } })" class="p-2 text-slate-400 hover:text-red-400 bg-slate-800/20 hover:bg-slate-800 rounded-xl transition-all" title="Supprimer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    @endif
                    <button @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $file->id }} } })" class="p-2 text-indigo-400 hover:text-white bg-indigo-500/10 hover:bg-indigo-500 rounded-xl transition-all" title="Partager">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                    </button>
                    <button @click.prevent="$dispatch('start-mega-download', { url: @js(route('download', $file->uuid)), filename: @js($file->name.'.'.pathinfo($file->file_path, PATHINFO_EXTENSION)) })" class="flex items-center gap-2 p-2 px-4 text-emerald-400 hover:text-white bg-emerald-500/10 hover:bg-emerald-500 rounded-xl transition-all font-medium text-sm border border-emerald-500/10 cursor-pointer" title="Télécharger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    </button>
                </div>
            </div>
            @endif
        @empty
            <div class="md:col-span-2 glass p-10 rounded-3xl text-center border-dashed border-2 border-slate-700/50">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-slate-600 mb-4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><circle cx="12" cy="15" r="3"></circle></svg>
                <h3 class="text-lg font-medium text-slate-300">{{ __('app.Aucun fichier disponible') }}</h3>
                <p class="text-slate-500 mt-1">{{ __('app.Vous n\'avez accès à aucun fichier pour le moment.') }}</p>
            </div>
        @endforelse
    </div>
</div>
