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

@php
    $tones = ['created' => 'badge-ok', 'updated' => 'badge-info', 'deleted' => 'badge-danger'];
    $icons = ['IsoFile' => 'ri-album-line', 'User' => 'ri-user-3-line', 'Group' => 'ri-team-line', 'ShareLink' => 'ri-links-line', 'StorageLocation' => 'ri-hard-drive-3-line', 'Invitation' => 'ri-mail-line'];
@endphp

<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Administration')" :title="__('app.Journal d\'audit')" :subtitle="__('app.Suivez toutes les actions effectuées sur la plateforme.')" />

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.Utilisateur') }}</th>
                        <th>{{ __('app.Action') }}</th>
                        <th>{{ __('app.Sujet') }}</th>
                        <th class="text-right">{{ __('app.Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->activities as $activity)
                        @php
                            $type = class_basename($activity->subject_type);
                            $subjectLabel = $activity->subject?->name ?? $activity->subject?->email ?? null;
                        @endphp
                        <tr wire:key="activity-{{ $activity->id }}">
                            <td>
                                <div class="flex items-center gap-2.5">
                                    @if ($activity->causer)
                                        <x-ui.avatar :user="$activity->causer" class="size-7 text-[10px]" />
                                    @else
                                        <span class="grid size-7 place-items-center rounded-full bg-surface-2 text-ink-3"><i class="ri-robot-2-line text-sm"></i></span>
                                    @endif
                                    <span class="text-ink">{{ $activity->causer->name ?? __('app.Système') }}</span>
                                </div>
                            </td>
                            <td><span class="badge {{ $tones[$activity->event ?? $activity->description] ?? '' }}">{{ __('app.'.$activity->description) }}</span></td>
                            <td>
                                <div class="flex items-center gap-2 text-ink-2">
                                    <i class="{{ $icons[$type] ?? 'ri-file-line' }} text-ink-3"></i>
                                    <span class="font-mono text-xs text-ink-3">{{ $type }} #{{ $activity->subject_id }}</span>
                                    @if ($subjectLabel)<span class="truncate text-ink">{{ \Illuminate\Support\Str::limit($subjectLabel, 40) }}</span>@endif
                                </div>
                            </td>
                            <td class="text-right font-mono text-xs whitespace-nowrap text-ink-3" title="{{ $activity->created_at->translatedFormat('j M Y, H:i:s') }}">{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 text-center text-ink-3">{{ __('app.Aucune activité enregistrée.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $this->activities->links('pagination.silo') }}</div>
</div>
