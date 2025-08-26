import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import Antd from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';
import { initializeTheme } from './composables/useAppearance';
import translator from '@/plugins/translator';


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
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        const locale = props.initialPage.props.locale || 'en';

        app
            .use(plugin)
            .use(ZiggyVue)
            .use(Antd)
            .use(translator, locale)
            .mount(el);
    },
    progress: {
        color: '#ff0033',
    },
});


initializeTheme();
document.documentElement.classList.remove('dark');
document.documentElement.classList.add('light');
