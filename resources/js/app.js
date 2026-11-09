import './bootstrap';
import './boarding-house-map';
import './boarding-house-browse-map';
import './admin-boarding-house-maps';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('boardmatchAssistant', (config = {}) => ({
    open: false,
    loading: false,
    question: '',
    error: '',
    messages: [],

    init() {
        this.resetConversation();
    },

    welcomeMessage() {
        return config.configured
            ? 'Hi! I’m the BoardMatch AI assistant. I answer only from records authorized for your account role. Ask about your listings, reservations, payments, messages, preferences, or how to use the system.'
            : 'The AI assistant is installed, but its server-side AI provider must be configured before it can answer questions.';
    },

    resetConversation() {
        this.messages = [{ role: 'assistant', content: this.welcomeMessage(), local: true }];
        this.question = '';
        this.error = '';
        this.scrollToLatest();
    },

    openAssistant() {
        this.open = true;
        this.$nextTick(() => {
            this.scrollToLatest();
            this.$refs.question?.focus({ preventScroll: true });
        });
    },

    close() {
        if (!this.open) {
            return;
        }

        this.open = false;
        this.error = '';
    },

    scrollToLatest() {
        this.$nextTick(() => {
            const container = this.$refs.messages;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    },

    async send() {
        const question = this.question.trim();
        if (this.loading || question.length < 2) {
            return;
        }

        if (!config.configured) {
            this.error = 'Configure the selected server-side AI provider, then clear the Laravel configuration cache.';
            return;
        }

        const history = this.messages
            .filter((message) => !message.local)
            .slice(-8)
            .map(({ role, content }) => ({ role, content }));

        this.messages.push({ role: 'user', content: question });
        this.question = '';
        this.error = '';
        this.loading = true;
        this.scrollToLatest();

        try {
            const response = await fetch(config.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ question, history }),
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validationMessage = Object.values(payload.errors || {}).flat()[0];
                throw new Error(validationMessage || payload.message || 'The AI assistant is temporarily unavailable.');
            }

            this.messages.push({ role: 'assistant', content: payload.answer });
        } catch (error) {
            this.error = error instanceof Error ? error.message : 'The AI assistant is temporarily unavailable.';
        } finally {
            this.loading = false;
            this.scrollToLatest();
            this.$nextTick(() => this.$refs.question?.focus({ preventScroll: true }));
        }
    },
}));

Alpine.data('walkInReservation', (config = {}) => ({
    walkInOpen: Boolean(config.walkInOpen),
    walkInSaving: false,
    walkInTenants: Array.isArray(config.walkInTenants) ? config.walkInTenants : [],
    walkInHouses: Array.isArray(config.walkInHouses) ? config.walkInHouses : [],
    walkIn: {
        tenant_id: String(config.walkIn?.tenant_id || ''),
        boarding_house_id: String(config.walkIn?.boarding_house_id || ''),
        room_id: String(config.walkIn?.room_id || ''),
        total_amount: config.walkIn?.total_amount ?? '',
        service_ids: Array.isArray(config.walkIn?.service_ids)
            ? config.walkIn.service_ids.map((id) => String(id))
            : [],
    },

    get walkInSelectedHouse() {
        return this.walkInHouses.find((house) => String(house.id) === String(this.walkIn.boarding_house_id)) || null;
    },

    get walkInTenantOptions() {
        if (!this.walkIn.boarding_house_id) {
            return this.walkInTenants;
        }

        return this.walkInTenants.filter((tenant) => String(tenant.house_id) === String(this.walkIn.boarding_house_id));
    },

    get walkInRoomOptions() {
        return this.walkInSelectedHouse?.rooms || [];
    },

    get walkInServiceOptions() {
        return this.walkInSelectedHouse?.services || [];
    },

    openWalkIn() {
        this.walkInSaving = false;
        this.walkInOpen = true;
        this.$nextTick(() => this.$refs.walkInHouse?.focus({ preventScroll: true }));
    },

    closeWalkIn() {
        if (!this.walkInSaving) {
            this.walkInOpen = false;
        }
    },

    onWalkInTenantChange() {
        const tenant = this.walkInTenants.find((item) => String(item.id) === String(this.walkIn.tenant_id));
        if (!tenant) {
            return;
        }

        this.walkIn.boarding_house_id = String(tenant.house_id);
        this.onWalkInHouseChange();
    },

    onWalkInHouseChange() {
        const tenant = this.walkInTenants.find((item) => String(item.id) === String(this.walkIn.tenant_id));
        if (tenant && String(tenant.house_id) !== String(this.walkIn.boarding_house_id)) {
            this.walkIn.tenant_id = '';
        }

        if (!this.walkInRoomOptions.some((room) => String(room.id) === String(this.walkIn.room_id))) {
            this.walkIn.room_id = '';
        }

        const validServiceIds = new Set(this.walkInServiceOptions.map((service) => String(service.id)));
        this.walkIn.service_ids = this.walkIn.service_ids.filter((id) => validServiceIds.has(String(id)));
    },

    onWalkInRoomChange() {
        const room = this.walkInRoomOptions.find((item) => String(item.id) === String(this.walkIn.room_id));
        if (room && Number(room.price) >= 0) {
            this.walkIn.total_amount = Number(room.price).toFixed(2);
        }
    },
}));

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
    const dashboardThemeEnabled = document.documentElement.getAttribute('data-theme-mode') === 'dashboard';
    const lightOnly = !dashboardThemeEnabled;
    const stored = getStorageItem('theme');
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const candidate = lightOnly ? 'light' : (theme || stored || systemTheme);
    const resolved = candidate === 'dark' ? 'dark' : 'light';

    document.documentElement.setAttribute('data-theme', resolved);
    if (!lightOnly) {
        setStorageItem('theme', resolved);
    }
    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        el.textContent = resolved === 'dark' ? 'Dark' : 'Light';
    });
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const darkModeEnabled = resolved === 'dark';
        const nextMode = darkModeEnabled ? 'light' : 'dark';

        button.setAttribute('aria-pressed', String(darkModeEnabled));
        button.setAttribute('aria-label', `Switch to ${nextMode} mode`);
        button.setAttribute('title', `Switch to ${nextMode} mode`);
    });
};

