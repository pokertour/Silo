<x-layouts.guest :title="__('app.Lien expiré')">
    <div class="w-full max-w-md animate-rise text-center">
        <div class="relative mx-auto mb-8 w-fit">
            <x-ui.disc size="xl" class="opacity-40 grayscale" />
            <span class="absolute inset-0 grid place-items-center"><span class="grid size-12 place-items-center rounded-full border border-line-strong bg-elevated text-2xl text-danger"><i class="ri-link-unlink"></i></span></span>
        </div>
        <p class="eyebrow mb-2">410 · {{ __('app.Lien expiré') }}</p>
        <h1 class="font-display text-3xl font-semibold text-ink">{{ __('app.Ce lien n\'est plus disponible') }}</h1>
        <p class="mt-3 text-[15px] text-ink-2">{{ __('app.Ce lien de partage est expiré ou a atteint sa limite de téléchargement.') }}</p>
        <p class="mt-1 text-sm text-ink-3">{{ __('app.Contactez la personne qui vous a partagé ce fichier pour obtenir un nouveau lien.') }}</p>
    </div>
</x-layouts.guest>
