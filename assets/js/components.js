(function () {
    'use strict';

    window.VisitinUI = window.VisitinUI || {};

    // COMPONENT: Sanitizer teks sebelum nilai API dirender ke innerHTML.
    window.VisitinUI.escapeHtml = function (value) {
        return String(value ?? '').replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    };

    // COMPONENT: Generator inisial untuk avatar tanpa perlu logika ulang di page.
    window.VisitinUI.initials = function (value) {
        const words = String(value || 'User').trim().split(/\s+/).filter(Boolean);
        return words.slice(0, 2).map(word => word.charAt(0)).join('').toUpperCase() || 'U';
    };

    // COMPONENT: Setter textContent dengan fallback seragam untuk data kosong.
    window.VisitinUI.text = function (element, value, fallback = '-') {
        if (element) element.textContent = String(value || fallback);
    };

    // COMPONENT: HTTP client terpusat untuk semua request FE ke API aplikasi.
    window.VisitinUI.api = {
        baseUrl: function () {
            return document.querySelector('meta[name="app-base"]')?.content || '';
        },
        request: async function (action, options = {}) {
            const method = String(options.method || 'GET').toUpperCase();
            const query = new URLSearchParams(options.params || {});
            query.set('action', action);
            const requestOptions = {
                method,
                credentials: 'include',
                headers: { Accept: 'application/json', ...(options.headers || {}) }
            };

            if (options.body !== undefined) {
                requestOptions.headers['Content-Type'] = 'application/json';
                requestOptions.body = JSON.stringify(options.body);
            }

            const response = await fetch(`${this.baseUrl()}/api/?${query.toString()}`, requestOptions);
            const raw = await response.text();
            let body;
            try {
                body = JSON.parse(raw);
            } catch (error) {
                throw new Error(raw || `HTTP ${response.status}`);
            }

            const status = Number(body.status);
            const isSuccess = response.ok && (Number.isFinite(status)
                ? status >= 200 && status < 300
                : body.status === 'success');
            if (!isSuccess) {
                const apiError = new Error(body.message || `Request ${action} gagal.`);
                apiError.status = status || response.status;
                apiError.data = body.data;
                throw apiError;
            }
            return body;
        },
        get: function (action, params = {}) {
            return this.request(action, { method: 'GET', params });
        },
        post: function (action, body = {}, params = {}) {
            return this.request(action, { method: 'POST', params, body });
        }
    };

    // COMPONENT: Formatter bersama agar angka, tanggal, dan teks tampil seragam.
    window.VisitinUI.format = {
        currency: function (value, currency = 'IDR') {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency, maximumFractionDigits: 0 }).format(Number(value || 0));
        },
        number: function (value) {
            return new Intl.NumberFormat('id-ID').format(Number(value || 0));
        },
        date: function (value, options = { day: 'numeric', month: 'short', year: 'numeric' }) {
            if (!value) return '-';
            const parsed = new Date(value);
            return Number.isNaN(parsed.getTime()) ? '-' : parsed.toLocaleDateString('id-ID', options);
        },
        text: function (value, fallback = '-') {
            const normalized = String(value ?? '').trim();
            return normalized || fallback;
        }
    };

    // COMPONENT: Debounce untuk search/filter supaya request tidak berulang saat user mengetik.
    window.VisitinUI.debounce = function (callback, wait = 300) {
        let timer = null;
        return function (...args) {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => callback.apply(this, args), wait);
        };
    };

    // COMPONENT: Tab controller generic. Panel memakai data-ui-tab-panel="key".
    window.VisitinUI.bindTabs = function (root) {
        if (!root || root.dataset.tabsBound === 'true') return null;
        const tabs = Array.from(root.querySelectorAll('[data-ui-tab]'));
        const panels = Array.from(root.querySelectorAll('[data-ui-tab-panel]'));
        if (!tabs.length) return null;

        root.dataset.tabsBound = 'true';
        function activate(key) {
            tabs.forEach(function (tab) {
                const active = tab.dataset.uiTab === key;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
                panel.hidden = panel.dataset.uiTabPanel !== key;
            });
            root.dispatchEvent(new CustomEvent('ui:tabchange', { detail: { key } }));
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () { activate(tab.dataset.uiTab); });
        });
        const initial = tabs.find(tab => tab.getAttribute('aria-selected') === 'true') || tabs[0];
        activate(initial.dataset.uiTab);
        return { activate };
    };

    // COMPONENT: State controller untuk loading, empty, error, dan content dalam satu slot.
    window.VisitinUI.setState = function (root, state) {
        if (!root) return;
        root.dataset.uiState = state;
        root.querySelectorAll('[data-ui-state]').forEach(function (element) {
            element.hidden = element.dataset.uiState !== state;
        });
        root.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
    };

    // COMPONENT: Toast wrapper menjaga page tidak perlu mengetahui implementasi toast global.
    window.VisitinUI.notify = function (message, type = 'info') {
        if (typeof window.showToast === 'function') window.showToast(message, type);
    };

    // COMPONENT: Preferensi tampilan per user yang diterapkan lintas halaman.
    const preferenceDefaults = {
        fontScale: 1,
        fontFamily: 'system'
    };
    const fontFamilies = {
        system: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
        inter: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
        roboto: "'Roboto', Arial, Helvetica, sans-serif",
        jakarta: "'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
        nunito: "'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
        arial: "Arial, Helvetica, sans-serif"
    };

    function preferenceStorageKey(userKey) {
        return `visitin_preferences_${String(userKey || 'guest').trim()}`;
    }

    window.VisitinUI.preferences = {
        defaults: function () {
            return { ...preferenceDefaults };
        },
        load: function (userKey) {
            try {
                const stored = JSON.parse(localStorage.getItem(preferenceStorageKey(userKey)) || '{}');
                const fontScale = Number(stored.fontScale);
                return {
                    fontScale: fontScale >= 0.9 && fontScale <= 1.2 ? fontScale : preferenceDefaults.fontScale,
                    fontFamily: fontFamilies[stored.fontFamily] ? stored.fontFamily : preferenceDefaults.fontFamily
                };
            } catch (error) {
                return { ...preferenceDefaults };
            }
        },
        save: function (userKey, preferences) {
            const safe = {
                fontScale: Math.min(1.2, Math.max(0.9, Number(preferences.fontScale) || 1)),
                fontFamily: fontFamilies[preferences.fontFamily] ? preferences.fontFamily : preferenceDefaults.fontFamily
            };
            localStorage.setItem(preferenceStorageKey(userKey), JSON.stringify(safe));
            this.apply(safe);
            return safe;
        },
        apply: function (preferences) {
            const safe = preferences || preferenceDefaults;
            document.documentElement.style.setProperty('--app-font-scale', String(safe.fontScale || 1));
            document.documentElement.style.setProperty('--app-font-family', fontFamilies[safe.fontFamily] || fontFamilies.system);
        },
        init: function (userKey) {
            this.apply(this.load(userKey));
        }
    };

    const preferenceTarget = document.querySelector('[data-preference-user]');
    if (preferenceTarget) {
        window.VisitinUI.preferences.init(preferenceTarget.dataset.preferenceUser);
    }

    // COMPONENT: Modal konfirmasi reusable untuk logout, delegasi, dan aksi penting.
    window.VisitinUI.confirm = function (options = {}) {
        const escapeHtml = window.VisitinUI.escapeHtml;
        const title = options.title || 'Konfirmasi tindakan';
        const message = options.message || 'Apakah kamu yakin melanjutkan tindakan ini?';
        const confirmLabel = options.confirmLabel || 'Ya, lanjutkan';
        const cancelLabel = options.cancelLabel || 'Batal';
        const icon = options.icon || 'fa-arrow-right-from-bracket';

        return new Promise(function (resolve) {
            const backdrop = document.createElement('div');
            backdrop.className = 'ui-confirm-backdrop';
            backdrop.setAttribute('role', 'presentation');
            backdrop.innerHTML = `
                <section class="ui-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="ui-confirm-title">
                    <div class="ui-confirm-icon" aria-hidden="true"><i class="fa-solid ${escapeHtml(icon)}"></i></div>
                    <h2 class="ui-confirm-title" id="ui-confirm-title">${escapeHtml(title)}</h2>
                    <p class="ui-confirm-message">${escapeHtml(message)}</p>
                    <div class="ui-confirm-actions">
                        <button type="button" class="ui-action-button ui-action-button--quiet" data-confirm-cancel>${escapeHtml(cancelLabel)}</button>
                        <button type="button" class="ui-action-button ui-action-button--danger" data-confirm-ok>${escapeHtml(confirmLabel)}</button>
                    </div>
                </section>
            `;
            document.body.appendChild(backdrop);

            const cancelButton = backdrop.querySelector('[data-confirm-cancel]');
            const confirmButton = backdrop.querySelector('[data-confirm-ok]');
            const previousFocus = document.activeElement;

            function close(result) {
                document.removeEventListener('keydown', handleKeydown);
                backdrop.remove();
                if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
                resolve(result);
            }

            function handleKeydown(event) {
                if (event.key === 'Escape') close(false);
            }

            cancelButton.addEventListener('click', function () { close(false); });
            confirmButton.addEventListener('click', function () { close(true); });
            backdrop.addEventListener('click', function (event) {
                if (event.target === backdrop) close(false);
            });
            document.addEventListener('keydown', handleKeydown);
            confirmButton.focus();
        });
    };

    // COMPONENT: Controller panel filter yang bisa dipakai ulang di banyak page.
    window.VisitinUI.bindFilterPanel = function (root) {
        if (!root || root.dataset.filterBound === 'true') return null;

        const trigger = root.querySelector('[data-filter-trigger]');
        const panel = root.querySelector('[data-filter-panel]');
        const closeButton = root.querySelector('[data-filter-close]');
        if (!trigger || !panel) return null;

        root.dataset.filterBound = 'true';

        function setOpen(open) {
            panel.hidden = !open;
            root.classList.toggle('is-open', open);
            trigger.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        trigger.addEventListener('click', function () {
            setOpen(panel.hidden);
        });

        if (closeButton) {
            closeButton.addEventListener('click', function () {
                setOpen(false);
                trigger.focus();
            });
        }

        root.addEventListener('click', function (event) {
            if (!event.target.closest('[data-filter-apply]')) return;
            window.setTimeout(function () {
                setOpen(false);
            }, 0);
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) setOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape' || panel.hidden) return;
            setOpen(false);
            trigger.focus();
        });

        setOpen(!panel.hasAttribute('hidden'));
        return { open: () => setOpen(true), close: () => setOpen(false), toggle: () => setOpen(panel.hidden) };
    };

    // COMPONENT: Penanda controller untuk view card mobile dan tabel desktop.
    window.VisitinUI.bindResponsiveDataView = function (root) {
        if (!root || root.dataset.responsiveDataBound === 'true') return null;
        if (!root.querySelector('[data-responsive-cards]') || !root.querySelector('[data-responsive-table]')) return null;

        root.dataset.responsiveDataBound = 'true';
        return root;
    };

    // COMPONENT: Auto-initializer semua component declarative setelah DOM siap.
    function initFilterPanels() {
        document.querySelectorAll('[data-filter-component]').forEach(function (root) {
            window.VisitinUI.bindFilterPanel(root);
        });
        document.querySelectorAll('[data-responsive-data]').forEach(function (root) {
            window.VisitinUI.bindResponsiveDataView(root);
        });
        document.querySelectorAll('[data-ui-tabs]').forEach(function (root) {
            window.VisitinUI.bindTabs(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFilterPanels);
    } else {
        initFilterPanels();
    }
})();
