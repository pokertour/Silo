@php
    $tabs = [
        'general' => ['icon' => 'ri-palette-line', 'label' => __('app.Général')],
        'storage' => ['icon' => 'ri-hard-drive-3-line', 'label' => __('app.Stockages Disponibles')],
        'about' => ['icon' => 'ri-information-line', 'label' => __('app.À propos')],
    ];
    $swatches = ['#c5f82a', '#4cc2ff', '#f6c445', '#ff7a59', '#b58cff', '#34d399', '#6366f1', '#f472b6'];
    $driverOptions = [
        ['value' => 'local', 'label' => 'Local'],
        ['value' => 'nfs', 'label' => 'NFS / Symlink'],
        ['value' => 's3', 'label' => 'Amazon S3 / S3 Compatible'],
        ['value' => 'ftp', 'label' => 'FTP'],
        ['value' => 'sftp', 'label' => 'SFTP (SSH)'],
    ];
    $driverIcons = ['local' => 'ri-hard-drive-3-line', 's3' => 'ri-cloud-line', 'ftp' => 'ri-server-line', 'sftp' => 'ri-terminal-box-line'];
@endphp

<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Administration')" :title="__('app.Configuration')" :subtitle="__('app.Gérez les paramètres globaux et les emplacements de stockage.')" />

    <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        <nav class="scrollbar-none flex gap-1 overflow-x-auto lg:flex-col">
            @foreach ($tabs as $key => $tab)
                <button type="button" wire:click="$set('activeTab', '{{ $key }}')" @class(['nav-item shrink-0', 'is-active' => $activeTab === $key])>
                    <i class="{{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        <div class="min-w-0">
            @if ($activeTab === 'general')
                <form wire:submit="saveSettings" class="card p-6" x-data="{ color: $wire.entangle('primary_color') }">
                    <h2 class="panel-title mb-6"><i class="ri-palette-line text-accent-ink"></i>{{ __('app.Paramètres Généraux') }}</h2>
                    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_300px]">
                        <div class="space-y-6">
                            <div>
                                <label class="label" for="app-name">{{ __('app.Nom de l\'application') }}</label>
                                <input wire:model="app_name" id="app-name" type="text" class="input">
                                @error('app_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <span class="label">{{ __('app.Couleur principale') }}</span>
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach ($swatches as $swatch)
                                        <button type="button" @click="color = @js($swatch)" class="size-9 rounded-xl ring-offset-2 ring-offset-surface transition hover:scale-110"
                                                :class="color.toLowerCase() === @js($swatch) && 'ring-2 ring-ink'" style="background: {{ $swatch }}" title="{{ $swatch }}"></button>
                                    @endforeach
                                    <label class="relative grid size-9 cursor-pointer place-items-center overflow-hidden rounded-xl border border-dashed border-line-strong text-ink-3" title="{{ __('app.Personnalisée') }}">
                                        <i class="ri-add-line"></i>
                                        <input type="color" x-model="color" class="absolute inset-0 cursor-pointer opacity-0">
                                    </label>
                                    <input type="text" x-model="color" maxlength="7" class="input input-mono h-9 w-28 uppercase">
                                </div>
                                @error('primary_color') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <span class="label">{{ __('app.Logo de l\'application') }}</span>
                                <div class="flex items-center gap-4 rounded-2xl border border-line bg-bg-2 p-4">
                                    <div class="grid size-16 shrink-0 place-items-center rounded-2xl border border-line bg-surface p-2">
                                        @if ($logo)
                                            <img src="{{ $logo->temporaryUrl() }}" alt="" class="max-h-full object-contain">
                                        @elseif (\App\Models\SystemSetting::get('app_logo'))
                                            <img src="{{ $current_logo_url }}" alt="" class="max-h-full object-contain">
                                        @else
                                            <x-ui.mark :size="40" />
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap gap-2">
                                            <label for="logo-input" class="btn btn-secondary btn-sm cursor-pointer"><i class="ri-image-add-line"></i>{{ __('app.Changer le logo') }}</label>
                                            @if (\App\Models\SystemSetting::get('app_logo'))
                                                <button type="button" wire:click="deleteLogo" class="btn btn-ghost btn-sm text-danger"><i class="ri-delete-bin-6-line"></i>{{ __('app.Retirer') }}</button>
                                            @endif
                                        </div>
                                        <input type="file" wire:model="logo" id="logo-input" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">
                                        <p class="mt-2 text-xs text-ink-3">{{ __('app.PNG, JPG ou WebP, carré, 200 px minimum.') }}</p>
                                    </div>
                                </div>
                                @error('logo') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Aperçu en direct --}}
                        <div>
                            <span class="label">{{ __('app.Aperçu') }}</span>
                            <div class="rounded-2xl border border-line bg-bg-2 p-5" :style="`--accent: ${color}`">
                                <div class="mb-5 flex items-center gap-2.5"><x-ui.mark :size="28" /><span class="font-display font-bold text-ink">{{ $app_name }}</span></div>
                                <div class="nav-item is-active mb-1"><i class="ri-album-line"></i>{{ __('app.Bibliothèque') }}</div>
                                <div class="nav-item mb-5"><i class="ri-links-line"></i>{{ __('app.Mes Partages') }}</div>
                                <span class="btn btn-sm w-full" :style="`background: ${color}`"><i class="ri-add-line"></i>{{ __('app.Ajouter une image') }}</span>
                                <div class="meter mt-5"><span style="width: 62%"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end border-t border-line pt-5">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-3-line"></i>{{ __('app.Enregistrer les paramètres') }}</button>
                    </div>
                </form>
            @endif

            @if ($activeTab === 'storage')
                <div class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_420px]">
                    <section class="space-y-3">
                        @foreach ($locations as $location)
                            @php $driver = $location->configuration['display_driver'] ?? $location->driver; @endphp
                            <div wire:key="location-{{ $location->id }}" @class(['card flex flex-col gap-4 p-5 sm:flex-row sm:items-center', 'ring-1 ring-accent-ink/40' => $location->is_default])>
                                <span class="grid size-12 shrink-0 place-items-center rounded-2xl border border-line bg-surface-2 text-xl text-ink-2"><i class="{{ $driverIcons[$driver] ?? 'ri-hard-drive-3-line' }}"></i></span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex items-center gap-2 font-mono text-[15px] font-medium text-ink">{{ $location->name }}
                                        @if ($location->is_default)<span class="badge badge-accent">{{ __('app.Défaut') }}</span>@endif
                                    </p>
                                    <p class="mt-0.5 text-xs text-ink-3 uppercase">{{ $driver }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="testConnection({{ $location->id }})" wire:loading.attr="disabled" wire:target="testConnection({{ $location->id }})" class="btn btn-secondary btn-sm">
                                        <i class="ri-pulse-line" wire:loading.remove wire:target="testConnection({{ $location->id }})"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="testConnection({{ $location->id }})"></i>{{ __('app.Tester') }}
                                    </button>
                                    @unless ($location->is_default)
                                        <button type="button" wire:click="setAsDefault({{ $location->id }})" class="icon-btn" title="{{ __('app.Définir par défaut') }}"><i class="ri-star-line"></i></button>
                                        <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                                                @click="$store.dialog.confirm({ title: @js(__('app.Supprimer ce stockage ?')), message: @js($location->name), confirm: @js(__('app.Supprimer')), tone: 'danger' }).then(ok => ok && $wire.deleteLocation({{ $location->id }}))"><i class="ri-delete-bin-6-line"></i></button>
                                    @endunless
                                </div>
                            </div>
                        @endforeach
                    </section>

                    <form wire:submit="saveStorage" class="card h-fit p-6">
                        <h2 class="panel-title mb-5"><i class="ri-add-circle-line text-accent-ink"></i>{{ __('app.Nouveau stockage') }}</h2>
                        <div class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-1">
                                <div>
                                    <label class="label" for="storage-name">{{ __('app.Nom (identifiant interne)') }}</label>
                                    <input wire:model="storage_name" id="storage-name" type="text" class="input input-mono" placeholder="nas_backup">
                                    @error('storage_name') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="label">{{ __('app.Driver') }}</label>
                                    <x-custom-select wire:model.live="storage_driver" :options="$driverOptions" icon="ri-plug-line" />
                                </div>
                            </div>
                            @if (in_array($storage_driver, ['ftp', 'sftp']))
                                <div class="grid grid-cols-[1fr_96px] gap-3">
                                    <div>
                                        <label class="label" for="config-host">{{ __('app.Serveur') }}</label>
                                        <input wire:model="config_host" id="config-host" type="text" class="input input-mono" placeholder="ftp.example.com">
                                    </div>
                                    <div>
                                        <label class="label" for="config-port">Port</label>
                                        <input wire:model="config_port" id="config-port" type="number" class="input input-mono" placeholder="{{ $storage_driver === 'ftp' ? 21 : 22 }}">
                                    </div>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="label" for="config-username">{{ __('app.Username') }}</label>
                                        <input wire:model="config_username" id="config-username" type="text" class="input" autocomplete="off">
                                    </div>
                                    <div>
                                        <label class="label" for="config-password">{{ __('app.Password') }}</label>
                                        <input wire:model="config_password" id="config-password" type="password" class="input" autocomplete="new-password">
                                    </div>
                                </div>
                            @endif
                            @if ($storage_driver === 's3')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="label" for="config-key">{{ __('app.AWS Key') }}</label>
                                        <input wire:model="config_key" id="config-key" type="text" class="input input-mono" autocomplete="off">
                                    </div>
                                    <div>
                                        <label class="label" for="config-secret">{{ __('app.Secret') }}</label>
                                        <input wire:model="config_secret" id="config-secret" type="password" class="input" autocomplete="new-password">
                                    </div>
                                    <div>
                                        <label class="label" for="config-bucket">Bucket</label>
                                        <input wire:model="config_bucket" id="config-bucket" type="text" class="input input-mono">
                                    </div>
                                    <div>
                                        <label class="label" for="config-region">{{ __('app.Region') }}</label>
                                        <input wire:model="config_region" id="config-region" type="text" class="input input-mono" placeholder="eu-west-3">
                                    </div>
                                </div>
                                <div>
                                    <label class="label" for="config-endpoint">Endpoint <span class="normal-case tracking-normal">({{ __('app.optionnel') }})</span></label>
                                    <input wire:model="config_endpoint" id="config-endpoint" type="url" class="input input-mono" placeholder="https://s3.example.com">
                                    @error('config_endpoint') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            @endif
                            @if (in_array($storage_driver, ['local', 'nfs', 'ftp', 'sftp']))
                                <div>
                                    <label class="label" for="config-root">{{ __('app.Chemin racine (Optionnel)') }}</label>
                                    <input wire:model="config_root" id="config-root" type="text" class="input input-mono" placeholder="{{ in_array($storage_driver, ['local', 'nfs']) ? storage_path('app/'.($storage_name ?: 'iso')) : '/' }}">
                                </div>
                            @endif
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line px-3.5 py-3">
                                <input wire:model="storage_is_default" type="checkbox" class="size-4 accent-[var(--accent)]">
                                <span class="text-sm text-ink-2">{{ __('app.Définir comme stockage par défaut') }}</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary mt-6 w-full"><i class="ri-save-3-line"></i>{{ __('app.Enregistrer le Stockage') }}</button>
                    </form>
                </div>
            @endif

            @if ($activeTab === 'about')
                <section class="card relative overflow-hidden p-8">
                    <div class="pointer-events-none absolute inset-0 backdrop-dots opacity-70 [mask-image:linear-gradient(to_left,black,transparent_70%)]"></div>
                    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center">
                        <x-ui.mark :size="72" />
                        <div>
                            <h2 class="font-display text-3xl font-semibold text-ink">Silo</h2>
                            <p class="font-mono text-sm text-ink-2">{{ __('app.Version') }} {{ config('version.current') }}</p>
                        </div>
                    </div>
                    <dl class="relative mt-8 grid gap-4 border-t border-line pt-6 sm:grid-cols-3">
                        <div><dt class="spec">{{ __('app.Créateur') }}</dt><dd class="mt-1 text-ink">Pokertour</dd></div>
                        <div><dt class="spec">{{ __('app.Licence') }}</dt><dd class="mt-1 text-ink">MIT</dd></div>
                        <div><dt class="spec">{{ __('app.Dépôt Git Officiel') }}</dt><dd class="mt-1"><a href="https://github.com/pokertour/Silo" target="_blank" rel="noopener" class="font-mono text-sm text-accent-ink hover:underline">github.com/pokertour/Silo <i class="ri-external-link-line"></i></a></dd></div>
                    </dl>
                </section>
            @endif
        </div>
    </div>
</div>
