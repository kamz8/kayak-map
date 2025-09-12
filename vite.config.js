import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import browsersync from "vite-plugin-browser-sync";
import vuetify from 'vite-plugin-vuetify';

const host = 'kayak-map.test';
export default defineConfig({
    plugins: [
        vue(),
        vuetify({ autoImport: true }),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/dashboard/main.js',
            ],
            refresh: true,
        }),
    ],
    optimizeDeps: {
        include: ['vue', 'vue-router', 'vuetify', 'leaflet', 'axios', 'vue-leaflet-markercluster']
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@assets': path.resolve(__dirname, 'storage/app/public/assets'),
            '@dashboard': path.resolve(__dirname, 'resources/js/dashboard'),
            '@ui': path.resolve(__dirname, 'resources/js/dashboard/components/ui'),
            '@dashboard-modules': path.resolve(__dirname, 'resources/js/dashboard/modules')
        }
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                dashboard: 'resources/js/dashboard/main.js'
            },
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.eot') ||
                        assetInfo.name.endsWith('.woff') ||
                        assetInfo.name.endsWith('.ttf')) {
                        return 'fonts/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                }
            }
        }
    },
    server: {
        host: host,
        port: 5173,
        https: {
            key: fs.readFileSync(`./docker/ssl/cert.key`),
            cert: fs.readFileSync(`./docker/ssl/cert.crt`),
        },
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
        hmr: {
            host: 'kayak-map.test',
            proxy: host
        },
        watch: {
            usePolling: false,
        },
        proxy: {
            '/api': {
                target: 'https://nginx',
                changeOrigin: true,
                secure: false,
            },
        },
    }
});
