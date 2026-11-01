{{-- Coquille commune des modales Livewire (wire-elements/modal). --}}
@props(['title', 'subtitle' => null, 'icon' => 'ri-file-line', 'tone' => 'default'])
@php
    $iconTone = [
        'danger' => 'bg-danger/12 text-danger',
        'accent' => 'bg-accent-soft text-accent-ink',
    ][$tone] ?? 'bg-surface-2 text-ink-2';
@endphp
<div class="modal-panel">
    <header class="flex items-start gap-4 border-b border-line px-6 py-5">
        <span class="grid size-11 shrink-0 place-items-center rounded-2xl text-xl {{ $iconTone }}"><i class="{{ $icon }}"></i></span>
        <div class="min-w-0 flex-1">
            <h2 class="truncate font-display text-lg font-semibold text-ink">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-0.5 truncate text-sm text-ink-2">{{ $subtitle }}</p>
            @endif
        </div>
        <button type="button" wire:click="$dispatch('closeModal')" class="icon-btn -mt-1 -mr-2" aria-label="{{ __('app.Fermer') }}"><i class="ri-close-line"></i></button>
    </header>
    <div class="px-6 py-5">
        {{ $slot }}
    </div>
    @isset($footer)
        <footer class="flex flex-col-reverse gap-2 border-t border-line bg-surface/60 px-6 py-4 sm:flex-row sm:justify-end">
            {{ $footer }}
        </footer>
    @endisset
</div>
