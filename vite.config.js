import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import electron from 'vite-plugin-electron/simple';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                },
            },
        }),
        vuetify({ autoImport: true }),
        electron({
            main: { entry: 'electron/main.cjs' },
            preload: { input: { preload: 'electron/preload.cjs' } },
        }),

    ],
});
