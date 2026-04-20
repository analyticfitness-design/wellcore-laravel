import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/vue/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js/vue',
        },
    },
    build: {
        rollupOptions: {
            output: {
                // Split vendor chunks so changes in app code don't invalidate
                // cached vendor bundles at the browser level.
                manualChunks(id) {
                    if (id.includes('node_modules/chart.js')) return 'chart';
                    if (id.includes('node_modules/axios')) return 'axios';
                    if (/node_modules\/(vue|vue-router|pinia|@vue)\b/.test(id)) return 'vue-core';
                },
            },
        },
    },
    server: {
        host: 'localhost',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
