<div class="p-6 lg:p-10 space-y-10 max-w-4xl mx-auto">
    <header>
        <h2 class="text-3xl font-bold">{{ __('app.Nouveau Fichier ISO') }}</h2>
        <p class="text-slate-500 mt-1">{{ __('app.Chargez et décrivez votre image disque.') }}</p>
    </header>

    <div class="glass p-6 md:p-8 rounded-3xl">
        
        @if (session()->has('message'))
            <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-3 rounded-xl mb-6">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6" x-data="resumableUpload()" x-init="initResumable()">
            <div>
                <input type="hidden" wire:model="tempFilePath">
                @error('tempFilePath') <span class="text-red-400 text-xs mb-4 block text-center font-bold">{{ $message }}</span> @enderror

                <!-- File Input Zone -->
                <div x-show="!uploading && !completed" id="resumable-drop-target" class="border-2 border-dashed border-slate-700/50 hover:border-indigo-500/50 bg-slate-900/30 rounded-2xl p-8 text-center transition-all">
                    <div class="cursor-pointer flex flex-col items-center justify-center space-y-3" id="resumable-browse">
                        <div style="background-color: color-mix(in srgb, var(--primary-hex), transparent 90%)" class="p-4 rounded-full text-indigo-400">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-300 block mb-1">{{ __('app.Cliquez pour sélectionner un fichier ou déposez-le ici') }}</span>
                            <span class="text-xs text-slate-500 block">{{ __('Gros fichiers ISO, IMG, ZIP supportés sans limite') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Import from URL -->
                <div x-show="!uploading && !completed" class="mt-4">
                    <div class="flex items-center gap-3 my-4">
                        <div class="flex-1 h-px bg-slate-800"></div>
                        <span class="text-xs text-slate-500 uppercase tracking-widest font-bold">{{ __('app.ou importer depuis une URL') }}</span>
                        <div class="flex-1 h-px bg-slate-800"></div>
                    </div>
                    <div class="flex gap-3">
                        <input type="url" wire:model="importUrl" placeholder="https://releases.ubuntu.com/…/ubuntu-24.04-live-server-amd64.iso"
                               class="flex-1 bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all placeholder:text-slate-600 text-sm font-mono">
                        <button type="button" wire:click="importFromUrl" wire:loading.attr="disabled"
                                class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-medium text-sm transition-all shrink-0 cursor-pointer">
                            {{ __('app.Importer') }}
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">{{ __('app.Le serveur téléchargera le fichier en arrière-plan — remplissez d\'abord le nom et les métadonnées ci-dessous.') }}</p>
                    @error('importUrl') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Progress Bar -->
                <div x-show="uploading" class="mt-4 p-6 glass rounded-2xl border border-indigo-500/30">
                    <div class="flex justify-between text-sm font-medium text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('app.Téléversement et assemblage en cours...') }}
                        </span>
                        <span x-text="progress + '%'" class="text-indigo-400"></span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                    </div>
                    <div class="text-xs text-center text-slate-500 mt-2">{{ __('(Ne fermez pas cette page)') }}</div>
                </div>

                <div x-show="completed" style="display: none;" class="mt-4 p-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl flex items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <div>
                        <span class="font-bold block" x-text="selectedFile"></span>
                        <span class="text-sm opacity-80">{{ __('Fichier transféré avec succès. Vous pouvez maintenant enregistrer.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Resumable Script -->
            <script>
            function resumableUpload() {
                return {
                    uploading: false,
                    completed: !!@this.tempFilePath,
                    progress: 0,
                    selectedFile: '',
                    
                    initResumable() {
                        var r = new Resumable({
                            target: '/upload-chunk',
                            chunkSize: 5 * 1024 * 1024,
                            simultaneousUploads: 3,
                            testChunks: true,
                            throttleProgressCallbacks: 1,
                            query: {
                                _token: '{{ csrf_token() }}'
                            }
                        });

                        r.assignBrowse(document.getElementById('resumable-browse'));
                        // Déposer un fichier n'importe où sur la page fonctionne
                        r.assignDrop(document.body);
                        document.body.addEventListener('dragover', (e) => e.preventDefault());

                        r.on('fileAdded', (file) => {
                            this.selectedFile = file.fileName;
                            if (!@this.name) {
                                @this.set('name', file.fileName.replace(/\.[^/.]+$/, ""));
                            }
                            this.uploading = true;
                            r.upload();
                        });

                        r.on('fileProgress', (file) => {
                            this.progress = Math.floor(r.progress() * 100);
                        });

                        r.on('fileSuccess', (file, message) => {
                            this.uploading = false;
                            this.completed = true;
                            let response = JSON.parse(message);
                            @this.set('tempFilePath', response.temp_path);
                            @this.set('originalFileName', response.original_name);
                        });

                        r.on('fileError', (file, message) => {
                            this.uploading = false;
                            alert('Erreur lors du téléversement : ' + message);
                        });
                    }
                }
            }
            </script>

            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Nom / Titre *') }}</label>
                    <input wire:model="name" type="text" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none" placeholder="{{ __('ex: Ubuntu 24.04 LTS Server') }}" required>
                    @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Type de Système') }}</label>
                    <x-custom-select 
                        wire:model="os_type" 
                        :options="[
                            ['value' => 'Linux', 'label' => 'Linux'],
                            ['value' => 'Windows', 'label' => 'Windows'],
                            ['value' => 'macOS', 'label' => 'macOS'],
                            ['value' => 'BSD', 'label' => 'BSD'],
                            ['value' => 'Autre', 'label' => __('app.Autre')],
                        ]"
                    />
                    @error('os_type') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Version') }}</label>
                    <input wire:model="version" type="text" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all placeholder:text-slate-600 font-medium" placeholder="{{ __('ex: 24.04') }}">
                    @error('version') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Architecture') }}</label>
                    <x-custom-select 
                        wire:model="architecture" 
                        :options="[
                            ['value' => 'x86_64', 'label' => 'amd64 / x86_64'],
                            ['value' => 'x86', 'label' => 'i386 / x86'],
                            ['value' => 'arm64', 'label' => 'arm64 / aarch64'],
                            ['value' => 'armhf', 'label' => 'armhf'],
                        ]"
                    />
                    @error('architecture') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Description') }}</label>
                    <textarea wire:model="description" rows="3" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all placeholder:text-slate-600 font-medium" placeholder="{{ __('Notes ou instructions supplémentaires...') }}"></textarea>
                    @error('description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.SHA-256 attendu (optionnel)') }}</label>
                    <input type="text" wire:model="expected_checksum" maxlength="64" spellcheck="false"
                           class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all placeholder:text-slate-600 font-mono text-sm"
                           placeholder="{{ __('app.Collez l\'empreinte officielle pour vérifier l\'intégrité après upload') }}">
                    @error('expected_checksum') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Étiquettes (Tags)') }}</label>
                    
                    @if(count($selectedTags) > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($selectedTags as $tag)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/20 text-indigo-300 rounded-lg text-sm border border-indigo-500/30">
                            {{ $tag }}
                            <button type="button" wire:click="removeTag(@js($tag))" class="hover:text-indigo-100 focus:outline-none cursor-pointer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </span>
                        @endforeach
                    </div>
                    @endif

                    <div class="relative w-full">
                        <input wire:model.live.debounce.300ms="searchTag" wire:keydown.enter.prevent="addTag($event.target.value)" type="text" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none transition-all placeholder:text-slate-600 font-medium" placeholder="{{ __('app.Rechercher ou créer un tag (Entrée pour valider)') }}">
                        
                        @if(strlen($searchTag) > 0)
                        <div class="absolute z-50 left-0 right-0 mt-2 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-48 overflow-y-auto">
                            @forelse($availableTags as $availableTag)
                                @if(!in_array($availableTag->name, $selectedTags))
                                <button type="button" wire:click.prevent="addTag(@js($availableTag->name))" class="w-full flex items-center px-4 py-2 hover:bg-slate-700 text-left text-sm text-slate-300 transition-colors border-b border-white/5 last:border-0 cursor-pointer">
                                    {{ $availableTag->name }}
                                </button>
                                @endif
                            @empty
                                <button type="button" wire:click.prevent="addTag(@js($searchTag))" class="w-full flex items-center px-4 py-2 hover:bg-slate-700 text-left text-sm text-slate-300 transition-colors cursor-pointer">
                                    <span class="text-indigo-400 mr-2">+</span> {{ __('app.Créer') }} "{{ $searchTag }}"
                                </button>
                            @endforelse
                        </div>
                        @endif
                    </div>
                    @error('selectedTags') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Visibilité (Groupe)') }}</label>
                    @php
                        $groupOptions = $groups->map(fn($g) => ['value' => $g->id, 'label' => '🔒 ' . $g->name])
                                               ->prepend(['value' => '', 'label' => '🌍 ' . __('app.Accès Public (Tous les membres)')])
                                               ->toArray();
                    @endphp
                    <x-custom-select wire:model="group_id" :options="$groupOptions" />
                </div>
                
                @role('admin')
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Emplacement de Stockage (Admin seul)') }}</label>
                    @php
                        $storageOptions = $storageLocations->map(fn($loc) => [
                            'value' => $loc->id, 
                            'label' => $loc->name . ' (' . $loc->driver . ')' . ($loc->is_default ? ' - ' . __('app.Défaut') : '')
                        ])->toArray();
                    @endphp
                    <x-custom-select wire:model="storage_id" :options="$storageOptions" />
                </div>
                @endrole
            </div>

            <button type="submit" wire:loading.attr="disabled" x-bind:disabled="!completed" style="background-color: var(--primary-hex)" class="cursor-pointer w-full hover:brightness-110 disabled:opacity-50 text-white font-semibold py-3 rounded-xl shadow-lg mt-6 transition-all flex justify-center items-center gap-2">
                <span wire:loading.remove wire:target="save" x-text="completed ? '{{ __('app.Enregistrer les informations') }}' : (uploading ? '{{ __('app.Upload en cours...') }}' : '{{ __('app.Sélectionnez un fichier pour commencer') }}')">{{ __('app.Sélectionnez un fichier pour commencer') }}</span>
                <span wire:loading wire:target="save">{{ __('app.Traitement en cours...') }}</span>
            </button>
        </form>
    </div>
</div>
