<div>
    @isset($jsPath)
        <script>{!! file_get_contents($jsPath) !!}</script>
    @endisset
    @isset($cssPath)
        <style>{!! file_get_contents($cssPath) !!}</style>
    @endisset

    <div x-data="LivewireUIModal()"
         x-on:close.stop="setShowPropertyTo(false)"
         x-on:keydown.escape.window="show && closeModalOnEscape()"
         x-show="show"
         class="fixed inset-0 z-[65] overflow-y-auto"
         style="display: none;">
        <div class="flex min-h-dvh items-end justify-center sm:items-center sm:p-6">
            <div x-show="show"
                 x-on:click="closeModalOnClickAway()"
                 x-transition:enter="duration-300 ease-out" x-transition:enter-start="opacity-0"
                 x-transition:leave="duration-200 ease-in" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

            <div x-show="show && showActiveComponent"
                 x-transition:enter="duration-300 ease-out"
                 x-transition:enter-start="translate-y-8 opacity-0 sm:translate-y-0 sm:scale-95"
                 x-transition:leave="duration-200 ease-in"
                 x-transition:leave-end="translate-y-8 opacity-0 sm:translate-y-0 sm:scale-95"
                 x-bind:class="modalWidth"
                 class="relative w-full transform text-left transition-all sm:w-full"
                 id="modal-container"
                 x-trap.noscroll.inert="show && showActiveComponent"
                 aria-modal="true">
                @forelse($components as $id => $component)
                    <div x-show.immediate="activeComponent == '{{ $id }}'" x-ref="{{ $id }}" wire:key="{{ $id }}">
                        @livewire($component['name'], $component['arguments'], key($id))
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
<!-- Safelist for dynamic modal widths: sm:max-w-sm sm:max-w-md sm:max-w-lg sm:max-w-xl sm:max-w-2xl sm:max-w-3xl sm:max-w-4xl sm:max-w-5xl sm:max-w-6xl sm:max-w-7xl -->