const sidebarMobileMedia = window.matchMedia('(max-width: 767px)');
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

const sidebarScrollStorageKey = () => `boardmatch:${sidebarShellName()}:sidebar-scroll`;

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

const setupSidebarScrollMemory = () => {
    const nav = document.querySelector('.user-sidebar-nav, .admin-sidebar-nav');

    if (!nav) {
        return;
    }

    const restore = () => {
        const stored = Number.parseInt(getStorageItem(sidebarScrollStorageKey()) || '0', 10);

        if (Number.isFinite(stored) && stored > 0) {
            nav.scrollTop = Math.min(stored, nav.scrollHeight - nav.clientHeight);
        }

        const activeItem = nav.querySelector('[aria-current="page"]');
        if (activeItem instanceof HTMLElement) {
            const navRect = nav.getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();
            const isVisible = itemRect.top >= navRect.top && itemRect.bottom <= navRect.bottom;

            if (!isVisible) {
                activeItem.scrollIntoView({ block: 'nearest' });
                setStorageItem(sidebarScrollStorageKey(), String(nav.scrollTop));
            }
        }
    };

    const save = () => setStorageItem(sidebarScrollStorageKey(), String(nav.scrollTop));

    window.requestAnimationFrame(restore);
    window.setTimeout(restore, 120);

    nav.addEventListener('scroll', save, { passive: true });
    window.addEventListener('pagehide', save);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            save();
        }
    });
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
    setupSidebarScrollMemory();

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
        if (navLink) {
            const nav = navLink.closest('.user-sidebar-nav, .admin-sidebar-nav');
            if (nav) {
                setStorageItem(sidebarScrollStorageKey(), String(nav.scrollTop));
            }
        }

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

    window.addEventListener('pageshow', () => {
        if (sidebarMobileMedia.matches) {
            applyMobileSidebar('closed');
        } else {
            syncSidebarControls();
        }
    });
};

const setupReservationCountdowns = () => {
    const formatDuration = (remainingMs) => {
        const totalHours = Math.max(0, Math.floor(remainingMs / 3600000));
        const days = Math.floor(totalHours / 24);
        const hours = totalHours % 24;
        const parts = [];

        if (days > 0) {
            parts.push(`${days} day${days === 1 ? '' : 's'}`);
        }

        parts.push(`${hours} hour${hours === 1 ? '' : 's'}`);

        return parts.join(' ');
    };

    document.querySelectorAll('[data-reservation-countdown]').forEach((root) => {
        if (root.dataset.countdownReady === 'true') {
            return;
        }

        root.dataset.countdownReady = 'true';
        const expiresAt = root.getAttribute('data-expires-at');
        const label = root.querySelector('[data-countdown-label]');
        if (!expiresAt || !label) {
            return;
        }

        const expiryTime = new Date(expiresAt).getTime();
        if (Number.isNaN(expiryTime)) {
            return;
        }

        const tick = () => {
            const remaining = expiryTime - Date.now();

            if (remaining <= 0) {
                label.textContent = root.getAttribute('data-expired-message') || 'Reservation expired.';
                window.setTimeout(() => window.location.reload(), 1500);
                window.clearInterval(timer);
                return;
            }

            label.textContent = `Reservation expires in ${formatDuration(remaining)}`;
        };

        tick();
        const timer = window.setInterval(tick, 60000);
    });
};

