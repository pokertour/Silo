import './bootstrap';

import Resumable from 'resumablejs';
window.Resumable = Resumable;

/*
|--------------------------------------------------------------------------
| Interface Silo : thème, dialogues, toasts, palette de commandes
|--------------------------------------------------------------------------
| Alpine est fourni par Livewire : on enregistre nos stores et composants
| à l'événement « alpine:init ».
*/

const THEME_KEY = 'silo-theme';

function applyTheme(theme) {
    const root = document.documentElement;
    root.classList.toggle('light', theme === 'light');
    root.classList.toggle('dark', theme !== 'light');
}

// Réappliqué après chaque navigation wire:navigate (le <html> est conservé,
// mais on garde la logique au même endroit que le script anti-flash du layout).
document.addEventListener('livewire:navigated', () => {
    applyTheme(localStorage.getItem(THEME_KEY) || 'dark');
});

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.store('theme', {
        current: localStorage.getItem(THEME_KEY) || 'dark',
        toggle() {
            this.current = this.current === 'light' ? 'dark' : 'light';
            localStorage.setItem(THEME_KEY, this.current);
            applyTheme(this.current);
        },
    });

    /*
     * Dialogue de confirmation : remplace window.confirm().
     *   $store.dialog.confirm({ title, message, confirm, tone: 'danger' })
     *       .then(ok => ok && $wire.deleteSelected())
     */
    Alpine.store('dialog', {
        open: false,
        title: '',
        message: '',
        confirmLabel: '',
        cancelLabel: '',
        tone: 'default',
        icon: 'ri-question-line',
        defaults: { confirm: 'OK', cancel: 'Cancel' },
        resolver: null,

        confirm(options = {}) {
            this.title = options.title || '';
            this.message = options.message || '';
            this.confirmLabel = options.confirm || this.defaults.confirm;
            this.cancelLabel = options.cancel || this.defaults.cancel;
            this.tone = options.tone || 'default';
            this.icon = options.icon || (this.tone === 'danger' ? 'ri-delete-bin-6-line' : 'ri-question-line');
            this.open = true;

            return new Promise((resolve) => {
                this.resolver = resolve;
            });
        },

        close(result) {
            this.open = false;
            if (this.resolver) {
                this.resolver(result);
                this.resolver = null;
            }
        },
    });

    /*
     * Pile de toasts. Reçoit :
     *  - l'événement Livewire « toastMagic » ({ status, title, message })
     *  - l'événement « toast-magic » ({ type, message }) du gestionnaire de téléchargement
     *  - window.toast({ status, title, message }) depuis n'importe quel script
     */
    Alpine.data('toastStack', () => ({
        toasts: [],
        nextId: 1,

        init() {
            window.toast = (detail) => this.push(detail);
        },

        push(detail = {}) {
            const payload = Array.isArray(detail) ? detail[0] || {} : detail;
            const status = payload.status || payload.type || 'info';
            const toast = {
                id: this.nextId++,
                status,
                title: payload.title || '',
                message: payload.message || '',
                visible: true,
            };
            this.toasts.push(toast);
            setTimeout(() => this.dismiss(toast.id), status === 'error' ? 7000 : 4500);
        },

        dismiss(id) {
            const toast = this.toasts.find((t) => t.id === id);
            if (toast) {
                toast.visible = false;
                setTimeout(() => (this.toasts = this.toasts.filter((t) => t.id !== id)), 250);
            }
        },

        icon(status) {
            return {
                success: 'ri-checkbox-circle-fill',
                error: 'ri-error-warning-fill',
                warning: 'ri-alert-fill',
                info: 'ri-information-fill',
            }[status] || 'ri-information-fill';
        },
    }));

    /*
     * Palette de commandes (⌘K / Ctrl+K) : navigation et recherche
     * dans la bibliothèque. Les entrées sont fournies par le layout.
     */
    Alpine.data('commandPalette', (items = [], searchUrl = '/isos') => ({
        open: false,
        query: '',
        active: 0,
        items,

        get results() {
            const q = this.query.trim().toLowerCase();
            const matches = q
                ? this.items.filter((item) => (item.label + ' ' + (item.group || '')).toLowerCase().includes(q))
                : this.items;
            const list = matches.slice(0, 8);
            if (q) {
                list.unshift({ label: this.query.trim(), group: 'search', icon: 'ri-search-line', search: true });
            }
            return list;
        },

        show() {
            this.open = true;
            this.query = '';
            this.active = 0;
            // Après l'activation du piège à focus (x-trap)
            setTimeout(() => this.$refs.input?.focus(), 60);
        },

        move(delta) {
            const count = this.results.length;
            if (count) {
                this.active = (this.active + delta + count) % count;
            }
        },

        go(item = this.results[this.active]) {
            if (!item) return;
            const url = item.search ? `${searchUrl}?search=${encodeURIComponent(item.label)}` : item.url;
            this.open = false;
            window.Livewire ? window.Livewire.navigate(url) : (window.location.href = url);
        },
    }));

    // Copie dans le presse-papiers avec retour visuel
    Alpine.data('copyable', (text = '') => ({
        copied: false,
        async copy(value = text) {
            try {
                await navigator.clipboard.writeText(value);
                this.copied = true;
                setTimeout(() => (this.copied = false), 1800);
            } catch {
                window.toast?.({ status: 'error', message: value });
            }
        },
    }));
});
