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
        // Target modern browsers (all w/ native ESM + top-level await).
        // Removes ~46 KB of legacy polyfills that Lighthouse flagged.
        target: 'es2022',
        cssTarget: 'chrome100',
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/chart.js')) return 'chart';
                    if (id.includes('node_modules/axios')) return 'axios';
                    if (/node_modules\/(vue|vue-router|pinia|@vue)\b/.test(id)) return 'vue-core';
                },
            },
        },
    },
    esbuild: {
        // Strip console.* from production bundles; keep console.error/warn.
        pure: ['console.log', 'console.debug', 'console.info'],
        legalComments: 'none',
    },
    server: {
        host: 'localhost',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
