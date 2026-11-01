@props(['logoUrl'])

<aside class="w-72 glass hidden lg:flex flex-col h-screen fixed z-50">
    <div class="p-8">
        <div class="flex items-center gap-3">
            <img src="{{ $logoUrl }}" alt="" class="w-10 h-10 object-contain">
            <span class="text-xl font-bold tracking-tight">
                @php
                    $name = \App\Models\SystemSetting::get('app_name', 'Silo');
                    $parts = explode(' ', $name, 2);
                @endphp
                {{ $parts[0] }}@if (isset($parts[1]))
                    <span class="text-indigo-500"> {{ $parts[1] }}</span>
                @endif
            </span>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-1.5 py-4 overflow-y-auto custom-scrollbar" x-data="{
        openAdmin: {{ request()->routeIs('admin.share-links') ? 'true' : 'false' }},
        openAccess: {{ request()->routeIs('admin.users') || request()->routeIs('admin.groups') || request()->routeIs('admin.invitations') ? 'true' : 'false' }},
        openSystem: {{ request()->routeIs('admin.audit') || request()->routeIs('admin.settings') || request()->routeIs('admin.stats') ? 'true' : 'false' }}
    }">
        <a href="{{ route('dashboard') }}" wire:navigate
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('dashboard') || request()->routeIs('upload') ? 'sidebar-active text-indigo-400' : 'hover:bg-slate-800/50 text-slate-400 hover:text-white' }} transition-all text-sm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            {{ __('app.Dashboard') }}
        </a>
        <a href="{{ route('isos') }}" wire:navigate
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('isos') ? 'sidebar-active text-indigo-400' : 'hover:bg-slate-800/50 text-slate-400 hover:text-white' }} transition-all text-sm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            {{ __('app.Mes ISOs') }}
        </a>
        <a href="{{ route('share-links') }}" wire:navigate
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('share-links') ? 'sidebar-active text-indigo-400' : 'hover:bg-slate-800/50 text-slate-400 hover:text-white' }} transition-all text-sm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                <polyline points="16 6 12 2 8 6"></polyline>
                <line x1="12" y1="2" x2="12" y2="15"></line>
            </svg>
            {{ __('app.Mes Partages') }}
        </a>

        @role('admin')
            <div class="pt-6 pb-2 px-4 text-[10px] font-bold text-slate-600 uppercase tracking-widest">
                {{ __('app.Administration') }}
            </div>

            <!-- Section: Gestion ISO -->
            <div class="space-y-1">
                <button @click="openAdmin = !openAdmin"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition-all text-sm group"
                    :class="openAdmin ? 'sidebar-active text-white font-semibold' :
                        'hover:bg-slate-800/50 text-slate-400 hover:text-white'">
                    <div class="flex items-center gap-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                        {{ __('app.Gestion ISO') }}
                    </div>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="3" :class="openAdmin ? 'rotate-180' : ''"
                        class="transition-transform duration-200">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div x-show="openAdmin" x-collapse>
                    <div class="mt-1 space-y-1 pr-4">
                        <a href="{{ route('admin.share-links') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.share-links') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Liens de Partage') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section: Accès -->
            <div class="space-y-1">
                <button @click="openAccess = !openAccess"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition-all text-sm group"
                    :class="openAccess ? 'sidebar-active text-white font-semibold' :
                        'hover:bg-slate-800/50 text-slate-400 hover:text-white'">
                    <div class="flex items-center gap-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        {{ __('app.Accès & Membres') }}
                    </div>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="3" :class="openAccess ? 'rotate-180' : ''"
                        class="transition-transform duration-200">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div x-show="openAccess" x-collapse>
                    <div class="mt-1 space-y-1 pr-4">
                        <a href="{{ route('admin.users') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.users') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Utilisateurs') }}
                        </a>
                        <a href="{{ route('admin.groups') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.groups') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Groupes') }}
                        </a>
                        <a href="{{ route('admin.invitations') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.invitations') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Invitations') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section: Système -->
            <div class="space-y-1">
                <button @click="openSystem = !openSystem"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition-all text-sm group"
                    :class="openSystem ? 'sidebar-active text-white font-semibold' :
                        'hover:bg-slate-800/50 text-slate-400 hover:text-white'">
                    <div class="flex items-center gap-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                            <rect x="9" y="9" width="6" height="6"></rect>
                            <line x1="9" y1="1" x2="9" y2="4"></line>
                            <line x1="15" y1="1" x2="15" y2="4"></line>
                            <line x1="9" y1="20" x2="9" y2="23"></line>
                            <line x1="15" y1="20" x2="15" y2="23"></line>
                            <line x1="20" y1="9" x2="23" y2="9"></line>
                            <line x1="20" y1="15" x2="23" y2="15"></line>
                            <line x1="1" y1="9" x2="4" y2="9"></line>
                            <line x1="1" y1="15" x2="4" y2="15"></line>
                        </svg>
                        {{ __('app.Système') }}
                    </div>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="3" :class="openSystem ? 'rotate-180' : ''"
                        class="transition-transform duration-200">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div x-show="openSystem" x-collapse>
                    <div class="mt-1 space-y-1 pr-4">
                        <a href="{{ route('admin.stats') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.stats') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Statistiques') }}
                        </a>
                        <a href="{{ route('admin.settings') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.settings') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Configuration') }}
                        </a>
                        <a href="{{ route('admin.audit') }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2 rounded-xl {{ request()->routeIs('admin.audit') ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-white' }} transition-all text-[11px] ml-9 uppercase tracking-wider">
                            {{ __('app.Logs d\'Activité') }}
                        </a>
                    </div>
                </div>
            </div>
        @endrole
    </nav>

    <div class="p-4 border-t border-slate-800 flex justify-between items-center bg-slate-900/10 relative">
        <span
            class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">{{ __('app.Personnel') }}</span>
        @livewire('notifications-dropdown')
    </div>

    <div class="p-4 border-t border-slate-800">
        @auth
            <div class="group relative rounded-2xl overflow-hidden transition-all duration-300">
                {{-- Hover gradient background --}}
                <div
                    class="absolute inset-0 bg-linear-to-r from-indigo-600/0 to-purple-600/0 group-hover:from-indigo-600/10 group-hover:to-purple-600/10 transition-all duration-500 rounded-2xl">
                </div>
                <div
                    class="relative flex items-center justify-between gap-3 p-4 rounded-2xl border border-transparent group-hover:border-indigo-500/20 transition-all duration-300">
                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-3 flex-1 min-w-0">
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            @if (auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                    class="w-10 h-10 rounded-full object-cover ring-4 ring-indigo-500/30"
                                    alt="avatar">
                            @else
                                <div
                                    class="w-10 h-10 rounded-full bg-linear-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-3xl font-bold ring-4 ring-indigo-500/20">
                                    {{ substr(auth()->user()->name, 0, 2) }}
                                </div>
                            @endif
                            <div
                                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-slate-900">
                            </div>
                        </div>
                        {{-- Name & role --}}
                        <div class="min-w-0">
                            <div class="text-sm font-semibold truncate group-hover:text-indigo-300 transition-colors">
                                {{ auth()->user()->name }}</div>
                            <div
                                class="text-xs text-slate-500 group-hover:text-slate-400 transition-colors flex items-center gap-1">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" class="opacity-60">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                {{ __('app.Mon Profil') }}
                            </div>
                        </div>
                    </a>
                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="{{ __('app.Déconnexion') }}"
                            class="text-slate-500 hover:text-red-400 p-2 rounded-xl hover:bg-red-500/10 transition-all">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</aside>
