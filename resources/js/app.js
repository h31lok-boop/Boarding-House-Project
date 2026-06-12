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

const sidebarMobileMedia = window.matchMedia('(max-width: 768px)');
let sidebarControlsReady = false;

const getStorageItem = (key) => {
    try {
        return localStorage.getItem(key);
    } catch (error) {
        return null;
    }
};

const setStorageItem = (key, value) => {
    try {
        localStorage.setItem(key, value);
    } catch (error) {
        // Keep the UI usable if browser storage is unavailable.
    }
};

const sidebarShellName = () => {
    if (document.querySelector('.admin-shell')) {
        return 'admin';
    }

    if (document.querySelector('.user-shell')) {
        return 'user';
    }

    return 'default';
};

const sidebarStorageKey = () => `boardmatch:${sidebarShellName()}:sidebar`;

const storedSidebarState = () => {
    const stored = getStorageItem(sidebarStorageKey());

    return stored === 'collapsed' ? 'collapsed' : 'expanded';
};

const syncSidebarControls = () => {
    const isMobile = sidebarMobileMedia.matches;
    const mobileOpen = document.documentElement.getAttribute('data-sidebar-mobile') === 'open';
    const desktopExpanded = document.documentElement.getAttribute('data-sidebar') !== 'collapsed';
    const expanded = isMobile ? mobileOpen : desktopExpanded;

    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
        btn.setAttribute('aria-expanded', String(expanded));
        btn.setAttribute('aria-label', isMobile
            ? (mobileOpen ? 'Close sidebar' : 'Open sidebar')
            : (desktopExpanded ? 'Collapse sidebar' : 'Expand sidebar'));
    });

    document.documentElement.classList.toggle('sidebar-mobile-open', isMobile && mobileOpen);
    document.body?.classList.toggle('sidebar-mobile-open', isMobile && mobileOpen);
};

const applySidebar = (state) => {
    const resolved = ['expanded', 'collapsed'].includes(state) ? state : storedSidebarState();

    document.documentElement.setAttribute('data-sidebar', resolved);
    setStorageItem(sidebarStorageKey(), resolved);
    syncSidebarControls();
};

const applyMobileSidebar = (state = 'closed') => {
    const resolved = state === 'open' ? 'open' : 'closed';

    document.documentElement.setAttribute('data-sidebar-mobile', resolved);
    syncSidebarControls();
};

const toggleSidebar = () => {
    if (sidebarMobileMedia.matches) {
        const current = document.documentElement.getAttribute('data-sidebar-mobile') || 'closed';
        applyMobileSidebar(current === 'open' ? 'closed' : 'open');
        return;
    }

    const current = document.documentElement.getAttribute('data-sidebar') || 'expanded';
    applySidebar(current === 'collapsed' ? 'expanded' : 'collapsed');
};

const setupSidebarControls = () => {
    const shell = document.querySelector('.user-shell, .admin-shell');

    if (!shell || !document.querySelector('[data-sidebar-toggle]')) {
        return;
    }

    if (sidebarControlsReady) {
        syncSidebarControls();
        return;
    }

    sidebarControlsReady = true;
    applySidebar();
    applyMobileSidebar('closed');

    const closestFromEvent = (event, selector) => (
        event.target instanceof Element ? event.target.closest(selector) : null
    );

    document.addEventListener('click', (event) => {
        const toggle = closestFromEvent(event, '[data-sidebar-toggle]');
        if (toggle) {
            event.preventDefault();
            toggleSidebar();
            return;
        }

        if (closestFromEvent(event, '[data-sidebar-overlay]')) {
            applyMobileSidebar('closed');
            return;
        }

        const navLink = closestFromEvent(event, '.user-sidebar-nav a[href], .admin-sidebar-nav a[href]');
        if (sidebarMobileMedia.matches && navLink) {
            applyMobileSidebar('closed');
            return;
        }

        const sidebarOpen = document.documentElement.getAttribute('data-sidebar-mobile') === 'open';
        const clickedInsideSidebar = closestFromEvent(event, '.user-sidebar, .admin-sidebar');
        if (sidebarMobileMedia.matches && sidebarOpen && !clickedInsideSidebar) {
            applyMobileSidebar('closed');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebarMobileMedia.matches) {
            applyMobileSidebar('closed');
        }
    });

    const onBreakpointChange = () => {
        if (!sidebarMobileMedia.matches) {
            applyMobileSidebar('closed');
        }

        syncSidebarControls();
    };

    if (typeof sidebarMobileMedia.addEventListener === 'function') {
        sidebarMobileMedia.addEventListener('change', onBreakpointChange);
    } else {
        sidebarMobileMedia.addListener(onBreakpointChange);
    }
};

