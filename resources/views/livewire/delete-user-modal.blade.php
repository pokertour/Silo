<div class="glass w-full rounded-2xl overflow-hidden shadow-2xl bg-slate-900/90 border border-red-500/20" style="backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
    <header class="p-6 border-b border-white/5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="bg-red-500/10 p-2 rounded-full text-red-500">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-100">{{ __('app.Supprimer') }} {{ __('app.Utilisateur') }}</h3>
        </div>
        <button wire:click="$dispatch('closeModal')" class="p-2 text-slate-400 hover:text-white transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </header>

    <div class="p-6">
        <p class="text-slate-300 mb-2">
            {{ __('app.Êtes-vous sûr de vouloir supprimer') }}
            <strong class="text-white">{{ $userName }}</strong> ?
        </p>
        <p class="text-slate-500 text-sm mb-6">{{ $userEmail }}</p>
        <span class="text-red-400 text-sm block mb-6">{{ __('app.Cette action est irréversible.') }}</span>

        <div class="flex items-center gap-3">
            <button wire:click="$dispatch('closeModal')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-medium py-3 rounded-xl transition-all">
                {{ __('app.Annuler') }}
            </button>
            <button wire:click="deleteUser" wire:loading.attr="disabled" class="flex-1 bg-red-600 hover:bg-red-500 text-white font-semibold py-3 rounded-xl shadow-lg transition-all flex justify-center items-center">
                <span wire:loading.remove wire:target="deleteUser">{{ __('app.Confirmer') }}</span>
                <span wire:loading wire:target="deleteUser">{{ __('app.En cours...') }}</span>
            </button>
        </div>
    </div>
</div>
