import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import browsersync from "vite-plugin-browser-sync";

const host = 'kayak-map.test';
export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        browsersync({
            host: '0.0.0.0',
            port: 3000,
            proxy: 'https://nginx',
            https: {
                key: fs.readFileSync(`./docker/ssl/cert.key`),
                cert: fs.readFileSync(`./docker/ssl/cert.crt`),
            },
            open: false
        })
    ],
    optimizeDeps: {
        include: ['leaflet', 'vue-leaflet-markercluster']
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@assets': path.resolve(__dirname, 'storage/app/public/assets')
        }
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            input: 'resources/js/app.js',
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        https: {
            key: fs.readFileSync(`./docker/ssl/cert.key`),
            cert: fs.readFileSync(`./docker/ssl/cert.crt`),
        },
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
        hmr: {
            host: host,
            protocol: 'wss',
            port: 5173,
            overlay: false,
        },
        watch: {
            usePolling: true,
            interval: 1000,
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
