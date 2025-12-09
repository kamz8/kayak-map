import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import browsersync from "vite-plugin-browser-sync";
import vuetify from 'vite-plugin-vuetify';

const host = 'kayak-map.test';
const isDocker = process.env.DOCKER_ENV === 'true';

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
        include: ['vue', 'vue-router', 'vuetify', 'leaflet', 'leaflet-draw', 'axios', 'vue-leaflet-markercluster']
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
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        https: {
            key: fs.readFileSync('./docker/ssl/cert.key'),
            cert: fs.readFileSync('./docker/ssl/cert.crt'),
        },
        origin: 'https://kayak-map.test:5173',
        hmr: {
            protocol: 'wss',
            host: 'kayak-map.test',
            port: 5173,
        },
        cors: {
            origin: 'https://kayak-map.test',
            credentials: true,
        },
        headers: {
            'Access-Control-Allow-Origin': 'https://kayak-map.test',
            'Access-Control-Allow-Credentials': 'true',
            'Cross-Origin-Resource-Policy': 'cross-origin',
            'Cross-Origin-Embedder-Policy': 'unsafe-none',
            'Cross-Origin-Opener-Policy': 'unsafe-none',
        },
        watch: {
            usePolling: true,
            interval: 300,
        },
        proxy: {
            '^/api': {
                target: isDocker ? 'http://nginx:80' : `http://kayak-map.test:80`,
                changeOrigin: true,
                secure: false,
            },
            '^/storage': {
                target: isDocker ? 'http://nginx:80' : `http://kayak-map.test:80`,
                changeOrigin: true,
                secure: false,
            },
        },
        allowedHosts: [
            'kayak-map.test',
            'localhost',
            '.test',
        ],
    },
});
