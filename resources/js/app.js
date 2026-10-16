import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

Alpine.plugin(collapse);
Alpine.plugin(focus);

// Initialize Alpine global theme store
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        if (this.dark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme: this.dark ? 'dark' : 'light' }
        }));
    }
});

// System theme preference listener (when user has not chosen explicit override)
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        const isDark = e.matches;
        if (Alpine.store('theme')) {
            Alpine.store('theme').dark = isDark;
        }
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme: isDark ? 'dark' : 'light', source: 'system' }
        }));
    }
});

window.Alpine = Alpine;
Alpine.start();
