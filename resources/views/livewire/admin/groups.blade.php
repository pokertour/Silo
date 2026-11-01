<div class="p-6 lg:p-10 space-y-10">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-bold">{{ __('app.Gestion des Groupes') }} <span class="text-indigo-500/50 text-xl">({{ $groups->total() }})</span></h2>
            <p class="text-slate-500 mt-1">{{ __('app.Organisez vos utilisateurs pour des partages ISO ciblés.') }}</p>
        </div>
        <div class="flex items-center w-full md:w-auto mt-4 md:mt-0">
            <div class="relative w-full md:w-64 lg:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Rechercher un groupe...') }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-200 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all placeholder:text-slate-500">
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Create Group Form -->
        <div class="glass p-6 rounded-3xl h-fit">
            <h3 class="text-xl font-bold mb-4">{{ __('app.Créer un groupe') }}</h3>
            <form wire:submit="create" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Nom du groupe') }}</label>
                    <input wire:model="name" type="text" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none" placeholder="{{ __('ex: Developpeurs') }}">
                    @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Description') }}</label>
                    <textarea wire:model="description" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none" rows="2" placeholder="{{ __('Accès aux ISO liés au développement') }}"></textarea>
                    @error('description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-xl shadow-lg mt-4 transition-all">
                    {{ __('app.Créer') }}
                </button>
            </form>
        </div>

        <!-- Groups List & Assignment -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-xl font-bold mb-4">{{ __('Groupes Existants') }}</h3>
            @forelse($groups as $group)
            <div class="glass p-5 rounded-3xl">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-lg text-indigo-100">{{ $group->name }}</h4>
                        <p class="text-sm text-slate-400 mt-1">{{ $group->description ?? __('app.Aucune description') }}</p>
                    </div>
                    <button wire:click="delete({{ $group->id }})" class="p-2 text-slate-500 hover:text-red-400 transition-colors bg-white/5 rounded-xl" title="{{ __('app.Supprimer') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>

                <div class="border-t border-white/5 pt-4 mt-4">
                    <div class="flex items-center justify-between mb-3 whitespace-nowrap gap-4">
                        <h5 class="text-sm font-medium text-slate-300">{{ __('app.Membres') }} ({{ $group->users->count() }})</h5>
                        
                        <!-- Search to Add Member -->
                        <div class="relative w-full max-w-xs">
                            @if($activeGroupId === $group->id)
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-500">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                </div>
                                <input wire:model.live.debounce.300ms="userSearch" autofocus type="text" placeholder="{{ __('app.Ajouter un membre...') }}" class="w-full bg-slate-950/50 border border-indigo-500/50 rounded-lg py-1.5 pl-8 pr-3 text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 transition-all placeholder:text-slate-500" @click.away="$wire.setActiveGroup(null)">
                                
                                @if(!empty($searchResults))
                                <div class="absolute z-50 left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-1 duration-200">
                                    @php $anyFound = false; @endphp
                                    @foreach($searchResults as $result)
                                        @if(!$group->users->contains('id', $result->id))
                                            @php $anyFound = true; @endphp
                                            <button wire:click="addUser({{ $group->id }}, {{ $result->id }})" class="w-full flex items-center gap-2 p-2 hover:bg-indigo-500/10 text-left transition-colors border-b border-white/5 last:border-0">
                                                <div class="w-7 h-7 rounded-full bg-indigo-500/20 flex items-center justify-center text-[10px] font-bold text-indigo-400 shrink-0">
                                                    {{ substr($result->name, 0, 2) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[11px] font-medium text-slate-200 truncate">{{ $result->name }}</div>
                                                    <div class="text-[9px] text-slate-500 truncate">{{ $result->email }}</div>
                                                </div>
                                            </button>
                                        @endif
                                    @endforeach
                                    @if(!$anyFound)
                                        <div class="p-2 text-[10px] text-slate-500 italic text-center">{{ __('app.Aucun utilisateur trouvé ou déjà membre.') }}</div>
                                    @endif
                                </div>
                                @endif
                            @else
                                <button wire:click="setActiveGroup({{ $group->id }})" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-indigo-400 text-xs transition-all border border-transparent hover:border-indigo-500/20">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
                                    {{ __('app.Ajouter un membre...') }}
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto pr-1 custom-scrollbar">
                        @forelse($group->users as $user)
                        <div class="flex items-center justify-between p-2 bg-white/5 rounded-xl group/member border border-transparent hover:border-white/10 transition-all">
                            <div class="flex items-center gap-2 min-w-0">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" class="w-7 h-7 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-linear-to-tr from-slate-700 to-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-400 shrink-0">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="text-xs text-slate-300 truncate">{{ $user->name }}</span>
                            </div>
                            <button wire:click="removeUser({{ $group->id }}, {{ $user->id }})" class="p-1.5 text-slate-500 hover:text-red-400 opacity-0 group-hover/member:opacity-100 transition-all hover:bg-red-500/10 rounded-lg" title="{{ __('app.Retirer') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        @empty
                        <div class="col-span-full py-6 text-center text-xs text-slate-600 italic bg-slate-900/20 rounded-2xl border border-dashed border-white/5">
                            {{ __('app.Aucun membre dans ce groupe.') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="text-slate-500 text-center py-10 bg-slate-900/30 rounded-2xl border border-white/5">
                {{ __('app.Aucun groupe créé.') }}
            </div>
            @endforelse
            
            @if($groups->hasPages())
            <div class="mt-6">
                {{ $groups->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
