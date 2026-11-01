<div class="p-6 lg:p-10 space-y-10">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-bold">{{ __('app.Gestion des Utilisateurs') }} <span class="text-indigo-500/50 text-xl">({{ $users->total() }})</span></h2>
            <p class="text-slate-500 mt-1">{{ __('app.Gérez les membres de votre plateforme et leurs rôles.') }}</p>
        </div>
        <div class="flex items-center w-full md:w-auto mt-4 md:mt-0">
            <div class="relative w-full md:w-64 lg:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Rechercher un utilisateur...') }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-200 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all placeholder:text-slate-500">
            </div>
        </div>
    </header>

    <div class="glass rounded-3xl overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-900/50 border-b border-white/5">
                    <th class="p-4 font-semibold text-slate-300 text-sm">{{ __('app.Nom') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm hidden md:table-cell">{{ __('app.Email') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm">{{ __('app.Rôle') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm hidden lg:table-cell">{{ __('app.Statut') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm hidden lg:table-cell">{{ __('app.Dernière activité') }}</th>
                    <th class="p-4 font-semibold text-slate-300 text-sm text-right">{{ __('app.Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($users as $user)
                @php
                    $isOnline = $user->last_activity_at && $user->last_activity_at->diffInMinutes(now()) < 15;
                @endphp
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="relative shrink-0">
                                <div class="w-9 h-9 rounded-full bg-linear-to-tr from-indigo-500 to-purple-500 overflow-hidden flex items-center justify-center text-xs font-bold shadow-lg">
                                    @if($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($user->name, 0, 2) }}
                                    @endif
                                </div>
                                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-slate-900 {{ $isOnline ? 'bg-emerald-500' : 'bg-slate-600' }}"></div>
                            </div>
                            <span class="font-medium text-slate-200">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="p-4 text-slate-400 text-sm hidden md:table-cell">{{ $user->email }}</td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            @if($user->hasRole('admin'))
                                <span class="bg-indigo-500/20 text-indigo-400 text-[10px] uppercase font-bold px-2 py-1 rounded-full border border-indigo-500/20">Admin</span>
                            @else
                                <span class="bg-slate-800 text-slate-400 text-[10px] uppercase font-bold px-2 py-1 rounded-full">{{ __('app.Membre') }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="p-4 hidden lg:table-cell">
                        @if($isOnline)
                            <span class="flex items-center gap-1.5 text-emerald-400 text-xs font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ __('app.En ligne') }}
                            </span>
                        @elseif($user->last_activity_at)
                            <span class="text-slate-500 text-xs">{{ $user->last_activity_at->diffForHumans() }}</span>
                        @else
                            <span class="text-slate-600 text-xs">{{ __('app.Jamais') }}</span>
                        @endif
                    </td>
                    <td class="p-4 text-slate-500 text-xs hidden lg:table-cell">
                        {{ $user->last_activity_at ? $user->last_activity_at->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            @if($user->id !== auth()->id())
                                <button wire:click="toggleAdmin({{ $user->id }})" class="text-xs {{ $user->hasRole('admin') ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' }} transition-colors whitespace-nowrap">
                                    {{ $user->hasRole('admin') ? __('app.Retirer Admin') : __('app.Rendre Admin') }}
                                </button>
                                <button @click.prevent="$dispatch('openModal', { component: 'delete-user-modal', arguments: { userId: {{ $user->id }} } })" class="p-1.5 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="{{ __('app.Supprimer') }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            @else
                                <span class="text-xs text-slate-600">{{ __('app.Vous (Admin)') }}</span>
                            @endif
                        </div>
                @endforeach
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="p-4 border-t border-white/5 bg-slate-900/30">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    @if (session()->has('error'))
        <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl">
            {{ session('error') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-3 rounded-xl">
            {{ session('message') }}
        </div>
    @endif
</div>
