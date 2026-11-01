@php
    $archOptions = [
        ['value' => '', 'label' => __('app.Optionnel')],
        ['value' => 'x86_64', 'label' => 'x86_64 / amd64'],
        ['value' => 'arm64', 'label' => 'ARM64 / aarch64'],
        ['value' => 'x86', 'label' => 'x86 / i386'],
    ];
    if ($architecture && ! collect($archOptions)->contains('value', $architecture)) {
        $archOptions[] = ['value' => $architecture, 'label' => $architecture];
    }
    $groupOptions = $groups->map(fn ($g) => ['value' => $g->id, 'label' => $g->name])
        ->prepend(['value' => '', 'label' => __('app.Public (Tout le monde)')])->toArray();
@endphp
<div>
    <form wire:submit="save">
        <x-ui.modal :title="__('app.Éditer l\'ISO')" :subtitle="$name" icon="ri-pencil-line">
            <div class="space-y-4">
                <div>
                    <label class="label" for="edit-name">{{ __('app.Nom du Fichier') }}</label>
                    <input wire:model="name" id="edit-name" type="text" class="input" required>
                    @error('name') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="edit-os">{{ __('app.Système d\'Exploitation') }}</label>
                        <input wire:model="os_type" id="edit-os" type="text" class="input" placeholder="Linux, Windows…">
                    </div>
                    <div>
                        <label class="label" for="edit-version">{{ __('app.Version') }}</label>
                        <input wire:model="version" id="edit-version" type="text" class="input input-mono" placeholder="24.04">
                    </div>
                    <div>
                        <label class="label">{{ __('app.Architecture') }}</label>
                        <x-custom-select wire:model="architecture" :options="$archOptions" icon="ri-cpu-line" />
                    </div>
                    <div>
                        <label class="label">{{ __('app.Visibilité (Groupe)') }}</label>
                        <x-custom-select wire:model="group_id" :options="$groupOptions" icon="ri-eye-line" />
                        @error('group_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="label" for="edit-description">{{ __('app.Description') }}</label>
                    <textarea wire:model="description" id="edit-description" rows="3" class="input" placeholder="{{ __('app.Informations supplémentaires...') }}"></textarea>
                </div>
            </div>
            <x-slot:footer>
                <button type="button" wire:click="$dispatch('closeModal')" class="btn btn-secondary">{{ __('app.Annuler') }}</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                    <i class="ri-save-3-line" wire:loading.remove wire:target="save"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="save"></i>{{ __('app.Sauvegarder') }}
                </button>
            </x-slot:footer>
        </x-ui.modal>
    </form>
</div>
