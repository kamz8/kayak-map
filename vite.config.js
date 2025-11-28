import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import vuetify from 'vite-plugin-vuetify';

const host = 'kayak-map.test';
const isDocker = process.env.DOCKER_ENV === 'true';

export default defineConfig({
    plugins: [
        vue({
            template: {
                compilerOptions: {
                    whitespace: 'condense'
                }
            }
        }),
        vuetify({
            autoImport: true,
        }),
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
        include: [
            'vue',
            'vue-router',
            'axios',
            'vuex',
        ],
        exclude: [
            'vuetify',
            'vue-leaflet-markercluster'
        ]
    },

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@assets': path.resolve(__dirname, 'storage/app/public/assets'),
            '@dashboard': path.resolve(__dirname, 'resources/js/dashboard'),
            '@ui': path.resolve(__dirname, 'resources/js/dashboard/components/ui'),
            '@dashboard-modules': path.resolve(__dirname, 'resources/js/dashboard/modules'),
            '@composables': path.resolve(__dirname, 'resources/js/dashboard/composables'),
            '@utils': path.resolve(__dirname, 'resources/js/dashboard/utils'),
            '@store': path.resolve(__dirname, 'resources/js/dashboard/store'),
            'vuetify/lib': 'vuetify',
        }
    },

    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@import "resources/sass/variables.scss";`
            },
            sass: {
                charset: false
            }
        }
    },

    build: {
        manifest: true,
        outDir: 'public/build',
        assetsDir: '',
        chunkSizeWarningLimit: 1600,
        commonjsOptions: {
            include: [/leaflet/, /node_modules/] // 🎯 WYMUŚ COMMONJS DLA LEAFLET
        },
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                dashboard: 'resources/js/dashboard/main.js'
            },
            output: {
                manualChunks: (id) => {
                    const moduleMatch = id.match(/\/dashboard\/modules\/([^\/]+)/);
                    if (moduleMatch) {
                        return `modules/${moduleMatch[1]}`;
                    }

                    if (id.includes('node_modules')) {
                        if (id.includes('vuex')) return 'vendors/vuex';
                        if (id.includes('vuetify')) return 'vendors/vuetify';
                        if (id.includes('leaflet')) return 'vendors/leaflet'; // 🎯 LEAFLET CHUNK
                        if (id.includes('/vue/')) return 'vendors/vue';
                        return 'vendors/other';
                    }

                    if (id.includes('/dashboard/')) {
                        if (id.includes('/components/ui/')) return 'dashboard/ui';
                        if (id.includes('/composables/')) return 'dashboard/composables';
                        return 'dashboard/core';
                    }
                },

                chunkFileNames: (chunkInfo) => {
                    const name = chunkInfo.name || 'chunk';

                    if (name.startsWith('modules/')) {
                        const moduleName = name.replace('modules/', '');
                        return `modules/${moduleName}-[hash].js`;
                    }

                    if (name.startsWith('vendors/')) {
                        const vendorName = name.replace('vendors/', '');
                        return `vendors/${vendorName}-[hash].js`;
                    }

                    if (name.startsWith('dashboard/')) {
                        const dashboardPart = name.replace('dashboard/', '');
                        return `dashboard/${dashboardPart}-[hash].js`;
                    }

                    return `chunks/${name}-[hash].js`;
                },

                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.eot') ||
                        assetInfo.name.endsWith('.woff') ||
                        assetInfo.name.endsWith('.woff2') ||
                        assetInfo.name.endsWith('.ttf')) {
                        return '[name][extname]';
                    }
                    return '[name]-[hash][extname]';
                }
            }
        }
    },

    server: {
        host: isDocker ? '0.0.0.0' : host,
        port: 5173,

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
            ...(isDocker ? {
                port: 5173,
                host: 'kayak-map.test',
                clientPort: 443,
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
