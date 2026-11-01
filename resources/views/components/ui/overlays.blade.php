{{-- Toasts et dialogue de confirmation (remplacent alert() / confirm()). --}}
<div x-data="toastStack"
     x-on:toast-magic.camel.window="push($event.detail)"
     x-on:toast-magic.window="push($event.detail)"
     class="pointer-events-none fixed inset-x-0 top-3 z-[90] flex flex-col items-center gap-2 px-3 sm:inset-x-auto sm:top-auto sm:right-5 sm:bottom-5 sm:items-end"
     aria-live="polite">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition duration-300 ease-out"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
             x-transition:leave="transition duration-200 ease-in"
             x-transition:leave-end="opacity-0 sm:translate-x-4"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border border-line-strong bg-elevated/95 p-3.5 pr-2 shadow-2xl backdrop-blur-xl">
            <i class="mt-0.5 text-xl leading-none"
               :class="[icon(toast.status), {
                   'text-ok': toast.status === 'success',
                   'text-danger': toast.status === 'error',
                   'text-warn': toast.status === 'warning',
                   'text-info': !['success', 'error', 'warning'].includes(toast.status),
               }]"></i>
            <div class="min-w-0 flex-1">
                <p x-show="toast.title" x-text="toast.title" class="text-sm font-semibold text-ink"></p>
                <p x-show="toast.message" x-text="toast.message" class="text-[13px] leading-snug text-ink-2"></p>
            </div>
            <button type="button" @click="dismiss(toast.id)" class="icon-btn size-7" aria-label="{{ __('app.Fermer') }}"><i class="ri-close-line text-base"></i></button>
        </div>
    </template>
</div>

<div x-data
     x-init="$store.dialog.defaults = { confirm: @js(__('app.Confirmer')), cancel: @js(__('app.Annuler')) }"
     x-show="$store.dialog.open"
     x-on:keydown.escape.window="$store.dialog.open && $store.dialog.close(false)"
     class="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-6"
     role="alertdialog" aria-modal="true" x-cloak>
    <div x-show="$store.dialog.open" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$store.dialog.close(false)"></div>
    <div x-show="$store.dialog.open"
         x-trap.noscroll="$store.dialog.open"
         x-transition:enter="transition duration-300 ease-out"
         x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-end="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
         class="modal-panel relative w-full sm:max-w-md">
        <div class="flex gap-4 p-6">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl text-2xl"
                  :class="$store.dialog.tone === 'danger' ? 'bg-danger/12 text-danger' : 'bg-accent-soft text-accent-ink'">
                <i :class="$store.dialog.icon"></i>
            </span>
            <div class="min-w-0 pt-1">
                <h2 class="font-display text-lg font-semibold text-ink" x-text="$store.dialog.title"></h2>
                <p class="mt-1 text-sm text-ink-2" x-show="$store.dialog.message" x-text="$store.dialog.message"></p>
            </div>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-line bg-surface/60 px-6 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" @click="$store.dialog.close(false)" x-text="$store.dialog.cancelLabel"></button>
            <button type="button" class="btn" :class="$store.dialog.tone === 'danger' ? 'btn-danger' : 'btn-primary'"
                    @click="$store.dialog.close(true)" x-text="$store.dialog.confirmLabel"></button>
        </div>
    </div>
</div>