const setupModalIsolation = () => {
    let activeModal = null;
    let focusBeforeOpen = null;
    let queued = false;
    const lockedElements = new Map();
    const modalSelector = [
        '[data-modal-root]',
        '[role="dialog"]',
        '.fixed.inset-0',
        '.fixed.inset-x-0.inset-y-0',
    ].join(',');
    const modalNamePattern = /(modal|dialog|overlay|confirm)/i;
    const modalContentSelector = [
        '[data-modal-panel]',
        'form',
        '.ui-card',
        '.rounded-lg',
        '.rounded-xl',
        '.rounded-2xl',
        '.bg-white',
    ].join(',');

    const isVisible = (el) => {
        if (el.hidden || el.classList.contains('hidden') || el.style.display === 'none') {
            return false;
        }

        const style = window.getComputedStyle(el);

        return style.display !== 'none'
            && style.visibility !== 'hidden'
            && el.getClientRects().length > 0;
    };

    const fillsViewport = (el) => {
        if (!el.classList.contains('fixed')) {
            return false;
        }

        return el.classList.contains('inset-0')
            || (el.classList.contains('inset-x-0') && el.classList.contains('inset-y-0'));
    };

    const hasHighZIndex = (el) => {
        const classBased = [...el.classList].some((className) => (
            className === 'z-40'
            || className === 'z-50'
            || className.startsWith('z-[')
        ));
        const zIndex = Number.parseInt(window.getComputedStyle(el).zIndex, 10);

        return classBased || (!Number.isNaN(zIndex) && zIndex >= 40);
    };

    const isModalCandidate = (el) => {
        if (!(el instanceof HTMLElement)) {
            return false;
        }

        if (el.matches('[data-modal-skip], [data-modal-backdrop]')) {
            return false;
        }

        if (el.matches('[data-modal-root], [role="dialog"]')) {
            return true;
        }

        const modalIdentity = [
            el.id,
            el.getAttribute('aria-label'),
            el.getAttribute('x-data'),
            el.getAttribute('x-show'),
        ].filter(Boolean).join(' ');

        return fillsViewport(el)
            && hasHighZIndex(el)
            && (
                el.hasAttribute('x-show')
                || modalNamePattern.test(modalIdentity)
                || Boolean(el.querySelector(modalContentSelector))
            );
    };

    const openModals = () => [...document.querySelectorAll(modalSelector)]
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
        const modalChanged = topModal !== activeModal;

        if (newlyOpened) {
            focusBeforeOpen = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        }

        document.querySelectorAll('[data-modal-active="true"]').forEach((modal) => {
            if (modal !== topModal) {
                modal.removeAttribute('data-modal-active');
            }
        });

        if (topModal) {
            topModal.setAttribute('data-modal-root', '');
            topModal.setAttribute('data-modal-active', 'true');
            topModal.setAttribute('role', topModal.getAttribute('role') || 'dialog');
            topModal.setAttribute('aria-modal', 'true');
            topModal.setAttribute('aria-hidden', 'false');
        }

        document.documentElement.classList.toggle('modal-open', Boolean(topModal));
        document.body.classList.toggle('modal-open', Boolean(topModal));

        if (modalChanged) {
            lockBackground(topModal);
        }

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

    const keepScrollInModal = (event) => {
        if (!activeModal) {
            return;
        }

        if (activeModal.contains(event.target) && !isBackdropInteraction(event)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    };

    document.addEventListener('wheel', keepScrollInModal, { capture: true, passive: false });
    document.addEventListener('touchmove', keepScrollInModal, { capture: true, passive: false });

    document.addEventListener('focusin', (event) => {
        if (!activeModal || activeModal.contains(event.target)) {
            return;
        }

        event.stopPropagation();
        focusModal(activeModal);
    }, true);

    new MutationObserver(queueModalState).observe(document.body, {
        attributes: true,
        attributeFilter: ['aria-hidden', 'class', 'open', 'style', 'hidden'],
        childList: true,
        subtree: true,
    });

    queueModalState();
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    setupSidebarControls();
    setupModalIsolation();

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    });
});

Alpine.start();
