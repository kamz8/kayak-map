import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import browsersync from 'vite-plugin-browsersync';

export default defineConfig({
    plugins: [
        vue(),
        browsersync({
            // Opcje konfiguracji BrowserSync
            host: 'https://kayak-map.test',
            port: 3000,
            proxy: 'http://localhost:5173'
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
        proxy: {
            '/api': {
                target: 'https://kayak-map.test',
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, '')
            }
        }
    }
});
