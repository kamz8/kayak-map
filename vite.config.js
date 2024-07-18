import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import browsersync from 'vite-plugin-browser-sync';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

const host = 'kayak-map.test';
export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            detectTls: host,
            refresh: true,
        }),
        browsersync({
            host: host,
            port: 3000,
            proxy: host,
            https: true,
            open: false
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js')
        }
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            input: 'resources/js/app.js'
        }
    },
    server: {
        host: host,
        port: 5173,
        hmr: { host },
        https: {
            key: fs.readFileSync(`C:\\Users\\User\\.config\\herd\\config\\valet\\Certificates\\${host}.key`),
            cert: fs.readFileSync(`C:\\Users\\User\\.config\\herd\\config\\valet\\Certificates\\${host}.crt`),
        },
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
        proxy: {
            '/api': {
                target: host,
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, '')
            }
        }
    }
});
