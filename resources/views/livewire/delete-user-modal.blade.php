<div>
    <x-ui.modal :title="__('app.Supprimer').' '.__('app.Utilisateur')" icon="ri-user-unfollow-line" tone="danger">
        <p class="text-[15px] text-ink-2">{{ __('app.Êtes-vous sûr de vouloir supprimer') }} <strong class="font-semibold text-ink">{{ $userName }}</strong> ?</p>
        <p class="mt-1 font-mono text-xs text-ink-3">{{ $userEmail }}</p>
        <p class="mt-4 flex items-center gap-2 rounded-xl border border-danger/25 bg-danger/10 px-3 py-2.5 text-xs text-danger"><i class="ri-alert-line text-sm"></i>{{ __('app.Cette action est irréversible.') }}</p>
        <x-slot:footer>
            <button type="button" wire:click="$dispatch('closeModal')" class="btn btn-secondary">{{ __('app.Annuler') }}</button>
            <button type="button" wire:click="deleteUser" wire:loading.attr="disabled" wire:target="deleteUser" class="btn btn-danger">
                <i class="ri-delete-bin-6-line" wire:loading.remove wire:target="deleteUser"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="deleteUser"></i>{{ __('app.Supprimer') }}
            </button>
        </x-slot:footer>
    </x-ui.modal>
</div>
