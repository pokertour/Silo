<div class="glass w-full rounded-2xl shadow-2xl bg-slate-900/90 border border-white/10" style="backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
    <header class="p-6 border-b border-white/5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-500/10 p-2 rounded-full text-indigo-400">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-100">{{ __('app.Éditer l\'ISO') }}</h3>
        </div>
        <button wire:click="$dispatch('closeModal')" class="p-2 text-slate-400 hover:text-white transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </header>

    <form wire:submit="save">
        <div class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('app.Nom du Fichier') }} <span class="text-red-400">*</span></label>
                <input wire:model="name" type="text" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" required>
                @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('app.Système d\'Exploitation') }}</label>
                    <input wire:model="os_type" type="text" placeholder="ex: Ubuntu, Windows..." class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('app.Version') }}</label>
                    <input wire:model="version" type="text" placeholder="ex: 24.04 LTS" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('app.Architecture') }}</label>
                    <x-custom-select 
                        wire:model="architecture" 
                        :options="[
                            ['value' => '', 'label' => __('app.Optionnel')],
                            ['value' => 'amd64', 'label' => 'x86_64 / amd64'],
                            ['value' => 'arm64', 'label' => 'ARM64 / aarch64'],
                            ['value' => 'i386', 'label' => 'x86 / i386'],
                        ]"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('app.Visibilité (Groupe)') }}</label>
                    @php
                        $groupOptions = $groups->map(fn($g) => [
                            'value' => $g->id, 
                            'label' => '🔒 ' . $g->name
                        ])->prepend(['value' => '', 'label' => '🌍 ' . __('app.Public (Tout le monde)')])->toArray();
                    @endphp
                    <x-custom-select wire:model="group_id" :options="$groupOptions" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('app.Description') }}</label>
                <textarea wire:model="description" rows="3" placeholder="{{ __('app.Informations supplémentaires...') }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"></textarea>
            </div>
        </div>

        <div class="p-6 border-t border-white/5 bg-slate-900/50 flex flex-col md:flex-row gap-3">
            <button type="button" wire:click="$dispatch('closeModal')" class="flex-1 md:flex-none px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-xl transition-all">
                {{ __('app.Annuler') }}
            </button>
            <button type="submit" wire:loading.attr="disabled" style="background-color: var(--primary-hex)" class="flex-1 hover:brightness-110 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-500/10 transition-all flex justify-center items-center gap-2 group">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    {{ __('app.Sauvegarder') }}
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="4.93" x2="19.07" y2="7.76"></line></svg>
                    {{ __('app.En cours...') }}
                </span>
            </button>
        </div>
    </form>
</div>
