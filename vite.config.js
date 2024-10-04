import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import browsersync from 'vite-plugin-browser-sync';  // Dodane statyczne importowanie

const host = 'kayak-map.test';

export default defineConfig(({ command, mode }) => {
    const isDevelopment = mode === 'development';

    let httpsConfig = false;

    if (isDevelopment) {
        try {
            httpsConfig = {
                key: fs.readFileSync(`C:\\Users\\User\\.config\\herd\\config\\valet\\Certificates\\${host}.key`),
                cert: fs.readFileSync(`C:\\Users\\User\\.config\\herd\\config\\valet\\Certificates\\${host}.crt`),
            };
        } catch (error) {
            console.warn('Nie można załadować certyfikatów SSL. HTTPS nie będzie dostępny.');
        }
    }

    const plugins = [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            detectTls: host,
            refresh: true,
        }),
    ];

    if (isDevelopment) {
        plugins.push(
            browsersync({
                host: host,
                port: 3000,
                proxy: host,
                https: true,
                open: false,
            })
        );
    }

    return {
        plugins,
        optimizeDeps: {
            include: ['leaflet', 'vue-leaflet-markercluster']
        },
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
        server: isDevelopment ? {
            host: host,
            port: 5173,
            hmr: { host },
            https: httpsConfig,
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
        } : undefined
    };
});
