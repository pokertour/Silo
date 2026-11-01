<?php

use App\Livewire\Concerns\AuthorizesAdmin;
use Spatie\Activitylog\Models\Activity;
use function Livewire\Volt\{state, with, computed, usesPagination, layout, uses};

layout('components.layouts.app');

uses([AuthorizesAdmin::class]);

usesPagination();


$activities = computed(function () {
    return Activity::with('causer', 'subject')
        ->latest()
        ->paginate(15);
});

?>

<div class="p-6 lg:p-10 space-y-8">
    <header>
        <h2 class="text-3xl font-bold">{{ __('app.Logs d\'Activité') }}</h2>
        <p class="text-slate-500 mt-1">{{ __('app.Suivez toutes les actions effectuées sur la plateforme.') }}</p>
    </header>

    <div class="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <th class="px-6 py-4">{{ __('app.Utilisateur') }}</th>
                        <th class="px-6 py-4">{{ __('app.Action') }}</th>
                        <th class="px-6 py-4">{{ __('app.Sujet') }}</th>
                        <th class="px-6 py-4">{{ __('app.Date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($this->activities as $activity)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-linear-to-tr from-indigo-500/20 to-purple-500/20 flex items-center justify-center text-xs font-bold text-indigo-300">
                                    {{ $activity->causer ? strtoupper(substr($activity->causer->name, 0, 2)) : '??' }}
                                </div>
                                <span class="text-sm font-medium">{{ $activity->causer->name ?? __('app.Système') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                @if($activity->description === 'created') bg-emerald-500/10 text-emerald-400
                                @elseif($activity->description === 'updated') bg-blue-500/10 text-blue-400
                                @elseif($activity->description === 'deleted') bg-red-500/10 text-red-400
                                @else bg-slate-500/10 text-slate-400 @endif">
                                {{ __('app.' . $activity->description) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-slate-300">
                                {{ class_basename($activity->subject_type) }}
                                <span class="text-slate-600 ml-1">#{{ $activity->subject_id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                            {{ $activity->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center text-slate-500">
                            {{ __('app.Aucune activité enregistrée.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 border-t border-white/5 bg-black/20">
            {{ $this->activities->links() }}
        </div>
    </div>
</div>