const setupModalIsolation = () => {
    let activeModal = null;
    let focusBeforeOpen = null;
    let queued = false;
    const lockedElements = new Map();
    const modalSelector = [
        '[data-modal-root]',
        '[role="dialog"]',
        '.bm-modal-overlay',
        '.fixed.inset-0',
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
        /* A closed native dialog must never be inferred as visible from our
           active-modal CSS. Otherwise display:flex keeps it on screen after
           close(), and the next observer pass incorrectly activates it again. */
        if (el instanceof HTMLDialogElement && !el.open) {
            return false;
        }

        if (el.hasAttribute('x-cloak')) {
            return false;
        }

        if (
            el.hidden
            || (
                el.getAttribute('aria-hidden') === 'true'
                && !(el instanceof HTMLDialogElement && el.open)
            )
            || el.classList.contains('hidden')
            || el.style.display === 'none'
        ) {
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

        if (el.classList.contains('bm-modal-overlay')) {
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
                || el.getAttribute('aria-modal') === 'true'
            );
    };

    const modalOrigins = new WeakMap();

    /*
     * A fixed dialog can still be trapped below a sticky app bar when one of
     * its ancestors establishes a stacking context (overflow, transform,
     * filter, containment, and similar dashboard layout rules do this). Move
     * an opened overlay to the document body so "fixed" really means the
     * viewport. Alpine keeps the initialized data stack on the moved element,
     * so existing x-show, x-model, and click handlers continue to work.
     * Native <dialog> elements already live in the browser top layer.
     */
    const mountModalAtDocumentLevel = (modal) => {
        if (
            !(modal instanceof HTMLElement)
            || modal instanceof HTMLDialogElement
            || modal.parentElement === document.body
        ) {
            return;
        }

        if (!modalOrigins.has(modal)) {
            const placeholder = document.createComment('boardmatch-modal-origin');
            modal.parentNode?.insertBefore(placeholder, modal);
            modalOrigins.set(modal, placeholder);
        }

        document.body.appendChild(modal);
        modal.setAttribute('data-modal-portaled', 'true');
    };

    const openModals = () => {
        const modals = [...document.querySelectorAll(modalSelector)]
            .filter((el) => isModalCandidate(el) && isVisible(el));

        modals.forEach(mountModalAtDocumentLevel);

        return modals;
    };

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

    const resetModalScroll = (modal) => {
        if (!modal) {
            return;
        }

        modal.scrollTop = 0;
        modal.querySelectorAll(':scope > .bm-modal, :scope > [data-modal-panel]')
            .forEach((panel) => {
                panel.scrollTop = 0;
            });
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

                if (modal instanceof HTMLDialogElement && !modal.open) {
                    modal.setAttribute('aria-hidden', 'true');
                }
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
            resetModalScroll(topModal);
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

    /* A page restored from the browser back/forward cache can retain the body
       class from a modal that no longer exists. Re-evaluate instead of leaving
       the entire dashboard frozen. */
    window.addEventListener('pageshow', queueModalState);
    window.addEventListener('pagehide', restoreLockedElements);

    queueModalState();
};

const setupGlobalLoadingOverlay = () => {
    if (document.body.dataset.loadingOverlayReady === 'true') {
        return;
    }

    document.body.dataset.loadingOverlayReady = 'true';

    const messagePathPattern = /(^|\/)(messages?|conversations?|chat)(\/|$)/i;
    const currentPageIsMessaging = messagePathPattern.test(window.location.pathname);
    const minimumDuration = 1000;
    const loadingDeadlineKey = 'boardmatch-loading-visible-until';
    const overlay = document.createElement('div');
    let safetyTimer = null;
    let minimumTimer = null;

    overlay.className = 'boardmatch-loading-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.setAttribute('aria-label', 'BoardMatch is loading');
    overlay.innerHTML = `
        <div class="boardmatch-loading-content" role="status" aria-live="polite">
            <div class="boardmatch-loading-mark" aria-hidden="true">
                <img src="/images/boardmatch-final-logo.png" alt="" class="boardmatch-loading-logo">
            </div>
            <div class="boardmatch-loading-text">
                <span>Loading</span>
                <span class="boardmatch-loading-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            </div>
        </div>`;
    document.body.appendChild(overlay);

    const storedDeadline = () => {
        try {
            return Number.parseInt(sessionStorage.getItem(loadingDeadlineKey) || '0', 10) || 0;
        } catch {
            return 0;
        }
    };

    const storeDeadline = (deadline) => {
        try {
            sessionStorage.setItem(loadingDeadlineKey, String(deadline));
        } catch {
            // Private browsing or strict storage policies must not break navigation.
        }
    };

    const clearDeadline = () => {
        try {
            sessionStorage.removeItem(loadingDeadlineKey);
        } catch {
            // Nothing else is required when session storage is unavailable.
        }
    };

    const reset = () => {
        window.clearTimeout(safetyTimer);
        window.clearTimeout(minimumTimer);
        safetyTimer = null;
        minimumTimer = null;
        clearDeadline();
        document.body.classList.remove('boardmatch-is-loading');
        document.body.removeAttribute('aria-busy');
        overlay.setAttribute('aria-hidden', 'true');
    };

    const show = () => {
        if (currentPageIsMessaging) {
            return;
        }

        if (document.body.classList.contains('boardmatch-is-loading')) {
            return;
        }

        window.clearTimeout(safetyTimer);
        storeDeadline(Date.now() + minimumDuration);
        document.body.classList.add('boardmatch-is-loading');
        document.body.setAttribute('aria-busy', 'true');
        overlay.setAttribute('aria-hidden', 'false');
        safetyTimer = window.setTimeout(reset, 12000);
    };

    const resumeMinimumDisplay = () => {
        if (currentPageIsMessaging) {
            reset();
            return;
        }

        const remaining = storedDeadline() - Date.now();
        if (remaining <= 0) {
            reset();
            return;
        }

        window.clearTimeout(safetyTimer);
        window.clearTimeout(minimumTimer);
        document.body.classList.add('boardmatch-is-loading');
        document.body.setAttribute('aria-busy', 'true');
        overlay.setAttribute('aria-hidden', 'false');
        minimumTimer = window.setTimeout(reset, remaining);
    };

    const urlFor = (value) => {
        try {
            return new URL(value, window.location.href);
        } catch {
            return null;
        }
    };

    const isMessageUrl = (url) => Boolean(url && messagePathPattern.test(url.pathname));
    const isExcludedElement = (element) => Boolean(element?.closest?.('[data-no-loading-overlay], [data-messaging-interaction]'));
    const isModifiedClick = (event) => event.button !== 0
        || event.metaKey
        || event.ctrlKey
        || event.shiftKey
        || event.altKey;

    const eligibleLink = (link) => {
        if (!(link instanceof HTMLAnchorElement) || !link.href || isExcludedElement(link)) {
            return false;
        }

        if (link.hasAttribute('download')) {
            return false;
        }

        const target = (link.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') {
            return false;
        }

        const url = urlFor(link.href);
        if (!url || url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol) || isMessageUrl(url)) {
            return false;
        }

        const sameDocument = url.pathname === window.location.pathname
            && url.search === window.location.search;

        return !sameDocument;
    };

    const eligibleForm = (form) => {
        if (!(form instanceof HTMLFormElement) || isExcludedElement(form)) {
            return false;
        }

        const target = (form.getAttribute('target') || '').toLowerCase();
        if ((target && target !== '_self') || form.getAttribute('method')?.toLowerCase() === 'dialog') {
            return false;
        }

        return !isMessageUrl(urlFor(form.action || window.location.href));
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || isModifiedClick(event) || currentPageIsMessaging) {
            return;
        }

        const link = event.target.closest?.('a[href]');
        if (eligibleLink(link)) {
            window.setTimeout(() => {
                if (!event.defaultPrevented) {
                    show();
                }
            }, 0);
            return;
        }

        const trigger = event.target.closest?.('[data-loading-overlay="true"]');
        if (trigger && !isExcludedElement(trigger)) {
            window.setTimeout(() => {
                if (!event.defaultPrevented) {
                    show();
                }
            }, 0);
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        window.setTimeout(() => {
            if (!event.defaultPrevented && eligibleForm(form)) {
                show();
            }
        }, 0);
    });

    window.addEventListener('beforeunload', show);
    window.addEventListener('pageshow', resumeMinimumDisplay);
    window.addEventListener('load', resumeMinimumDisplay);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            resumeMinimumDisplay();
        }
    });

    resumeMinimumDisplay();
};

document.addEventListener('DOMContentLoaded', () => {
    setupReservationCountdowns();
    applyTheme();
    setupSidebarControls();
    setupModalIsolation();
    setupGlobalLoadingOverlay();

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    });
});

Alpine.start();
