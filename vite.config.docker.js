import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';

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
    ],
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
    server: {
        host: '0.0.0.0',  // Nasłuchiwanie na wszystkich interfejsach sieciowych
        port: 5173,       // Port Vite
        hmr: {
            host: 'kayak.test',  // Używamy tej samej domeny co aplikacja
            protocol: 'wss',     // WebSocket Secure, aby pasował do HTTPS
        },
        cors: true,  // Włączamy wsparcie CORS dla zewnętrznych zasobów
        watch: {
            usePolling: true,  // Polling zapewnia poprawne działanie w Dockerze
        },
    },
});
