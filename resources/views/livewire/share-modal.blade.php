<div class="glass w-full rounded-2xl overflow-hidden shadow-2xl bg-slate-900/90" style="backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
    <header class="p-6 border-b border-white/5 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-100">{{ __('app.Partager') }} : {{ $isoName }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ __('app.Générez un lien public sécurisé.') }}</p>
        </div>
        <button wire:click="$dispatch('closeModal')" class="p-2 text-slate-400 hover:text-white transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </header>

    <div class="p-6 space-y-6">
        @if(!$generated_url)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Mot de passe (Optionnel)') }}</label>
                <div class="flex items-center gap-2">
                    <input wire:model="password" id="share-password" type="text" class="flex-1 w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none text-slate-200 font-mono" placeholder="{{ __('app.Laisse vide pour aucun mot de passe') }}">
                    <button type="button" wire:click="generatePassword" title="{{ __('app.Générer un mot de passe') }}" class="shrink-0 p-3 bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-400 rounded-xl border border-indigo-500/20 transition-all">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a5 5 0 0 1 5 5v2H7V7a5 5 0 0 1 5-5z"/><rect x="3" y="9" width="18" height="13" rx="2"/><line x1="12" y1="13" x2="12" y2="17"/></svg>
                    </button>
                </div>
                @error('password') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Limite de téléchargements') }}</label>
                <input wire:model="max_downloads" type="number" min="1" step="1" inputmode="numeric" pattern="[0-9]*" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none text-slate-200" placeholder="{{ __('app.Illimité') }}">
                @error('max_downloads') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">{{ __("app.Date d'expiration") }}</label>
                <input wire:model="expires_at" type="datetime-local" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/50 outline-none text-slate-200" style="color-scheme: dark;">
                @error('expires_at') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <button wire:click="generate" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl shadow-lg transition-all flex justify-center mt-2">
            <span wire:loading.remove wire:target="generate">{{ __('app.Générer le lien') }}</span>
            <span wire:loading wire:target="generate">{{ __('app.En cours...') }}</span>
        </button>
        @else
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/50 rounded-xl mb-4">
            <div class="flex items-center gap-2 text-emerald-400 font-bold mb-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                {{ __('app.Lien généré avec succès !') }}
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">{{ __('app.Lien Public') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $generated_url }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-300 font-mono outline-none selection:bg-indigo-500/50" onclick="this.select()">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-black/20 p-2 rounded-lg text-slate-300 border border-white/5">
                        <span class="text-slate-500 block mb-0.5">{{ __('app.Mot de passe') }}</span>
                        {{ $password ?: __('app.Aucun') }}
                    </div>
                    <div class="bg-black/20 p-2 rounded-lg text-slate-300 border border-white/5">
                        <span class="text-slate-500 block mb-0.5">{{ __('app.Limite') }}</span>
                        {{ $max_downloads ?: __('app.Illimité') }}
                    </div>
                </div>
            </div>
        </div>
        <button wire:click="$dispatch('closeModal')" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-medium py-3 rounded-xl transition-all">
            {{ __('app.Fermer') }}
        </button>
        @endif
    </div>
</div>
