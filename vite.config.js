import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/filament.scss',
                'resources/js/filament.js',
                'resources/css/app.scss',
                'resources/js/app.js',
                'resources/js/flowbite-livewire.js',
                // Charts used in Filament widgets
                'resources/js/ticket-chart.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Optimize chunk size
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split vendor code
                    'vendor': ['lodash', 'axios'],
                },
            },
        },
        // Minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console logs in production
                drop_debugger: true,
            },
        },
        // Optimize chunk size warnings
        chunkSizeWarningLimit: 1000,
        // Enable CSS code splitting
        cssCodeSplit: true,
    },
    // Server optimization
    server: {
        hmr: {
            overlay: false,
        },
    },
});
