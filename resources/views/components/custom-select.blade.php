{{-- Liste déroulante stylée, liée à une propriété Livewire via wire:model. --}}
@props([
    'options' => [],
    'placeholder' => null,
    'icon' => null,
])
<div x-data="{
        open: false,
        value: @entangle($attributes->wire('model')),
        options: @js($options),
        get selectedLabel() {
            const option = this.options.find(o => String(o.value) === String(this.value ?? ''));
            return option ? option.label : @js($placeholder ?? __('app.Sélectionner…'));
        },
        pick(option) { this.value = option.value; this.open = false; },
    }"
     class="relative w-full" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" @click="open = !open"
            class="input flex items-center gap-2 text-left"
            :class="open && 'border-accent-ink ring-4 ring-accent-soft'" :aria-expanded="open">
        @if ($icon)<i class="{{ $icon }} text-ink-3"></i>@endif
        <span class="flex-1 truncate" :class="(value === '' || value === null) ? 'text-ink-3' : 'text-ink'" x-text="selectedLabel"></span>
        <i class="ri-arrow-down-s-line text-lg text-ink-3 transition" :class="open && 'rotate-180'"></i>
    </button>
    <div x-show="open" x-cloak
         x-transition:enter="transition duration-100 ease-out" x-transition:enter-start="-translate-y-1 opacity-0"
         class="absolute z-50 mt-1.5 max-h-64 w-full min-w-48 overflow-y-auto rounded-xl border border-line-strong bg-elevated p-1 shadow-2xl">
        <template x-for="option in options" :key="String(option.value)">
            <button type="button" @click="pick(option)"
                    class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm"
                    :class="String(value ?? '') === String(option.value) ? 'bg-surface-2 text-ink' : 'text-ink-2 hover:bg-surface-2 hover:text-ink'">
                <span class="truncate" x-text="option.label"></span>
                <i class="ri-check-line text-accent-ink" x-show="String(value ?? '') === String(option.value)"></i>
            </button>
        </template>
        <template x-if="options.length === 0">
            <p class="px-3 py-2 text-center text-xs text-ink-3">{{ __('app.Aucune option disponible') }}</p>
        </template>
    </div>
</div>
