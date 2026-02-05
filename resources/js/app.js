import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const applyTheme = (theme) => {
    const resolved = theme || localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', resolved);
    localStorage.setItem('theme', resolved);
    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        el.textContent = resolved === 'dark' ? 'Dark' : 'Light';
    });
};

const applySidebar = (state) => {
    const resolved = state || localStorage.getItem('sidebar') || 'expanded';
    document.documentElement.setAttribute('data-sidebar', resolved);
    localStorage.setItem('sidebar', resolved);
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    applySidebar();
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    });
    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-sidebar') || 'expanded';
            applySidebar(current === 'collapsed' ? 'expanded' : 'collapsed');
        });
    });
});

Alpine.start();
