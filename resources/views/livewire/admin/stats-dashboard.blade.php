@php
    use Illuminate\Support\Number;

    $metrics = [
        ['label' => __('app.Images disque'), 'value' => $stats['total_isos'], 'icon' => 'ri-disc-line'],
        ['label' => __('app.Membres'), 'value' => $stats['total_users'], 'icon' => 'ri-user-3-line'],
        ['label' => __('app.Groupes'), 'value' => $stats['total_groups'], 'icon' => 'ri-team-line'],
        ['label' => __('app.Volume total'), 'value' => Number::fileSize($stats['total_size'], precision: 1), 'icon' => 'ri-hard-drive-3-line'],
    ];
    $maxOs = max(1, $osDistribution->max('count') ?? 1);
@endphp

<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Administration')" :title="__('app.Vue d\'ensemble')" :subtitle="__('app.Monitorage de l\'infrastructure Silo')">
        <x-slot:actions>
            <span class="badge badge-ok h-8 px-3 text-[11px]"><span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-ok opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-ok"></span></span>{{ __('app.Opérationnel') }}</span>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="card grid grid-cols-2 overflow-hidden lg:grid-cols-4">
        @foreach ($metrics as $index => $metric)
            <div @class(['relative p-5 sm:p-6', 'border-r border-line' => $index % 2 === 0, 'lg:border-r' => $index < 3, 'border-b border-line lg:border-b-0' => $index < 2])>
                <div class="flex items-center justify-between"><span class="spec">{{ $metric['label'] }}</span><i class="{{ $metric['icon'] }} text-lg text-ink-3"></i></div>
                <p class="mt-4 font-display text-3xl font-semibold tracking-tight text-ink tabular-nums sm:text-4xl">{{ $metric['value'] }}</p>
                @if ($index === 3)<span class="absolute inset-x-0 bottom-0 h-[3px] bg-accent"></span>@endif
            </div>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        {{-- Stockages --}}
        <section class="card p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="panel-title"><i class="ri-server-line text-accent-ink"></i>{{ __('app.Répartition par Stockage') }}</h2>
                <a href="{{ route('admin.settings') }}" wire:navigate class="btn btn-ghost btn-sm">{{ __('app.Gérer') }}<i class="ri-arrow-right-line"></i></a>
            </div>
            <div class="space-y-5">
                @forelse ($storageUsage as $usage)
                    @php
                        $percent = $usage->capacity
                            ? (($usage->capacity - $usage->free) / $usage->capacity) * 100
                            : ($stats['total_size'] > 0 ? ($usage->total_size / $stats['total_size']) * 100 : 0);
                        $tone = $percent > 90 ? 'bg-danger' : ($percent > 75 ? 'bg-warn' : 'bg-accent');
                        $filled = (int) round($percent / 100 * 40);
                    @endphp
                    <div>
                        <div class="mb-2 flex items-end justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="grid size-9 place-items-center rounded-xl border border-line bg-surface-2 text-ink-2"><i class="ri-hard-drive-3-line"></i></span>
                                <div>
                                    <p class="font-mono text-sm font-medium text-ink">{{ $usage->disk }}</p>
                                    <p class="text-xs text-ink-3">{{ trans_choice('app.:count image disque|:count images disque', $usage->count, ['count' => $usage->count]) }}</p>
                                </div>
                            </div>
                            <p class="font-mono text-sm text-ink">{{ Number::fileSize($usage->total_size, precision: 1) }}</p>
                        </div>
                        <div class="meter h-2" title="{{ number_format($percent, 1) }}%"><span class="{{ $tone }}" style="width: {{ $percent }}%"></span></div>
                        @if ($usage->capacity)
                            <p class="mt-1.5 font-mono text-[11px] text-ink-3">{{ Number::fileSize($usage->free, precision: 1) }} {{ __('app.libres sur') }} {{ Number::fileSize($usage->capacity, precision: 1) }} · {{ number_format($percent, 1) }}% {{ __('app.utilisé') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-3">{{ __('app.Aucune donnée disponible') }}</p>
                @endforelse
            </div>
        </section>

        {{-- Systèmes --}}
        <section class="card p-6">
            <h2 class="panel-title mb-5"><i class="ri-pie-chart-2-line text-accent-ink"></i>{{ __('app.Top OS') }}</h2>
            <div class="space-y-4">
                @forelse ($osDistribution as $os)
                    <div class="flex items-center gap-3">
                        <x-ui.disc size="sm" :os="$os->os_type" />
                        <div class="min-w-0 flex-1">
                            <div class="mb-1.5 flex items-baseline justify-between gap-2">
                                <span class="truncate text-sm font-medium text-ink">{{ $os->os_type }}</span>
                                <span class="font-mono text-xs text-ink-3">{{ $os->count }} · {{ round(($os->count / max($stats['total_isos'], 1)) * 100) }}%</span>
                            </div>
                            <div class="meter"><span style="width: {{ $os->count / $maxOs * 100 }}%"></span></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-3">{{ __('app.Aucune donnée disponible') }}</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Derniers fichiers --}}
    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-line px-6 py-4">
            <h2 class="panel-title"><i class="ri-time-line text-accent-ink"></i>{{ __('app.Derniers fichiers ajoutés') }}</h2>
            <a href="{{ route('isos') }}" wire:navigate class="btn btn-ghost btn-sm">{{ __('app.Tout voir') }}<i class="ri-arrow-right-line"></i></a>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.Fichier') }}</th>
                        <th>{{ __('app.Propriétaire & Groupe') }}</th>
                        <th class="text-right">{{ __('app.Taille') }}</th>
                        <th class="text-right">{{ __('app.Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentIsos as $iso)
                        <tr>
                            <td>
                                <a href="{{ route('isos.show', $iso) }}" wire:navigate class="flex items-center gap-3">
                                    <x-ui.disc :file="$iso" size="sm" />
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-ink">{{ $iso->name }}</span>
                                        <span class="block font-mono text-[11px] text-ink-3">{{ $iso->os_type ?? __('app.Inconnu') }}</span>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-ui.avatar :user="$iso->user" class="size-6 text-[10px]" />
                                    <span class="text-ink-2">{{ $iso->user->name ?? __('app.Système') }}</span>
                                    @if ($iso->group)<span class="badge badge-info">{{ $iso->group->name }}</span>@endif
                                </div>
                            </td>
                            <td class="text-right font-mono text-ink-2">{{ Number::fileSize($iso->size, precision: 1) }}</td>
                            <td class="text-right font-mono text-xs whitespace-nowrap text-ink-3">{{ $iso->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-ink-3">{{ __('app.Aucun fichier récent') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
