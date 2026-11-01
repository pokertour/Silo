<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-slate-400 hover:text-indigo-400 transition-all focus:outline-none">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        @if($unreadCount > 0)
        <span class="absolute top-1 right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-indigo-500 text-[10px] font-bold text-white items-center justify-center border-2 border-slate-900 leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute bottom-full left-0 mb-3 w-80 glass-heavy rounded-3xl overflow-hidden shadow-2xl z-[9999] border border-white/10 origin-bottom-left">
        
        <div class="p-4 border-b border-white/5 flex items-center justify-between bg-slate-900">
            <h3 class="font-bold text-sm text-slate-200">{{ __('app.Notifications') }}</h3>
            <div class="flex items-center gap-3">
                @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-[10px] text-indigo-400 hover:text-indigo-300 transition-colors uppercase font-bold tracking-wider">
                    {{ __('app.Tout lire') }}
                </button>
                @endif
                <button wire:click="clearAll" class="text-slate-500 hover:text-red-400 transition-colors p-1" title="{{ __('app.Tout effacer') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto custom-scrollbar bg-slate-900">
            @forelse($notifications as $notification)
            <div class="p-4 border-b border-white/5 last:border-0 hover:bg-slate-800 transition-all flex gap-3 relative group {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div class="shrink-0 w-8 h-8 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                    @if(($notification->data['icon'] ?? '') === 'users')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-3-3.87"></path><path d="M9 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle><circle cx="17" cy="7" r="4"></circle></svg>
                    @else
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $notification->data['message'] }}</p>
                    <span class="text-[10px] text-slate-500 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                @if(!$notification->read_at)
                <button wire:click="markAsRead('{{ $notification->id }}')" class="absolute top-4 right-4 w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]" title="{{ __('app.Marquer comme lu') }}"></button>
                @endif
            </div>
            @empty
            <div class="p-10 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-800/50 flex items-center justify-center mx-auto text-slate-600">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </div>
                <p class="text-xs text-slate-500 font-medium">{{ __('app.Aucune notification pour le moment.') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
