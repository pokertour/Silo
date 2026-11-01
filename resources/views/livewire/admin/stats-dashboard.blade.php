@php
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
@endphp

<div class="p-6 lg:p-10 space-y-8 animate-in fade-in duration-700">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-white uppercase font-display italic">Dashboard <span class="text-indigo-500">Stats</span></h2>
            <p class="text-slate-500 mt-1 font-medium italic opacity-70">{{ __('app.Monitorage de l\'infrastructure Silo') }}</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-1">{{ __('app.Status Système') }}</span>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-black uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    {{ __('app.Online') }}
                </div>
            </div>
        </div>
    </header>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-1">
        <x-stat-card 
            :title="__('app.Total ISO Files')" 
            :value="$stats['total_isos']" 
            color="indigo"
            class="hover:-rotate-1"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            :title="__('app.Membres Actifs')" 
            :value="$stats['total_users']" 
            color="purple"
            class="hover:rotate-1"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            :title="__('app.Groupes Organisation')" 
            :value="$stats['total_groups']" 
            color="emerald"
            class="hover:-rotate-1"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            :title="__('app.Usage Stockage Global')" 
            :value="formatBytes($stats['total_size'])" 
            color="pink"
            class="hover:rotate-1"
        >
            <x-slot:icon>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Storage Distribution -->
        <div class="lg:col-span-8 glass p-8 rounded-[3rem] space-y-8 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-200 flex items-center gap-3 italic">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v10l4.5 4.5"></path><circle cx="12" cy="12" r="10"></circle></svg>
                    </div>
                    {{ __('app.Répartition par Stockage') }}
                </h3>
            </div>

            <div class="space-y-6">
                @foreach($storageUsage as $usage)
                    <div class="group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest italic">{{ $usage->disk }}</span>
                                <span class="bg-indigo-500/20 text-indigo-400 text-[9px] font-black px-2 py-0.5 rounded-full border border-indigo-500/30 uppercase tracking-widest">{{ $usage->count }} {{ strtolower(__('app.Fichiers')) }}</span>
                            </div>
                            <span class="text-sm font-black text-white italic">{{ formatBytes($usage->total_size) }}</span>
                        </div>
                        <div class="w-full h-3 bg-slate-900/50 rounded-full overflow-hidden border border-white/5 relative">
                            @php
                                if ($usage->capacity) {
                                    // Occupation physique réelle du disque
                                    $percent = (($usage->capacity - $usage->free) / $usage->capacity) * 100;
                                } else {
                                    $percent = $stats['total_size'] > 0 ? ($usage->total_size / $stats['total_size']) * 100 : 0;
                                }
                                $barColor = $percent > 90 ? 'from-red-600 to-red-400' : ($percent > 75 ? 'from-amber-600 to-amber-400' : 'from-indigo-600 to-indigo-400');
                            @endphp
                            <div class="h-full bg-linear-to-r {{ $barColor }} rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(79,70,229,0.35)]" style="width: {{ $percent }}%"></div>
                        </div>
                        @if($usage->capacity)
                            <p class="text-[10px] text-slate-500 mt-1.5 uppercase tracking-widest font-bold">
                                {{ formatBytes($usage->free) }} {{ __('app.libres sur') }} {{ formatBytes($usage->capacity) }} ({{ number_format($percent, 1) }}% {{ __('app.utilisé') }})
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Empty State -->
            @if($storageUsage->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-slate-600 italic font-medium uppercase tracking-widest text-xs opacity-50">{{ __('app.Aucune donnée disponible') }}</p>
                </div>
            @endif
        </div>

        <!-- OS Distribution -->
        <div class="lg:col-span-4 glass p-8 rounded-[3rem] flex flex-col items-center justify-center relative overflow-hidden">
            <h3 class="w-full text-xl font-bold text-slate-200 mb-8 flex items-center gap-3 italic">
                <div class="w-10 h-10 rounded-2xl bg-pink-500/10 text-pink-400 flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 10V4h-6v6H4v6h6v6h6v-6h6v-6h-6z"></path></svg>
                </div>
                {{ __('app.Top OS') }}
            </h3>
            
            <div class="w-full space-y-4">
                @foreach($osDistribution as $os)
                    <div class="flex items-center justify-between p-4 bg-slate-900/40 rounded-3xl border border-white/5 transition-all hover:bg-slate-900/60">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-white/5 flex items-center justify-center text-lg">
                                @if(stripos($os->os_type, 'windows') !== false) 🪟
                                @elseif(stripos($os->os_type, 'linux') !== false) 🐧
                                @elseif(stripos($os->os_type, 'esx') !== false || stripos($os->os_type, 'vmware') !== false) ☁️
                                @else 📦 @endif
                            </div>
                            <div>
                                <div class="text-sm font-black text-white uppercase italic tracking-tighter">{{ $os->os_type }}</div>
                                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $os->count }} ISOs</div>
                            </div>
                        </div>
                        <div class="text-indigo-400 font-black italic">{{ round(($os->count / max($stats['total_isos'], 1)) * 100) }}%</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent ISOs Table -->
    <div class="glass rounded-[3rem] overflow-hidden">
        <div class="p-8 pb-4 border-b border-white/5 flex items-center justify-between">
             <h3 class="text-xl font-bold text-slate-200 flex items-center gap-3 italic">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10"></path><path d="M18 20V4"></path><path d="M6 20v-4"></path></svg>
                </div>
                {{ __('app.Derniers fichiers ajoutés') }}
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/30 border-b border-white/5 font-display tracking-widest text-[10px] font-black uppercase text-slate-500">
                        <th class="px-8 py-5">{{ __('app.Fichier') }}</th>
                        <th class="px-8 py-5">{{ __('app.Propriétaire & Groupe') }}</th>
                        <th class="px-8 py-5">{{ __('app.Taille') }}</th>
                        <th class="px-8 py-5">{{ __('app.Date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentIsos as $iso)
                    <tr class="hover:bg-white/2 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-white/10 flex items-center justify-center transition-transform group-hover:scale-110 group-hover:rotate-3 shadow-lg">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-indigo-400 opacity-80"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-100 italic tracking-tight">{{ $iso->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest opacity-60">{{ $iso->os_type ?? __('app.Inconnu') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1">
                                <div class="text-sm font-bold text-slate-200">{{ $iso->user->name ?? __('app.Système') }}</div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-0.5"></span>
                                    <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ $iso->group->name ?? __('app.Aucun') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-sm font-black text-white italic opacity-80">{{ formatBytes($iso->size) }}</td>
                        <td class="px-8 py-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $iso->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center text-slate-600 font-bold uppercase italic tracking-widest text-xs opacity-50">{{ __('app.Aucun fichier récent') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
