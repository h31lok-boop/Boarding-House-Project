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

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    });
});

Alpine.start();
