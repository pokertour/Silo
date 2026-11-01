{{-- Palette de commandes : ⌘K / Ctrl+K, « / » ou bouton de recherche. --}}
<div x-data="commandPalette(@js(\App\Support\Navigation::paletteItems(auth()->user())), @js(route('isos')))"
     x-on:open-palette.window="show()"
     x-on:keydown.window.prevent.meta.k="open ? (open = false) : show()"
     x-on:keydown.window.prevent.ctrl.k="open ? (open = false) : show()"
     x-on:keydown.window.slash="if (!['INPUT', 'TEXTAREA'].includes($event.target.tagName) && !$event.target.isContentEditable) { $event.preventDefault(); show() }"
     x-show="open" x-cloak
     class="fixed inset-0 z-[70] flex items-start justify-center px-3 pt-[12vh]">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
    <div x-show="open" x-trap.noscroll="open"
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-2 scale-[0.98] opacity-0"
         @keydown.escape="open = false" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()"
         class="modal-panel relative w-full max-w-xl rounded-3xl">
        <div class="flex items-center gap-3 border-b border-line px-5">
            <i class="ri-search-line text-lg text-ink-3"></i>
            <input x-ref="input" x-model="query" @input="active = 0" type="text" autocomplete="off" spellcheck="false"
                   placeholder="{{ __('app.Rechercher une image ou aller à…') }}"
                   class="h-14 flex-1 bg-transparent text-[15px] text-ink outline-none placeholder:text-ink-3">
            <kbd>esc</kbd>
        </div>
        <ul class="max-h-[50vh] overflow-y-auto p-2" role="listbox">
            <template x-for="(item, index) in results" :key="index + item.label">
                <li>
                    <button type="button" @click="go(item)" @mouseenter="active = index"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm"
                            :class="active === index ? 'bg-surface-2 text-ink' : 'text-ink-2'">
                        <span class="grid size-8 place-items-center rounded-lg border border-line bg-surface" :class="active === index && 'text-accent-ink'"><i :class="item.icon"></i></span>
                        <span class="flex-1 truncate">
                            <template x-if="item.search"><span>{{ __('app.Rechercher') }} « <span class="font-semibold text-ink" x-text="item.label"></span> »</span></template>
                            <template x-if="!item.search"><span x-text="item.label"></span></template>
                        </span>
                        <span class="spec" x-text="item.search ? @js(__('app.Bibliothèque')) : item.group"></span>
                    </button>
                </li>
            </template>
        </ul>
        <div class="flex items-center gap-4 border-t border-line px-5 py-3 text-xs text-ink-3">
            <span class="flex items-center gap-1.5"><kbd>↑</kbd><kbd>↓</kbd> {{ __('app.naviguer') }}</span>
            <span class="flex items-center gap-1.5"><kbd>↵</kbd> {{ __('app.ouvrir') }}</span>
            <span class="ml-auto hidden sm:block">{{ __('app.Astuce : tapez « / » pour ouvrir') }}</span>
        </div>
    </div>
</div>
