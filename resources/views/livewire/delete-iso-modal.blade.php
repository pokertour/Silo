<div>
    <x-ui.modal :title="__('app.Supprimer').' ISO'" icon="ri-delete-bin-6-line" tone="danger">
        <p class="text-[15px] text-ink-2">{{ __('app.Êtes-vous sûr de vouloir supprimer définitivement le fichier') }} <strong class="font-semibold text-ink">{{ $isoName }}</strong> ?</p>
        
        <p class="mt-4 flex items-center gap-2 rounded-xl border border-danger/25 bg-danger/10 px-3 py-2.5 text-xs text-danger"><i class="ri-alert-line text-sm"></i>{{ __('app.Cette action est irréversible.') }}</p>
        <x-slot:footer>
            <button type="button" wire:click="$dispatch('closeModal')" class="btn btn-secondary">{{ __('app.Annuler') }}</button>
            <button type="button" wire:click="deleteIso" wire:loading.attr="disabled" wire:target="deleteIso" class="btn btn-danger">
                <i class="ri-delete-bin-6-line" wire:loading.remove wire:target="deleteIso"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="deleteIso"></i>{{ __('app.Supprimer') }}
            </button>
        </x-slot:footer>
    </x-ui.modal>
</div>
