<div>
    <x-ui.modal :title="__('app.Partager')" :subtitle="$isoName" icon="ri-share-forward-line" tone="accent">
        @if (! $generated_url)
            <div class="space-y-4">
                <div>
                    <label class="label" for="share-password">{{ __('app.Mot de passe (Optionnel)') }}</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i class="ri-lock-password-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
                            <input wire:model="password" id="share-password" type="text" autocomplete="off" class="input input-mono pl-10" placeholder="{{ __('app.Laisse vide pour aucun mot de passe') }}">
                        </div>
                        <button type="button" wire:click="generatePassword" class="btn btn-secondary px-3" title="{{ __('app.Générer un mot de passe') }}"><i class="ri-magic-line"></i></button>
                    </div>
                    @error('password') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="share-max">{{ __('app.Limite de téléchargements') }}</label>
                        <input wire:model="max_downloads" id="share-max" type="number" min="1" step="1" inputmode="numeric" class="input input-mono" placeholder="{{ __('app.Illimité') }}">
                        @error('max_downloads') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="share-expires">{{ __("app.Date d'expiration") }}</label>
                        <input wire:model="expires_at" id="share-expires" type="datetime-local" class="input input-mono">
                        @error('expires_at') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="flex items-start gap-2 rounded-xl bg-surface p-3 text-xs text-ink-2"><i class="ri-information-line mt-px text-sm text-accent-ink"></i>{{ __('app.Toute personne disposant du lien pourra télécharger le fichier. Vous serez notifié à chaque téléchargement.') }}</p>
            </div>
        @else
            <div x-data="copyable(@js($generated_url))" class="space-y-4">
                <div class="flex items-center gap-3 rounded-2xl border border-ok/30 bg-ok/10 p-4 text-sm text-ink">
                    <i class="ri-checkbox-circle-fill text-xl text-ok"></i>{{ __('app.Lien généré avec succès !') }}
                </div>
                <div>
                    <label class="label">{{ __('app.Lien Public') }}</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $generated_url }}" onclick="this.select()" class="input input-mono flex-1 text-[12.5px]">
                        <button type="button" class="btn btn-primary" @click="copy()"><i :class="copied ? 'ri-check-line' : 'ri-file-copy-line'"></i><span x-text="copied ? @js(__('app.Copié !')) : @js(__('app.Copier'))"></span></button>
                    </div>
                </div>
                <dl class="grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-surface p-3"><dt class="spec">{{ __('app.Mot de passe') }}</dt><dd class="mt-1 truncate font-mono text-sm text-ink">{{ $password ?: __('app.Aucun') }}</dd></div>
                    <div class="rounded-xl bg-surface p-3"><dt class="spec">{{ __('app.Limite') }}</dt><dd class="mt-1 font-mono text-sm text-ink">{{ $max_downloads ?: '∞' }}</dd></div>
                    <div class="rounded-xl bg-surface p-3"><dt class="spec">{{ __('app.Expiration') }}</dt><dd class="mt-1 truncate font-mono text-sm text-ink">{{ $expires_at ? \Illuminate\Support\Carbon::parse($expires_at)->translatedFormat('j M') : '∞' }}</dd></div>
                </dl>
            </div>
        @endif

        <x-slot:footer>
            @if (! $generated_url)
                <button type="button" wire:click="$dispatch('closeModal')" class="btn btn-secondary">{{ __('app.Annuler') }}</button>
                <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" class="btn btn-primary">
                    <i class="ri-link" wire:loading.remove wire:target="generate"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="generate"></i>{{ __('app.Générer le lien') }}
                </button>
            @else
                <button type="button" wire:click="$dispatch('closeModal')" class="btn btn-secondary">{{ __('app.Fermer') }}</button>
            @endif
        </x-slot:footer>
    </x-ui.modal>
</div>
