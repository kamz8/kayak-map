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
        // Docker container binds to all interfaces but uses correct host for URLs
        host: isDocker ? '0.0.0.0' : host,
        port: 5173,

        // Override server origin for Docker to generate correct URLs
        ...(isDocker ? {
            origin: 'https://kayak-map.test:443'
        } : {}),

        https: {
            key: fs.readFileSync(`./docker/ssl/cert.key`),
            cert: fs.readFileSync(`./docker/ssl/cert.crt`),
        },
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
        },
        hmr: {
            // Docker HMR configuration
            ...(isDocker ? {
                port: 24678,
                host: '0.0.0.0', // Bind to all interfaces for container
                clientPort: 443, // Client connects through nginx SSL
                path: '/_vite/ws', // Custom WebSocket path for nginx proxy
            } : {
                host: 'kayak-map.test',
                port: 5173,
            }),
        },
        watch: {
            usePolling: isDocker,
            ...(isDocker ? {
                interval: 1000,
                binaryInterval: 2000,
            } : {}),
        },
        proxy: {
            '/api': {
                target: isDocker ? 'https://nginx' : 'https://kayak-map.test',
                changeOrigin: true,
                secure: false,
            },
        },
    }
});
