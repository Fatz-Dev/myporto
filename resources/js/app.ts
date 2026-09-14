import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { initializeTheme } from './composables/useAppearance';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => {
        if (!title) return appName;
        const trimmed = title.trim();
        if (trimmed === appName) return appName;
        // Strip any existing trailing - appName or — appName to prevent double branding
        const cleaned = trimmed.replace(new RegExp(`\\s*[-—]\\s*${appName}$`, 'i'), '');
        return `${cleaned} - ${appName}`;
    },
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
        const directPath = `./pages/${name}.vue`;
        if (pages[directPath]) {
            return pages[directPath]();
        }
        const lowerPath = directPath.toLowerCase();
        const foundKey = Object.keys(pages).find((k) => k.toLowerCase() === lowerPath);
        if (foundKey && pages[foundKey]) {
            return pages[foundKey]();
        }
        return resolvePageComponent(directPath, pages);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
