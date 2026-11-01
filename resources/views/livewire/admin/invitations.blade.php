<div class="p-6 lg:p-10 space-y-10">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-bold">{{ __('app.Gestion des Invitations') }} <span class="text-indigo-500/50 text-xl">({{ $invitations->total() }})</span></h2>
            <p class="text-slate-500 mt-1">{{ __('app.Générez des jetons d\'accès pour inviter de nouveaux utilisateurs.') }}</p>
        </div>
        <div class="flex items-start gap-4 flex-col md:flex-row">
            <div class="glass flex items-center p-1 rounded-xl w-full md:w-auto focus-within:ring-2 focus-within:ring-indigo-500/50 transition-all border border-white/5">
                <input wire:model="email" type="email" placeholder="{{ __('app.Adresse email') }}" class="bg-transparent border-none focus:ring-0 focus:outline-none outline-none text-sm text-slate-200 px-3 py-1 w-full md:w-64 placeholder:text-slate-500">
                <div class="h-6 w-px bg-white/10 mx-2"></div>
                <input wire:model="expires_in_days" type="number" min="1" max="365" class="w-16 bg-transparent text-center border-none focus:ring-0 focus:outline-none outline-none text-sm text-slate-200" title="{{ __('app.Jours de validité') }}">
                <span class="text-xs text-slate-500 mr-3">{{ __('app.jours') }}</span>
                <button wire:click="generate" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-all shadow-lg flex items-center gap-2 whitespace-nowrap">
                    <span wire:loading.remove wire:target="generate">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        {{ __('app.Envoyer') }}
                    </span>
                    <span wire:loading wire:target="generate">{{ __('app.Envoi...') }}</span>
                </button>
            </div>
            @error('email') <span class="text-red-400 text-xs self-center">{{ $message }}</span> @enderror
        </div>
    </header>

    <div class="flex items-center w-full md:w-80">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Rechercher une invitation/email...') }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-200 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all placeholder:text-slate-500">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($invitations as $invitation)
        <div class="glass p-5 rounded-3xl relative overflow-hidden group border {{ $invitation->used_at ? 'border-slate-800/50 opacity-60' : ($invitation->expires_at < now() ? 'border-red-500/20' : 'border-emerald-500/20') }}">
            
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 rounded-xl {{ $invitation->used_at ? 'bg-slate-800 text-slate-500' : ($invitation->expires_at < now() ? 'bg-red-500/10 text-red-400' : 'bg-emerald-500/10 text-emerald-400') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                </div>
                
                @if(!$invitation->used_at && $invitation->expires_at >= now())
                    <button wire:click="revoke({{ $invitation->id }})" class="text-xs text-red-400 opacity-0 group-hover:opacity-100 transition-opacity bg-red-500/10 hover:bg-red-500/20 px-2 py-1 rounded">{{ __('app.Révoquer') }}</button>
                @endif
            </div>

            <h4 class="font-mono text-base font-bold tracking-wider mb-2 text-indigo-100 break-all">{{ $invitation->token }}</h4>
            
            <div class="space-y-1 mt-4 text-xs">
                <div class="flex justify-between border-b border-white/5 pb-1">
                    <span class="text-slate-500">{{ __('app.Statut') }}:</span>
                    @if($invitation->used_at)
                        <span class="text-slate-400">{{ __('app.Utilisée') }} {{ $invitation->email }}</span>
                    @elseif($invitation->expires_at < now())
                        <span class="text-red-400">{{ __('app.Expirée') }}</span>
                    @else
                        <span class="text-emerald-400 font-medium">{{ __('app.Valide') }}</span>
                    @endif
                </div>
                @if($invitation->used_at)
                <div class="flex justify-between pt-1">
                    <span class="text-slate-500">{{ __('app.Enregistré le') }}:</span>
                    <span class="text-slate-300">{{ $invitation->used_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-1">
                    <span class="text-slate-500">{{ __('app.Expiration') }}:</span>
                    <span class="text-slate-300">{{ $invitation->expires_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="text-slate-500">{{ __('Lien direct') }}:</span>
                    <span class="text-indigo-400 cursor-pointer hover:text-indigo-300 truncate max-w-[150px]" title="{{ route('register', ['token' => $invitation->token, 'email' => $invitation->email]) }}" onclick="navigator.clipboard.writeText('{{ route('register', ['token' => $invitation->token, 'email' => $invitation->email]) }}')">{{ __('app.Copier le lien') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12 glass rounded-3xl border border-white/5">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-slate-600 mb-4"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <h3 class="text-lg font-medium text-slate-300">{{ __('app.Aucune invitation.') }}</h3>
            <p class="text-slate-500 mt-1 max-w-sm mx-auto">{{ __('Générez un nouveau jeton pour permettre à un utilisateur de créer un compte.') }}</p>
        </div>
        @endforelse
    </div>
    </div>

    @if($invitations->hasPages())
    <div class="mt-6">
        {{ $invitations->links() }}
    </div>
    @endif
</div>
