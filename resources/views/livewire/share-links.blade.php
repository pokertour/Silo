<div class="p-6 lg:p-10 space-y-8">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-bold">{{ __('app.Liens de Partage') }} <span class="text-indigo-500/50 text-xl">({{ $links->total() }})</span></h2>
            <p class="text-slate-500 mt-1">{{ __('app.Gérez vos liens de partage publics.') }}</p>
        </div>
        <div class="flex items-center w-full md:w-auto mt-4 md:mt-0">
            <div class="relative w-full md:w-64 lg:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('app.Rechercher (Nom, OS...)') }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-200 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all placeholder:text-slate-500">
            </div>
        </div>
    </header>

    @if (session()->has('message'))
        <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-3 rounded-xl text-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="glass rounded-3xl overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-900/50 border-b border-white/5">
                    <th class="p-4 font-semibold text-slate-300 text-sm">{{ __('app.Fichier ISO') }}</th>
                    @role('admin')
                    <th class="p-4 font-semibold text-slate-300 text-sm hidden md:table-cell">{{ __('app.Créé par') }}</th>
                    @endrole
                    <th class="p-4 font-semibold text-slate-300 text-sm hidden lg:table-cell">{{ __('app.Expiration') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm hidden lg:table-cell">{{ __('app.Téléchargements') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm">{{ __('app.Statut') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm">{{ __('app.Lien') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm text-right">{{ __('app.Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($links as $link)
                @php
                    $valid = $link->isValid();
                    $locked = $link->password !== null;
                @endphp
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <div>
                                <div class="font-medium text-slate-200 text-sm">{{ $link->isoFile?->name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $link->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </td>
                    @role('admin')
                    <td class="p-4 text-slate-400 text-sm hidden md:table-cell">{{ $link->user?->name ?? '—' }}</td>
                    @endrole
                    <td class="p-4 hidden lg:table-cell">
                        @if($link->expires_at)
                            <span class="text-xs {{ $link->expires_at->isPast() ? 'text-red-400' : 'text-slate-400' }}">
                                {{ $link->expires_at->format('d/m/Y H:i') }}
                            </span>
                        @else
                            <span class="text-slate-600 text-xs">∞</span>
                        @endif
                    </td>
                    <td class="p-4 text-slate-400 text-sm hidden lg:table-cell">
                        <span class="{{ $link->max_downloads && $link->download_count >= $link->max_downloads ? 'text-red-400' : 'text-slate-400' }}">
                            {{ $link->download_count }}
                            @if($link->max_downloads) / {{ $link->max_downloads }} @else <span class="text-slate-600">/ ∞</span> @endif
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            @if($valid)
                                <span class="flex items-center gap-1.5 text-emerald-400 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ __('app.Actif') }}
                                </span>
                            @else
                                <span class="text-xs text-red-400 font-medium">{{ __('app.Expiré') }}</span>
                            @endif
                            @if($locked)
                                <span class="text-yellow-500/80 mt-0.5" title="{{ __('app.Protégé par mot de passe') }}">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="p-4">
                        <div x-data="{ copied: false }">
                            <button
                                @click="navigator.clipboard.writeText('{{ route('share.show', $link->token) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="text-xs flex items-center gap-1.5 transition-all duration-300"
                                :class="copied ? 'text-emerald-400' : 'text-indigo-400 hover:text-indigo-300'"
                            >
                                <span x-show="!copied" class="flex items-center gap-1">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    {{ __('app.Copier') }}
                                </span>
                                <span x-show="copied" class="flex items-center gap-1" x-cloak>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    {{ __('app.Lien copié !') }}
                                </span>
                            </button>
                        </div>
                    </td>
                    <td class="p-4 text-right">
                        <button @click.prevent="$dispatch('openModal', { component: 'delete-share-link-modal', arguments: { linkId: {{ $link->id }} } })" class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-10 text-center text-slate-500">{{ __('app.Aucun lien de partage.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($links->hasPages())
        <div class="p-4 border-t border-white/5 bg-slate-900/30">
            {{ $links->links() }}
        </div>
        @endif
    </div>
</div>
