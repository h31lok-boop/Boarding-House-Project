import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_STORAGE_KEY = 'theme';

const isValidTheme = (theme) => theme === 'light' || theme === 'dark';

const getSystemTheme = () => {
    const mediaQuery = window.matchMedia?.('(prefers-color-scheme: dark)');

    return mediaQuery?.matches ? 'dark' : 'light';
};

const getStoredTheme = () => {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY);

        return isValidTheme(stored) ? stored : null;
    } catch (error) {
        return null;
    }
};

const persistTheme = (theme) => {
    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (error) {
        // Ignore storage failures so private browsing still gets a working toggle.
    }
};

const syncThemeControls = (theme) => {
    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        el.textContent = theme === 'dark' ? 'Dark' : 'Light';
    });

    document.querySelectorAll('[data-theme-icon="light"]').forEach((el) => {
        el.classList.toggle('hidden', theme === 'dark');
    });

    document.querySelectorAll('[data-theme-icon="dark"]').forEach((el) => {
        el.classList.toggle('hidden', theme !== 'dark');
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const nextTheme = theme === 'dark' ? 'light' : 'dark';
        button.setAttribute('aria-pressed', String(theme === 'dark'));
        button.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
        button.setAttribute('title', `Switch to ${nextTheme} mode`);
    });
};

const applyTheme = (theme, options = {}) => {
    const resolved = isValidTheme(theme) ? theme : getStoredTheme() || getSystemTheme();

    document.documentElement.setAttribute('data-theme', resolved);
    document.documentElement.style.colorScheme = resolved;

    if (options.persist) {
        persistTheme(resolved);
    }

    syncThemeControls(resolved);
};

const applySidebar = (state) => {
    let stored = null;

    try {
        stored = localStorage.getItem('sidebar');
    } catch (error) {
        stored = null;
    }

    const resolved = state || stored || 'expanded';
    document.documentElement.setAttribute('data-sidebar', resolved);

    try {
        localStorage.setItem('sidebar', resolved);
    } catch (error) {
        // Ignore storage failures.
    }
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    applySidebar();

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark', { persist: true });
        });
    });

    const systemThemeQuery = window.matchMedia?.('(prefers-color-scheme: dark)');
    systemThemeQuery?.addEventListener?.('change', () => {
        if (! getStoredTheme()) {
            applyTheme();
        }
    });

    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-sidebar') || 'expanded';
            applySidebar(current === 'collapsed' ? 'expanded' : 'collapsed');
        });
    });
});

Alpine.start();
