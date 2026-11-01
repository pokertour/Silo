@props([
    'options' => [],
    'selected' => null,
    'placeholder' => __('Sélectionner...'),
    'name' => '',
])

<div x-data="{ 
    open: false, 
    value: @entangle($attributes->wire('model')),
    options: {{ json_encode($options) }},
    get selectedLabel() {
        let opt = this.options.find(o => o.value == this.value);
        return opt ? opt.label : '{{ $placeholder }}';
    }
}" 
class="relative w-full" 
@click.away="open = false">
    
    <!-- Trigger -->
    <button type="button" 
            @click="open = !open"
            class="w-full flex items-center justify-between bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all group/sel hover:bg-slate-800/50"
            :class="open ? 'ring-2 ring-indigo-500/50 border-indigo-500' : ''">
        <span class="truncate font-medium" :class="!value ? 'text-slate-500' : 'text-slate-100'" x-text="selectedLabel"></span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-500 group-hover/sel:text-indigo-400 transition-all" :class="open ? 'rotate-180 text-indigo-400' : ''">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <!-- Dropdown -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-[100] w-full mt-2 glass-heavy rounded-2xl border border-white/10 shadow-2xl py-2 max-h-60 overflow-y-auto custom-scrollbar"
         style="display: none;">
        
        <template x-for="option in options" :key="option.value">
            <button type="button" 
                    @click="value = option.value; open = false; $dispatch('change', option.value)"
                    class="w-full text-left px-4 py-3 text-sm transition-all flex items-center justify-between group/opt"
                    :class="value == option.value ? 'bg-slate-800 text-indigo-300' : 'text-slate-300 hover:bg-slate-800 hover:text-white'">
                <span x-text="option.label"></span>
                <svg x-show="value == option.value" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-indigo-400">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </button>
        </template>

        @if(count($options) === 0)
            <div class="px-4 py-3 text-xs text-slate-500 text-center italic">
                {{ __('app.Aucune option disponible') }}
            </div>
        @endif
    </div>
</div>
