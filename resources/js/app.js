import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import "/node_modules/select2/dist/css/select2.css";


// jQuery
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'jquery-ui-dist/jquery-ui';
import 'datatables.net-bs5';
import 'datatables.net-select-bs5';

// Bootstrap JS
// Detailed Comment: Import complete Bootstrap bundle and attach to window.bootstrap to globally expose Tab, Modal, Dropdown, Toast, Tooltip, Popover, and Collapse components, preventing runtime TypeErrors when accessing components like bootstrap.Tab.getInstance
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// SweetAlert
import Swal from 'sweetalert2';
window.Swal = Swal;

// Select2
// Detailed Comment: Import select2 UMD bundle which self-initializes $.fn.select2 on the global jQuery instance without calling select2() directly
import 'select2';


// Custom JS
import './components.js';
import './logout.js';
import './patient.js';
import './secretary-management.js';

/**
 * Detailed Comment: Global Header Theme Switcher Controller.
 * Manages active theme state ('light', 'dark', 'system'), synchronizes with localStorage,
 * updates HTML 'data-bs-theme' attribute, updates UI indicators (icons, labels, active checkmarks),
 * dispatches custom 'themeChanged' events, and reacts to OS prefers-color-scheme updates.
 */
function initThemeSwitcher() {
    const THEME_KEY = 'theme';

    const getStoredTheme = () => {
        try {
            return localStorage.getItem(THEME_KEY) || 'system';
        } catch (e) {
            return 'system';
        }
    };

    const setStoredTheme = (theme) => {
        try {
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {
            console.warn('localStorage is not accessible for theme persistence:', e);
        }
    };

    const resolveTheme = (theme) => {
        if (theme === 'dark' || theme === 'light') {
            return theme;
        }
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const setTheme = (theme) => {
        const appliedTheme = resolveTheme(theme);
        document.documentElement.setAttribute('data-bs-theme', appliedTheme);

        // Detailed Comment: Dispatch window-level themeChanged event for components and chart instances
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme, appliedTheme }
        }));

        return appliedTheme;
    };

    const showActiveTheme = (theme) => {
        const themeSwitcher = document.querySelector('#bd-theme');
        if (!themeSwitcher) return;

        const activeIcon = document.querySelector('#theme-active-icon');
        const activeText = document.querySelector('#theme-active-text');

        if (theme === 'dark') {
            if (activeIcon) activeIcon.className = 'fa-solid fa-moon me-1 text-info';
            if (activeText) activeText.textContent = 'Dark';
        } else if (theme === 'light') {
            if (activeIcon) activeIcon.className = 'fa-solid fa-sun me-1 text-warning';
            if (activeText) activeText.textContent = 'Light';
        } else {
            if (activeIcon) activeIcon.className = 'fa-solid fa-circle-half-stroke me-1 text-secondary';
            if (activeText) activeText.textContent = 'System';
        }

        document.querySelectorAll('[data-bs-theme-value]').forEach(btn => {
            const val = btn.getAttribute('data-bs-theme-value');
            const checkIcon = btn.querySelector('.theme-check-icon');
            if (val === theme) {
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('aria-pressed', 'false');
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        });
    };

    const currentTheme = getStoredTheme();
    setTheme(currentTheme);
    showActiveTheme(currentTheme);

    // Detailed Comment: Listen for OS prefers-color-scheme changes when system theme is active
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const stored = getStoredTheme();
            if (stored === 'system') {
                setTheme('system');
                showActiveTheme('system');
            }
        });
    }

    // Detailed Comment: Delegated click listener for theme dropdown option buttons
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-bs-theme-value]');
        if (!btn) return;
        const selected = btn.getAttribute('data-bs-theme-value');
        if (!selected) return;

        setStoredTheme(selected);
        setTheme(selected);
        showActiveTheme(selected);
    });
}

// Detailed Comment: Initialize theme switcher when DOM is ready or immediately if already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeSwitcher);
} else {
    initThemeSwitcher();
}
