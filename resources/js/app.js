import './bootstrap';

import Alpine from 'alpinejs';

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
