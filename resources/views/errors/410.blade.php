<x-layouts.guest :title="__('app.Lien expiré')">
    <div class="w-full max-w-lg glass rounded-3xl p-8 shadow-2xl relative overflow-hidden text-center">
        <div class="flex justify-center mb-6">
            <div class="bg-amber-500/10 p-4 rounded-3xl border border-amber-500/20">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-400"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>
        <h1 class="text-2xl font-bold mb-3">{{ __('app.Ce lien n\'est plus disponible') }}</h1>
        <p class="text-slate-400 text-sm mb-8">{{ __('app.Ce lien de partage est expiré ou a atteint sa limite de téléchargement.') }}</p>
        <p class="text-slate-500 text-xs">{{ __('app.Contactez la personne qui vous a partagé ce fichier pour obtenir un nouveau lien.') }}</p>
    </div>
</x-layouts.guest>
