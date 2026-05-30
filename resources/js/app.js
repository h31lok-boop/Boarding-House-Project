import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const focusableSelector = [
    'a[href]',
    'area[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    'iframe',
    'object',
    'embed',
    '[contenteditable]',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

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

const setupModalIsolation = () => {
    let activeModal = null;
    let focusBeforeOpen = null;
    let queued = false;
    const lockedElements = new Map();

    const isVisible = (el) => {
        const style = window.getComputedStyle(el);

        return style.display !== 'none'
            && style.visibility !== 'hidden'
            && el.getClientRects().length > 0;
    };

    const isModalCandidate = (el) => {
        if (!(el instanceof HTMLElement)) {
            return false;
        }

        if (el.matches('[data-modal-root]')) {
            return true;
        }

        return el.classList.contains('fixed')
            && el.classList.contains('inset-0')
            && el.classList.contains('z-50')
            && (
                el.hasAttribute('x-show')
                || /modal/i.test(el.id || '')
                || Boolean(el.querySelector('form, .ui-card'))
            );
    };

    const openModals = () => [...document.querySelectorAll('[data-modal-root], .fixed.inset-0.z-50')]
        .filter((el) => isModalCandidate(el) && isVisible(el));

    const focusablesIn = (modal) => [...modal.querySelectorAll(focusableSelector)]
        .filter((el) => {
            const style = window.getComputedStyle(el);

            return style.display !== 'none'
                && style.visibility !== 'hidden'
                && !el.hasAttribute('disabled')
                && el.getClientRects().length > 0;
        });

    const focusModal = (modal) => {
        const focusables = focusablesIn(modal);
        const target = focusables[0] || modal;

        if (!modal.hasAttribute('tabindex')) {
            modal.setAttribute('tabindex', '-1');
        }

        target.focus({ preventScroll: true });
    };

    const lockElement = (el) => {
        if (!(el instanceof HTMLElement) || lockedElements.has(el)) {
            return;
        }

        lockedElements.set(el, {
            ariaHidden: el.getAttribute('aria-hidden'),
            inert: el.inert,
            pointerEvents: el.style.pointerEvents,
            userSelect: el.style.userSelect,
        });

        el.inert = true;
        el.style.pointerEvents = 'none';
        el.style.userSelect = 'none';
        el.setAttribute('aria-hidden', 'true');
    };

    const restoreLockedElements = () => {
        lockedElements.forEach((previous, el) => {
            el.inert = previous.inert;
            el.style.pointerEvents = previous.pointerEvents;
            el.style.userSelect = previous.userSelect;

            if (previous.ariaHidden === null) {
                el.removeAttribute('aria-hidden');
            } else {
                el.setAttribute('aria-hidden', previous.ariaHidden);
            }
        });

        lockedElements.clear();
    };

    const lockBackground = (modal) => {
        restoreLockedElements();

        if (!modal) {
            return;
        }

        const toLock = new Set();
        let current = modal;

        while (current && current !== document.body && current.parentElement) {
            [...current.parentElement.children].forEach((sibling) => {
                if (sibling !== current && !sibling.contains(modal)) {
                    toLock.add(sibling);
                }
            });

            current = current.parentElement;
        }

        toLock.forEach(lockElement);
    };

    const applyModalState = () => {
        const modals = openModals();
        const topModal = modals.at(-1) || null;
        const newlyOpened = topModal && topModal !== activeModal;

        if (newlyOpened) {
            focusBeforeOpen = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        }

        document.querySelectorAll('[data-modal-active="true"]').forEach((modal) => {
            if (modal !== topModal) {
                modal.removeAttribute('data-modal-active');
            }
        });

        if (topModal) {
            topModal.setAttribute('data-modal-active', 'true');
            topModal.setAttribute('role', topModal.getAttribute('role') || 'dialog');
            topModal.setAttribute('aria-modal', 'true');
        }

        document.body.classList.toggle('modal-open', Boolean(topModal));
        lockBackground(topModal);

        if (newlyOpened) {
            window.setTimeout(() => focusModal(topModal), 0);
        }

        if (!topModal && activeModal && focusBeforeOpen && document.body.contains(focusBeforeOpen)) {
            focusBeforeOpen.focus({ preventScroll: true });
            focusBeforeOpen = null;
        }

        activeModal = topModal;
    };

    const queueModalState = () => {
        if (queued) {
            return;
        }

        queued = true;
        window.requestAnimationFrame(() => {
            queued = false;
            applyModalState();
        });
    };

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab' || !activeModal) {
            return;
        }

        const focusables = focusablesIn(activeModal);

        if (focusables.length === 0) {
            event.preventDefault();
            focusModal(activeModal);
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus({ preventScroll: true });
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus({ preventScroll: true });
        } else if (!activeModal.contains(document.activeElement)) {
            event.preventDefault();
            first.focus({ preventScroll: true });
        }
    }, true);

    const isBackdropInteraction = (event) => {
        if (!activeModal) {
            return false;
        }

        const path = typeof event.composedPath === 'function' ? event.composedPath() : [];

        return event.target === activeModal
            || path.some((el) => el instanceof HTMLElement && el.hasAttribute('data-modal-backdrop'));
    };

    const keepInteractionInModal = (event) => {
        if (!activeModal) {
            return;
        }

        if (activeModal.contains(event.target) && !isBackdropInteraction(event)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        focusModal(activeModal);
    };

    document.addEventListener('pointerdown', keepInteractionInModal, true);
    document.addEventListener('click', keepInteractionInModal, true);

    document.addEventListener('focusin', (event) => {
        if (!activeModal || activeModal.contains(event.target)) {
            return;
        }

        event.stopPropagation();
        focusModal(activeModal);
    }, true);

    new MutationObserver(queueModalState).observe(document.body, {
        attributes: true,
        attributeFilter: ['class', 'style', 'hidden'],
        childList: true,
        subtree: true,
    });

    queueModalState();
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    applySidebar();
    setupModalIsolation();

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
