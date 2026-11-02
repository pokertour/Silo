@php
    $osOptions = [
        ['value' => 'Linux', 'label' => 'Linux'],
        ['value' => 'Windows', 'label' => 'Windows'],
        ['value' => 'macOS', 'label' => 'macOS'],
        ['value' => 'BSD', 'label' => 'BSD'],
        ['value' => 'Autre', 'label' => __('app.Autre')],
    ];
    $archOptions = [
        ['value' => 'x86_64', 'label' => 'amd64 / x86_64'],
        ['value' => 'x86', 'label' => 'i386 / x86'],
        ['value' => 'arm64', 'label' => 'arm64 / aarch64'],
        ['value' => 'armhf', 'label' => 'armhf'],
    ];
    $groupOptions = $groups->map(fn ($g) => ['value' => $g->id, 'label' => $g->name])
        ->prepend(['value' => '', 'label' => __('app.Accès Public (Tous les membres)')])
        ->toArray();
@endphp

<div class="animate-rise" x-data="resumableUpload" x-init="initResumable()">
    <x-ui.page-header :eyebrow="__('app.Téléverser')" :title="__('app.Ajouter une image disque')" :subtitle="__('app.Chargez et décrivez votre image disque.')" />

    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        {{-- Source --}}
        <section class="card flex flex-col p-5 sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="panel-title"><span class="grid size-6 place-items-center rounded-md bg-accent font-mono text-xs font-bold text-on-accent">1</span>{{ __('app.Source') }}</h2>
                <div class="flex rounded-xl border border-line p-0.5" x-show="!uploading && !completed">
                    <button type="button" @click="mode = 'file'" class="btn btn-sm" :class="mode === 'file' ? 'bg-surface-2 text-ink' : 'text-ink-3'"><i class="ri-upload-2-line"></i>{{ __('app.Fichier') }}</button>
                    <button type="button" @click="mode = 'url'" class="btn btn-sm" :class="mode === 'url' ? 'bg-surface-2 text-ink' : 'text-ink-3'"><i class="ri-link"></i>URL</button>
                </div>
            </div>

            <input type="hidden" wire:model="tempFilePath">
            @error('tempFilePath') <p class="field-error mb-3"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror

            {{-- Zone de dépôt --}}
            <div x-show="mode === 'file' && !uploading && !completed" id="resumable-browse"
                 class="group relative flex min-h-[320px] flex-1 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed px-6 py-10 text-center transition"
                 :class="dragging ? 'border-accent bg-accent-soft' : 'border-line-strong hover:border-ink-3'">
                <div class="pointer-events-none absolute inset-0 backdrop-dots opacity-70"></div>
                <div class="relative mb-6 transition duration-500 group-hover:-translate-y-1">
                    <x-ui.disc size="lg" os="linux" class="transition duration-700 group-hover:rotate-180" />
                    <span class="absolute -right-2 -bottom-2 grid size-9 place-items-center rounded-full bg-accent text-lg text-on-accent ring-4 ring-surface"><i class="ri-arrow-up-line"></i></span>
                </div>
                <p class="relative font-display text-lg font-semibold text-ink">{{ __('app.Déposez votre image ici') }}</p>
                <p class="relative mt-1 text-sm text-ink-2">{{ __('app.ou cliquez pour parcourir vos fichiers') }}</p>
                <p class="relative mt-5 flex flex-wrap justify-center gap-1">
                    @foreach (['iso', 'img', 'qcow2', 'vmdk', 'vhdx', 'wim', 'zip'] as $ext)
                        <span class="badge">{{ $ext }}</span>
                    @endforeach
                </p>
                <p class="relative mt-4 text-xs text-ink-3"><i class="ri-refresh-line"></i> {{ __('app.Upload par morceaux, reprise automatique en cas de coupure') }}</p>
            </div>

            {{-- Import depuis une URL --}}
            <div x-show="mode === 'url' && !uploading && !completed" x-cloak class="flex flex-1 flex-col justify-center rounded-2xl border border-line bg-bg-2 p-6">
                <span class="mb-4 grid size-12 place-items-center rounded-2xl bg-accent-soft text-2xl text-accent-ink"><i class="ri-earth-line"></i></span>
                <h3 class="font-display text-lg font-semibold text-ink">{{ __('app.Importer depuis une URL') }}</h3>
                <p class="mt-1 mb-5 text-sm text-ink-2">{{ __('app.Le serveur téléchargera le fichier en arrière-plan — remplissez d\'abord le nom et les métadonnées ci-dessous.') }}</p>
                <label class="label" for="importUrl">URL</label>
                <input id="importUrl" type="url" wire:model="importUrl" class="input input-mono" placeholder="https://releases.ubuntu.com/24.04/ubuntu-24.04-live-server-amd64.iso">
                @error('importUrl') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
            </div>

            {{-- Progression --}}
            <div x-show="uploading" x-cloak class="flex min-h-[320px] flex-1 flex-col items-center justify-center rounded-2xl border border-line bg-bg-2 px-6 text-center">
                <div class="relative mb-6 grid place-items-center">
                    <svg class="absolute size-[132px] -rotate-90" viewBox="0 0 100 100" aria-hidden="true">
                        <circle cx="50" cy="50" r="46" fill="none" stroke="var(--line-strong)" stroke-width="3" />
                        <circle cx="50" cy="50" r="46" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round"
                                stroke-dasharray="289" :stroke-dashoffset="289 - (289 * progress / 100)" class="transition-all duration-300" />
                    </svg>
                    <x-ui.disc size="lg" os="linux" class="animate-spin-slow" />
                </div>
                <p class="font-display text-4xl font-semibold tabular-nums text-ink" x-text="progress + '%'"></p>
                <p class="mt-2 max-w-xs truncate font-mono text-sm text-ink-2" x-text="selectedFile"></p>
                <p class="mt-4 text-xs text-ink-3"><i class="ri-information-line"></i> {{ __('app.(Ne fermez pas cette page)') }}</p>
            </div>

            {{-- Terminé --}}
            <div x-show="completed" x-cloak class="flex min-h-[320px] flex-1 flex-col items-center justify-center rounded-2xl border border-ok/30 bg-ok/5 px-6 text-center">
                <span class="mb-5 grid size-16 place-items-center rounded-full bg-ok/15 text-3xl text-ok"><i class="ri-check-line"></i></span>
                <p class="font-display text-lg font-semibold text-ink">{{ __('app.Fichier prêt') }}</p>
                <p class="mt-1 max-w-xs truncate font-mono text-sm text-ink-2" x-text="selectedFile || @js($originalFileName)"></p>
                <p class="mt-3 text-sm text-ink-2">{{ __('app.Fichier transféré avec succès. Vous pouvez maintenant enregistrer.') }}</p>
            </div>
        </section>

        {{-- Métadonnées --}}
        <section class="card p-5 sm:p-6">
            <h2 class="panel-title mb-5"><span class="grid size-6 place-items-center rounded-md bg-accent font-mono text-xs font-bold text-on-accent">2</span>{{ __('app.Détails') }}</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="name">{{ __('app.Nom / Titre *') }}</label>
                    <input id="name" wire:model="name" type="text" class="input" placeholder="{{ __('app.ex: Ubuntu 24.04 LTS Server') }}" required>
                    @error('name') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">{{ __('app.Type de Système') }}</label>
                    <x-custom-select wire:model="os_type" :options="$osOptions" icon="ri-terminal-box-line" />
                    @error('os_type') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">{{ __('app.Architecture') }}</label>
                    <x-custom-select wire:model="architecture" :options="$archOptions" icon="ri-cpu-line" />
                    @error('architecture') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="version">{{ __('app.Version') }}</label>
                    <input id="version" wire:model="version" type="text" class="input input-mono" placeholder="{{ __('app.ex: 24.04') }}">
                    @error('version') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">{{ __('app.Visibilité (Groupe)') }}</label>
                    <x-custom-select wire:model="group_id" :options="$groupOptions" icon="ri-eye-line" />
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="description">{{ __('app.Description') }}</label>
                    <textarea id="description" wire:model="description" rows="3" class="input" placeholder="{{ __('app.Notes ou instructions supplémentaires...') }}"></textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="expected_checksum">{{ __('app.SHA-256 attendu (optionnel)') }}</label>
                    <div class="relative">
                        <i class="ri-fingerprint-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
                        <input id="expected_checksum" type="text" wire:model="expected_checksum" maxlength="64" spellcheck="false" class="input input-mono pl-10"
                               placeholder="{{ __('app.Collez l\'empreinte officielle pour vérifier l\'intégrité après upload') }}">
                    </div>
                    @error('expected_checksum') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Étiquettes --}}
                <div class="sm:col-span-2">
                    <label class="label" for="tag-search">{{ __('app.Étiquettes (Tags)') }}</label>
                    <div class="relative">
                        <div class="input flex h-auto min-h-11 flex-wrap items-center gap-1.5 py-1.5">
                            @foreach ($selectedTags as $tag)
                                <span wire:key="tag-{{ $loop->index }}" class="inline-flex h-7 items-center gap-1 rounded-lg bg-surface pl-2.5 pr-1 text-xs font-medium text-ink ring-1 ring-line-strong">
                                    {{ $tag }}
                                    <button type="button" wire:click="removeTag(@js($tag))" class="grid size-5 place-items-center rounded-md text-ink-3 hover:bg-surface-2 hover:text-ink"><i class="ri-close-line"></i></button>
                                </span>
                            @endforeach
                            <input id="tag-search" wire:model.live.debounce.300ms="searchTag" wire:keydown.enter.prevent="addTag($event.target.value)" type="text"
                                   class="h-8 min-w-40 flex-1 bg-transparent text-sm outline-none placeholder:text-ink-3"
                                   placeholder="{{ __('app.Rechercher ou créer un tag (Entrée pour valider)') }}">
                        </div>
                        @if (strlen($searchTag) > 0)
                            <div class="absolute z-30 mt-1.5 w-full overflow-hidden rounded-xl border border-line-strong bg-elevated p-1 shadow-2xl">
                                @forelse ($availableTags as $availableTag)
                                    @if (! in_array($availableTag->name, $selectedTags))
                                        <button type="button" wire:click.prevent="addTag(@js($availableTag->name))" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-ink-2 hover:bg-surface-2 hover:text-ink">
                                            <span class="size-2 rounded-full" style="background: {{ $availableTag->color }}"></span>{{ $availableTag->name }}
                                        </button>
                                    @endif
                                @empty
                                    <button type="button" wire:click.prevent="addTag(@js($searchTag))" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-ink-2 hover:bg-surface-2 hover:text-ink">
                                        <i class="ri-add-line text-accent-ink"></i>{{ __('app.Créer') }} « {{ $searchTag }} »
                                    </button>
                                @endforelse
                            </div>
                        @endif
                    </div>
                    @error('selectedTags') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                @role('admin')
                    <div class="sm:col-span-2">
                        <label class="label">{{ __('app.Emplacement de Stockage (Admin seul)') }}</label>
                        @php
                            $storageOptions = $storageLocations->map(fn ($loc) => [
                                'value' => $loc->id,
                                'label' => $loc->name.' · '.$loc->driver.($loc->is_default ? ' · '.__('app.Défaut') : ''),
                            ])->toArray();
                        @endphp
                        <x-custom-select wire:model="storage_id" :options="$storageOptions" icon="ri-hard-drive-3-line" />
                    </div>
                @endrole
            </div>

            {{-- Validation --}}
            <div class="mt-6 flex flex-col gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-ink-3" x-text="mode === 'url' ? @js(__('app.L\'import démarrera en arrière-plan.')) : (completed ? @js(__('app.Tout est prêt.')) : (uploading ? @js(__('app.Upload en cours...')) : @js(__('app.Sélectionnez un fichier pour commencer'))))"></p>
                <button x-show="mode === 'url'" type="button" wire:click="importFromUrl" wire:loading.attr="disabled" wire:target="importFromUrl" class="btn btn-primary btn-lg">
                    <i class="ri-download-cloud-2-line"></i>{{ __('app.Importer') }}
                </button>
                <button x-show="mode === 'file'" type="submit" wire:loading.attr="disabled" wire:target="save" :disabled="!completed" class="btn btn-primary btn-lg">
                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2"><i class="ri-save-3-line"></i>{{ __('app.Enregistrer les informations') }}</span>
                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i>{{ __('app.Traitement en cours...') }}</span>
                </button>
            </div>
        </section>
    </form>

    @script
    <script>
        Alpine.data('resumableUpload', () => ({
                mode: $wire.initialSource === 'url' ? 'url' : 'file',
                uploading: false,
                completed: !!$wire.tempFilePath,
                dragging: false,
                progress: 0,
                selectedFile: '',

                initResumable() {
                    const r = new Resumable({
                        target: @js(route('upload.chunk')),
                        chunkSize: @js(config('upload.chunk_size_mb')) * 1024 * 1024,
                        simultaneousUploads: 3,
                        testChunks: true,
                        throttleProgressCallbacks: 1,
                        query: { _token: @js(csrf_token()) },
                    });

                    r.assignBrowse(document.getElementById('resumable-browse'));
                    // Déposer un fichier n'importe où sur la page fonctionne
                    r.assignDrop(document.body);
                    document.body.addEventListener('dragover', (e) => { e.preventDefault(); this.dragging = true; });
                    document.body.addEventListener('dragleave', () => (this.dragging = false));
                    document.body.addEventListener('drop', () => (this.dragging = false));

                    r.on('fileAdded', (file) => {
                        this.mode = 'file';
                        this.selectedFile = file.fileName;
                        if (!$wire.name) {
                            $wire.set('name', file.fileName.replace(/\.[^/.]+$/, ''));
                        }
                        this.uploading = true;
                        r.upload();
                    });

                    r.on('fileProgress', () => {
                        this.progress = Math.floor(r.progress() * 100);
                    });

                    r.on('fileSuccess', (file, message) => {
                        this.uploading = false;
                        this.completed = true;
                        const response = JSON.parse(message);
                        $wire.set('tempFilePath', response.temp_path);
                        $wire.set('originalFileName', response.original_name);
                    });

                    r.on('fileError', (file, message) => {
                        this.uploading = false;
                        window.toast({ status: 'error', title: @js(__('app.Erreur lors du téléversement')), message: String(message).slice(0, 200) });
                    });
                },
        }));
    </script>
    @endscript
</div>
