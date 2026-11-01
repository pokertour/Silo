<div class="glass w-full rounded-2xl overflow-hidden shadow-2xl bg-slate-900/90 border border-red-500/20" style="backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
    <header class="p-6 border-b border-white/5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="bg-red-500/10 p-2 rounded-full text-red-500">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-100">{{ __('app.Supprimer') }} ISO</h3>
        </div>
        <button wire:click="$dispatch('closeModal')" class="p-2 text-slate-400 hover:text-white transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </header>

    <div class="p-6">
        <p class="text-slate-300 mb-6">
            {{ __('app.Êtes-vous sûr de vouloir supprimer définitivement le fichier') }} 
            <strong class="text-white">{{ $isoName }}</strong> ?<br>
            <span class="text-red-400 text-sm mt-2 block">{{ __('app.Cette action est irréversible.') }}</span>
        </p>

        <div class="flex items-center gap-3">
            <button wire:click="$dispatch('closeModal')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-medium py-3 rounded-xl transition-all">
                {{ __('app.Annuler') }}
            </button>
            <button wire:click="deleteIso" wire:loading.attr="disabled" class="flex-1 bg-red-600 hover:bg-red-500 text-white font-semibold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center">
                <span wire:loading.remove wire:target="deleteIso">{{ __('app.Confirmer') }}</span>
                <span wire:loading wire:target="deleteIso">{{ __('app.En cours...') }}</span>
            </button>
        </div>
    </div>
</div>
