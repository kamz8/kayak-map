import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath } from 'url'
import path from 'path'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    include: ['tests/vitest/**/*.spec.js'],
    setupFiles: ['./tests/vitest/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: [
        'resources/js/dashboard/store/**/*.js',
        'resources/js/dashboard/composables/**/*.js',
        'resources/js/dashboard/components/**/*.vue'
      ],
      exclude: [
        'tests/**',
        '**/*.spec.js',
        '**/*.test.js'
      ]
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      '@dashboard': path.resolve(__dirname, './resources/js/dashboard'),
      '@ui': path.resolve(__dirname, './resources/js/dashboard/components/ui')
    }
  }
})
