import './bootstrap';

import Alpine from 'alpinejs';
import { animate, stagger } from 'animejs';

window.Alpine = Alpine;

Alpine.data('themeSwitcher', () => ({
    dark: false,
    sidebarOpen: true,

    init() {
        // Theme Logic
        const storedTheme = window.localStorage.getItem('theme');
        if (storedTheme === 'dark') {
            this.dark = true;
        } else if (storedTheme === 'light') {
            this.dark = false;
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            this.dark = true;
        }

        // Sidebar Logic
        const storedSidebar = window.localStorage.getItem('sidebarOpen');
        if (storedSidebar !== null) {
            this.sidebarOpen = storedSidebar === 'true';
        }

        this.apply();
    },

    toggle() {
        this.dark = !this.dark;
        window.localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        this.apply();
    },

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        window.localStorage.setItem('sidebarOpen', this.sidebarOpen);
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
        document.documentElement.setAttribute('data-theme', this.dark ? 'dark' : 'light');
    },
}));

Alpine.start();

const runLoginAnimation = () => {
    const root = document.querySelector('[data-login-anim="login"]');
    if (!root) return;

    const header = root.querySelector('[data-anim="header"]');
    const fields = root.querySelectorAll('[data-anim="field"]');
    const actions = root.querySelector('[data-anim="actions"]');
    const form = root.querySelector('[data-anim="form"]');

    if (form) {
        animate(form, {
            opacity: [0, 1],
            scale: [0.98, 1],
            duration: 500,
            easing: 'easeOutQuad',
        });
    }

    if (header) {
        animate(header, {
            opacity: [0, 1],
            translateY: [24, 0],
            scale: [0.98, 1],
            duration: 700,
            delay: 50,
            easing: 'easeOutExpo',
        });
    }

    const brandTitle = document.querySelector('[data-login-anim="brand"] [data-anim="title"]');
    if (brandTitle) {
        animate(brandTitle, {
            opacity: [0, 1],
            translateY: [10, 0],
            duration: 450,
            delay: 80,
            easing: 'easeOutQuad',
        });
    }

    if (fields.length) {
        animate(fields, {
            opacity: [0, 1],
            translateY: [16, 0],
            scale: [0.98, 1],
            delay: stagger(140, { start: 200 }),
            duration: 650,
            easing: 'easeOutExpo',
        });
    }

    if (actions) {
        animate(actions, {
            opacity: [0, 1],
            translateY: [16, 0],
            scale: [0.98, 1],
            delay: 650,
            duration: 600,
            easing: 'easeOutBack',
        });
    }
};

document.addEventListener('DOMContentLoaded', runLoginAnimation);
