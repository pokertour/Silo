<div class="p-6 lg:p-10 space-y-8">
    <header>
        <h2 class="text-3xl font-bold tracking-tight text-white uppercase font-display">{{ __('app.Configuration') }}</h2>
        <p class="text-slate-500 mt-1">{{ __('app.Gérez les paramètres globaux et les emplacements de stockage.') }}</p>
    </header>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Vertical Tabs Navigation -->
        <aside class="w-full lg:w-72 shrink-0">
            <nav class="glass p-3 rounded-3xl space-y-2">
                <button wire:click="$set('activeTab', 'general')" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ $activeTab === 'general' ? 'sidebar-active text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    <span class="font-semibold">{{ __('app.Général') }}</span>
                </button>
                <button wire:click="$set('activeTab', 'storage')" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ $activeTab === 'storage' ? 'sidebar-active text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    <span class="font-semibold">{{ __('app.Stockages Disponibles') }}</span>
                </button>
                <div class="h-px bg-white/5 mx-4 my-2"></div>
                <button wire:click="$set('activeTab', 'about')" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ $activeTab === 'about' ? 'sidebar-active text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span class="font-semibold">{{ __('app.À propos') }}</span>
                </button>
            </nav>
        </aside>

        <!-- Dynamic Content Area -->
        <main class="flex-1 min-w-0">
            @if($activeTab === 'general')
                <div class="glass p-8 rounded-3xl space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <h3 class="text-xl font-bold text-slate-200 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
                             <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        </div>
                        {{ __('app.Paramètres Généraux') }}
                    </h3>

                    <form wire:submit="saveSettings" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Nom de l\'application') }}</label>
                                    <input wire:model="app_name" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-2xl px-4 py-3 outline-none focus:border-indigo-500/50 transition-all font-semibold text-slate-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Couleur principale') }}</label>
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-12 h-12 rounded-xl bg-slate-800 border-2 border-slate-700/50 overflow-hidden cursor-pointer ripple">
                                            <input wire:model="primary_color" type="color" class="absolute inset-0 w-full h-full scale-150 cursor-pointer">
                                        </div>
                                        <input wire:model="primary_color" type="text" class="flex-1 bg-slate-900/40 border border-slate-700/50 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none font-mono text-sm uppercase">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Logo de l\'application') }}</label>
                                <div class="flex items-center gap-6 p-6 bg-slate-900/40 border border-slate-700/50 rounded-3xl group/logo border-dashed">
                                    <div class="relative">
                                        <img src="{{ $logo ? $logo->temporaryUrl() : $current_logo_url }}" class="w-20 h-20 object-contain rounded-xl shadow-lg ring-1 ring-white/5">
                                        @if($current_logo_url && !str_contains($current_logo_url, 'logo.svg'))
                                        <button type="button" wire:click="deleteLogo" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg transition-transform hover:scale-110">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" wire:model="logo" class="hidden" id="logo-input">
                                        <label for="logo-input" class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-medium cursor-pointer transition-all border border-white/5">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                            {{ __('app.Changer le logo') }}
                                        </label>
                                        <p class="text-[10px] text-slate-500 mt-2 lowercase opacity-60">{{ __('app.SVG, PNG ou JPG. Recommandé : Carré, min 200px.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" style="background-color: var(--primary-hex)" class="px-8 py-3.5 hover:brightness-110 text-white font-bold rounded-2xl shadow-xl shadow-indigo-500/10 transition-all flex items-center gap-2 group">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="group-hover:rotate-12 transition-transform"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                {{ __('app.Enregistrer les paramètres') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($activeTab === 'storage')
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <!-- List -->
                    <div class="glass p-8 rounded-3xl space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-200">{{ __('app.Stockages Disponibles') }}</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($locations as $location)
                            <div class="flex items-center justify-between p-4 bg-slate-900/40 rounded-2xl border border-white/5 group/loc">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center">
                                        @if($location->driver === 'local')
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                                        @else
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-200 flex items-center gap-2">
                                            {{ $location->name }}
                                            @if($location->is_default)
                                            <span class="text-[10px] bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full border border-indigo-500/30 uppercase tracking-tighter">{{ __('app.Défaut') }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-500 uppercase">{{ $location->driver }}</div>
                                    </div>
                                </div>
                                <div class="flex gap-1.5">
                                    <button wire:click="testConnection({{ $location->id }})" class="p-2 text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 rounded-xl transition-all" title="Tester">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"></path></svg>
                                    </button>
                                    @if(!$location->is_default)
                                        <button wire:click="setAsDefault({{ $location->id }})" class="p-2 text-slate-500 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-xl transition-all" title="Défaut">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        </button>
                                        <button wire:click="deleteLocation({{ $location->id }})" class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all" title="Supprimer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="glass p-8 rounded-3xl">
                        <h3 class="text-xl font-bold text-slate-200 mb-6 flex items-center gap-3">
                             <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
                            </div>
                            {{ __('app.Emplacement de Stockage (Admin seul)') }}
                        </h3>
                        
                        <form wire:submit="saveStorage" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Nom (identifiant interne)') }}</label>
                                        <input wire:model="storage_name" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none focus:border-indigo-500/50">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Driver') }}</label>
                                        <x-custom-select 
                                            wire:model.live="storage_driver" 
                                            :options="[
                                                ['value' => 'local', 'label' => 'Local'],
                                                ['value' => 'nfs', 'label' => 'NFS / Symlink'],
                                                ['value' => 's3', 'label' => 'Amazon S3 / S3 Compatible'],
                                                ['value' => 'ftp', 'label' => 'FTP'],
                                                ['value' => 'sftp', 'label' => 'SFTP (SSH)'],
                                            ]"
                                        />
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-indigo-500/5 rounded-2xl border border-indigo-500/10 cursor-pointer group/check" @click="$refs.defCheck.click()">
                                        <div class="relative flex items-center justify-center w-6 h-6 shrink-0">
                                            <input x-ref="defCheck" wire:model="storage_is_default" type="checkbox" class="peer h-6 w-6 rounded-lg border-2 border-slate-700 bg-slate-900 checked:bg-indigo-500 checked:border-indigo-500 focus:ring-offset-0 focus:ring-0 transition-all cursor-pointer appearance-none">
                                            <svg class="absolute w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-400 group-hover/check:text-slate-200 transition-colors">{{ __('app.Définir comme stockage par défaut') }}</span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                     @if(in_array($storage_driver, ['local', 'nfs', 'ftp', 'sftp']))
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Chemin racine (Optionnel)') }}</label>
                                        <input wire:model="config_root" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none" placeholder="{{ $storage_driver === 'local' ? storage_path('app/iso') : '/' }}">
                                    </div>
                                    @endif
                                    @if(in_array($storage_driver, ['ftp', 'sftp']))
                                    <div>
                                        <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Serveur') }}</label>
                                        <input wire:model="config_host" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none" placeholder="ftp.example.com">
                                    </div>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    @if($storage_driver === 's3')
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.AWS Key') }}</label>
                                                <input wire:model="config_key" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Secret') }}</label>
                                                <input wire:model="config_secret" type="password" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Region') }}</label>
                                                <input wire:model="config_region" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none">
                                            </div>
                                        </div>
                                    @endif
                                    @if(in_array($storage_driver, ['ftp', 'sftp']))
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Username') }}</label>
                                            <input wire:model="config_username" type="text" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5 text-slate-400">{{ __('app.Password') }}</label>
                                            <input wire:model="config_password" type="password" class="w-full bg-slate-900/40 border border-slate-700/50 rounded-xl px-4 py-3 outline-none">
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-4">
                                <button type="submit" style="background-color: var(--primary-hex)" class="px-8 py-3.5 hover:brightness-110 text-white font-bold rounded-2xl shadow-xl shadow-indigo-500/10 transition-all flex items-center gap-2 group">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="group-hover:translate-x-1 transition-transform"><path d="M12 5v14M5 12h14"></path></svg>
                                    {{ __('app.Enregistrer le Stockage') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($activeTab === 'about')
                <div class="glass p-10 rounded-3xl space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="text-center space-y-4">
                        <div class="w-24 h-24 bg-slate-900/40 rounded-3xl mx-auto flex items-center justify-center shadow-2xl shadow-indigo-500/10 border border-white/5 overflow-hidden">
                            <img src="{{ $current_logo_url }}" class="w-16 h-16 object-contain grayscale opacity-80 group-hover:grayscale-0 transition-all">
                        </div>
                        <h3 class="text-2xl font-black tracking-tight text-white uppercase italic">Silo</h3>
                        <p class="text-slate-500 font-medium font-mono text-xs tracking-widest uppercase">Version {{ config('version.current') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 font-display italic">
                        <div class="p-6 bg-slate-900/40 rounded-3xl border border-white/5 space-y-2">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest opacity-60">{{ __('app.Créateur') }}</div>
                            <div class="text-slate-200 font-black tracking-tight text-lg uppercase italic">Pokertour</div>
                        </div>
                        <div class="p-6 bg-slate-900/40 rounded-3xl border border-white/5 space-y-2">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest opacity-60">{{ __('app.Licence') }}</div>
                            <div class="text-slate-200 font-bold tracking-tight">MIT Enterprise</div>
                        </div>
                        <div class="p-6 bg-slate-900/40 rounded-3xl border border-white/5 space-y-2 md:col-span-2">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest opacity-60">{{ __('app.Dépôt Git Officiel') }}</div>
                            <a href="https://github.com/pokertour/Silo" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition-colors font-mono text-xs break-all">
                                https://github.com/pokertour/Silo
                            </a>
                        </div>
                    </div>

                    <div class="pt-8 text-center">
                        <p class="text-[11px] text-slate-600 uppercase tracking-widest font-black opacity-30 italic">
                            &copy; {{ date('Y') }} Silo &bull; {{ __('app.Tous droits réservés') }}
                        </p>
                    </div>
                </div>
            @endif
        </main>
    </div>
</div>
