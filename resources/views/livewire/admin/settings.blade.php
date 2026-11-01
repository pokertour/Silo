@php
    $tabs = [
        'general' => ['icon' => 'ri-palette-line', 'label' => __('app.Général')],
        'storage' => ['icon' => 'ri-hard-drive-3-line', 'label' => __('app.Stockages Disponibles')],
        'mail' => ['icon' => 'ri-mail-settings-line', 'label' => __('app.E-mail')],
        'about' => ['icon' => 'ri-information-line', 'label' => __('app.À propos')],
    ];
    $swatches = ['#0071e3', '#5856d6', '#af52de', '#ff2d55', '#ff3b30', '#ff9500', '#34c759', '#30b0c7', '#8e8e93'];
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
                                <div class="nav-item is-active mb-1"><i class="ri-disc-line"></i>{{ __('app.Bibliothèque') }}</div>
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

            @if ($activeTab === 'mail')
                <form wire:submit="saveMail" class="card p-6" x-data="{ enabled: $wire.entangle('mail_enabled') }">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="panel-title"><i class="ri-mail-settings-line text-accent-ink"></i>{{ __('app.Serveur d\'envoi (SMTP)') }}</h2>
                            <p class="mt-1 max-w-xl text-sm text-ink-3">{{ __('app.Utilisé pour les invitations, la réinitialisation des mots de passe et les notifications.') }}</p>
                        </div>
                        @if ($mail_enabled && $mail_host)
                            <span class="badge badge-ok"><i class="ri-checkbox-circle-fill"></i>{{ __('app.Configuration personnalisée') }}</span>
                        @else
                            <span class="badge">{{ __('app.Configuration du .env') }} · {{ config('mail.default') }}</span>
                        @endif
                    </div>

                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-line bg-bg-2 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-medium text-ink">{{ __('app.Utiliser ces paramètres') }}</span>
                            <span class="block text-xs text-ink-3">{{ __('app.Désactivé, Silo utilise les variables MAIL_* de l\'environnement.') }}</span>
                        </span>
                        <input type="checkbox" x-model="enabled" class="size-5 shrink-0 accent-[var(--accent)]">
                    </label>

                    <div class="mt-6 space-y-5 transition" :class="!enabled && 'opacity-50'">
                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_110px]">
                            <div>
                                <label class="label" for="mail-host">{{ __('app.Serveur') }}</label>
                                <input wire:model="mail_host" id="mail-host" type="text" class="input input-mono" placeholder="smtp.example.com" autocomplete="off">
                                @error('mail_host') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label" for="mail-port">Port</label>
                                <input wire:model="mail_port" id="mail-port" type="number" min="1" max="65535" class="input input-mono" placeholder="587">
                                @error('mail_port') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <span class="label">{{ __('app.Sécurité') }}</span>
                            <div class="segmented" x-data="{ enc: $wire.entangle('mail_encryption') }">
                                <button type="button" class="chip" :class="enc === 'tls' && 'is-active'" @click="enc = 'tls'; $wire.mail_port = 587">STARTTLS</button>
                                <button type="button" class="chip" :class="enc === 'ssl' && 'is-active'" @click="enc = 'ssl'; $wire.mail_port = 465">SSL / TLS</button>
                                <button type="button" class="chip" :class="enc === 'none' && 'is-active'" @click="enc = 'none'">{{ __('app.Aucune') }}</button>
                            </div>
                            @error('mail_encryption') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="label" for="mail-username">{{ __('app.Username') }}</label>
                                <input wire:model="mail_username" id="mail-username" type="text" class="input" autocomplete="off">
                                @error('mail_username') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label" for="mail-password">{{ __('app.Password') }}</label>
                                <input wire:model="mail_password" id="mail-password" type="password" class="input" autocomplete="new-password"
                                       placeholder="{{ $mail_password_set ? __('app.Inchangé') : '' }}">
                                @if ($mail_password_set)
                                    <label class="mt-2 flex items-center gap-2 text-xs text-ink-3">
                                        <input wire:model="mail_clear_password" type="checkbox" class="size-3.5 accent-[var(--accent)]">{{ __('app.Supprimer le mot de passe enregistré') }}
                                    </label>
                                @endif
                                @error('mail_password') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
                            <div>
                                <label class="label" for="mail-from-address">{{ __('app.Adresse d\'expédition') }}</label>
                                <input wire:model="mail_from_address" id="mail-from-address" type="email" class="input" placeholder="silo@example.com">
                                @error('mail_from_address') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label" for="mail-from-name">{{ __('app.Nom d\'expéditeur') }}</label>
                                <input wire:model="mail_from_name" id="mail-from-name" type="text" class="input" placeholder="{{ config('app.name') }}">
                                @error('mail_from_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col-reverse gap-2 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" wire:click="sendTestMail" wire:loading.attr="disabled" wire:target="sendTestMail" class="btn btn-secondary">
                            <i class="ri-send-plane-line" wire:loading.remove wire:target="sendTestMail"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="sendTestMail"></i>
                            {{ __('app.Envoyer un e-mail de test') }}
                        </button>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-3-line"></i>{{ __('app.Enregistrer') }}</button>
                    </div>
                    <p class="mt-3 text-xs text-ink-3">{{ __('app.Le test utilise la configuration enregistrée et est envoyé à :email.', ['email' => auth()->user()->email]) }}</p>
                </form>
            @endif

            @if ($activeTab === 'about' && $system)
                <div class="space-y-6" x-data="{
                        latest: null,
                        current: @js($system['version']),
                        init() {
                            fetch('https://api.github.com/repos/pokertour/Silo/releases/latest', { headers: { Accept: 'application/vnd.github+json' } })
                                .then(r => r.ok ? r.json() : null)
                                .then(r => { if (r && r.tag_name) this.latest = r })
                                .catch(() => {})
                        },
                        get upToDate() { return this.latest && this.latest.tag_name.replace(/^v/, '') === String(this.current).replace(/^v/, '') },
                    }">
                    <section class="card px-6 pt-12 pb-8 text-center">
                        <x-ui.mark :size="112" class="mx-auto" />
                        <h2 class="mt-6 font-display text-4xl font-semibold tracking-tight text-ink">Silo</h2>
                        <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Version') }} <span class="font-mono">{{ $system['version'] }}</span></p>
                        <p class="mx-auto mt-4 max-w-md text-[15px] text-ink-2">{{ __('app.Un coffre-fort auto-hébergé pour vos images disque : déposez, organisez et partagez ISO, disques de VM et grandes archives.') }}</p>

                        <div class="mt-5 flex min-h-7 justify-center" x-cloak>
                            <template x-if="latest && upToDate">
                                <span class="badge badge-ok"><i class="ri-checkbox-circle-fill"></i>{{ __('app.Silo est à jour') }}</span>
                            </template>
                            <template x-if="latest && !upToDate">
                                <a :href="latest.html_url" target="_blank" rel="noopener" class="badge badge-accent hover:opacity-80">
                                    <i class="ri-sparkling-2-fill"></i><span x-text="@js(__('app.Dernière version disponible')) + ' : ' + latest.tag_name"></span><i class="ri-arrow-right-up-line"></i>
                                </a>
                            </template>
                        </div>

                        <div class="mt-6 flex flex-wrap justify-center gap-2">
                            <a href="https://github.com/pokertour/Silo" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><i class="ri-github-fill"></i>GitHub</a>
                            <a href="https://pokertour.github.io/Silo/" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><i class="ri-global-line"></i>{{ __('app.Site web') }}</a>
                            <a href="https://github.com/pokertour/Silo/releases" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><i class="ri-history-line"></i>{{ __('app.Notes de version') }}</a>
                            <a href="https://github.com/pokertour/Silo/issues/new" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><i class="ri-bug-line"></i>{{ __('app.Signaler un problème') }}</a>
                        </div>
                    </section>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <section class="card overflow-hidden">
                            <h3 class="eyebrow px-[18px] pt-4 pb-2">{{ __('app.Système') }}</h3>
                            @foreach ([
                                ['ri-git-commit-line', __('app.Version'), $system['version']],
                                ['ri-code-s-slash-line', 'PHP', $system['php']],
                                ['ri-stack-line', 'Laravel', $system['laravel']],
                                ['ri-database-2-line', __('app.Base de données'), $system['database']],
                                ['ri-flashlight-line', __('app.Cache / file d\'attente'), $system['cache'].' / '.$system['queue']],
                                ['ri-mail-line', __('app.E-mail'), $system['mail'].' ('.($system['mail_source'] === 'admin' ? __('app.administration') : '.env').')'],
                                ['ri-server-line', __('app.Environnement'), $system['environment']],
                            ] as [$icon, $label, $value])
                                <div class="flex items-center justify-between gap-4 border-t border-line px-[18px] py-2.5 text-sm">
                                    <span class="flex items-center gap-2.5 text-ink-2"><i class="{{ $icon }} text-ink-3"></i>{{ $label }}</span>
                                    <span class="truncate font-mono text-[13px] text-ink">{{ $value }}</span>
                                </div>
                            @endforeach
                        </section>

                        <section class="card overflow-hidden">
                            <h3 class="eyebrow px-[18px] pt-4 pb-2">{{ __('app.Bibliothèque') }}</h3>
                            @foreach (array_filter([
                                ['ri-disc-line', __('app.Images disque'), number_format($system['files'], 0, ',', ' ')],
                                ['ri-pie-chart-2-line', __('app.Volume total'), \Illuminate\Support\Number::fileSize($system['size'], precision: 1)],
                                ['ri-hard-drive-3-line', __('app.Stockages'), $system['storages']],
                                $system['free'] ? ['ri-hard-drive-line', __('app.Espace libre (stockage par défaut)'), $system['free']] : null,
                            ]) as [$icon, $label, $value])
                                <div class="flex items-center justify-between gap-4 border-t border-line px-[18px] py-2.5 text-sm">
                                    <span class="flex items-center gap-2.5 text-ink-2"><i class="{{ $icon }} text-ink-3"></i>{{ $label }}</span>
                                    <span class="font-mono text-[13px] text-ink">{{ $value }}</span>
                                </div>
                            @endforeach
                            <div class="border-t border-line px-[18px] py-4 text-xs text-ink-3">
                                {{ __('app.Créé par') }} <a href="https://github.com/pokertour" target="_blank" rel="noopener" class="text-accent-ink hover:underline">Pokertour</a>
                                · {{ __('app.Publié sous licence') }} <span class="text-ink-2">MIT</span>
                            </div>
                        </section>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
